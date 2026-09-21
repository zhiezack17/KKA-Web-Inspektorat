<?php
class SesiController {
    private Auth $auth;
    public function __construct(Auth $auth) { 
        $this->auth = $auth; 
        $auth->require(); 
        if ($this->auth->isOperatorSpt()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Perencanaan (Operator SPT) difokuskan pada pengelolaan administrasi penugasan (Nota Dinas & SPT) dan tidak memiliki akses ke Kertas Kerja Audit.');
            redirect('penugasan/spt');
        }
        if ($this->auth->isOperatorTl()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Tindak Lanjut (TLHP) difokuskan pada pemantauan rekomendasi dan tidak memiliki akses ke Kertas Kerja Audit.');
            redirect('tlhp');
        }
    }

    public function index(): void {
        $q       = trim((string) input('q', ''));
        $tahun   = (int) input('tahun', 0);
        $desaId  = (int) input('desa', 0);
        $bidId   = (int) input('bidang', 0);

        $where = '1=1'; $p = [];
        if ($q !== '')   { $where .= ' AND (s.objek_audit LIKE ? OR s.no_kka LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
        if ($tahun > 0)  { $where .= ' AND s.tahun_anggaran = ?'; $p[] = $tahun; }
        if ($desaId > 0) { $where .= ' AND s.desa_id = ?'; $p[] = $desaId; }
        if ($bidId > 0)  { $where .= ' AND s.bidang_id = ?'; $p[] = $bidId; }

        // Isolasi data: auditor hanya melihat miliknya sendiri, admin melihat semua
        [$ow, $op] = owner_where($this->auth);
        $where .= $ow; $p = array_merge($p, $op);

        $sesi = DB::all("
            SELECT s.id, s.objek_audit, s.semester, s.tahun_anggaran, s.no_kka, s.dibuat_oleh, s.tanggal_dibuat,
                   s.bidang_id, s.sub_bidang_id, s.status,
                   d.nama AS desa, b.nama AS bidang, b.urutan AS bidang_urutan, sb.nama AS sub_bidang
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            WHERE $where ORDER BY b.urutan, sb.nama, s.created_at DESC
        ", $p);

        $desa    = DB::all('SELECT id, nama FROM kka_desa ORDER BY nama');
        $bidang  = DB::all('SELECT id, nama FROM kka_bidang ORDER BY urutan');
        $tahuns  = DB::all("SELECT DISTINCT tahun_anggaran AS t FROM kka_sesi s WHERE 1=1 $ow ORDER BY t DESC", $op);

        view('sesi/index', compact('sesi','desa','bidang','tahuns','q','tahun','desaId','bidId'));
    }

    public function create(): void {
        $desa = DB::all('
            SELECT d.id, CONCAT(d.nama, " — Kec. ", k.nama) AS nama
            FROM kka_desa d JOIN kka_kecamatan k ON k.id=d.kecamatan_id
            ORDER BY d.nama
        ');
        $bidang = DB::all('SELECT id, nama FROM kka_bidang ORDER BY urutan');
        $auditors  = $this->auditorList();
        $allUsers  = $this->allUserList();
        $sharedIds = [];
        $canShare  = true;
        view('sesi/create', compact('desa','bidang','auditors','allUsers','sharedIds','canShare'));
    }

    public function store(): void {
        only_post(); csrf_check();
        $ketuaId = ((int) input('ketua_tim_id')) ?: null;
        $dalnisId = ((int) input('dalnis_id')) ?: null;
        $irbanId = ((int) input('irban_id')) ?: null;

        $direviewOleh = trim((string) input('direview_oleh')) ?: null;
        if (!$direviewOleh && $ketuaId) {
            $u = DB::one('SELECT nama, nip FROM kka_users WHERE id = ?', [$ketuaId]);
            if ($u) $direviewOleh = $u['nama'];
        }

        $dievaluasiOleh = trim((string) input('dievaluasi_oleh')) ?: null;
        if (!$dievaluasiOleh && $dalnisId) {
            $u = DB::one('SELECT nama, nip FROM kka_users WHERE id = ?', [$dalnisId]);
            if ($u) $dievaluasiOleh = $u['nama'];
        }

        $irbanNama = trim((string) input('irban_nama')) ?: null;
        if (!$irbanNama && $irbanId) {
            $u = DB::one('SELECT nama, nip FROM kka_users WHERE id = ?', [$irbanId]);
            if ($u) $irbanNama = $u['nama'];
        }

        $data = [
            'desa_id'        => (int) input('desa_id'),
            'bidang_id'      => (int) input('bidang_id'),
            'sub_bidang_id'  => ((int) input('sub_bidang_id')) ?: null,
            'objek_audit'    => trim((string) input('objek_audit')),
            'kegiatan'       => trim((string) input('kegiatan')) ?: null,
            'pagu_anggaran'  => parse_money(input('pagu_anggaran', 0)),
            'semester'       => (int) input('semester', 1),
            'tahun_anggaran' => (int) input('tahun_anggaran', (int)date('Y')),
            'no_kka'         => trim((string) input('no_kka')) ?: null,
            'ref_kka'        => trim((string) input('ref_kka')) ?: null,
            'dibuat_oleh'    => trim((string) input('dibuat_oleh')) ?: null,
            'tanggal_dibuat' => input('tanggal_dibuat') ?: null,
            'ketua_tim_id'   => $ketuaId,
            'direview_oleh'  => $direviewOleh,
            'tanggal_review' => input('tanggal_review') ?: null,
            'dalnis_id'      => $dalnisId,
            'dievaluasi_oleh'  => $dievaluasiOleh,
            'tanggal_evaluasi' => input('tanggal_evaluasi') ?: null,
            'irban_id'       => $irbanId,
            'irban_nama'     => $irbanNama,
            'no_lha'         => trim((string) input('no_lha')) ?: null,
            'status'         => 'DRAFT',
            'created_by'     => $this->auth->id(),
        ];

        if (!$data['desa_id'] || !$data['bidang_id'] || !$data['objek_audit']) {
            flash('error', 'Desa, Bidang, dan Objek Audit wajib diisi.');
            redirect('sesi/create');
        }
        $id = DB::insert('kka_sesi', $data);
        $this->syncShares($id, (array) input('shared_users', []));
        flash('success', 'Sesi audit dibuat. Sekarang silakan tambahkan rincian belanja.');
        redirect('sesi/show?id=' . $id);
    }

    public function show(): void {
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        guard_sesi($this->auth, $sesi);

        $rincian = DB::all('SELECT * FROM kka_rincian WHERE sesi_id = ? ORDER BY urutan, id', [$id]);
        $lampiran = DB::all('SELECT * FROM kka_lampiran WHERE sesi_id = ? ORDER BY created_at DESC', [$id]);
        $sharedWith = DB::all('SELECT u.nama, u.jabatan FROM kka_sesi_share sh
                               JOIN kka_users u ON u.id = sh.user_id
                               WHERE sh.sesi_id = ? ORDER BY u.nama', [$id]);

        $totals = [
            'pagu'      => (float) $sesi['pagu_anggaran'],
            'pagu_rinci'=> array_sum(array_column($rincian, 'pagu_anggaran')),
            'dikwitansi'=> array_sum(array_column($rincian, 'biaya_dikwitansi')),
            'realisasi' => array_sum(array_column($rincian, 'realisasi')),
        ];
        $totals['selisih'] = $totals['realisasi'] - $totals['dikwitansi'];
        $allUsers = $this->allUserList();

        view('sesi/show', compact('sesi','rincian','lampiran','totals','sharedWith','allUsers'));
    }

    public function edit(): void {
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        guard_sesi($this->auth, $sesi);
        $desa = DB::all('SELECT d.id, CONCAT(d.nama, " — Kec. ", k.nama) AS nama
                         FROM kka_desa d JOIN kka_kecamatan k ON k.id=d.kecamatan_id ORDER BY d.nama');
        $bidang = DB::all('SELECT id, nama FROM kka_bidang ORDER BY urutan');
        $subBidang = DB::all('SELECT id, nama FROM kka_sub_bidang WHERE bidang_id = ? ORDER BY nama', [$sesi['bidang_id']]);
        $auditors  = $this->auditorList();
        $allUsers  = $this->allUserList();
        $sharedIds = array_map('intval', array_column(
            DB::all('SELECT user_id FROM kka_sesi_share WHERE sesi_id = ?', [$sesi['id']]), 'user_id'));
        $canShare  = $this->auth->isAdmin() || (int) $sesi['created_by'] === (int) $this->auth->id();
        view('sesi/edit', compact('sesi','desa','bidang','subBidang','auditors','allUsers','sharedIds','canShare'));
    }

    public function update(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        guard_sesi($this->auth, $sesi);

        $ketuaId = ((int) input('ketua_tim_id')) ?: null;
        $dalnisId = ((int) input('dalnis_id')) ?: null;
        $irbanId = ((int) input('irban_id')) ?: null;

        $direviewOleh = trim((string) input('direview_oleh')) ?: null;
        if (!$direviewOleh && $ketuaId) {
            $u = DB::one('SELECT nama, nip FROM kka_users WHERE id = ?', [$ketuaId]);
            if ($u) $direviewOleh = $u['nama'];
        }

        $dievaluasiOleh = trim((string) input('dievaluasi_oleh')) ?: null;
        if (!$dievaluasiOleh && $dalnisId) {
            $u = DB::one('SELECT nama, nip FROM kka_users WHERE id = ?', [$dalnisId]);
            if ($u) $dievaluasiOleh = $u['nama'];
        }

        $irbanNama = trim((string) input('irban_nama')) ?: null;
        if (!$irbanNama && $irbanId) {
            $u = DB::one('SELECT nama, nip FROM kka_users WHERE id = ?', [$irbanId]);
            if ($u) $irbanNama = $u['nama'];
        }

        $data = [
            'desa_id'        => (int) input('desa_id'),
            'bidang_id'      => (int) input('bidang_id'),
            'sub_bidang_id'  => ((int) input('sub_bidang_id')) ?: null,
            'objek_audit'    => trim((string) input('objek_audit')),
            'kegiatan'       => trim((string) input('kegiatan')) ?: null,
            'pagu_anggaran'  => parse_money(input('pagu_anggaran', 0)),
            'semester'       => (int) input('semester', 1),
            'tahun_anggaran' => (int) input('tahun_anggaran', (int)date('Y')),
            'no_kka'         => trim((string) input('no_kka')) ?: null,
            'ref_kka'        => trim((string) input('ref_kka')) ?: null,
            'dibuat_oleh'    => trim((string) input('dibuat_oleh')) ?: null,
            'tanggal_dibuat' => input('tanggal_dibuat') ?: null,
            'ketua_tim_id'   => $ketuaId,
            'direview_oleh'  => $direviewOleh,
            'tanggal_review' => input('tanggal_review') ?: null,
            'dalnis_id'      => $dalnisId,
            'dievaluasi_oleh'  => $dievaluasiOleh,
            'tanggal_evaluasi' => input('tanggal_evaluasi') ?: null,
            'irban_id'       => $irbanId,
            'irban_nama'     => $irbanNama,
            'no_lha'         => trim((string) input('no_lha')) ?: null,
            'kesimpulan'     => trim((string) input('kesimpulan')) ?: null,
            'sumber_data'    => trim((string) input('sumber_data')) ?: null,
        ];
        DB::update('kka_sesi', $data, ['id' => $id]);
        // Hanya perbarui daftar berbagi bila form memang mengirim penanda 'manage_shares'
        // (form Kesimpulan di halaman detail tidak mengirimnya, jadi berbagi tidak terhapus).
        if (input('manage_shares') && ($this->auth->isAdmin() || (int) $sesi['created_by'] === (int) $this->auth->id())) {
            $this->syncShares($id, (array) input('shared_users', []));
        }
        flash('success', 'Sesi audit diperbarui.');
        redirect('sesi/show?id=' . $id);
    }

    /** Auditor mengajukan KKA ke Ketua Tim */
    public function ajukan(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        guard_sesi($this->auth, $sesi);

        $currUid   = (int) $this->auth->id();
        $isAdmin   = $this->auth->isAdmin();
        $ketuaId   = (int) ($sesi['ketua_tim_id'] ?? 0);
        $creatorId = (int) ($sesi['created_by'] ?? 0);

        // Jika user adalah Ketua Tim pada sesi ini dan bukan pembuat KKA, pengajuan harus oleh auditor/penyusun
        if (!$isAdmin && $ketuaId > 0 && $ketuaId === $currUid && $creatorId !== $currUid) {
            flash('error', 'Anda bertindak sebagai Ketua Tim pada sesi ini. Pengajuan KKA dilakukan oleh Tim Pemeriksa / Auditor penyusun.');
            redirect('sesi/show?id=' . $id);
        }

        $st = $sesi['status'] ?? 'DRAFT';
        if (!in_array($st, ['DRAFT', 'PERLU_REVISI'])) {
            flash('error', 'Hanya KKA berstatus Draft atau Perlu Revisi yang dapat diajukan.');
            redirect('sesi/show?id=' . $id);
        }

        DB::update('kka_sesi', [
            'status' => 'REVIEW_KETUA',
            'tgl_reviu_ketua' => null,
        ], ['id' => $id]);

        flash('success', 'KKA berhasil diajukan ke Ketua Tim untuk direviu.');
        redirect('sesi/show?id=' . $id);
    }

    /** Reviu dan Persetujuan oleh Ketua Tim */
    public function reviuKetua(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        guard_sesi($this->auth, $sesi);

        $currUid   = (int) $this->auth->id();
        $currUser  = $this->auth->user();
        $currNama  = trim($currUser['nama'] ?? '');
        $currNip   = preg_replace('/\s+/', '', $currUser['nip'] ?? '');
        $isAdmin   = $this->auth->isAdmin();
        $ketuaId   = (int) ($sesi['ketua_tim_id'] ?? 0);

        $matchKetuaName = ($currNama !== '' && !empty($sesi['direview_oleh']) && (
            stripos($sesi['direview_oleh'], $currNama) !== false ||
            stripos($currNama, trim($sesi['direview_oleh'])) !== false ||
            (!empty($currNip) && strpos(preg_replace('/\s+/', '', $sesi['direview_oleh']), $currNip) !== false)
        ));

        // Kunci wewenang: hanya Ketua Tim yang ditugaskan (id cocok atau nama cocok) atau Admin yang boleh reviu
        $isAuthorizedKetua = $isAdmin || ($ketuaId > 0 && $ketuaId === $currUid) || $matchKetuaName || ($ketuaId === 0 && $this->auth->isKetua());
        if (!$isAuthorizedKetua) {
            flash('error', 'Hanya Ketua Tim yang ditugaskan (' . e($sesi['ketua_nama'] ?: ($sesi['direview_oleh'] ?: 'Ketua Tim')) . ') atau Administrator yang berhak melakukan Reviu Ketua Tim.');
            redirect('sesi/show?id=' . $id);
        }

        $aksi = (string) input('aksi');
        $catatan = trim((string) input('catatan_reviu', ''));

        if ($aksi === 'setuju') {
            $u = $this->auth->user();
            $assignedKetua = ($ketuaId > 0) ? $ketuaId : $currUid;
            $update = [
                'status' => 'REVIEW_DALNIS',
                'tgl_reviu_ketua' => date('Y-m-d H:i:s'),
                'ketua_tim_id' => $assignedKetua,
            ];
            if ($catatan !== '') {
                $update['catatan_reviu_ketua'] = $catatan;
            }
            if (empty($sesi['direview_oleh'])) {
                $update['direview_oleh'] = $u['nama'] . (!empty($u['nip']) ? ' (NIP. '.$u['nip'].')' : '');
            }
            if (empty($sesi['tanggal_review'])) {
                $update['tanggal_review'] = date('Y-m-d');
            }
            DB::update('kka_sesi', $update, ['id' => $id]);
            flash('success', 'KKA disetujui Ketua Tim dan diteruskan ke Pengendali Teknis (Dalnis).');
        } elseif ($aksi === 'revisi') {
            if ($catatan === '') {
                flash('error', 'Harap isi catatan reviu/arahan revisi untuk auditor.');
                redirect('sesi/show?id=' . $id);
            }
            DB::update('kka_sesi', [
                'status' => 'PERLU_REVISI',
                'catatan_reviu_ketua' => $catatan,
                'ketua_tim_id' => ($ketuaId > 0) ? $ketuaId : $currUid,
            ], ['id' => $id]);
            flash('warning', 'KKA dikembalikan ke Auditor dengan catatan revisi.');
        }

        redirect('sesi/show?id=' . $id);
    }

    /** Reviu dan Pengesahan oleh Pengendali Teknis (Dalnis) */
    public function reviuDalnis(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        guard_sesi($this->auth, $sesi);

        $currUid   = (int) $this->auth->id();
        $currUser  = $this->auth->user();
        $currNama  = trim($currUser['nama'] ?? '');
        $currNip   = preg_replace('/\s+/', '', $currUser['nip'] ?? '');
        $isAdmin   = $this->auth->isAdmin();
        $dalnisId  = (int) ($sesi['dalnis_id'] ?? 0);
        $isMadya   = $this->auth->isDalnis();

        $matchDalnisName = ($currNama !== '' && !empty($sesi['dievaluasi_oleh']) && (
            stripos($sesi['dievaluasi_oleh'], $currNama) !== false ||
            stripos($currNama, trim($sesi['dievaluasi_oleh'])) !== false ||
            (!empty($currNip) && strpos(preg_replace('/\s+/', '', $sesi['dievaluasi_oleh']), $currNip) !== false)
        ));

        // Kunci wewenang Dalnis:
        // 1. Bukan admin harus memiliki kompetensi/role Dalnis (Auditor Madya) atau namanya tercantum di KKA
        if (!$isAdmin && !$isMadya && $dalnisId !== $currUid && !$matchDalnisName) {
            flash('error', 'Akses ditolak. Pengesahan KKA pada tahap Dalnis hanya dapat dilakukan oleh Auditor Madya / Pengendali Teknis (Dalnis).');
            redirect('sesi/show?id=' . $id);
        }
        // 2. Jika Dalnis sudah ditugaskan secara spesifik pada sesi ini (id > 0), harus Dalnis bersangkutan atau Admin
        if (!$isAdmin && $dalnisId > 0 && $dalnisId !== $currUid) {
            flash('error', 'Hanya Pengendali Teknis yang ditugaskan (' . e($sesi['dalnis_nama'] ?: ($sesi['dievaluasi_oleh'] ?: 'Dalnis')) . ') atau Administrator yang berhak mengesahkan KKA ini.');
            redirect('sesi/show?id=' . $id);
        }

        $aksi = (string) input('aksi');
        $catatan = trim((string) input('catatan_reviu', ''));

        if ($aksi === 'sahkan') {
            $u = $this->auth->user();
            $assignedDalnis = ($dalnisId > 0) ? $dalnisId : $currUid;
            $update = [
                'status' => 'SELESAI_FINAL',
                'tgl_reviu_dalnis' => date('Y-m-d H:i:s'),
                'dalnis_id' => $assignedDalnis,
            ];
            if ($catatan !== '') {
                $update['catatan_reviu_dalnis'] = $catatan;
            }
            if (empty($sesi['dievaluasi_oleh'])) {
                $update['dievaluasi_oleh'] = $u['nama'];
            }
            if (empty($sesi['tanggal_evaluasi'])) {
                $update['tanggal_evaluasi'] = date('Y-m-d');
            }
            DB::update('kka_sesi', $update, ['id' => $id]);
            flash('success', 'KKA berhasil disahkan oleh Pengendali Teknis (Dalnis). Dokumen KKA telah Sah/Final.');
        } elseif ($aksi === 'revisi') {
            if ($catatan === '') {
                flash('error', 'Harap isi catatan arahan perbaikan dari Dalnis.');
                redirect('sesi/show?id=' . $id);
            }
            DB::update('kka_sesi', [
                'status' => 'PERLU_REVISI',
                'catatan_reviu_dalnis' => $catatan,
                'dalnis_id' => ($dalnisId > 0) ? $dalnisId : $currUid,
            ], ['id' => $id]);
            flash('warning', 'KKA dikembalikan dengan catatan arahan perbaikan dari Dalnis.');
        }

        redirect('sesi/show?id=' . $id);
    }

    public function updateRoutingSlip(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        if (!$sesi) { flash('error', 'Sesi audit tidak ditemukan.'); redirect('sesi'); }
        if (!sesi_is_owned($this->auth, $sesi)) {
            flash('error', 'Anda tidak memiliki akses ke data audit ini.');
            redirect('sesi/show?id=' . $id);
        }

        $noSpt         = trim((string) input('no_spt'));
        $tglSpt        = input('tgl_spt') ?: null;
        $tglSptSelesai = input('tgl_spt_selesai') ?: null;
        $alamatObjek   = trim((string) input('alamat_objek'));
        $jenisAudit    = trim((string) input('jenis_audit'));
        $noLha         = trim((string) input('no_lha'));
        $tglLha        = input('tgl_lha') ?: null;
        $irbanNama     = trim((string) input('irban_nama'));
        $catatanDalnis = trim((string) input('catatan_reviu_dalnis'));
        $catatanIrban  = trim((string) input('catatan_reviu_irban'));

        $update = [
            'no_spt'               => $noSpt,
            'tgl_spt'              => $tglSpt,
            'tgl_spt_selesai'      => $tglSptSelesai,
            'alamat_objek'         => $alamatObjek,
            'jenis_audit'          => $jenisAudit,
            'no_lha'               => $noLha,
            'tgl_lha'              => $tglLha,
            'irban_nama'           => $irbanNama,
            'catatan_reviu_dalnis' => $catatanDalnis,
            'catatan_reviu_irban'  => $catatanIrban,
        ];

        if ($catatanDalnis !== '' && empty($sesi['tgl_reviu_dalnis'])) {
            $update['tgl_reviu_dalnis'] = date('Y-m-d H:i:s');
        }
        if ($catatanIrban !== '' && empty($sesi['tgl_reviu_irban'])) {
            $update['tgl_reviu_irban'] = date('Y-m-d H:i:s');
        }

        DB::update('kka_sesi', $update, ['id' => $id]);
        flash('success', 'Data & Lembar Catatan Routing Slip LHA berhasil disimpan.');
        redirect('sesi/show?id=' . $id);
    }

    public function delete(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesi = $this->loadSesi($id);
        if (!$sesi) { flash('error','Sesi tidak ditemukan.'); redirect('sesi'); }
        if (!$this->auth->isAdmin() && (int)$sesi['created_by'] !== $this->auth->id()) {
            flash('error', 'Hanya admin atau pembuat yang dapat menghapus sesi ini.');
            redirect('sesi');
        }
        // Hapus file lampiran fisik
        $lamps = DB::all('SELECT nama_file FROM kka_lampiran WHERE sesi_id = ?', [$id]);
        foreach ($lamps as $l) {
            $p = $GLOBALS['cfg']['upload_dir'] . '/' . $l['nama_file'];
            if (is_file($p)) @unlink($p);
        }
        DB::delete('kka_sesi', ['id' => $id]);
        flash('success', 'Sesi audit dihapus.');
        redirect('sesi');
    }

    public function subBidangJson(): void {
        $bid = (int) input('bidang_id');
        header('Content-Type: application/json');
        echo json_encode(DB::all('SELECT id, nama FROM kka_sub_bidang WHERE bidang_id = ? ORDER BY nama', [$bid]));
    }

    /** Daftar auditor aktif (selain diri sendiri) untuk pilihan berbagi. */
    private function auditorList(): array {
        return DB::all(
            "SELECT id, nama, jabatan FROM kka_users
             WHERE role = 'auditor' AND is_active = 1 AND id <> ?
             ORDER BY nama", [$this->auth->id()]);
    }

    /** Ganti total daftar auditor yang diberi akses ke sesi. */
    private function syncShares(int $sesiId, array $userIds): void {
        DB::q('DELETE FROM kka_sesi_share WHERE sesi_id = ?', [$sesiId]);
        $selfId = (int) $this->auth->id();
        $clean  = array_unique(array_filter(array_map('intval', $userIds)));
        foreach ($clean as $uid) {
            if ($uid === $selfId) continue; // pemilik tidak perlu di-share
            $ok = DB::scalar("SELECT 1 FROM kka_users WHERE id = ? AND is_active = 1 AND role = 'auditor' LIMIT 1", [$uid]);
            if ($ok) DB::q('INSERT INTO kka_sesi_share (sesi_id, user_id) VALUES (?, ?)', [$sesiId, $uid]);
        }
    }

    /** Daftar seluruh akun aktif (auditor & admin) untuk penunjukan Ketua Tim / Dalnis. */
    private function allUserList(): array {
        return DB::all("SELECT id, nama, nip, jabatan, role FROM kka_users WHERE is_active = 1 ORDER BY nama");
    }

    private function loadSesi(int $id): ?array {
        return DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama,
                   uk.nama AS ketua_nama, uk.nip AS ketua_nip,
                   ud.nama AS dalnis_nama, ud.nip AS dalnis_nip,
                   ui.nama AS irban_pejabat_nama, ui.nip AS irban_pejabat_nip, ui.jabatan AS irban_jabatan
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            LEFT JOIN kka_users uk ON uk.id = s.ketua_tim_id
            LEFT JOIN kka_users ud ON ud.id = s.dalnis_id
            LEFT JOIN kka_users ui ON ui.id = s.irban_id
            WHERE s.id = ?', [$id]);
    }
}
