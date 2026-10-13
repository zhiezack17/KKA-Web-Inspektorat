<?php
class RincianController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    /** Pastikan sesi induk milik user (atau admin) dan tidak terkunci. */
    private function assertSesiCanEdit(int $sesiId): array {
        $sesi = DB::one('SELECT id, created_by, status FROM kka_sesi WHERE id = ?', [$sesiId]);
        if (!sesi_is_owned($this->auth, $sesi)) {
            flash('error', 'Anda tidak memiliki akses ke sesi audit ini.');
            redirect('sesi');
        }
        $status = $sesi['status'] ?? 'DRAFT';
        if (in_array($status, ['REVIEW_KETUA', 'REVIEW_DALNIS', 'SELESAI_FINAL'])) {
            $info = kka_status_info($status);
            flash('error', 'KKA sedang dalam tahap ' . $info['label'] . ' (data terkunci). Perubahan rincian belanja tidak diizinkan.');
            redirect('sesi/show?id=' . $sesiId);
        }
        return $sesi;
    }

    public function store(): void {
        only_post(); csrf_check();
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);
        $uraian = trim((string) input('uraian'));
        if (!$sesiId || $uraian === '') {
            flash('error', 'Uraian belanja wajib diisi.');
            redirect('sesi/show?id=' . $sesiId);
        }
        $next = (int) DB::scalar('SELECT COALESCE(MAX(urutan),0)+1 FROM kka_rincian WHERE sesi_id = ?', [$sesiId]);
        $potongPpn = !empty(input('potong_ppn')) ? 1 : 0;
        $nomPpn    = parse_money(input('nominal_ppn', 0));
        $potongPph = trim((string) input('potong_pph')) ?: null;
        $nomPph    = parse_money(input('nominal_pph', 0));
        $statusPajak = trim((string) input('status_pajak', 'TIDAK_TERUTANG'));
        if (!in_array($statusPajak, ['TIDAK_TERUTANG', 'SUDAH_SETOR', 'BELUM_SETOR'])) {
            $statusPajak = 'TIDAK_TERUTANG';
        }
        $ntpn = trim((string) input('ntpn')) ?: null;
        $jenisPenerima = strtoupper(trim((string) input('jenis_penerima', 'PENERIMA')));
        if (!in_array($jenisPenerima, ['TPK', 'PENERIMA'])) {
            $jenisPenerima = 'PENERIMA';
        }

        DB::insert('kka_rincian', [
            'sesi_id'         => $sesiId,
            'urutan'          => $next,
            'uraian'          => $uraian,
            'pagu_anggaran'   => parse_money(input('pagu_anggaran', 0)),
            'biaya_dikwitansi'=> parse_money(input('biaya_dikwitansi', 0)),
            'realisasi'       => parse_money(input('realisasi', 0)),
            'jenis_penerima'  => $jenisPenerima,
            'penerima'        => trim((string) input('penerima')) ?: null,
            'keterangan'      => trim((string) input('keterangan')) ?: null,
            'potong_ppn'      => $potongPpn,
            'nominal_ppn'     => $nomPpn,
            'potong_pph'      => $potongPph,
            'nominal_pph'     => $nomPph,
            'status_pajak'    => $statusPajak,
            'ntpn'            => $ntpn,
        ]);
        flash('success', 'Rincian belanja ditambahkan.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function update(): void {
        only_post(); csrf_check();
        $id     = (int) input('id');
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);
        $uraian = trim((string) input('uraian'));
        // Pastikan rincian milik sesi yang sesuai (cegah update arbitrary)
        $row = DB::one('SELECT id FROM kka_rincian WHERE id = ? AND sesi_id = ?', [$id, $sesiId]);
        if (!$row) { flash('error', 'Rincian tidak ditemukan.'); redirect('sesi/show?id=' . $sesiId); }
        if ($uraian === '') { flash('error', 'Uraian belanja wajib diisi.'); redirect('sesi/show?id=' . $sesiId); }

        $potongPpn = !empty(input('potong_ppn')) ? 1 : 0;
        $nomPpn    = parse_money(input('nominal_ppn', 0));
        $potongPph = trim((string) input('potong_pph')) ?: null;
        $nomPph    = parse_money(input('nominal_pph', 0));
        $statusPajak = trim((string) input('status_pajak', 'TIDAK_TERUTANG'));
        if (!in_array($statusPajak, ['TIDAK_TERUTANG', 'SUDAH_SETOR', 'BELUM_SETOR'])) {
            $statusPajak = 'TIDAK_TERUTANG';
        }
        $ntpn = trim((string) input('ntpn')) ?: null;
        $jenisPenerima = strtoupper(trim((string) input('jenis_penerima', 'PENERIMA')));
        if (!in_array($jenisPenerima, ['TPK', 'PENERIMA'])) {
            $jenisPenerima = 'PENERIMA';
        }

        // Jika parent memiliki anak, jangan timpa biaya_dikwitansi dan realisasi dari input form parent
        $childCount = (int) DB::scalar('SELECT COUNT(*) FROM kka_rincian WHERE parent_id = ?', [$id]);
        if ($childCount > 0) {
            DB::update('kka_rincian', [
                'uraian'          => $uraian,
                'pagu_anggaran'   => parse_money(input('pagu_anggaran', 0)),
                'jenis_penerima'  => $jenisPenerima,
                'penerima'        => trim((string) input('penerima')) ?: null,
                'keterangan'      => trim((string) input('keterangan')) ?: null,
                'potong_ppn'      => $potongPpn,
                'nominal_ppn'     => $nomPpn,
                'potong_pph'      => $potongPph,
                'nominal_pph'     => $nomPph,
                'status_pajak'    => $statusPajak,
                'ntpn'            => $ntpn,
            ], ['id' => $id]);
            $this->syncParentTotals($id);
        } else {
            DB::update('kka_rincian', [
                'uraian'          => $uraian,
                'pagu_anggaran'   => parse_money(input('pagu_anggaran', 0)),
                'biaya_dikwitansi'=> parse_money(input('biaya_dikwitansi', 0)),
                'realisasi'       => parse_money(input('realisasi', 0)),
                'jenis_penerima'  => $jenisPenerima,
                'penerima'        => trim((string) input('penerima')) ?: null,
                'keterangan'      => trim((string) input('keterangan')) ?: null,
                'potong_ppn'      => $potongPpn,
                'nominal_ppn'     => $nomPpn,
                'potong_pph'      => $potongPph,
                'nominal_pph'     => $nomPph,
                'status_pajak'    => $statusPajak,
                'ntpn'            => $ntpn,
            ], ['id' => $id]);
        }
        flash('success', 'Rincian diperbarui.');
        redirect('sesi/show?id=' . $sesiId);
    }

    /** Simpan Sub-Uraian Kwitansi / Bon SPJ di bawah Rekening Induk */
    public function storeSub(): void {
        only_post(); csrf_check();
        $parentId = (int) input('parent_id');
        $sesiId   = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);

        $parent = DB::one('SELECT id, sesi_id FROM kka_rincian WHERE id = ? AND sesi_id = ?', [$parentId, $sesiId]);
        if (!$parent) {
            flash('error', 'Rekening belanja induk tidak ditemukan.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $uraian = trim((string) input('uraian'));
        if ($uraian === '') {
            flash('error', 'Uraian rincian bon / kwitansi wajib diisi.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $noBukti  = trim((string) input('no_bukti')) ?: null;
        $tglBukti = trim((string) input('tgl_bukti')) ?: null;
        if ($tglBukti && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglBukti)) {
            $tglBukti = null;
        }

        $biayaDikwitansi = parse_money(input('biaya_dikwitansi', 0));
        $realisasi       = parse_money(input('realisasi', 0));
        if ($realisasi == 0 && $biayaDikwitansi > 0) {
            $realisasi = $biayaDikwitansi;
        }

        $potongPpn = !empty(input('potong_ppn')) ? 1 : 0;
        $nomPpn    = parse_money(input('nominal_ppn', 0));
        $potongPph = trim((string) input('potong_pph')) ?: null;
        $nomPph    = parse_money(input('nominal_pph', 0));
        $statusPajak = trim((string) input('status_pajak', 'TIDAK_TERUTANG'));
        if (!in_array($statusPajak, ['TIDAK_TERUTANG', 'SUDAH_SETOR', 'BELUM_SETOR'])) {
            $statusPajak = 'TIDAK_TERUTANG';
        }
        $ntpn = trim((string) input('ntpn')) ?: null;
        $jenisPenerima = strtoupper(trim((string) input('jenis_penerima', 'PENERIMA')));
        if (!in_array($jenisPenerima, ['TPK', 'PENERIMA'])) {
            $jenisPenerima = 'PENERIMA';
        }
        $penerima   = trim((string) input('penerima')) ?: null;
        $keterangan = trim((string) input('keterangan')) ?: null;

        // Upload File Bukti Bon / Amprah jika ada
        $fileLampiran = null;
        if (!empty($_FILES['file_bukti']['name']) && !empty($_FILES['file_bukti']['tmp_name']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['file_bukti'];
            $uploadDir = $GLOBALS['cfg']['upload_dir'] ?? (__DIR__ . '/../../uploads');
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowedExts)) {
                $savedName = 'bon_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $uploadDir . '/' . $savedName)) {
                    $fileLampiran = $savedName;
                }
            }
        }

        $nextUrutan = (int) DB::scalar('SELECT COALESCE(MAX(urutan),0)+1 FROM kka_rincian WHERE sesi_id = ?', [$sesiId]);

        DB::insert('kka_rincian', [
            'sesi_id'          => $sesiId,
            'parent_id'        => $parentId,
            'urutan'           => $nextUrutan,
            'uraian'           => $uraian,
            'no_bukti'         => $noBukti,
            'tgl_bukti'        => $tglBukti,
            'pagu_anggaran'    => 0,
            'biaya_dikwitansi' => $biayaDikwitansi,
            'realisasi'        => $realisasi,
            'jenis_penerima'   => $jenisPenerima,
            'penerima'         => $penerima,
            'keterangan'       => $keterangan,
            'potong_ppn'       => $potongPpn,
            'nominal_ppn'      => $nomPpn,
            'potong_pph'       => $potongPph,
            'nominal_pph'      => $nomPph,
            'status_pajak'     => $statusPajak,
            'ntpn'             => $ntpn,
            'file_lampiran'    => $fileLampiran,
        ]);

        $this->syncParentTotals($parentId);

        flash('success', 'Rincian bukti bon / kwitansi berhasil ditambahkan.');
        redirect('sesi/show?id=' . $sesiId);
    }

    /** Update Sub-Uraian Kwitansi / Bon SPJ */
    public function updateSub(): void {
        only_post(); csrf_check();
        $id       = (int) input('id');
        $parentId = (int) input('parent_id');
        $sesiId   = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);

        $row = DB::one('SELECT * FROM kka_rincian WHERE id = ? AND sesi_id = ? AND parent_id = ?', [$id, $sesiId, $parentId]);
        if (!$row) {
            flash('error', 'Data rincian bon tidak ditemukan.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $uraian = trim((string) input('uraian'));
        if ($uraian === '') {
            flash('error', 'Uraian rincian bon / kwitansi wajib diisi.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $noBukti  = trim((string) input('no_bukti')) ?: null;
        $tglBukti = trim((string) input('tgl_bukti')) ?: null;
        if ($tglBukti && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglBukti)) {
            $tglBukti = null;
        }

        $biayaDikwitansi = parse_money(input('biaya_dikwitansi', 0));
        $realisasi       = parse_money(input('realisasi', 0));
        if ($realisasi == 0 && $biayaDikwitansi > 0) {
            $realisasi = $biayaDikwitansi;
        }

        $potongPpn = !empty(input('potong_ppn')) ? 1 : 0;
        $nomPpn    = parse_money(input('nominal_ppn', 0));
        $potongPph = trim((string) input('potong_pph')) ?: null;
        $nomPph    = parse_money(input('nominal_pph', 0));
        $statusPajak = trim((string) input('status_pajak', 'TIDAK_TERUTANG'));
        if (!in_array($statusPajak, ['TIDAK_TERUTANG', 'SUDAH_SETOR', 'BELUM_SETOR'])) {
            $statusPajak = 'TIDAK_TERUTANG';
        }
        $ntpn = trim((string) input('ntpn')) ?: null;
        $jenisPenerima = strtoupper(trim((string) input('jenis_penerima', 'PENERIMA')));
        if (!in_array($jenisPenerima, ['TPK', 'PENERIMA'])) {
            $jenisPenerima = 'PENERIMA';
        }
        $penerima   = trim((string) input('penerima')) ?: null;
        $keterangan = trim((string) input('keterangan')) ?: null;

        $fileLampiran = $row['file_lampiran'];
        if (!empty($_FILES['file_bukti']['name']) && !empty($_FILES['file_bukti']['tmp_name']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['file_bukti'];
            $uploadDir = $GLOBALS['cfg']['upload_dir'] ?? (__DIR__ . '/../../uploads');
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowedExts)) {
                $savedName = 'bon_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $uploadDir . '/' . $savedName)) {
                    if ($fileLampiran && is_file($uploadDir . '/' . $fileLampiran)) {
                        @unlink($uploadDir . '/' . $fileLampiran);
                    }
                    $fileLampiran = $savedName;
                }
            }
        }

        DB::update('kka_rincian', [
            'uraian'           => $uraian,
            'no_bukti'         => $noBukti,
            'tgl_bukti'        => $tglBukti,
            'biaya_dikwitansi' => $biayaDikwitansi,
            'realisasi'        => $realisasi,
            'jenis_penerima'   => $jenisPenerima,
            'penerima'         => $penerima,
            'keterangan'       => $keterangan,
            'potong_ppn'       => $potongPpn,
            'nominal_ppn'      => $nomPpn,
            'potong_pph'       => $potongPph,
            'nominal_pph'      => $nomPph,
            'status_pajak'     => $statusPajak,
            'ntpn'             => $ntpn,
            'file_lampiran'    => $fileLampiran,
        ], ['id' => $id]);

        $this->syncParentTotals($parentId);

        flash('success', 'Rincian bon / kwitansi berhasil diperbarui.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function delete(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);

        $row = DB::one('SELECT * FROM kka_rincian WHERE id = ? AND sesi_id = ?', [$id, $sesiId]);
        if (!$row) { flash('error', 'Rincian tidak ditemukan.'); redirect('sesi/show?id=' . $sesiId); }

        $uploadDir = $GLOBALS['cfg']['upload_dir'] ?? (__DIR__ . '/../../uploads');

        if (!empty($row['parent_id'])) {
            $parentId = (int) $row['parent_id'];
            if (!empty($row['file_lampiran']) && is_file($uploadDir . '/' . $row['file_lampiran'])) {
                @unlink($uploadDir . '/' . $row['file_lampiran']);
            }
            DB::delete('kka_rincian', ['id' => $id]);
            $this->syncParentTotals($parentId);
            flash('success', 'Rincian bon / kwitansi berhasil dihapus.');
            redirect('sesi/show?id=' . $sesiId);
        }

        // Jika baris adalah induk, hapus file lampiran anak-anaknya terlebih dahulu
        $children = DB::all('SELECT id, file_lampiran FROM kka_rincian WHERE parent_id = ?', [$id]);
        foreach ($children as $ch) {
            if (!empty($ch['file_lampiran']) && is_file($uploadDir . '/' . $ch['file_lampiran'])) {
                @unlink($uploadDir . '/' . $ch['file_lampiran']);
            }
        }
        DB::delete('kka_rincian', ['parent_id' => $id]);
        DB::delete('kka_rincian', ['id' => $id]);
        flash('success', 'Rincian belanja beserta seluruh sub-kwitansi berhasil dihapus.');
        redirect('sesi/show?id=' . $sesiId);
    }

    /** Unduh / Tampilkan Lampiran Bon Bukti SPJ */
    public function downloadLampiran(): void {
        $id = (int) input('id');
        $row = DB::one('SELECT r.*, s.created_by
                        FROM kka_rincian r
                        JOIN kka_sesi s ON s.id = r.sesi_id
                        WHERE r.id = ?', [$id]);
        if (!$row || empty($row['file_lampiran'])) {
            http_response_code(404);
            exit('File bukti lampiran tidak ditemukan.');
        }
        if (!sesi_is_owned($this->auth, $row)) {
            http_response_code(403);
            exit('Anda tidak memiliki akses ke berkas ini.');
        }
        $uploadDir = $GLOBALS['cfg']['upload_dir'] ?? (__DIR__ . '/../../uploads');
        $path = $uploadDir . '/' . $row['file_lampiran'];
        if (!is_file($path)) {
            http_response_code(404);
            exit('File fisik tidak ditemukan di server.');
        }
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . addslashes(basename($row['file_lampiran'])) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    /** Sinkronkan Biaya Dikwitansi dan Realisasi Parent dari SUM Children */
    private function syncParentTotals(int $parentId): void {
        $stat = DB::one('
            SELECT COUNT(*) AS cnt,
                   COALESCE(SUM(biaya_dikwitansi), 0) AS tot_kwi,
                   COALESCE(SUM(realisasi), 0) AS tot_real
            FROM kka_rincian
            WHERE parent_id = ?', [$parentId]);
        if ($stat && (int)$stat['cnt'] > 0) {
            DB::update('kka_rincian', [
                'biaya_dikwitansi' => (float)$stat['tot_kwi'],
                'realisasi'        => (float)$stat['tot_real']
            ], ['id' => $parentId]);
        }
    }

    /** Unduh template CSV/Excel baku untuk pengisian audit offline di lapangan */
    public function downloadTemplate(): void {
        $sesiId = (int) input('sesi_id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama, b.nama AS bidang_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            WHERE s.id = ?', [$sesiId]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Akses ditolak'); }

        $filename = 'Template_KKA_' . preg_replace('/[^A-Za-z0-9]/', '_', $sesi['desa_nama']) . '_S' . $sesi['semester'] . '_' . $sesi['tahun_anggaran'] . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM agar Excel di Windows membaca huruf dan angka secara akurat
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header identitas penugasan (kolom A ringkas agar tidak melebarkan kolom No di Excel)
        fputcsv($out, ['KKA', 'INSPEKTORAT KABUPATEN ROKAN HILIR - PENGISIAN OFFLINE']);
        fputcsv($out, ['Desa', $sesi['desa_nama']]);
        fputcsv($out, ['Kecamatan', $sesi['kecamatan_nama']]);
        fputcsv($out, ['Bidang', $sesi['bidang_nama']]);
        fputcsv($out, ['Objek', $sesi['objek_audit']]);
        fputcsv($out, ['Tahun', $sesi['tahun_anggaran'] . ' (Semester ' . $sesi['semester'] . ')']);
        fputcsv($out, ['Petunjuk', 'Isi data belanja mulai baris ke-9. Baris contoh boleh ditimpa/dihapus. Angka bisa tanpa titik atau dengan Rp.']);
        fputcsv($out, []); // Baris kosong pemisah

        // Baris Header Tabel Kolom Baku
        fputcsv($out, [
            'No',
            'Uraian Belanja *',
            'Pagu Anggaran (Rp)',
            'Nilai Kwitansi (Rp) *',
            'Realisasi Fisik (Rp) *',
            'Nama Penerima / Toko Rekanan',
            'Keterangan / Bukti Pajak'
        ]);

        // Baris Contoh Pengisian
        fputcsv($out, [
            '1',
            'Pembangunan Saluran Drainase Dusun I',
            '50000000',
            '48500000',
            '48500000',
            'Toko Bangunan Berkah Mandiri',
            'Bukti kwitansi lengkap, PPN/PPh disetor'
        ]);

        fclose($out);
        exit;
    }

    /** Impor rincian belanja dari file Excel (.xlsx) atau CSV */
    public function importExcel(): void {
        only_post(); csrf_check();
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);

        if (empty($_FILES['file_excel']['name']) || empty($_FILES['file_excel']['tmp_name'])) {
            flash('error', 'Silakan pilih file PDF LRA atau file Excel (.xlsx/.csv) untuk diimpor.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $file     = $_FILES['file_excel'];
        $tmpPath  = $file['tmp_name'];
        $origName = $file['name'];

        require_once __DIR__ . '/../lib/LraParserService.php';

        try {
            $parsedItems = LraParserService::parse($tmpPath, $origName);
        } catch (Exception $e) {
            flash('error', 'Gagal memproses file: ' . $e->getMessage());
            redirect('sesi/show?id=' . $sesiId);
        }

        if (empty($parsedItems)) {
            flash('warning', 'Tidak ada data belanja yang berhasil diekstrak dari dokumen. Pastikan dokumen berisi rincian kegiatan belanja dan nominal.');
            redirect('sesi/show?id=' . $sesiId);
        }

        // Filter agar hanya item yang sesuai dengan bidang sesi ini jika dokumen berisi banyak bidang
        $sesiRow = DB::one('SELECT bidang_id FROM kka_sesi WHERE id = ?', [$sesiId]);
        $targetBidang = (int)($sesiRow['bidang_id'] ?? 0);
        if ($targetBidang > 0) {
            $filtered = array_values(array_filter($parsedItems, function($it) use ($targetBidang) {
                return (int)($it['bidang_id'] ?? 0) === $targetBidang;
            }));
            if (!empty($filtered)) {
                $parsedItems = $filtered;
            }
        }

        // Opsi replace / ganti seluruh data lama
        $modeReplace = (int) input('mode_replace', 0);
        if ($modeReplace === 1) {
            DB::delete('kka_rincian', ['sesi_id' => $sesiId]);
            $urutan = 1;
        } else {
            $urutan = (int) DB::scalar('SELECT COALESCE(MAX(urutan),0)+1 FROM kka_rincian WHERE sesi_id = ?', [$sesiId]);
        }

        $imported = 0;
        foreach ($parsedItems as $item) {
            DB::insert('kka_rincian', [
                'sesi_id'          => $sesiId,
                'urutan'           => $urutan++,
                'uraian'           => $item['uraian'],
                'pagu_anggaran'    => $item['pagu_anggaran'] ?? 0,
                'biaya_dikwitansi' => $item['biaya_dikwitansi'] ?? 0,
                'realisasi'        => $item['realisasi'] ?? 0,
                'penerima'         => $item['penerima'] ?? null,
                'keterangan'       => $item['keterangan'] ?? null,
            ]);
            $imported++;
        }

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $fileTypeLabel = ($ext === 'pdf') ? 'dokumen PDF LRA' : 'file Excel/CSV';
        flash('success', 'Berhasil mengekstrak & mengimpor ' . $imported . ' kegiatan belanja dari ' . $fileTypeLabel . '. Auditor sekarang cukup memverifikasi nilai fisik kwitansi dan pajaknya.');
        redirect('sesi/show?id=' . $sesiId);
    }

    /** Parsing nilai uang yang aman untuk format angka Excel maupun format teks Rupiah Indonesia */
    private function parseMoneySmart($v): float {
        if (is_int($v) || is_float($v)) return (float)$v;
        $s = trim((string)$v);
        if ($s === '') return 0.0;
        // Jika angka murni (misal 50000000 atau 50000000.50)
        if (preg_match('/^-?\d+(\.\d+)?$/', $s)) {
            return (float)$s;
        }
        return parse_money($s);
    }
}
