<?php
/**
 * Controller Manajemen Integrasi Google Drive
 * Inspektorat Kabupaten Rokan Hilir - KKA Digital
 */

declare(strict_types=1);

class GoogleDriveController
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $auth->require();
        
        // Batasi akses untuk Operator SPT (Perencanaan) dan Operator TL (Tindak Lanjut)
        if ($this->auth->isOperatorSpt() || $this->auth->isOperatorTl()) {
            flash('warning', 'Akses dibatasi: Peran Anda tidak memiliki akses ke Manajemen Arsip Google Drive.');
            redirect('dashboard');
            exit;
        }
    }

    /**
     * Halaman Utama Dasbor Integrasi Google Drive
     */
    public function index(): void
    {
        $service = GoogleDriveService::getInstance();
        $isConfigured = GoogleDriveService::isConfigured();

        $connectionInfo = null;
        $errorMessage = null;

        if ($isConfigured) {
            try {
                $connectionInfo = $service->testConnection();
            } catch (Throwable $e) {
                $errorMessage = $e->getMessage();
            }
        }

        // Dokumen yang telah disinkronkan (50 terbaru)
        $syncedFiles = DB::all("
            SELECT g.*, d.nama AS nama_desa, k.nama AS nama_kecamatan, u.nama AS uploader_nama
            FROM kka_gdrive_sync g
            LEFT JOIN kka_desa d ON d.id = g.desa_id
            LEFT JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_users u ON u.id = g.created_by
            ORDER BY g.synced_at DESC
            LIMIT 50
        ");

        // Statistik per Irban
        $statsIrban = DB::all("
            SELECT irban_nama, COUNT(*) AS total_files, COALESCE(SUM(file_size), 0) AS total_bytes
            FROM kka_gdrive_sync
            GROUP BY irban_nama
            ORDER BY irban_nama ASC
        ");

        // Statistik per Tipe Dokumen
        $statsTipe = DB::all("
            SELECT tipe_dokumen, COUNT(*) AS total_files
            FROM kka_gdrive_sync
            GROUP BY tipe_dokumen
            ORDER BY total_files DESC
        ");

        // Total Berkas Tersinkron
        $totalSyncedCount = (int)DB::val("SELECT COUNT(*) FROM kka_gdrive_sync WHERE status = 'SYNCED'");
        $totalBytesSynced = (float)DB::val("SELECT COALESCE(SUM(file_size), 0) FROM kka_gdrive_sync WHERE status = 'SYNCED'");

        // Daftar LHP yang siap disinkronkan
        $tahun = (int)input('tahun', (int)date('Y'));
        $daftarLhp = DB::all("
            SELECT d.id AS desa_id, d.nama AS nama_desa, k.nama AS nama_kec,
                   (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS total_temuan,
                   (SELECT COALESCE(SUM(t.nominal), 0) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS nominal_temuan,
                   (SELECT id FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'LHP_FINAL' AND g.desa_id = d.id AND g.tahun_anggaran = ? ORDER BY id DESC LIMIT 1) AS gdrive_sync_id,
                   (SELECT web_view_link FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'LHP_FINAL' AND g.desa_id = d.id AND g.tahun_anggaran = ? ORDER BY id DESC LIMIT 1) AS gdrive_link,
                   (SELECT synced_at FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'LHP_FINAL' AND g.desa_id = d.id AND g.tahun_anggaran = ? ORDER BY id DESC LIMIT 1) AS gdrive_synced_at
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            WHERE EXISTS (SELECT 1 FROM kka_sesi s WHERE s.desa_id = d.id AND s.tahun_anggaran = ?)
               OR EXISTS (SELECT 1 FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?)
            ORDER BY k.nama ASC, d.nama ASC
        ", [$tahun, $tahun, $tahun, $tahun, $tahun, $tahun, $tahun]);

        // Daftar Opname Kas
        $daftarKas = DB::all("
            SELECT o.id, o.no_bap, o.tgl_pemeriksaan, o.tahun_anggaran, o.total_kas_riil, o.selisih_kas,
                   d.nama AS nama_desa, k.nama AS nama_kec,
                   (SELECT web_view_link FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'OPNAME_KAS' AND g.ref_id = o.id LIMIT 1) AS gdrive_link,
                   (SELECT synced_at FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'OPNAME_KAS' AND g.ref_id = o.id LIMIT 1) AS gdrive_synced_at
            FROM kka_opname_kas o
            JOIN kka_desa d ON d.id = o.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY o.tgl_pemeriksaan DESC
            LIMIT 20
        ");

        $parentFolderLink = $service->getParentFolderLink();
        $parentFolderName = $service->getParentFolderName();

        view('gdrive/index', compact(
            'isConfigured',
            'connectionInfo',
            'errorMessage',
            'parentFolderLink',
            'parentFolderName',
            'syncedFiles',
            'statsIrban',
            'statsTipe',
            'totalSyncedCount',
            'totalBytesSynced',
            'daftarLhp',
            'daftarKas',
            'tahun'
        ));
    }

    /**
     * AJAX Test Koneksi Realtime
     */
    public function test(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $service = GoogleDriveService::getInstance();
            $res = $service->testConnection();
            echo json_encode($res);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        exit;
    }

    /**
     * Sinkronisasi Berkas LHP Final Desa
     */
    public function syncLhp(): void
    {
        $desaId = (int)input('desa_id');
        $tahun  = (int)input('tahun', (int)date('Y'));
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

        if ($desaId <= 0) {
            $this->responseError('Parameter Desa tidak valid.', $isAjax);
            return;
        }

        try {
            $lhpCtrl = new LhpController($this->auth);
            $data = $lhpCtrl->getLhpData($desaId, $tahun);
            if (!$data) {
                $this->responseError('Data LHP untuk desa ini tidak ditemukan.', $isAjax);
                return;
            }

            // Render HTML Naskah LHP Lengkap (Cover, Bab I s.d IV, Matriks & TTD)
            $htmlContent = view_render('print/lhp', $data);

            // Konversi HTML ke PDF Resmi via Google Drive API Engine
            $gdrive = GoogleDriveService::getInstance();
            $namaDesaClean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['desa']['nama']);
            $pdfContent = $gdrive->convertHtmlToPdf($htmlContent, "LHP_{$namaDesaClean}_TA{$tahun}");

            // Tentukan info Irban
            $spt = $data['spt'] ?? [];
            $irbanId   = !empty($spt['wakil_pj_id']) ? (int)$spt['wakil_pj_id'] : null;
            $irbanNama = $spt['wakil_pj_nama'] ?? null;

            // Buat struktur folder resmi di Google Drive
            $hierarchy = GoogleDriveService::buildDesaHierarchy($desaId, $tahun, '04_LHP_FINAL', $irbanId, $irbanNama);
            $irbanFolder = $hierarchy[1];

            $targetFolderId = $gdrive->ensurePath($hierarchy);

            // Nama Berkas Resmi (.pdf)
            $fileName = "LHP_FINAL_Kepenghuluan_{$namaDesaClean}_TA{$tahun}.pdf";
            $description = "Naskah Laporan Hasil Pengawasan (LHP) Final Kepenghuluan {$data['desa']['nama']} TA {$tahun} (Format PDF) - Inspektorat Kab. Rokan Hilir";

            // Upload PDF ke Google Drive
            $uploadRes = $gdrive->uploadOrUpdateFile($fileName, 'application/pdf', $pdfContent, $targetFolderId, $description);

            // Simpan log sinkronisasi di database
            $this->recordSync(
                'LHP_FINAL',
                $desaId,
                $desaId,
                $tahun,
                $irbanFolder,
                $fileName,
                'application/pdf',
                $uploadRes['id'],
                $targetFolderId,
                $uploadRes['web_view_link'],
                (int)$uploadRes['size']
            );

            $msg = "LHP Kepenghuluan {$data['desa']['nama']} TA {$tahun} berhasil disimpan di Google Drive!";

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'       => true,
                    'message'       => $msg,
                    'drive_file_id' => $uploadRes['id'],
                    'web_view_link' => $uploadRes['web_view_link'],
                    'synced_at'     => date('d/m/Y H:i'),
                ]);
                exit;
            }

            flash('success', $msg);
            redirect('lhp/show?desa_id=' . $desaId . '&tahun=' . $tahun);

        } catch (Throwable $e) {
            $this->responseError('Gagal sinkronisasi LHP ke Google Drive: ' . $e->getMessage(), $isAjax);
        }
    }

    /**
     * Sinkronisasi Berita Acara Pemeriksaan Kas (Opname Kas)
     */
    public function syncOpnameKas(): void
    {
        $id = (int)input('id');
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

        if ($id <= 0) {
            $this->responseError('Parameter ID Opname Kas tidak valid.', $isAjax);
            return;
        }

        try {
            $row = DB::one("
                SELECT o.*, d.nama AS desa_nama, k.nama AS kecamatan_nama, spt.no_spt, spt.tgl_spt,
                       spt.wakil_pj_id, spt.wakil_pj_nama
                FROM kka_opname_kas o
                JOIN kka_desa d ON d.id = o.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                LEFT JOIN kka_spt spt ON spt.id = o.spt_id
                WHERE o.id = ?
            ", [$id]);

            if (!$row) {
                $this->responseError('Data Berita Acara Pemeriksaan Kas tidak ditemukan.', $isAjax);
                return;
            }

            $rincianKertas = json_decode($row['rincian_uang_kertas'] ?? '[]', true) ?: [];
            $rincianLogam  = json_decode($row['rincian_uang_logam'] ?? '[]', true) ?: [];
            $terbilangKasFisik = terbilang_rupiah((float)$row['total_kas_fisik']);
            $terbilangKasRiil  = terbilang_rupiah((float)$row['total_kas_riil']);
            $terbilangBku      = terbilang_rupiah((float)$row['saldo_bku']);
            $terbilangSelisih  = terbilang_rupiah(abs((float)$row['selisih_kas']));

            $htmlContent = view_render('print/opname_kas', compact(
                'row', 'rincianKertas', 'rincianLogam',
                'terbilangKasFisik', 'terbilangKasRiil', 'terbilangBku', 'terbilangSelisih'
            ));

            $desaId = (int)$row['desa_id'];
            $tahun  = (int)$row['tahun_anggaran'];
            $irbanId   = !empty($row['wakil_pj_id']) ? (int)$row['wakil_pj_id'] : null;
            $irbanNama = $row['wakil_pj_nama'] ?? null;

            $namaDesaClean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['desa_nama']);

            $gdrive = GoogleDriveService::getInstance();
            $pdfContent = $gdrive->convertHtmlToPdf($htmlContent, "BAP_Opname_Kas_{$namaDesaClean}_TA{$tahun}");

            $hierarchy = GoogleDriveService::buildDesaHierarchy($desaId, $tahun, '03_OPNAME_KAS', $irbanId, $irbanNama);
            $irbanFolder = $hierarchy[1];

            $targetFolderId = $gdrive->ensurePath($hierarchy);

            $fileName = "BAP_Opname_Kas_{$namaDesaClean}_TA{$tahun}.pdf";
            $description = "Berita Acara Pemeriksaan Kas (Opname Kas) Kepenghuluan {$row['desa_nama']} TA {$tahun} (Format PDF)";

            $uploadRes = $gdrive->uploadOrUpdateFile($fileName, 'application/pdf', $pdfContent, $targetFolderId, $description);

            $this->recordSync(
                'OPNAME_KAS',
                $id,
                $desaId,
                $tahun,
                $irbanFolder,
                $fileName,
                'application/pdf',
                $uploadRes['id'],
                $targetFolderId,
                $uploadRes['web_view_link'],
                (int)$uploadRes['size']
            );

            $msg = "Berita Acara Opname Kas {$row['desa_nama']} berhasil disimpan di Google Drive!";

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'       => true,
                    'message'       => $msg,
                    'drive_file_id' => $uploadRes['id'],
                    'web_view_link' => $uploadRes['web_view_link'],
                    'synced_at'     => date('d/m/Y H:i'),
                ]);
                exit;
            }

            flash('success', $msg);
            redirect('opname-kas');

        } catch (Throwable $e) {
            $this->responseError('Gagal sinkronisasi Opname Kas ke Google Drive: ' . $e->getMessage(), $isAjax);
        }
    }

    /**
     * Sinkronisasi Kertas Kerja Audit (KKA Sesi)
     */
    public function syncKka(): void
    {
        $id = (int)input('id');
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_GET['ajax']);

        if ($id <= 0) {
            $this->responseError('Parameter ID Sesi tidak valid.', $isAjax);
            return;
        }

        try {
            $sesi = DB::one("
                SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama, b.nama AS bidang_nama
                FROM kka_sesi s
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                WHERE s.id = ?
            ", [$id]);

            if (!$sesi) {
                $this->responseError('Data Sesi KKA tidak ditemukan.', $isAjax);
                return;
            }

            $rincian = DB::all("SELECT * FROM kka_rincian WHERE sesi_id = ? ORDER BY nomor_urut ASC", [$id]);
            $htmlContent = view_render('print/sesi', compact('sesi', 'rincian'));

            $desaId = (int)$sesi['desa_id'];
            $tahun  = (int)$sesi['tahun_anggaran'];
            $irbanId   = !empty($sesi['irban_id']) ? (int)$sesi['irban_id'] : null;
            $irbanNama = $sesi['irban_nama'] ?? null;

            $safeKkaNo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sesi['no_kka'] ?: ('Sesi_' . $id));
            $safeDesa  = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sesi['desa_nama']);

            $gdrive = GoogleDriveService::getInstance();
            $pdfContent = $gdrive->convertHtmlToPdf($htmlContent, "KKA_{$safeKkaNo}_{$safeDesa}_TA{$tahun}");

            $hierarchy = GoogleDriveService::buildDesaHierarchy($desaId, $tahun, '02_KERTAS_KERJA_KKA', $irbanId, $irbanNama);
            $irbanFolder = $hierarchy[1];

            $targetFolderId = $gdrive->ensurePath($hierarchy);

            $fileName = "KKA_{$safeKkaNo}_{$safeDesa}_TA{$tahun}.pdf";
            $description = "Kertas Kerja Pemeriksaan Belanja (KKA) Kepenghuluan {$sesi['desa_nama']} TA {$tahun} (Format PDF)";

            $uploadRes = $gdrive->uploadOrUpdateFile($fileName, 'application/pdf', $pdfContent, $targetFolderId, $description);

            $this->recordSync(
                'KKA_RINCIAN',
                $id,
                $desaId,
                $tahun,
                $irbanFolder,
                $fileName,
                'application/pdf',
                $uploadRes['id'],
                $targetFolderId,
                $uploadRes['web_view_link'],
                (int)$uploadRes['size']
            );

            $msg = "Kertas Kerja (KKA) {$sesi['objek_audit']} berhasil disimpan di Google Drive!";

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'       => true,
                    'message'       => $msg,
                    'drive_file_id' => $uploadRes['id'],
                    'web_view_link' => $uploadRes['web_view_link'],
                    'synced_at'     => date('d/m/Y H:i'),
                ]);
                exit;
            }

            flash('success', $msg);
            redirect('sesi/show?id=' . $id);

        } catch (Throwable $e) {
            $this->responseError('Gagal sinkronisasi KKA ke Google Drive: ' . $e->getMessage(), $isAjax);
        }
    }

    /**
     * Sinkronisasi Massal (1-Click Sync All)
     */
    public function syncAll(): void
    {
        $tahun = (int)input('tahun', (int)date('Y'));

        try {
            $desaList = DB::all("
                SELECT DISTINCT d.id, d.nama
                FROM kka_desa d
                WHERE EXISTS (SELECT 1 FROM kka_sesi s WHERE s.desa_id = d.id AND s.tahun_anggaran = ?)
                   OR EXISTS (SELECT 1 FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?)
            ", [$tahun, $tahun]);

            $syncedCount = 0;
            $lhpCtrl = new LhpController($this->auth);
            $gdrive  = GoogleDriveService::getInstance();

            foreach ($desaList as $d) {
                $desaId = (int)$d['id'];
                $data = $lhpCtrl->getLhpData($desaId, $tahun);
                if (!$data) continue;

                $htmlContent = view_render('print/lhp', $data);
                $spt = $data['spt'] ?? [];
                $irbanId   = !empty($spt['wakil_pj_id']) ? (int)$spt['wakil_pj_id'] : null;
                $irbanNama = $spt['wakil_pj_nama'] ?? null;

                $hierarchy = GoogleDriveService::buildDesaHierarchy($desaId, $tahun, '04_LHP_FINAL', $irbanId, $irbanNama);
                $irbanFolder = $hierarchy[1];
                $targetFolderId = $gdrive->ensurePath($hierarchy);

                $namaDesaClean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $d['nama']);
                $pdfContent = $gdrive->convertHtmlToPdf($htmlContent, "LHP_{$namaDesaClean}_TA{$tahun}");

                $fileName = "LHP_FINAL_Kepenghuluan_{$namaDesaClean}_TA{$tahun}.pdf";
                $description = "Naskah LHP Final Kepenghuluan {$d['nama']} TA {$tahun} (Format PDF)";

                $uploadRes = $gdrive->uploadOrUpdateFile($fileName, 'application/pdf', $pdfContent, $targetFolderId, $description);

                $this->recordSync(
                    'LHP_FINAL',
                    $desaId,
                    $desaId,
                    $tahun,
                    $irbanFolder,
                    $fileName,
                    'application/pdf',
                    $uploadRes['id'],
                    $targetFolderId,
                    $uploadRes['web_view_link'],
                    (int)$uploadRes['size']
                );

                $syncedCount++;
            }

            // Juga sinkronkan Opname Kas yang ada
            $kasList = DB::all("SELECT id FROM kka_opname_kas WHERE tahun_anggaran = ?", [$tahun]);
            foreach ($kasList as $k) {
                // panggil sinkronisasi opname kas
                $_GET['id'] = $k['id'];
                $_GET['ajax'] = '1';
                $this->syncOpnameKas();
                $syncedCount++;
            }

            flash('success', "Sinkronisasi massal selesai! Sebanyak {$syncedCount} dokumen berhasil dicadangkan ke Google Drive.");
            redirect('gdrive?tahun=' . $tahun);

        } catch (Throwable $e) {
            flash('error', 'Terjadi kesalahan saat sinkronisasi massal: ' . $e->getMessage());
            redirect('gdrive?tahun=' . $tahun);
        }
    }

    /**
     * Helper mencatat riwayat ke tabel basis data
     */
    private function recordSync(
        string $tipeDokumen,
        int $refId,
        int $desaId,
        int $tahun,
        string $irbanNama,
        string $fileName,
        string $mimeType,
        string $driveFileId,
        string $driveFolderId,
        string $webViewLink,
        int $fileSize
    ): void {
        $userId = (int)($this->auth->user()['id'] ?? 0) ?: null;

        // Cek apakah data sync sudah ada sebelumnya untuk ref_id & tipe_dokumen ini
        $existing = DB::one("SELECT id FROM kka_gdrive_sync WHERE tipe_dokumen = ? AND ref_id = ?", [$tipeDokumen, $refId]);

        if ($existing) {
            DB::q("
                UPDATE kka_gdrive_sync SET
                    file_name = ?, mime_type = ?, drive_file_id = ?,
                    drive_folder_id = ?, web_view_link = ?, file_size = ?,
                    status = 'SYNCED', synced_at = NOW(), updated_at = NOW(),
                    created_by = COALESCE(?, created_by)
                WHERE id = ?
            ", [$fileName, $mimeType, $driveFileId, $driveFolderId, $webViewLink, $fileSize, $userId, $existing['id']]);
        } else {
            DB::q("
                INSERT INTO kka_gdrive_sync (
                    tipe_dokumen, ref_id, desa_id, tahun_anggaran, irban_nama,
                    file_name, mime_type, drive_file_id, drive_folder_id,
                    web_view_link, file_size, status, created_by, synced_at, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'SYNCED', ?, NOW(), NOW())
            ", [$tipeDokumen, $refId, $desaId, $tahun, $irbanNama, $fileName, $mimeType, $driveFileId, $driveFolderId, $webViewLink, $fileSize, $userId]);
        }
    }

    private function responseError(string $msg, bool $isAjax): void
    {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }

        flash('error', $msg);
        redirect('gdrive');
    }

    /**
     * Memulai alur otorisasi OAuth 2.0 dengan Google
     */
    public function auth(): void
    {
        $cfg = $GLOBALS['cfg'] ?? [];
        $clientId = $cfg['gdrive_client_id'] ?? '';
        $appUrl = rtrim($cfg['app_url'] ?? 'https://kka.arsipdigital-inspektorat.com', '/');
        $redirectUri = $appUrl . '/gdrive/callback';

        if (empty($clientId)) {
            flash('error', 'Client ID Google OAuth belum dikonfigurasi.');
            redirect('gdrive');
            return;
        }

        $params = [
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/drive',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Callback OAuth 2.0 setelah pengguna mengizinkan akses
     */
    public function callback(): void
    {
        $code  = trim((string)input('code', ''));
        $error = trim((string)input('error', ''));

        if (!empty($error)) {
            flash('error', 'Otorisasi Google Drive dibatalkan atau gagal: ' . htmlspecialchars($error));
            redirect('gdrive');
            return;
        }

        if (empty($code)) {
            flash('error', 'Kode otorisasi Google tidak ditemukan.');
            redirect('gdrive');
            return;
        }

        $cfg = $GLOBALS['cfg'] ?? [];
        $clientId     = $cfg['gdrive_client_id'] ?? '';
        $clientSecret = $cfg['gdrive_client_secret'] ?? '';
        $appUrl       = rtrim($cfg['app_url'] ?? 'https://kka.arsipdigital-inspektorat.com', '/');
        $redirectUri  = $appUrl . '/gdrive/callback';

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'grant_type'    => 'authorization_code',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $tokenData = json_decode((string)$response, true);

        if ($httpCode >= 200 && !empty($tokenData['refresh_token'])) {
            $newRefreshToken = (string)$tokenData['refresh_token'];

            $this->updateEnvFile([
                'GOOGLE_OAUTH_REFRESH_TOKEN' => $newRefreshToken,
                'GOOGLE_DRIVE_FOLDER_ID'     => '',
                'GOOGLE_DRIVE_ACCOUNT_EMAIL' => 'teamirban4@gmail.com',
            ]);

            flash('success', 'Akun Google Drive berhasil dihubungkan! Semua dokumen KKA sekarang tersimpan di Drive teamirban4@gmail.com.');
        } else {
            $msg = $tokenData['error_description'] ?? ($tokenData['error'] ?? 'Gagal menukar kode otorisasi.');
            flash('error', 'Gagal memperoleh Refresh Token: ' . $msg);
        }

        redirect('gdrive');
    }

    /**
     * Simpan Refresh Token secara manual (misal didapat dari OAuth Playground)
     */
    public function saveToken(): void
    {
        csrf_check();
        $token = trim((string)input('refresh_token', ''));
        if (empty($token)) {
            flash('error', 'Token tidak boleh kosong.');
            redirect('gdrive');
            return;
        }

        // Jika pengguna memasukkan Authorization Code (berawalan 4/0...), coba tukar secara otomatis
        if (str_starts_with($token, '4/0')) {
            $cfg = $GLOBALS['cfg'] ?? [];
            $clientId     = $cfg['gdrive_client_id'] ?? '';
            $clientSecret = $cfg['gdrive_client_secret'] ?? '';
            $appUrl       = rtrim($cfg['app_url'] ?? 'https://kka.arsipdigital-inspektorat.com', '/');

            $redirectUris = [
                'https://developers.google.com/oauthplayground',
                $appUrl . '/gdrive/callback',
            ];

            $exchanged = false;
            foreach ($redirectUris as $redir) {
                $ch = curl_init('https://oauth2.googleapis.com/token');
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => http_build_query([
                        'code'          => $token,
                        'client_id'     => $clientId,
                        'client_secret' => $clientSecret,
                        'redirect_uri'  => $redir,
                        'grant_type'    => 'authorization_code',
                    ]),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
                ]);
                $res = curl_exec($ch);
                $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $data = json_decode((string)$res, true);
                if ($code >= 200 && !empty($data['refresh_token'])) {
                    $token = (string)$data['refresh_token'];
                    $exchanged = true;
                    break;
                }
            }

            if (!$exchanged) {
                flash('error', 'Kode yang Anda masukkan adalah Authorization Code sementara (berawalan 4/0...) dan sudah kedaluwarsa atau belum ditukar. Silakan gunakan tombol biru "Hubungkan Akun" di atas, atau ambil "Refresh token" (berawalan 1//0...) dari Step 2 Playground.');
                redirect('gdrive');
                return;
            }
        }

        $this->updateEnvFile([
            'GOOGLE_OAUTH_REFRESH_TOKEN' => $token,
            'GOOGLE_DRIVE_FOLDER_ID'     => '',
            'GOOGLE_DRIVE_ACCOUNT_EMAIL' => 'teamirban4@gmail.com',
        ]);

        flash('success', 'Refresh Token Google Drive berhasil disimpan! Koneksi telah diperbarui.');
        redirect('gdrive');
    }

    /**
     * Memperbarui file .env
     */
    private function updateEnvFile(array $keyValues): bool
    {
        $envPaths = [
            dirname(__DIR__, 2) . '/.env',
            '/www/wwwroot/kka/.env',
        ];

        $success = false;
        foreach ($envPaths as $envPath) {
            if (!file_exists($envPath)) continue;
            $content = (string)file_get_contents($envPath);
            foreach ($keyValues as $k => $v) {
                if (preg_match("/^{$k}=.*$/m", $content)) {
                    $content = preg_replace("/^{$k}=.*$/m", "{$k}={$v}", $content);
                } else {
                    $content .= "\n{$k}={$v}";
                }
            }
            if (@file_put_contents($envPath, $content) !== false) {
                $success = true;
            }
        }

        // Hapus cache token
        foreach (glob(sys_get_temp_dir() . '/kka_gdrive_token_*.json') as $oldCache) {
            @unlink($oldCache);
        }

        return $success;
    }
}
