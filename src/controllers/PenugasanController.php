<?php
/**
 * PenugasanController
 * Mengelola Alur Pra-Audit: Nota Dinas, Disposisi Inspektur, Penerbitan SPT,
 * serta Matriks Program Kerja Audit (PKA) dengan pembagian tugas Anggota 1 & 2.
 * Inspektorat Kabupaten Rokan Hilir
 */

class PenugasanController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $this->auth->require();
    }

    // ============================================================
    // 1. NOTA DINAS (PENGAJUAN OLEH IRBAN KE INSPEKTUR)
    // ============================================================

    public function notaDinas(): void {
        $status = trim((string) input('status', ''));
        $tahun  = (int) input('tahun', 0);
        $user   = $this->auth->user();
        $isInspektur = $this->auth->isInspektur();
        $isIrban     = $this->auth->isIrban();

        $where = '1=1';
        $params = [];

        // Jika Irban biasa (bukan admin dan bukan inspektur), prioritaskan ND wilayahnya
        if (!$this->auth->isAdmin() && !$isInspektur && $isIrban) {
            $where .= ' AND (nd.irban_id = ? OR nd.created_by = ?)';
            $params[] = $this->auth->id();
            $params[] = $this->auth->id();
        }

        if ($status !== '') {
            $where .= ' AND nd.status = ?';
            $params[] = $status;
        }

        if ($tahun > 0) {
            $where .= ' AND nd.tahun_anggaran = ?';
            $params[] = $tahun;
        }

        $list = DB::all("
            SELECT nd.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   spt.id AS spt_id, spt.no_spt, spt.status AS spt_status
            FROM kka_nota_dinas nd
            JOIN kka_desa d ON d.id = nd.desa_id
            JOIN kka_kecamatan k ON k.id = nd.kecamatan_id
            LEFT JOIN kka_spt spt ON spt.nota_dinas_id = nd.id
            WHERE $where
            ORDER BY nd.created_at DESC
        ", $params);

        // Hitung badge antrean untuk Inspektur
        $countMenungguDisposisi = (int) DB::val("
            SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DIAJUKAN_INSPEKTUR'
        ");

        view('penugasan/nd_index', compact('list', 'status', 'tahun', 'isInspektur', 'isIrban', 'countMenungguDisposisi'));
    }

    public function notaDinasCreate(): void {
        $user = $this->auth->user();
        
        $desa = DB::all('
            SELECT d.id, d.kecamatan_id, CONCAT(d.nama, " — Kec. ", k.nama) AS label,
                   d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY d.nama
        ');

        // Daftar Irban
        $irbans = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE role = 'irban' OR jabatan LIKE '%Inspektur Pembantu%'
            ORDER BY id
        ");

        // Daftar Dalnis (Madya)
        $dalnis = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE role = 'dalnis' OR jabatan LIKE '%Madya%'
            ORDER BY nama
        ");

        // Daftar Ketua Tim (Muda)
        $ketua = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE jabatan LIKE '%Muda%' OR role = 'auditor'
            ORDER BY nama
        ");

        // Daftar Anggota (Pertama / Terampil / Mahir, exclude irban)
        $anggota = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE is_active = 1 AND role NOT IN ('admin', 'inspektur', 'operator_spt', 'irban')
            ORDER BY nama
        ");

        view('penugasan/nd_create', compact('desa', 'irbans', 'dalnis', 'ketua', 'anggota', 'user'));
    }

    public function notaDinasStore(): void {
        only_post();
        csrf_check();

        $desaId      = (int) input('desa_id');
        $tahun       = (int) input('tahun_anggaran', date('Y'));
        $irbanId     = (int) input('irban_id');
        $dalnisId    = (int) input('dalnis_id');
        $ketuaTimId  = (int) input('ketua_tim_id');
        $anggotaIds  = (array) (input('anggota_ids') ?? []);
        $tglNd       = trim((string) input('tgl_nd', date('Y-m-d')));
        $tglMulai    = trim((string) input('tgl_mulai', date('Y-m-d')));
        $tglSelesai  = trim((string) input('tgl_selesai', date('Y-m-d', strtotime('+10 days'))));
        $lamaHari    = (int) input('lama_hari', 10);
        $tujuan      = trim((string) input('tujuan', 'Pemeriksaan Reguler Ketaatan Pengelolaan Keuangan Kepenghuluan'));
        $jenisAudit  = trim((string) input('jenis_audit', 'Audit Dengan Tujuan Tertentu (ADTT)'));
        $catatanIrban= trim((string) input('catatan_irban', ''));
        $submitAction= trim((string) input('action', 'draft'));

        if ($desaId <= 0 || $dalnisId <= 0 || $ketuaTimId <= 0) {
            flash('error', 'Desa, Pengendali Teknis, dan Ketua Tim wajib dipilih.');
            redirect('penugasan/nota-dinas/create');
        }

        $desa = DB::one('SELECT id, nama, kecamatan_id FROM kka_desa WHERE id = ?', [$desaId]);
        if (!$desa) {
            flash('error', 'Desa tidak valid.');
            redirect('penugasan/nota-dinas/create');
        }

        $irbanUser = DB::one('SELECT id, nama, jabatan FROM kka_users WHERE id = ?', [$irbanId]);
        $irbanNama = $irbanUser ? $irbanUser['nama'] : ($this->auth->user()['nama'] ?? 'Inspektur Pembantu');

        $dalnisUser = DB::one('SELECT id, nama FROM kka_users WHERE id = ?', [$dalnisId]);
        $ketuaUser  = DB::one('SELECT id, nama FROM kka_users WHERE id = ?', [$ketuaTimId]);

        // Kumpulkan data anggota terpilih
        $anggotaList = [];
        if (!empty($anggotaIds)) {
            $inClause = implode(',', array_map('intval', $anggotaIds));
            if ($inClause !== '') {
                $anggotaRows = DB::all("SELECT id, nama, nip, jabatan FROM kka_users WHERE id IN ($inClause)");
                foreach ($anggotaRows as $ar) {
                    $anggotaList[] = [
                        'id'      => (int)$ar['id'],
                        'nama'    => $ar['nama'],
                        'nip'     => $ar['nip'],
                        'jabatan' => $ar['jabatan'],
                    ];
                }
            }
        }

        // Generate No. ND jika belum ada
        $noNd = trim((string) input('no_nd'));
        if ($noNd === '') {
            $romawiBulan = ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][(int)date('n')];
            $seq = (int)DB::val("SELECT COUNT(*) FROM kka_nota_dinas WHERE tahun_anggaran = ?", [$tahun]) + 1;
            $noNd = sprintf("700/ND-IRBAN/%s/%d/%03d", $romawiBulan, $tahun, $seq);
        }

        $status = ($submitAction === 'ajukan') ? 'DIAJUKAN_INSPEKTUR' : 'DRAFT';

        $id = DB::insert('kka_nota_dinas', [
            'no_nd'          => $noNd,
            'tgl_nd'         => $tglNd,
            'irban_id'       => $irbanId,
            'irban_nama'     => $irbanNama,
            'desa_id'        => $desaId,
            'kecamatan_id'   => $desa['kecamatan_id'],
            'tahun_anggaran' => $tahun,
            'tujuan'         => $tujuan,
            'jenis_audit'    => $jenisAudit,
            'tgl_mulai'      => $tglMulai,
            'tgl_selesai'    => $tglSelesai,
            'lama_hari'      => $lamaHari,
            'dalnis_id'      => $dalnisId,
            'dalnis_nama'    => $dalnisUser['nama'] ?? '-',
            'ketua_tim_id'   => $ketuaTimId,
            'ketua_tim_nama' => $ketuaUser['nama'] ?? '-',
            'anggota_data'   => json_encode($anggotaList, JSON_UNESCAPED_UNICODE),
            'catatan_irban'  => $catatanIrban,
            'status'         => $status,
            'created_by'     => $this->auth->id(),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        if ($status === 'DIAJUKAN_INSPEKTUR') {
            flash('success', "Nota Dinas {$noNd} berhasil diajukan ke Inspektur untuk disposisi.");
        } else {
            flash('success', "Nota Dinas {$noNd} berhasil disimpan sebagai Draft.");
        }

        redirect('penugasan/nota-dinas');
    }

    public function notaDinasDisposisi(): void {
        only_post();
        csrf_check();

        if (!$this->auth->isInspektur() && !$this->auth->isAdmin()) {
            http_response_code(403);
            exit('Hanya Inspektur atau Administrator yang berwenang memberikan disposisi.');
        }

        $id      = (int) input('id');
        $aksi    = trim((string) input('aksi')); // 'setuju' atau 'tolak'
        $catatan = trim((string) input('catatan_inspektur', ''));

        $nd = DB::one('SELECT * FROM kka_nota_dinas WHERE id = ?', [$id]);
        if (!$nd) {
            flash('error', 'Nota Dinas tidak ditemukan.');
            redirect('penugasan/nota-dinas');
        }

        if ($aksi === 'setuju') {
            if ($catatan === '') {
                $catatan = 'Disetujui. Teruskan ke Bagian Perencanaan/SPT untuk penerbitan Surat Perintah Tugas (SPT).';
            }
            DB::update('kka_nota_dinas', [
                'status'            => 'DISETUJUI',
                'catatan_inspektur' => $catatan,
                'tgl_disposisi'     => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);

            flash('success', "Nota Dinas {$nd['no_nd']} telah DISETUJUI dan otomatis masuk ke antrean Bagian SPT.");
        } else {
            DB::update('kka_nota_dinas', [
                'status'            => 'DITOLAK',
                'catatan_inspektur' => $catatan ?: 'Perlu perbaikan/penyesuaian tim penugasan.',
                'tgl_disposisi'     => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);

            flash('warning', "Nota Dinas {$nd['no_nd']} dikembalikan ke Irban dengan catatan.");
        }

        redirect('penugasan/nota-dinas');
    }

    public function notaDinasEdit(): void {
        $id = (int) input('id');
        $nd = DB::one('SELECT * FROM kka_nota_dinas WHERE id = ?', [$id]);
        if (!$nd) {
            flash('error', 'Nota Dinas tidak ditemukan.');
            redirect('penugasan/nota-dinas');
        }

        $user = $this->auth->user();
        $isInspektur = $this->auth->isInspektur();
        $isAdmin = $this->auth->isAdmin();

        if (!$isAdmin && !$isInspektur) {
            if ($nd['irban_id'] !== $user['id'] && $nd['created_by'] !== $user['id']) {
                flash('error', 'Anda tidak memiliki hak akses untuk mengedit Nota Dinas ini.');
                redirect('penugasan/nota-dinas');
            }
        }

        $spt = DB::one('SELECT id, no_spt FROM kka_spt WHERE nota_dinas_id = ?', [$id]);

        $desa = DB::all('
            SELECT d.id, d.kecamatan_id, CONCAT(d.nama, " — Kec. ", k.nama) AS label,
                   d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY d.nama
        ');

        $irbans = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE role = 'irban' OR jabatan LIKE '%Inspektur Pembantu%'
            ORDER BY id
        ");

        $dalnis = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE role = 'dalnis' OR jabatan LIKE '%Madya%'
            ORDER BY nama
        ");

        $ketua = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE jabatan LIKE '%Muda%' OR role = 'auditor'
            ORDER BY nama
        ");

        $anggota = DB::all("
            SELECT id, nama, nip, jabatan FROM kka_users 
            WHERE is_active = 1 AND role NOT IN ('admin', 'inspektur', 'operator_spt', 'irban')
            ORDER BY nama
        ");

        $rawAnggota = json_decode($nd['anggota_data'] ?? '[]', true) ?: [];
        $selectedAnggotaIds = array_map('intval', array_column($rawAnggota, 'id'));

        view('penugasan/nd_edit', compact('nd', 'spt', 'desa', 'irbans', 'dalnis', 'ketua', 'anggota', 'selectedAnggotaIds', 'user'));
    }

    public function notaDinasUpdate(): void {
        only_post();
        csrf_check();

        $id = (int) input('id');
        $nd = DB::one('SELECT * FROM kka_nota_dinas WHERE id = ?', [$id]);
        if (!$nd) {
            flash('error', 'Nota Dinas tidak ditemukan.');
            redirect('penugasan/nota-dinas');
        }

        $user = $this->auth->user();
        $isAdmin = $this->auth->isAdmin();

        if (!$isAdmin && !$this->auth->isInspektur()) {
            if ($nd['irban_id'] !== $user['id'] && $nd['created_by'] !== $user['id']) {
                flash('error', 'Anda tidak memiliki hak akses untuk mengubah Nota Dinas ini.');
                redirect('penugasan/nota-dinas');
            }
        }

        $desaId      = (int) input('desa_id');
        $tahun       = (int) input('tahun_anggaran', date('Y'));
        $irbanId     = (int) input('irban_id');
        $dalnisId    = (int) input('dalnis_id');
        $ketuaTimId  = (int) input('ketua_tim_id');
        $anggotaIds  = (array) (input('anggota_ids') ?? []);
        $tglNd       = trim((string) input('tgl_nd', date('Y-m-d')));
        $tglMulai    = trim((string) input('tgl_mulai', date('Y-m-d')));
        $tglSelesai  = trim((string) input('tgl_selesai', date('Y-m-d', strtotime('+10 days'))));
        $lamaHari    = (int) input('lama_hari', 10);
        $tujuan      = trim((string) input('tujuan', 'Pemeriksaan Reguler Ketaatan Pengelolaan Keuangan Kepenghuluan'));
        $jenisAudit  = trim((string) input('jenis_audit', 'Audit Dengan Tujuan Tertentu (ADTT)'));
        $noNd        = trim((string) input('no_nd', $nd['no_nd']));
        $catatanIrban= trim((string) input('catatan_irban', ''));
        $submitAction= trim((string) input('action', ''));

        if ($desaId <= 0 || $dalnisId <= 0 || $ketuaTimId <= 0 || $irbanId <= 0) {
            flash('error', 'Desa, Irban, Pengendali Teknis, dan Ketua Tim wajib dipilih.');
            redirect("penugasan/nota-dinas/edit?id={$id}");
        }

        $desa = DB::one('SELECT id, nama, kecamatan_id FROM kka_desa WHERE id = ?', [$desaId]);
        if (!$desa) {
            flash('error', 'Desa tidak valid.');
            redirect("penugasan/nota-dinas/edit?id={$id}");
        }

        $irbanUser  = DB::one('SELECT id, nama, jabatan FROM kka_users WHERE id = ?', [$irbanId]);
        $dalnisUser = DB::one('SELECT id, nama FROM kka_users WHERE id = ?', [$dalnisId]);
        $ketuaUser  = DB::one('SELECT id, nama FROM kka_users WHERE id = ?', [$ketuaTimId]);

        $anggotaList = [];
        if (!empty($anggotaIds)) {
            $inClause = implode(',', array_map('intval', $anggotaIds));
            if ($inClause !== '') {
                $anggotaRows = DB::all("SELECT id, nama, nip, jabatan FROM kka_users WHERE id IN ($inClause)");
                foreach ($anggotaRows as $ar) {
                    $anggotaList[] = [
                        'id'      => (int)$ar['id'],
                        'nama'    => $ar['nama'],
                        'nip'     => $ar['nip'],
                        'jabatan' => $ar['jabatan'],
                    ];
                }
            }
        }

        $status = $nd['status'];
        if ($submitAction === 'ajukan') {
            $status = 'DIAJUKAN_INSPEKTUR';
        } elseif ($submitAction === 'draft') {
            $status = 'DRAFT';
        }

        DB::update('kka_nota_dinas', [
            'no_nd'          => $noNd,
            'tgl_nd'         => $tglNd,
            'irban_id'       => $irbanId,
            'irban_nama'     => $irbanUser['nama'] ?? $nd['irban_nama'],
            'desa_id'        => $desaId,
            'kecamatan_id'   => $desa['kecamatan_id'],
            'tahun_anggaran' => $tahun,
            'tujuan'         => $tujuan,
            'jenis_audit'    => $jenisAudit,
            'tgl_mulai'      => $tglMulai,
            'tgl_selesai'    => $tglSelesai,
            'lama_hari'      => $lamaHari,
            'dalnis_id'      => $dalnisId,
            'dalnis_nama'    => $dalnisUser['nama'] ?? '-',
            'ketua_tim_id'   => $ketuaTimId,
            'ketua_tim_nama' => $ketuaUser['nama'] ?? '-',
            'anggota_data'   => json_encode($anggotaList, JSON_UNESCAPED_UNICODE),
            'catatan_irban'  => $catatanIrban,
            'status'         => $status,
            'updated_at'     => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        flash('success', "Nota Dinas {$noNd} berhasil diperbarui.");
        redirect('penugasan/nota-dinas');
    }

    public function notaDinasDelete(): void {
        only_post();
        csrf_check();

        $id = (int) input('id');
        $nd = DB::one('SELECT * FROM kka_nota_dinas WHERE id = ?', [$id]);
        if (!$nd) {
            flash('error', 'Nota Dinas tidak ditemukan.');
            redirect('penugasan/nota-dinas');
        }

        $user = $this->auth->user();
        if (!$this->auth->isAdmin()) {
            if ($nd['irban_id'] !== $user['id'] && $nd['created_by'] !== $user['id']) {
                flash('error', 'Anda tidak memiliki hak akses untuk menghapus Nota Dinas ini.');
                redirect('penugasan/nota-dinas');
            }
        }

        $spt = DB::one('SELECT id, no_spt FROM kka_spt WHERE nota_dinas_id = ?', [$id]);
        if ($spt && !$this->auth->isAdmin()) {
            flash('error', "Nota Dinas ini tidak dapat dihapus karena sudah diterbitkan SPT: {$spt['no_spt']}.");
            redirect('penugasan/nota-dinas');
        }

        if ($spt && $this->auth->isAdmin()) {
            $pka = DB::one('SELECT id FROM kka_pka WHERE spt_id = ?', [$spt['id']]);
            if ($pka) {
                DB::run('DELETE FROM kka_pka_langkah WHERE pka_id = ?', [$pka['id']]);
                DB::run('DELETE FROM kka_pka WHERE id = ?', [$pka['id']]);
            }
            DB::run('DELETE FROM kka_spt WHERE id = ?', [$spt['id']]);
        }

        DB::run('DELETE FROM kka_nota_dinas WHERE id = ?', [$id]);

        flash('success', "Nota Dinas {$nd['no_nd']} berhasil dihapus.");
        redirect('penugasan/nota-dinas');
    }

    // ============================================================
    // 2. SURAT PERINTAH TUGAS (SPT)
    // ============================================================

    public function spt(): void {
        $tahun  = (int) input('tahun', 0);
        $status = trim((string) input('status', ''));

        $where = '1=1';
        $params = [];
        if ($tahun > 0) { $where .= ' AND s.tahun_anggaran = ?'; $params[] = $tahun; }
        if ($status !== '') { $where .= ' AND s.status = ?'; $params[] = $status; }

        $list = DB::all("
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   nd.no_nd, pka.id AS pka_id, pka.status AS pka_status
            FROM kka_spt s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = s.kecamatan_id
            LEFT JOIN kka_nota_dinas nd ON nd.id = s.nota_dinas_id
            LEFT JOIN kka_pka pka ON pka.spt_id = s.id
            WHERE $where
            ORDER BY s.created_at DESC
        ", $params);

        // Antrean Nota Dinas yang sudah disetujui tapi belum dibuatkan SPT
        $pendingNd = DB::all("
            SELECT nd.*, d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_nota_dinas nd
            JOIN kka_desa d ON d.id = nd.desa_id
            JOIN kka_kecamatan k ON k.id = nd.kecamatan_id
            LEFT JOIN kka_spt s ON s.nota_dinas_id = nd.id
            WHERE nd.status = 'DISETUJUI' AND s.id IS NULL
            ORDER BY nd.tgl_disposisi DESC
        ");

        $isInspektur = $this->auth->isInspektur();
        $isOperatorSpt = $this->auth->isOperatorSpt() || $this->auth->isAdmin();

        view('penugasan/spt_index', compact('list', 'pendingNd', 'tahun', 'status', 'isInspektur', 'isOperatorSpt'));
    }

    public function sptCreate(): void {
        $ndId = (int) input('nd_id');
        $nd = DB::one('
            SELECT nd.*, d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_nota_dinas nd
            JOIN kka_desa d ON d.id = nd.desa_id
            JOIN kka_kecamatan k ON k.id = nd.kecamatan_id
            WHERE nd.id = ?', [$ndId]);

        if (!$nd) {
            flash('error', 'Pilih Nota Dinas yang telah disetujui terlebih dahulu.');
            redirect('penugasan/spt');
        }

        // Cek apakah sudah ada SPT
        $existing = DB::one('SELECT id, no_spt FROM kka_spt WHERE nota_dinas_id = ?', [$ndId]);
        if ($existing) {
            flash('warning', "Nota Dinas ini telah diterbitkan SPT No. {$existing['no_spt']}.");
            redirect('penugasan/spt');
        }

        $anggotaList = json_decode($nd['anggota_data'] ?? '[]', true) ?: [];

        // Format standar No. SPT Rohil
        $seq = (int)DB::val("SELECT COUNT(*) FROM kka_spt WHERE tahun_anggaran = ?", [$nd['tahun_anggaran']]) + 1;
        $defaultNoSpt = sprintf("700.1.2.1/SPT/ITKAB-DESA/%d/%03d", $nd['tahun_anggaran'], $seq);

        $defaultDasarHukum = '';

        view('penugasan/spt_create', compact('nd', 'anggotaList', 'defaultNoSpt', 'defaultDasarHukum'));
    }

    public function sptStore(): void {
        only_post();
        csrf_check();

        $ndId       = (int) input('nota_dinas_id');
        $noSpt      = trim((string) input('no_spt'));
        $tglSpt     = trim((string) input('tgl_spt', date('Y-m-d')));
        $dasarHukum = trim((string) input('dasar_hukum', ''));
        $submitAction = trim((string) input('action', 'draft'));

        $nd = DB::one('SELECT * FROM kka_nota_dinas WHERE id = ?', [$ndId]);
        if (!$nd) {
            flash('error', 'Nota dinas tidak valid.');
            redirect('penugasan/spt');
        }

        if ($noSpt === '') {
            flash('error', 'Nomor Surat Perintah Tugas (SPT) wajib diisi.');
            redirect('penugasan/spt/create?nd_id=' . $ndId);
        }

        $status = ($submitAction === 'ajukan') ? 'MENUNGGU_TTD' : 'DRAFT';

        $sptId = DB::insert('kka_spt', [
            'nota_dinas_id'        => $ndId,
            'no_spt'               => $noSpt,
            'tgl_spt'              => $tglSpt,
            'tgl_mulai'            => $nd['tgl_mulai'],
            'tgl_selesai'          => $nd['tgl_selesai'],
            'lama_hari'            => $nd['lama_hari'],
            'desa_id'              => $nd['desa_id'],
            'kecamatan_id'         => $nd['kecamatan_id'],
            'tahun_anggaran'       => $nd['tahun_anggaran'],
            'tujuan'               => $nd['tujuan'],
            'dasar_hukum'          => $dasarHukum,
            'penanggung_jawab_nama'=> 'H. SARMAN SYAHRONI, ST., M.IP',
            'penanggung_jawab_nip' => '19760810 200312 1 004',
            'wakil_pj_id'          => $nd['irban_id'],
            'wakil_pj_nama'        => $nd['irban_nama'],
            'dalnis_id'            => $nd['dalnis_id'],
            'dalnis_nama'          => $nd['dalnis_nama'],
            'ketua_tim_id'         => $nd['ketua_tim_id'],
            'ketua_tim_nama'       => $nd['ketua_tim_nama'],
            'anggota_data'         => $nd['anggota_data'],
            'status'               => $status,
            'created_by'           => $this->auth->id(),
            'created_at'           => date('Y-m-d H:i:s'),
        ]);

        if ($status === 'MENUNGGU_TTD') {
            flash('success', "SPT {$noSpt} berhasil diajukan untuk tanda tangan / pengesahan Inspektur.");
        } else {
            flash('success', "SPT {$noSpt} disimpan sebagai Draft.");
        }

        redirect('penugasan/spt');
    }

    public function sptSahkan(): void {
        only_post();
        csrf_check();

        if (!$this->auth->isInspektur() && !$this->auth->isAdmin()) {
            http_response_code(403);
            exit('Hanya Inspektur atau Administrator yang berwenang mengesahkan SPT.');
        }

        $id = (int) input('id');
        $spt = DB::one('SELECT * FROM kka_spt WHERE id = ?', [$id]);
        if (!$spt) {
            flash('error', 'SPT tidak ditemukan.');
            redirect('penugasan/spt');
        }

        // Update status SPT menjadi DITERBITKAN
        DB::update('kka_spt', [
            'status'            => 'DITERBITKAN',
            'tgl_ttd_inspektur' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // ============================================================
        // OTOMASI PKA (PROGRAM KERJA AUDIT):
        // Saat SPT disahkan Inspektur, otomatis terbitkan kerangka PKA & Langkah Kerja
        // ============================================================
        $existingPka = DB::one('SELECT id FROM kka_pka WHERE spt_id = ?', [$id]);
        if (!$existingPka) {
            $pkaNo = 'PKA-' . preg_replace('/[^A-Za-z0-9]/', '-', $spt['no_spt']);
            $pkaId = DB::insert('kka_pka', [
                'spt_id'         => $id,
                'no_pka'         => $pkaNo,
                'tgl_pka'        => $spt['tgl_spt'],
                'desa_id'        => $spt['desa_id'],
                'tahun_anggaran' => $spt['tahun_anggaran'],
                'ketua_tim_id'   => $spt['ketua_tim_id'],
                'ketua_tim_nama' => $spt['ketua_tim_nama'],
                'dalnis_id'      => $spt['dalnis_id'],
                'dalnis_nama'    => $spt['dalnis_nama'],
                'irban_id'       => $spt['wakil_pj_id'],
                'irban_nama'     => $spt['wakil_pj_nama'],
                'status'         => 'DRAFT',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            // Ambil data anggota tim untuk pembagian tugas awal (default anggota 1 dan anggota 2)
            $anggotaList = json_decode($spt['anggota_data'] ?? '[]', true) ?: [];
            $anggota1 = $anggotaList[0] ?? null;
            $anggota2 = $anggotaList[1] ?? ($anggotaList[0] ?? null);

            // 7 Langkah Standar Program Kerja Audit APIP Desa:
            $standarLangkah = [
                [
                    'bidang_kode' => 'BID.1',
                    'bidang_nama' => 'Bidang 1: Penyelenggaraan Pemerintahan Desa',
                    'uraian'      => 'Pengujian kepatuhan pembayaran Penghasilan Tetap (Siltap) Kepala Desa & Perangkat, Tunjangan BPD, Operasional Perkantoran, serta kesesuaian daftar penerima.',
                    'tujuan'      => 'Memastikan tidak ada pembayaran ganda, potongan liar, atau penerima fiktif.',
                    'pelaksana_id'=> $anggota1 ? $anggota1['id'] : null,
                    'pelaksana_nm'=> $anggota1 ? $anggota1['nama'] : null,
                    'ref_kka'     => 'KKA-B.1.1',
                    'urutan'      => 1
                ],
                [
                    'bidang_kode' => 'BID.2',
                    'bidang_nama' => 'Bidang 2: Pelaksanaan Pembangunan Desa (Fisik Lapangan)',
                    'uraian'      => 'Pemeriksaan pekerjaan fisik/konstruksi: semenisasi jalan, drainase, posyandu. Uji volume (panjang x lebar x tebal), mutu bahan, dan dokumentasi foto lapangan STA 0%, 50%, 100%.',
                    'tujuan'      => 'Menguji kesesuaian realisasi fisik dengan RAB, gambar rencana, dan dokumen kontrak/SPK.',
                    'pelaksana_id'=> $anggota2 ? $anggota2['id'] : null,
                    'pelaksana_nm'=> $anggota2 ? $anggota2['nama'] : null,
                    'ref_kka'     => 'KKA-B.2.1',
                    'urutan'      => 2
                ],
                [
                    'bidang_kode' => 'BID.3',
                    'bidang_nama' => 'Bidang 3: Pembinaan Kemasyarakatan Desa',
                    'uraian'      => 'Pemeriksaan belanja kegiatan kepemudaan, keagamaan, pos kamling, dan kelembagaan masyarakat desa beserta bukti pertanggungjawabannya.',
                    'tujuan'      => 'Memastikan seluruh kegiatan pembinaan benar-benar dilaksanakan dan didukung bukti kwitansi sah.',
                    'pelaksana_id'=> $anggota1 ? $anggota1['id'] : null,
                    'pelaksana_nm'=> $anggota1 ? $anggota1['nama'] : null,
                    'ref_kka'     => 'KKA-B.3.1',
                    'urutan'      => 3
                ],
                [
                    'bidang_kode' => 'BID.4',
                    'bidang_nama' => 'Bidang 4: Pemberdayaan Masyarakat Desa',
                    'uraian'      => 'Pemeriksaan pengadaan bantuan bibit ternak/pertanian, pelatihan ketrampilan warga, serta penguatan modal BUMDesa.',
                    'tujuan'      => 'Memastikan bantuan diserahkan tepat sasaran dibuktikan dengan Berita Acara Serah Terima (BAST).',
                    'pelaksana_id'=> $anggota1 ? $anggota1['id'] : null,
                    'pelaksana_nm'=> $anggota1 ? $anggota1['nama'] : null,
                    'ref_kka'     => 'KKA-B.4.1',
                    'urutan'      => 4
                ],
                [
                    'bidang_kode' => 'BID.5',
                    'bidang_nama' => 'Bidang 5: Penanggulangan Bencana & BLT Desa',
                    'uraian'      => 'Pengujian penyaluran Bantuan Langsung Tunai (BLT Dana Desa): kecocokan KPM, tanda tangan penerima, dan bukti dokumentasi penyaluran.',
                    'tujuan'      => 'Memastikan tidak ada pemotongan hak KPM dan tidak ada penerima ganda dengan bansos lainnya.',
                    'pelaksana_id'=> $anggota2 ? $anggota2['id'] : null,
                    'pelaksana_nm'=> $anggota2 ? $anggota2['nama'] : null,
                    'ref_kka'     => 'KKA-B.5.1',
                    'urutan'      => 5
                ],
                [
                    'bidang_kode' => 'PAJAK',
                    'bidang_nama' => 'Pengujian Kepatuhan Perpajakan (PPN & PPh)',
                    'uraian'      => 'Pengujian pemotongan dan penyetoran Pajak Pertambahan Nilai (PPN) serta Pajak Penghasilan (PPh Pasal 21, 22, 23) atas seluruh belanja kena pajak ke Kas Negara.',
                    'tujuan'      => 'Memastikan tidak ada pajak yang telah dipungut oleh Bendahara Desa namun belum disetorkan (NTPN valid).',
                    'pelaksana_id'=> $anggota1 ? $anggota1['id'] : null,
                    'pelaksana_nm'=> $anggota1 ? $anggota1['nama'] : null,
                    'ref_kka'     => 'KKA-PAJAK',
                    'urutan'      => 6
                ],
                [
                    'bidang_kode' => 'KAS',
                    'bidang_nama' => 'Pemeriksaan Kas & Rekonsiliasi Rekening Bank',
                    'uraian'      => 'Pemeriksaan fisik sisa kas tunai di brankas Bendahara Desa (Cash Count) dan rekonsiliasi rekening koran kas desa di Bank Riau Kepri.',
                    'tujuan'      => 'Memastikan saldo kas fisik dan buku kas umum (BKU) sinkron tanpa selisih lebih/kurang.',
                    'pelaksana_id'=> null, // Bersama KT / Dalnis
                    'pelaksana_nm'=> 'Ketua Tim & Anggota',
                    'ref_kka'     => 'KKA-KAS',
                    'urutan'      => 7
                ],
            ];

            foreach ($standarLangkah as $sl) {
                DB::insert('kka_pka_langkah', [
                    'pka_id'             => $pkaId,
                    'bidang_kode'        => $sl['bidang_kode'],
                    'bidang_nama'        => $sl['bidang_nama'],
                    'uraian_prosedur'    => $sl['uraian'],
                    'tujuan_pengujian'   => $sl['tujuan'],
                    'pelaksana_user_id'  => $sl['pelaksana_id'],
                    'pelaksana_nama'     => $sl['pelaksana_nm'],
                    'waktu_rencana_jam'  => 8,
                    'ref_kka_nomor'      => $sl['ref_kka'],
                    'status_pelaksanaan' => 'BELUM',
                    'urutan'             => $sl['urutan'],
                ]);
            }
        }

        flash('success', "SPT {$spt['no_spt']} telah SAH disahkan oleh Inspektur! Kerangka Program Kerja Audit (PKA) otomatis terbentuk.");
        redirect('penugasan/spt');
    }

    // ============================================================
    // 3. PROGRAM KERJA AUDIT (PKA) & PEMBAGIAN TUGAS
    // ============================================================

    public function pka(): void {
        $tahun = (int) input('tahun', 0);
        $where = '1=1';
        $params = [];
        if ($tahun > 0) { $where .= ' AND p.tahun_anggaran = ?'; $params[] = $tahun; }

        $list = DB::all("
            SELECT p.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   s.no_spt, s.tgl_spt, s.tgl_mulai, s.tgl_selesai,
                   (SELECT COUNT(*) FROM kka_pka_langkah WHERE pka_id = p.id) AS total_langkah,
                   (SELECT COUNT(*) FROM kka_pka_langkah WHERE pka_id = p.id AND status_pelaksanaan = 'SELESAI') AS selesai_langkah
            FROM kka_pka p
            JOIN kka_desa d ON d.id = p.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_spt s ON s.id = p.spt_id
            WHERE $where
            ORDER BY p.created_at DESC
        ", $params);

        view('penugasan/pka_index', compact('list', 'tahun'));
    }

    public function pkaShow(): void {
        $id = (int) input('id');
        $pka = DB::one('
            SELECT p.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   s.no_spt, s.tgl_spt, s.tgl_mulai, s.tgl_selesai, s.lama_hari, s.anggota_data
            FROM kka_pka p
            JOIN kka_desa d ON d.id = p.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_spt s ON s.id = p.spt_id
            WHERE p.id = ?', [$id]);

        if (!$pka) {
            flash('error', 'Program Kerja Audit (PKA) tidak ditemukan.');
            redirect('penugasan/pka');
        }

        $langkah = DB::all('SELECT * FROM kka_pka_langkah WHERE pka_id = ? ORDER BY urutan, id', [$id]);
        $anggotaList = json_decode($pka['anggota_data'] ?? '[]', true) ?: [];

        $isKetuaTim = ($this->auth->id() === (int)$pka['ketua_tim_id']) || $this->auth->isAdmin();
        $isDalnis   = ($this->auth->id() === (int)$pka['dalnis_id']) || $this->auth->isAdmin();

        view('penugasan/pka_show', compact('pka', 'langkah', 'anggotaList', 'isKetuaTim', 'isDalnis'));
    }

    public function pkaUpdate(): void {
        only_post();
        csrf_check();

        $id = (int) input('id');
        $pka = DB::one('SELECT * FROM kka_pka WHERE id = ?', [$id]);
        if (!$pka) {
            flash('error', 'PKA tidak ditemukan.');
            redirect('penugasan/pka');
        }

        $langkahData = (array) (input('langkah') ?? []);
        $submitAction = trim((string) input('action', 'save'));

        foreach ($langkahData as $lId => $val) {
            $lId = (int)$lId;
            $pelaksanaId = !empty($val['pelaksana_user_id']) ? (int)$val['pelaksana_user_id'] : null;
            $pelaksanaNm = trim((string)($val['pelaksana_nama'] ?? ''));
            $refKka      = trim((string)($val['ref_kka_nomor'] ?? ''));
            $jam         = (int)($val['waktu_rencana_jam'] ?? 8);
            $waktuHari   = max(1, (int)($val['waktu_rencana_hari'] ?? 1));
            $realHari    = max(1, (int)($val['waktu_realisasi_hari'] ?? $waktuHari));
            $status      = in_array($val['status_pelaksanaan'] ?? '', ['BELUM','SEDANG','SELESAI']) ? $val['status_pelaksanaan'] : 'BELUM';

            // Jika ada pelaksanaId dipilih dari dropdown, ambil namanya
            if ($pelaksanaId > 0) {
                $u = DB::one('SELECT nama FROM kka_users WHERE id = ?', [$pelaksanaId]);
                if ($u) $pelaksanaNm = $u['nama'];
            }

            DB::update('kka_pka_langkah', [
                'pelaksana_user_id'        => $pelaksanaId,
                'pelaksana_nama'           => $pelaksanaNm,
                'pelaksana_realisasi_nama' => $pelaksanaNm,
                'ref_kka_nomor'            => $refKka,
                'waktu_rencana_jam'        => $jam,
                'waktu_rencana_hari'       => $waktuHari,
                'waktu_realisasi_hari'     => $realHari,
                'status_pelaksanaan'       => $status,
            ], 'id = ? AND pka_id = ?', [$lId, $id]);
        }

        if ($submitAction === 'ajukan') {
            DB::update('kka_pka', ['status' => 'REVIEW_DALNIS'], 'id = ?', [$id]);
            flash('success', 'Program Kerja Audit (PKA) berhasil diajukan ke Pengendali Teknis (Dalnis).');
        } else {
            flash('success', 'Alokasi tugas pelaksana PKA berhasil diperbarui.');
        }

        redirect('penugasan/pka/show?id=' . $id);
    }

    public function pkaApprove(): void {
        only_post();
        csrf_check();

        $id = (int) input('id');
        $catatan = trim((string) input('catatan_dalnis', 'Disetujui untuk dilaksanakan sesuai alokasi prosedur audit.'));

        $pka = DB::one('SELECT * FROM kka_pka WHERE id = ?', [$id]);
        if (!$pka) {
            flash('error', 'PKA tidak ditemukan.');
            redirect('penugasan/pka');
        }

        DB::update('kka_pka', [
            'status'           => 'DISETUJUI',
            'catatan_dalnis'   => $catatan,
            'tgl_reviu_dalnis' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        flash('success', 'Program Kerja Audit (PKA) telah DISETUJUI oleh Pengendali Teknis.');
        redirect('penugasan/pka/show?id=' . $id);
    }

    // ============================================================
    // 4. CETAK DOKUMEN (NOTA DINAS, SPT, PKA)
    // ============================================================

    public function printNotaDinas(): void {
        $id = (int) input('id');
        $nd = DB::one('
            SELECT nd.*, d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_nota_dinas nd
            JOIN kka_desa d ON d.id = nd.desa_id
            JOIN kka_kecamatan k ON k.id = nd.kecamatan_id
            WHERE nd.id = ?', [$id]);

        if (!$nd) { http_response_code(404); exit('Nota Dinas tidak ditemukan.'); }

        $irbanUser   = !empty($nd['irban_id']) ? DB::one('SELECT * FROM kka_users WHERE id = ?', [$nd['irban_id']]) : null;
        if (!$irbanUser && !empty($nd['irban_nama'])) {
            $irbanUser = DB::one('SELECT * FROM kka_users WHERE nama LIKE ?', ['%' . $nd['irban_nama'] . '%']);
        }
        if (!$irbanUser) {
            $irbanUser = DB::one("SELECT * FROM kka_users WHERE role = 'irban' AND (jabatan LIKE '%IV%' OR nama LIKE '%MARWAN%') LIMIT 1");
        }
        $dalnisUser  = DB::one('SELECT * FROM kka_users WHERE id = ?', [$nd['dalnis_id']]);
        $ketuaUser   = DB::one('SELECT * FROM kka_users WHERE id = ?', [$nd['ketua_tim_id']]);
        $anggotaList = json_decode($nd['anggota_data'] ?? '[]', true) ?: [];

        view('print/nota_dinas', compact('nd', 'irbanUser', 'dalnisUser', 'ketuaUser', 'anggotaList'));
    }

    public function printSpt(): void {
        $id = (int) input('id');
        $spt = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama, nd.no_nd, nd.tgl_nd, nd.jenis_audit
            FROM kka_spt s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = s.kecamatan_id
            LEFT JOIN kka_nota_dinas nd ON nd.id = s.nota_dinas_id
            WHERE s.id = ?', [$id]);

        if (!$spt) { http_response_code(404); exit('Surat Perintah Tugas (SPT) tidak ditemukan.'); }

        $irbanUser = !empty($spt['wakil_pj_id']) ? DB::one('SELECT * FROM kka_users WHERE id = ?', [$spt['wakil_pj_id']]) : null;
        if (!$irbanUser && !empty($spt['wakil_pj_nama'])) {
            $irbanUser = DB::one('SELECT * FROM kka_users WHERE nama LIKE ?', ['%' . $spt['wakil_pj_nama'] . '%']);
        }
        if (!$irbanUser) {
            $irbanUser = DB::one("SELECT * FROM kka_users WHERE role = 'irban' AND (jabatan LIKE '%IV%' OR nama LIKE '%MARWAN%') LIMIT 1");
        }

        $dalnisUser = !empty($spt['dalnis_id']) ? DB::one('SELECT * FROM kka_users WHERE id = ?', [$spt['dalnis_id']]) : null;
        $ketuaUser  = !empty($spt['ketua_tim_id']) ? DB::one('SELECT * FROM kka_users WHERE id = ?', [$spt['ketua_tim_id']]) : null;

        $rawAnggota = json_decode($spt['anggota_data'] ?? '[]', true) ?: [];
        $anggotaList = [];
        foreach ($rawAnggota as $ag) {
            $agId = (int)($ag['id'] ?? 0);
            $u = $agId > 0 ? DB::one('SELECT * FROM kka_users WHERE id = ?', [$agId]) : null;
            $anggotaList[] = [
                'id'      => $agId,
                'nama'    => $u['nama'] ?? ($ag['nama'] ?? ''),
                'nip'     => $u['nip'] ?? ($ag['nip'] ?? ''),
                'pangkat' => $u['pangkat'] ?? ($ag['pangkat'] ?? 'Penata / III.c'),
                'jabatan' => $u['jabatan'] ?? ($ag['jabatan'] ?? 'Auditor Ahli Pertama'),
            ];
        }

        view('print/spt', compact('spt', 'anggotaList', 'dalnisUser', 'ketuaUser', 'irbanUser'));
    }

    public function printPka(): void {
        $id = (int) input('id');
        $pka = DB::one('
            SELECT p.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   s.no_spt, s.tgl_spt, s.tgl_mulai, s.tgl_selesai, s.lama_hari, s.anggota_data
            FROM kka_pka p
            JOIN kka_desa d ON d.id = p.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_spt s ON s.id = p.spt_id
            WHERE p.id = ?', [$id]);

        if (!$pka) { http_response_code(404); exit('PKA tidak ditemukan.'); }

        $langkah = DB::all('SELECT * FROM kka_pka_langkah WHERE pka_id = ? ORDER BY urutan, id', [$id]);
        $anggotaList = json_decode($pka['anggota_data'] ?? '[]', true) ?: [];

        view('print/pka', compact('pka', 'langkah', 'anggotaList'));
    }
}

