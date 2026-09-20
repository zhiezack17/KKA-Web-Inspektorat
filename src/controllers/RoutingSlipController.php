<?php
declare(strict_types=1);

/**
 * Controller Routing Slip Kendali Mutu LHA / LHP Desa (Model Simondes)
 * Inspektorat Kabupaten Rokan Hilir
 * Standar: Reviu Berjenjang Ketua Tim -> Dalnis -> Irban -> Inspektur
 */
class RoutingSlipController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $auth->require();
        if ($this->auth->isOperatorSpt() || $this->auth->isOperatorTl()) {
            flash('warning', 'Akses dibatasi: Peran Anda tidak mengelola Routing Slip Kendali Mutu.');
            redirect('dashboard');
        }
    }

    /**
     * Daftar Register Routing Slip per Desa
     */
    public function index(): void {
        $defaultYear = (int) DB::val("SELECT MAX(tahun_anggaran) FROM kka_temuan") ?: (int)date('Y');
        $tahun = (int) input('tahun', $defaultYear);

        $daftar = DB::all("
            SELECT 
                d.id AS desa_id, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                spt.id AS spt_id, spt.no_spt, spt.tgl_spt, spt.status AS spt_status,
                spt.wakil_pj_nama, spt.dalnis_nama, spt.ketua_tim_nama,
                COUNT(DISTINCT s.id) AS total_sesi,
                COUNT(DISTINCT CASE WHEN s.status = 'SELESAI_FINAL' THEN s.id END) AS sesi_final,
                COALESCE((SELECT n.status_lhp FROM kka_lhp_narasi n WHERE n.desa_id = d.id AND n.tahun_anggaran = ? LIMIT 1), 'DRAFT') AS status_lhp,
                (SELECT n.tgl_disahkan_inspektur FROM kka_lhp_narasi n WHERE n.desa_id = d.id AND n.tahun_anggaran = ? LIMIT 1) AS tgl_disahkan_inspektur,
                (SELECT n.disahkan_oleh_nama FROM kka_lhp_narasi n WHERE n.desa_id = d.id AND n.tahun_anggaran = ? LIMIT 1) AS disahkan_oleh_nama,
                (SELECT n.catatan_dalnis FROM kka_lhp_narasi n WHERE n.desa_id = d.id AND n.tahun_anggaran = ? LIMIT 1) AS catatan_dalnis,
                (SELECT n.tgl_reviu_dalnis FROM kka_lhp_narasi n WHERE n.desa_id = d.id AND n.tahun_anggaran = ? LIMIT 1) AS tgl_reviu_dalnis,
                (SELECT n.catatan_irban FROM kka_lhp_narasi n WHERE n.desa_id = d.id AND n.tahun_anggaran = ? LIMIT 1) AS catatan_irban,
                (SELECT n.tgl_reviu_irban FROM kka_lhp_narasi n WHERE n.desa_id = d.id AND n.tahun_anggaran = ? LIMIT 1) AS tgl_reviu_irban
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_sesi s ON s.desa_id = d.id AND s.tahun_anggaran = ?
            LEFT JOIN kka_spt spt ON spt.desa_id = d.id AND spt.tahun_anggaran = ?
            GROUP BY d.id, d.nama, k.nama, spt.id, spt.no_spt, spt.tgl_spt, spt.status, spt.wakil_pj_nama, spt.dalnis_nama, spt.ketua_tim_nama
            HAVING total_sesi > 0 OR spt_id IS NOT NULL
            ORDER BY k.nama ASC, d.nama ASC
        ", [$tahun, $tahun, $tahun, $tahun, $tahun, $tahun, $tahun, $tahun, $tahun]);

        view('routing_slip/index', compact('daftar', 'tahun'));
    }

    /**
     * Detail & Lembar Catatan Reviu (Review Sheet) Routing Slip Desa
     */
    public function show(): void {
        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun', date('Y'));

        $lhpCtrl = new LhpController($this->auth);
        $data = $lhpCtrl->getLhpData($desaId, $tahun);
        if (!$data) {
            flash('error', 'Data Kepenghuluan tidak ditemukan.');
            redirect('routing-slip');
        }

        view('routing_slip/show', $data);
    }

    /**
     * Simpan Catatan Reviu Dalnis & Irban di Routing Slip
     */
    public function update(): void {
        only_post();
        csrf_check();

        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun_anggaran', date('Y'));
        $u      = $this->auth->user();
        $now    = date('Y-m-d H:i:s');

        $catatanDalnis = trim((string) input('catatan_dalnis'));
        $catatanIrban  = trim((string) input('catatan_irban'));

        $existing = DB::one("SELECT * FROM kka_lhp_narasi WHERE desa_id = ? AND tahun_anggaran = ?", [$desaId, $tahun]);

        $update = [
            'updated_by' => $u['id'] ?? null,
        ];

        // Jika user adalah Dalnis atau Admin dan mengisi catatan Dalnis
        if ($this->auth->isDalnis() || $this->auth->isAdmin()) {
            $update['catatan_dalnis'] = $catatanDalnis;
            if ($catatanDalnis !== '') {
                $update['tgl_reviu_dalnis'] = $now;
                $update['dalnis_nama'] = $u['nama'];
            }
        }

        // Jika user adalah Irban atau Admin dan mengisi catatan Irban
        if ($this->auth->isIrban() || $this->auth->isAdmin()) {
            $update['catatan_irban'] = $catatanIrban;
            if ($catatanIrban !== '') {
                $update['tgl_reviu_irban'] = $now;
                $update['irban_nama'] = $u['nama'];
            }
        }

        if ($existing) {
            DB::update('kka_lhp_narasi', $update, ['id' => $existing['id']]);
        } else {
            $update['desa_id'] = $desaId;
            $update['tahun_anggaran'] = $tahun;
            $update['status_lhp'] = 'DRAFT';
            DB::insert('kka_lhp_narasi', $update);
        }

        flash('success', 'Catatan arahan reviu pada Lembar Routing Slip berhasil disimpan.');
        redirect('routing-slip/show?desa_id=' . $desaId . '&tahun=' . $tahun);
    }

    /**
     * Cetak Lembar Resmi Routing Slip (Map Kuning F4)
     */
    public function print(): void {
        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun', date('Y'));

        // Jika dipanggil dengan 'id' (id sesi kka lama)
        $id = (int) input('id');
        if ($id > 0 && empty($desaId)) {
            $sesiRow = DB::one("SELECT desa_id, tahun_anggaran FROM kka_sesi WHERE id = ?", [$id]);
            if ($sesiRow) {
                $desaId = (int)$sesiRow['desa_id'];
                $tahun  = (int)$sesiRow['tahun_anggaran'];
            }
        }

        $lhpCtrl = new LhpController($this->auth);
        $data = $lhpCtrl->getLhpData($desaId, $tahun);
        if (!$data) {
            exit('Data Routing Slip tidak ditemukan.');
        }

        // Siapkan struktur sesi untuk backward-compatibility dengan template print/routing_slip.php
        $desa = $data['desa'];
        $spt  = $data['spt'] ?? [];
        $narasi = $data['narasi'] ?? [];

        // Ambil sesi pertama jika ada
        $firstSesi = $data['sesiList'][0] ?? [];

        $sesi = [
            'id'                   => $firstSesi['id'] ?? 0,
            'desa_id'              => $desa['id'],
            'desa_nama'            => $desa['nama'],
            'kecamatan_nama'       => $desa['kecamatan_nama'],
            'objek_audit'          => 'Kepenghuluan ' . $desa['nama'],
            'alamat_objek'         => 'Kepenghuluan ' . $desa['nama'] . ', Kec. ' . $desa['kecamatan_nama'],
            'semester'             => $firstSesi['semester'] ?? 1,
            'tahun_anggaran'       => $tahun,
            'jenis_audit'          => $spt['jenis_audit'] ?? 'Audit Dengan Tujuan Tertentu (ADTT)',
            'no_spt'               => $spt['no_spt'] ?? ($firstSesi['no_spt'] ?? ''),
            'tgl_spt'              => $spt['tgl_spt'] ?? ($firstSesi['tgl_spt'] ?? null),
            'tgl_spt_selesai'      => $spt['tgl_selesai'] ?? ($firstSesi['tgl_spt_selesai'] ?? null),
            'no_lha'               => $spt['no_lha'] ?? ('700/LHA-INSP/' . $tahun . '/' . sprintf('%03d', $desa['id'])),
            'tgl_lha'              => $narasi['tgl_disahkan_inspektur'] ?? null,
            'status'               => ($narasi['status_lhp'] ?? '') === 'DISAHKAN_INSPEKTUR' ? 'SELESAI_FINAL' : 'DRAFT',
            'updated_at'           => $narasi['tgl_disahkan_inspektur'] ?? date('Y-m-d H:i:s'),
            'tanggal_dibuat'       => $spt['tgl_spt'] ?? date('Y-m-d'),
            'tanggal_review'       => $firstSesi['tanggal_review'] ?? null,
            'tanggal_evaluasi'     => $firstSesi['tanggal_evaluasi'] ?? null,
            'tgl_reviu_ketua'      => $firstSesi['tgl_reviu_ketua'] ?? null,
            'tgl_reviu_dalnis'     => $narasi['tgl_reviu_dalnis'] ?? ($firstSesi['tgl_reviu_dalnis'] ?? null),
            'tgl_reviu_irban'      => $narasi['tgl_reviu_irban'] ?? ($firstSesi['tgl_reviu_irban'] ?? null),
            'catatan_reviu_dalnis' => $narasi['catatan_dalnis'] ?? ($firstSesi['catatan_reviu_dalnis'] ?? ''),
            'catatan_reviu_irban'  => $narasi['catatan_irban'] ?? ($firstSesi['catatan_reviu_irban'] ?? ''),
            'irban_nama'           => $spt['wakil_pj_nama'] ?? ($firstSesi['irban_nama'] ?? 'MARWAN, M.T'),
            'dievaluasi_oleh'      => $spt['dalnis_nama'] ?? ($firstSesi['dievaluasi_oleh'] ?? ''),
            'direview_oleh'        => $spt['ketua_tim_nama'] ?? ($firstSesi['direview_oleh'] ?? ''),
            'dibuat_oleh'          => $data['anggotaList'][0]['nama'] ?? ($firstSesi['dibuat_oleh'] ?? ''),
        ];

        $creatorUser = ['nama' => $sesi['dibuat_oleh'], 'nip' => $data['anggotaList'][0]['nip'] ?? ''];
        $ketuaUser   = ['nama' => $spt['ketua_tim_nama'] ?? '', 'nip' => $spt['ketua_tim_nip'] ?? ''];
        $dalnisUser  = ['nama' => $spt['dalnis_nama'] ?? '', 'nip' => $spt['dalnis_nip'] ?? ''];
        $irbanUser   = ['nama' => $spt['wakil_pj_nama'] ?? 'MARWAN, M.T', 'nip' => $spt['wakil_pj_nip'] ?? '19770727 200212 1 005'];
        $sharedWith  = $data['anggotaList'] ?? [];

        view('print/routing_slip', compact('sesi', 'creatorUser', 'ketuaUser', 'dalnisUser', 'irbanUser', 'sharedWith'));
    }
}
