<?php
/**
 * Google Drive REST API v3 Service
 * KKA Digital - Inspektorat Kabupaten Rokan Hilir
 * 
 * Mengelola integrasi penyimpanan berkas ke Google Drive:
 * - OAuth 2.0 refresh_token exchange & caching
 * - Otomatisasi pembentukan struktur hierarki folder per Irban, TA, dan Desa
 * - Pencadangan & sinkronisasi dokumen LHP, Opname Kas, KKA, SPT, dan Bukti TLHP
 */

declare(strict_types=1);

class GoogleDriveService
{
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;
    private string $parentFolderName;
    private ?string $parentFolderId;
    private ?string $accountEmail;

    private static ?GoogleDriveService $instance = null;

    public function __construct(?array $cfg = null)
    {
        $cfg = $cfg ?? ($GLOBALS['cfg'] ?? []);

        $this->clientId         = trim((string)($cfg['gdrive_client_id'] ?? ''));
        $this->clientSecret     = trim((string)($cfg['gdrive_client_secret'] ?? ''));
        $this->refreshToken     = trim((string)($cfg['gdrive_refresh_token'] ?? ''));
        $this->parentFolderName = trim((string)($cfg['gdrive_parent_folder_name'] ?? 'KKA DIGITAL INSPEKTORAT'));
        $this->parentFolderId   = !empty($cfg['gdrive_parent_folder_id']) ? trim((string)$cfg['gdrive_parent_folder_id']) : null;
        $this->accountEmail     = !empty($cfg['gdrive_account_email']) ? trim((string)$cfg['gdrive_account_email']) : 'teamirban4@gmail.com';
    }

    public function getParentFolderId(): string
    {
        if (empty($this->parentFolderId)) {
            $this->parentFolderId = $this->getOrCreateFolder($this->getParentFolderName(), null);
        }
        return (string)$this->parentFolderId;
    }

    public function getParentFolderLink(): string
    {
        try {
            $id = $this->getParentFolderId();
            return 'https://drive.google.com/drive/folders/' . $id;
        } catch (Throwable $e) {
            return 'https://drive.google.com/drive/my-drive';
        }
    }

    public function getParentFolderName(): string
    {
        return $this->parentFolderName ?: 'KKA DIGITAL INSPEKTORAT';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function isConfigured(): bool
    {
        $service = self::getInstance();
        return !empty($service->clientId) && !empty($service->clientSecret) && !empty($service->refreshToken);
    }

    /**
     * Memperoleh Access Token aktif dari refresh_token (dengan file cache sementara).
     */
    public function getAccessToken(): string
    {
        if (!self::isConfigured()) {
            throw new RuntimeException('Kredensial Google Drive API belum dikonfigurasi di file .env');
        }

        $cacheFile = sys_get_temp_dir() . '/kka_gdrive_token_' . md5($this->clientId . $this->refreshToken) . '.json';

        if (file_exists($cacheFile)) {
            $cached = json_decode((string)file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached['access_token']) && ($cached['expires_at'] ?? 0) > (time() + 60)) {
                return (string)$cached['access_token'];
            }
        }

        $postData = http_build_query([
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            $msg = 'Gagal menukarkan refresh_token Google OAuth (HTTP ' . $httpCode . '): ' . ($error ?: $response);
            error_log('[KKA_GDRIVE] ' . $msg);
            throw new RuntimeException($msg);
        }

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new RuntimeException('Respon Google OAuth tidak menyertakan access_token');
        }

        $accessToken = (string)$data['access_token'];
        $expiresIn   = (int)($data['expires_in'] ?? 3600);

        @file_put_contents($cacheFile, json_encode([
            'access_token' => $accessToken,
            'expires_at'   => time() + $expiresIn,
        ]));

        return $accessToken;
    }

    /**
     * Uji koneksi ke Google Drive & ambil informasi profil dan kuota penyimpanan.
     */
    public function testConnection(): array
    {
        $token = $this->getAccessToken();

        $ch = curl_init('https://www.googleapis.com/drive/v3/about?fields=user,storageQuota');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Google Drive API (HTTP ' . $httpCode . ')',
                'raw'     => $response,
            ];
        }

        $data = json_decode($response, true);
        $user = $data['user'] ?? [];
        $quota = $data['storageQuota'] ?? [];

        $limitBytes = (float)($quota['limit'] ?? 0);
        $usageBytes = (float)($quota['usage'] ?? 0);
        $percent = $limitBytes > 0 ? round(($usageBytes / $limitBytes) * 100, 2) : 0;

        return [
            'success'   => true,
            'connected' => true,
            'user'      => [
                'displayName'  => $user['displayName'] ?? 'Inspektorat Rohil',
                'emailAddress' => $user['emailAddress'] ?? $this->accountEmail ?? 'teamirban4@gmail.com',
                'photoLink'    => $user['photoLink'] ?? null,
            ],
            'quota'     => [
                'limit_bytes'      => $limitBytes,
                'usage_bytes'      => $usageBytes,
                'limit_formatted'  => $this->formatBytes($limitBytes),
                'usage_formatted'  => $this->formatBytes($usageBytes),
                'percentage'       => $percent,
            ],
        ];
    }

    /**
     * Mencari atau membuat folder baru di Google Drive.
     */
    public function getOrCreateFolder(string $name, ?string $parentId = null): string
    {
        $token = $this->getAccessToken();
        $safeName = str_replace("'", "\\'", $name);

        $query = "name = '{$safeName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
        if (!empty($parentId)) {
            $query .= " and '{$parentId}' in parents";
        } else {
            $query .= " and 'root' in parents";
        }

        $url = 'https://www.googleapis.com/drive/v3/files?q=' . rawurlencode($query) . '&fields=files(id,name,webViewLink)&pageSize=1';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if (!empty($data['files'][0]['id'])) {
            return (string)$data['files'][0]['id'];
        }

        // Jika belum ada, buat folder baru
        $body = [
            'name'     => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ];
        if (!empty($parentId)) {
            $body['parents'] = [$parentId];
        }

        $ch = curl_init('https://www.googleapis.com/drive/v3/files?fields=id,name,webViewLink');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $res = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $newFolder = json_decode((string)$res, true);
        if ($code >= 200 && $code < 300 && !empty($newFolder['id'])) {
            return (string)$newFolder['id'];
        }

        throw new RuntimeException("Gagal membuat folder Google Drive '{$name}': " . $res);
    }

    /**
     * Memastikan seluruh jalur folder hierarki berurutan telah dibuat di Google Drive.
     * Contoh: ['KKA DIGITAL INSPEKTORAT', 'IRBAN IV', 'TAHUN ANGGARAN 2025', 'Kepenghuluan Sei Sialang Hulu (Kec. Batu Hampar)', '04_LHP_FINAL']
     * Mengembalikan ID folder paling dalam (leaf folder ID).
     */
    public function ensurePath(array $pathSegments): string
    {
        $currentParentId = null;

        foreach ($pathSegments as $idx => $segment) {
            $segment = trim((string)$segment);
            if ($segment === '') continue;

            // Jika segment pertama adalah folder induk, gunakan getParentFolderId()
            if ($idx === 0) {
                $currentParentId = $this->getParentFolderId();
                continue;
            }

            $currentParentId = $this->getOrCreateFolder($segment, $currentParentId);
        }

        return (string)$currentParentId;
    }

    /**
     * Mengunggah berkas ke Google Drive (atau memperbarui jika nama yang sama sudah ada di folder tersebut).
     */
    public function uploadOrUpdateFile(
        string $fileName,
        string $mimeType,
        string $content,
        string $folderId,
        ?string $description = null
    ): array {
        $token = $this->getAccessToken();

        // 1. Periksa apakah berkas dengan nama yang sama sudah ada di folder tersebut
        $safeName = str_replace("'", "\\'", $fileName);
        $query = "name = '{$safeName}' and '{$folderId}' in parents and trashed = false";
        $urlSearch = 'https://www.googleapis.com/drive/v3/files?q=' . rawurlencode($query) . '&fields=files(id,name,webViewLink)&pageSize=1';

        $ch = curl_init($urlSearch);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
            ],
        ]);
        $searchRes = curl_exec($ch);
        curl_close($ch);

        $searchData = json_decode((string)$searchRes, true);
        $existingId = $searchData['files'][0]['id'] ?? null;

        // 2. Siapkan Multipart Request (RFC 2387)
        $boundary = '-------314159265358979323846';
        $delimiter = "\r\n--" . $boundary . "\r\n";
        $closeDelimiter = "\r\n--" . $boundary . "--";

        $meta = [
            'name'        => $fileName,
            'description' => $description ?? ('Dokumen KKA Digital Inspektorat Kab. Rokan Hilir - Diunggah ' . date('d-m-Y H:i:s')),
        ];

        if (!$existingId) {
            $meta['parents'] = [$folderId];
            $url = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink,webContentLink,size';
            $isUpdate = false;
        } else {
            $url = "https://www.googleapis.com/upload/drive/v3/files/{$existingId}?uploadType=multipart&fields=id,name,webViewLink,webContentLink,size";
            $isUpdate = true;
        }

        $multipartBody = $delimiter
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($meta)
            . $delimiter
            . "Content-Type: " . $mimeType . "\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($content))
            . $closeDelimiter;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CUSTOMREQUEST  => $isUpdate ? 'PATCH' : 'POST',
            CURLOPT_POSTFIELDS     => $multipartBody,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: multipart/related; boundary=' . $boundary,
                'Content-Length: ' . strlen($multipartBody),
                'Accept: application/json',
            ],
        ]);

        $res = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            $fileObj = json_decode((string)$res, true);
            $fileId  = (string)($fileObj['id'] ?? '');
            $webView = (string)($fileObj['webViewLink'] ?? "https://drive.google.com/file/d/{$fileId}/view");

            // Otomatis atur izin berkas agar dapat dilihat melalui tautan resmi (Anyone with link)
            $this->setFilePublic($fileId);

            return [
                'success'      => true,
                'is_update'    => $isUpdate,
                'id'           => $fileId,
                'name'         => $fileName,
                'folder_id'    => $folderId,
                'mime_type'    => $mimeType,
                'size'         => (int)($fileObj['size'] ?? strlen($content)),
                'web_view_link'=> $webView,
                'raw'          => $fileObj,
            ];
        }

        throw new RuntimeException("Gagal mengunggah berkas '{$fileName}' ke Google Drive (HTTP {$code}): " . ($err ?: $res));
    }

    /**
     * Mengonversi dokumen HTML ke berkas PDF native melalui Google Drive API Engine.
     * Alur:
     * 1. Sanitasi HTML (hapus tombol navigasi dan elemen .no-print).
     * 2. Ubah relative URL gambar (/assets/...) menjadi URL absolut HTTPS public.
     * 3. Unggah ke Google Drive sebagai Google Docs (mimeType: application/vnd.google-apps.document).
     * 4. Ekspor dokumen menjadi PDF binary murni (GET /drive/v3/files/{docId}/export?mimeType=application/pdf).
     * 5. Hapus Google Docs sementara tersebut agar ruang Drive tetap bersih.
     * 6. Mengembalikan konten biner PDF.
     */
    public function convertHtmlToPdf(string $html, string $title = 'Dokumen'): string
    {
        $token = $this->getAccessToken();

        // 1. Sanitasi HTML
        $cleanHtml = preg_replace('/<div class="no-print".*?<\/div>\s*<\/div>/s', '', $html);
        $cleanHtml = preg_replace('/<div class="no-print".*?<\/div>/s', '', $cleanHtml);

        // Ubah link asset relatif menjadi link absolut
        $baseUrl = rtrim($GLOBALS['app_base_url'] ?? 'https://kka.arsipdigital-inspektorat.com', '/');
        $cleanHtml = preg_replace('/src="(?:\/)?assets\//i', 'src="' . $baseUrl . '/assets/', $cleanHtml);

        // 2. Unggah sebagai Google Doc sementara
        $boundary = '-------314159265358979323846';
        $delimiter = "\r\n--" . $boundary . "\r\n";
        $closeDelimiter = "\r\n--" . $boundary . "--";

        $meta = [
            'name'     => 'TMP_DOC_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '_' . time(),
            'mimeType' => 'application/vnd.google-apps.document',
        ];

        $multipartBody = $delimiter
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($meta)
            . $delimiter
            . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . $cleanHtml
            . $closeDelimiter;

        $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_POSTFIELDS     => $multipartBody,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: multipart/related; boundary=' . $boundary,
                'Content-Length: ' . strlen($multipartBody),
            ],
        ]);

        $res = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $docObj = json_decode((string)$res, true);
        $docId = $docObj['id'] ?? null;

        if ($code < 200 || $code >= 300 || empty($docId)) {
            throw new RuntimeException("Gagal membuat Google Doc sementara untuk konversi PDF (HTTP {$code}): {$res}");
        }

        try {
            // 3. Ekspor ke PDF
            $chExport = curl_init("https://www.googleapis.com/drive/v3/files/{$docId}/export?mimeType=application/pdf");
            curl_setopt_array($chExport, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                ],
            ]);

            $pdfContent = curl_exec($chExport);
            $exportCode = (int)curl_getinfo($chExport, CURLINFO_HTTP_CODE);
            $exportErr  = curl_error($chExport);
            curl_close($chExport);

            if ($exportCode !== 200 || empty($pdfContent)) {
                throw new RuntimeException("Gagal mengekspor Google Doc ke PDF (HTTP {$exportCode}): {$exportErr}");
            }

            return (string)$pdfContent;

        } finally {
            // 4. Pastikan dokumen sementara selalu terhapus
            $this->deleteFile((string)$docId);
        }
    }

    /**
     * Menghapus berkas dari Google Drive.
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $token = $this->getAccessToken();
            $ch = curl_init("https://www.googleapis.com/drive/v3/files/{$fileId}");
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => 'DELETE',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                ],
            ]);
            $res = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ($code >= 200 && $code < 300);
        } catch (Throwable $e) {
            error_log('[KKA_GDRIVE] Gagal menghapus file ' . $fileId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Menjadikan berkas dapat dilihat oleh siapa saja yang memiliki tautan (Anyone with link can view).
     */
    public function setFilePublic(string $fileId): bool
    {
        try {
            $token = $this->getAccessToken();
            $ch = curl_init("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions");
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_POSTFIELDS     => json_encode([
                    'role' => 'reader',
                    'type' => 'anyone',
                ]),
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ],
            ]);
            $res = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ($code >= 200 && $code < 300);
        } catch (Throwable $e) {
            error_log('[KKA_GDRIVE] Gagal mengatur izin publik file ' . $fileId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolusi nama folder Irban standar Inspektorat Rokan Hilir: IRBAN I - V
     */
    public static function resolveIrbanFolder(?int $irbanId = null, ?string $irbanNama = null, ?string $kecamatanNama = null): string
    {
        if ($irbanId !== null) {
            switch ($irbanId) {
                case 39: return 'IRBAN I';
                case 40: return 'IRBAN II';
                case 41: return 'IRBAN III';
                case 42: return 'IRBAN IV';
                case 43: return 'IRBAN V';
            }
        }

        if (!empty($irbanNama)) {
            $upper = strtoupper($irbanNama);
            if (preg_match('/\b(IRBAN\s*V|PEMBANTU\s*V|5)\b/i', $upper)) return 'IRBAN V';
            if (preg_match('/\b(IRBAN\s*IV|PEMBANTU\s*IV|4)\b/i', $upper)) return 'IRBAN IV';
            if (preg_match('/\b(IRBAN\s*III|PEMBANTU\s*III|3)\b/i', $upper)) return 'IRBAN III';
            if (preg_match('/\b(IRBAN\s*II|PEMBANTU\s*II|2)\b/i', $upper)) return 'IRBAN II';
            if (preg_match('/\b(IRBAN\s*I|PEMBANTU\s*I|1)\b/i', $upper)) return 'IRBAN I';
        }

        if (!empty($kecamatanNama)) {
            $kec = strtolower(trim($kecamatanNama));
            // Wilayah Irban IV
            if (in_array($kec, ['batu hampar', 'rimba melintang', 'bangko pusako', 'kubu', 'kubu babussalam'])) {
                return 'IRBAN IV';
            }
            // Wilayah Irban II
            if (in_array($kec, ['bangko', 'sinaboi', 'pekaitan', 'pasir limau kapas'])) {
                return 'IRBAN II';
            }
            // Wilayah Irban I
            if (in_array($kec, ['bagan sinembah', 'bagan sinembah raya', 'balai jaya', 'simpang kanan'])) {
                return 'IRBAN I';
            }
            // Wilayah Irban III
            if (in_array($kec, ['tanah putih', 'tanah putih tanjung melawan', 'pujud', 'rantau kopar', 'tanjung medan'])) {
                return 'IRBAN III';
            }
        }

        return 'IRBAN IV'; // Default aman untuk sampling aktif Sei Sialang Hulu
    }

    /**
     * Membangun segmen jalur folder standar untuk desa tertentu.
     * Contoh:
     * ['KKA DIGITAL INSPEKTORAT', 'IRBAN IV', 'TAHUN ANGGARAN 2025', 'Kepenghuluan Sei Sialang Hulu (Kec. Batu Hampar)', '04_LHP_FINAL']
     */
    public static function buildDesaHierarchy(
        int $desaId,
        int $tahunAnggaran,
        string $subFolder = '04_LHP_FINAL',
        ?int $irbanId = null,
        ?string $irbanNama = null
    ): array {
        $desa = DB::one('SELECT d.id, d.nama as nama_desa, k.nama as nama_kec FROM kka_desa d LEFT JOIN kka_kecamatan k ON d.kecamatan_id = k.id WHERE d.id = ?', [$desaId]);
        
        $namaDesa = $desa['nama_desa'] ?? ('Desa_' . $desaId);
        $namaKec  = $desa['nama_kec'] ?? '';

        $irbanFolder = self::resolveIrbanFolder($irbanId, $irbanNama, $namaKec);

        $desaFolderName = 'Kepenghuluan ' . $namaDesa;
        if (!empty($namaKec)) {
            $desaFolderName .= ' (Kec. ' . $namaKec . ')';
        }

        $topFolder = $GLOBALS['cfg']['gdrive_parent_folder_name'] ?? 'KKA DIGITAL INSPEKTORAT';

        return [
            $topFolder,
            $irbanFolder,
            'TAHUN ANGGARAN ' . $tahunAnggaran,
            $desaFolderName,
            $subFolder,
        ];
    }

    private function formatBytes(float $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
