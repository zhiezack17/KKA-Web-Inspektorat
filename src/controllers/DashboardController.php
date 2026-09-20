<?php
class DashboardController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function index(): void {
        // Isolasi data: auditor hanya melihat statistik miliknya, admin melihat semua
        [$ow, $op] = owner_where($this->auth);

        $stats = [
            'desa'             => (int) DB::scalar('SELECT COUNT(*) FROM kka_desa'),
            'kec'              => (int) DB::scalar('SELECT COUNT(*) FROM kka_kecamatan'),
            'sesi'             => (int) DB::scalar("SELECT COUNT(*) FROM kka_sesi s WHERE 1=1 $ow", $op),
            'sesi_ty'          => (int) DB::scalar("SELECT COUNT(*) FROM kka_sesi s WHERE s.tahun_anggaran = ? $ow", array_merge([(int)date('Y')], $op)),
            'anggaran'         => (float) DB::scalar("SELECT COALESCE(SUM(s.pagu_anggaran),0) FROM kka_sesi s WHERE 1=1 $ow", $op),
            'dikwitansi'       => (float) DB::scalar("SELECT COALESCE(SUM(r.biaya_dikwitansi),0) FROM kka_rincian r JOIN kka_sesi s ON s.id = r.sesi_id WHERE 1=1 $ow", $op),
            'realisasi'        => (float) DB::scalar("SELECT COALESCE(SUM(r.realisasi),0) FROM kka_rincian r JOIN kka_sesi s ON s.id = r.sesi_id WHERE 1=1 $ow", $op),
            'nd_total'         => (int) DB::scalar("SELECT COUNT(*) FROM kka_nota_dinas"),
            'spt_total'        => (int) DB::scalar("SELECT COUNT(*) FROM kka_spt WHERE status = 'DITERBITKAN'"),
            'temuan_total'     => (int) DB::scalar("SELECT COUNT(*) FROM kka_temuan"),
            'temuan_nominal'   => (float) DB::scalar("SELECT COALESCE(SUM(nominal),0) FROM kka_temuan"),
            'pajak_sudah_setor'=> (float) DB::scalar("SELECT COALESCE(SUM(nominal_ppn + nominal_pph),0) FROM kka_rincian WHERE status_pajak = 'SUDAH_SETOR'"),
            'pajak_belum_setor'=> (float) DB::scalar("SELECT COALESCE(SUM(nominal_ppn + nominal_pph),0) FROM kka_rincian WHERE status_pajak = 'BELUM_SETOR'"),
        ];
        $stats['selisih'] = max(0, $stats['dikwitansi'] - $stats['realisasi']);
        $stats['spt_sample_id'] = (int) DB::scalar("SELECT id FROM kka_spt ORDER BY id DESC LIMIT 1");
        $lhpSample = DB::row("SELECT desa_id, tahun_anggaran FROM kka_temuan WHERE status = 'FINAL_LHP' LIMIT 1");
        if (!$lhpSample) {
            $lhpSample = DB::row("SELECT desa_id, tahun_anggaran FROM kka_sesi ORDER BY id DESC LIMIT 1");
        }
        $stats['sample_desa_id'] = $lhpSample['desa_id'] ?? 69;
        $stats['sample_tahun'] = $lhpSample['tahun_anggaran'] ?? (int)date('Y');

        // Pipeline Alur Pengawasan (Pra-Audit s/d LHP)
        $pipeline = [
            'nd_diajukan'   => (int) DB::scalar("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DIAJUKAN_INSPEKTUR'"),
            'nd_disetujui'  => (int) DB::scalar("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DISETUJUI'"),
            'spt_aktif'     => (int) DB::scalar("SELECT COUNT(*) FROM kka_spt WHERE status = 'DITERBITKAN'"),
            'sesi_kka'      => (int) DB::scalar("SELECT COUNT(*) FROM kka_sesi"),
            'temuan_final'  => (int) DB::scalar("SELECT COUNT(*) FROM kka_temuan WHERE status = 'FINAL_LHP'"),
            'lhp_desa'      => (int) DB::scalar("SELECT COUNT(DISTINCT desa_id) FROM kka_temuan WHERE status = 'FINAL_LHP'"),
        ];

        // Ringkasan per DESA yang diaudit (dilengkapi statistik temuan dan selisih belanja)
        $perDesa = DB::all("
            SELECT d.id, d.nama AS desa, k.nama AS kecamatan,
                   COUNT(s.id)                         AS jumlah,
                   COALESCE(SUM(s.pagu_anggaran),0)    AS pagu,
                   COALESCE((SELECT t.tahun_anggaran FROM kka_temuan t WHERE t.desa_id = d.id ORDER BY t.tahun_anggaran DESC LIMIT 1), MAX(s.tahun_anggaran)) AS tahun_terakhir,
                   (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = d.id) AS jml_temuan,
                   (SELECT COALESCE(SUM(t.nominal),0) FROM kka_temuan t WHERE t.desa_id = d.id) AS nominal_temuan,
                   (SELECT COALESCE(SUM(CASE WHEN (r.biaya_dikwitansi - r.realisasi) > 0 THEN (r.biaya_dikwitansi - r.realisasi) ELSE 0 END),0)
                    FROM kka_rincian r JOIN kka_sesi s2 ON s2.id = r.sesi_id WHERE s2.desa_id = d.id) AS selisih_fisik
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_sesi s ON s.desa_id = d.id
            WHERE 1=1 $ow
            GROUP BY d.id, d.nama, k.nama
            ORDER BY jml_temuan DESC, selisih_fisik DESC, jumlah DESC, d.nama ASC
        ", $op);

        $perBidang = DB::all("
            SELECT b.nama, COUNT(s.id) AS jumlah
            FROM kka_bidang b
            LEFT JOIN kka_sesi s ON s.bidang_id = b.id $ow
            GROUP BY b.id, b.nama ORDER BY b.urutan
        ", $op);

        view('dashboard/index', compact('stats','pipeline','perDesa','perBidang'));
    }

    public function workflow(): void {
        view('panduan/workflow');
    }
}
