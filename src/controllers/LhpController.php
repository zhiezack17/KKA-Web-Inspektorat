<?php
declare(strict_types=1);

/**
 * Controller Laporan Hasil Pengawasan (LHP) Otomatis Desa
 * Inspektorat Kabupaten Rokan Hilir
 * Standar: Naskah Dinas Pengawasan APIP & Permenpan RB
 */
class LhpController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $auth->require();
    }

    public function index(): void {
        $tahun = (int) input('tahun', date('Y'));

        // Ambil desa-desa yang memiliki sesi audit atau SPT
        $daftarLhp = DB::all("
            SELECT 
                d.id AS desa_id, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                spt.id AS spt_id, spt.no_spt, spt.tgl_spt, spt.status AS spt_status,
                spt.wakil_pj_nama, spt.dalnis_nama, spt.ketua_tim_nama,
                COUNT(DISTINCT s.id) AS total_sesi,
                COUNT(DISTINCT CASE WHEN s.status = 'SELESAI_FINAL' THEN s.id END) AS sesi_final,
                COALESCE(SUM(s.pagu_anggaran), 0) AS total_pagu,
                COALESCE((SELECT SUM(r.realisasi) FROM kka_rincian r JOIN kka_sesi s2 ON s2.id = r.sesi_id WHERE s2.desa_id = d.id AND s2.tahun_anggaran = ?), 0) AS total_realisasi,
                COALESCE((SELECT SUM(r.biaya_dikwitansi) FROM kka_rincian r JOIN kka_sesi s2 ON s2.id = r.sesi_id WHERE s2.desa_id = d.id AND s2.tahun_anggaran = ?), 0) AS total_kuitansi,
                (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS total_temuan,
                (SELECT COALESCE(SUM(t.nominal), 0) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS nominal_temuan
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_sesi s ON s.desa_id = d.id AND s.tahun_anggaran = ?
            LEFT JOIN kka_spt spt ON spt.desa_id = d.id AND spt.tahun_anggaran = ?
            GROUP BY d.id, d.nama, k.nama, spt.id, spt.no_spt, spt.tgl_spt, spt.status, spt.wakil_pj_nama, spt.dalnis_nama, spt.ketua_tim_nama
            HAVING total_sesi > 0 OR spt_id IS NOT NULL
            ORDER BY k.nama ASC, d.nama ASC
        ", [$tahun, $tahun, $tahun, $tahun, $tahun, $tahun]);

        view('lhp/index', compact('daftarLhp', 'tahun'));
    }

    /** Helper mengambil paket data lengkap untuk satu LHP Desa */
    private function getLhpData(int $desaId, int $tahun): ?array {
        $desa = DB::one("
            SELECT d.*, k.nama AS kecamatan_nama 
            FROM kka_desa d 
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id 
            WHERE d.id = ?
        ", [$desaId]);

        if (!$desa) return null;

        $spt = DB::one("
            SELECT * FROM kka_spt 
            WHERE desa_id = ? AND tahun_anggaran = ? 
            ORDER BY id DESC LIMIT 1
        ", [$desaId, $tahun]);

        $notaDinas = null;
        if ($spt && !empty($spt['nota_dinas_id'])) {
            $notaDinas = DB::one("SELECT * FROM kka_nota_dinas WHERE id = ?", [$spt['nota_dinas_id']]);
        }

        // Susunan Tim
        $anggotaList = [];
        if ($spt && !empty($spt['anggota_data'])) {
            $anggotaList = json_decode($spt['anggota_data'], true) ?: [];
        }

        // Sesi audit belanja & rekapitulasi per bidang
        $sesiList = DB::all("
            SELECT s.*, b.nama AS bidang_nama, b.urutan AS bidang_urutan,
                   COALESCE(SUM(r.realisasi), 0) AS tot_realisasi,
                   COALESCE(SUM(r.biaya_dikwitansi), 0) AS tot_kuitansi
            FROM kka_sesi s
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_rincian r ON r.sesi_id = s.id
            WHERE s.desa_id = ? AND s.tahun_anggaran = ?
            GROUP BY s.id, b.nama, b.urutan
            ORDER BY b.urutan ASC, s.id ASC
        ", [$desaId, $tahun]);

        // Rekapitulasi per Bidang 1 s.d 5
        $rekapBidang = DB::all("
            SELECT b.id, b.nama, b.urutan,
                   COALESCE(SUM(s.pagu_anggaran), 0) AS pagu,
                   COALESCE(SUM(r.realisasi), 0) AS realisasi,
                   COALESCE(SUM(r.biaya_dikwitansi), 0) AS kuitansi
            FROM kka_bidang b
            LEFT JOIN kka_sesi s ON s.bidang_id = b.id AND s.desa_id = ? AND s.tahun_anggaran = ?
            LEFT JOIN kka_rincian r ON r.sesi_id = s.id
            GROUP BY b.id, b.nama, b.urutan
            ORDER BY b.urutan ASC
        ", [$desaId, $tahun]);

        // Rekapitulasi Perpajakan Belanja Desa
        $rekapPajak = DB::one("
            SELECT 
                COALESCE(SUM(CASE WHEN r.potong_ppn = 1 THEN r.nominal_ppn ELSE 0 END), 0) AS total_ppn,
                COALESCE(SUM(CASE WHEN r.potong_pph IS NOT NULL AND r.potong_pph != '' THEN r.nominal_pph ELSE 0 END), 0) AS total_pph,
                COALESCE(SUM(CASE WHEN r.status_pajak = 'SUDAH_SETOR' THEN (r.nominal_ppn + r.nominal_pph) ELSE 0 END), 0) AS pajak_disetor,
                COALESCE(SUM(CASE WHEN r.status_pajak = 'BELUM_SETOR' THEN (r.nominal_ppn + r.nominal_pph) ELSE 0 END), 0) AS pajak_belum_setor
            FROM kka_rincian r
            JOIN kka_sesi s ON s.id = r.sesi_id
            WHERE s.desa_id = ? AND s.tahun_anggaran = ?
        ", [$desaId, $tahun]);

        // Daftar Temuan KTP 5 Unsur
        $daftarTemuan = DB::all("
            SELECT * FROM kka_temuan 
            WHERE desa_id = ? AND tahun_anggaran = ?
            ORDER BY id ASC
        ", [$desaId, $tahun]);

        $totalPagu = 0;
        $totalRealisasi = 0;
        $totalKuitansi = 0;
        foreach ($sesiList as $s) {
            $totalPagu += (float)$s['pagu_anggaran'];
            $totalRealisasi += (float)$s['tot_realisasi'];
            $totalKuitansi += (float)$s['tot_kuitansi'];
        }
        $totalSelisih = $totalRealisasi - $totalKuitansi;

        $totalNominalTemuan = 0;
        foreach ($daftarTemuan as $t) {
            $totalNominalTemuan += (float)$t['nominal'];
        }

        // Inspektur Resmi
        $inspektur = DB::one("SELECT * FROM kka_users WHERE role = 'inspektur' OR username = 'inspektur' LIMIT 1") ?: [
            'nama' => 'H. SARMAN SYAHRONI, ST., M.IP., CGCAE',
            'nip' => '19760810 200312 1 004',
            'jabatan' => 'Inspektur Daerah Kabupaten Rokan Hilir'
        ];

        return compact(
            'desa', 'spt', 'notaDinas', 'tahun', 'anggotaList', 'sesiList',
            'rekapBidang', 'rekapPajak', 'daftarTemuan', 'totalPagu',
            'totalRealisasi', 'totalKuitansi', 'totalSelisih', 'totalNominalTemuan',
            'inspektur'
        );
    }

    public function show(): void {
        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun', date('Y'));

        $data = $this->getLhpData($desaId, $tahun);
        if (!$data) {
            flash('error', 'Data Kepenghuluan tidak ditemukan.');
            redirect('lhp');
        }

        view('lhp/show', $data);
    }

    public function print(): void {
        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun', date('Y'));

        $data = $this->getLhpData($desaId, $tahun);
        if (!$data) {
            exit('Data LHP tidak ditemukan.');
        }

        view('print/lhp', $data);
    }
}
