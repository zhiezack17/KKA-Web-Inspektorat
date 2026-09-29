<?php
declare(strict_types=1);

/**
 * Controller Pemantauan Tindak Lanjut Hasil Pengawasan (TLHP 60 Hari)
 * Inspektorat Kabupaten Rokan Hilir
 * Standar: Pemantauan Tindak Lanjut APIP, Countdown 60 Hari & Rekap Pemulihan Kas Desa
 */
class TlhpController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $auth->require();
    }

    /**
     * Halaman Utama Pemantauan Tindak Lanjut (TLHP) & Rekap Kerugian
     */
    public function index(): void {
        $defaultYear = (int) DB::val("SELECT MAX(tahun_anggaran) FROM kka_tindak_lanjut");
        if (!$defaultYear) {
            $defaultYear = (int) DB::val("SELECT MAX(tahun_anggaran) FROM kka_temuan") ?: (int)date('Y');
        }
        $tahun  = (int) input('tahun', $defaultYear);
        $desaId = (int) input('desa_id', 0);

        // Auto-sinkronisasi temuan baru ke kka_tindak_lanjut
        $this->syncTemuanToTlhp($tahun);

        $params = [$tahun];
        $whereDesa = '';
        if ($desaId > 0) {
            $whereDesa = ' AND tl.desa_id = ?';
            $params[] = $desaId;
        }

        $list = DB::all("
            SELECT 
                tl.*,
                t.nomor_temuan, t.judul AS temuan_judul, t.nominal AS temuan_nominal,
                t.kondisi AS temuan_kondisi, t.rekomendasi AS temuan_rekomendasi,
                d.nama AS desa_nama, k.nama AS kecamatan_nama,
                u.nama AS verifikator_nama,
                DATEDIFF(tl.batas_waktu_tl, CURRENT_DATE) AS sisa_hari
            FROM kka_tindak_lanjut tl
            JOIN kka_temuan t ON t.id = tl.temuan_id
            JOIN kka_desa d ON d.id = tl.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_users u ON u.id = tl.diverifikasi_oleh
            WHERE tl.tahun_anggaran = ? $whereDesa
            ORDER BY tl.status ASC, tl.batas_waktu_tl ASC, tl.id ASC
        ", $params);

        // Rekapitulasi Statistik Eksekutif (Standar BPKP: S, BS, BD, TDTD)
        $summary = DB::one("
            SELECT 
                COUNT(*) AS total_rekomendasi,
                COALESCE(SUM(nominal_rekomendasi), 0) AS sum_rekomendasi,
                COALESCE(SUM(nominal_disetor), 0) AS sum_disetor,
                COALESCE(SUM(sisa_kerugian), 0) AS sum_sisa,
                COUNT(CASE WHEN status IN ('S', 'TUNTAS') THEN 1 END) AS count_s,
                COUNT(CASE WHEN status IN ('BS', 'PROSES') THEN 1 END) AS count_bs,
                COUNT(CASE WHEN status IN ('BD', 'BELUM') THEN 1 END) AS count_bd,
                COUNT(CASE WHEN status = 'TDTD' THEN 1 END) AS count_tdtd,
                COUNT(CASE WHEN status NOT IN ('S', 'TUNTAS', 'TDTD') AND DATEDIFF(batas_waktu_tl, CURRENT_DATE) < 0 THEN 1 END) AS count_terlambat
            FROM kka_tindak_lanjut tl
            WHERE tl.tahun_anggaran = ? $whereDesa
        ", $params);

        // Rekapitulasi Komprehensif Antar Kepenghuluan se-Kabupaten
        $rekapDesa = DB::all("
            SELECT 
                d.id AS desa_id,
                d.nama AS desa_nama,
                k.nama AS kecamatan_nama,
                (SELECT COUNT(*) FROM kka_sesi s WHERE s.desa_id = d.id AND s.tahun_anggaran = ?) AS total_sesi_kka,
                (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS total_temuan,
                (SELECT COALESCE(SUM(t.nominal), 0) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS sum_temuan,
                COUNT(tl.id) AS total_rekomendasi,
                COALESCE(SUM(tl.nominal_rekomendasi), 0) AS sum_rekomendasi,
                COALESCE(SUM(tl.nominal_disetor), 0) AS sum_disetor,
                COALESCE(SUM(tl.sisa_kerugian), 0) AS sum_sisa,
                COUNT(CASE WHEN tl.status IN ('S', 'TUNTAS') THEN 1 END) AS count_s,
                COUNT(CASE WHEN tl.status IN ('BS', 'PROSES') THEN 1 END) AS count_bs,
                COUNT(CASE WHEN tl.status IN ('BD', 'BELUM') THEN 1 END) AS count_bd,
                COUNT(CASE WHEN tl.status = 'TDTD' THEN 1 END) AS count_tdtd
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_tindak_lanjut tl ON tl.desa_id = d.id AND tl.tahun_anggaran = ?
            GROUP BY d.id, d.nama, k.nama
            HAVING (total_sesi_kka > 0 OR total_temuan > 0 OR total_rekomendasi > 0)
            ORDER BY k.nama ASC, d.nama ASC
        ", [$tahun, $tahun, $tahun, $tahun]);

        $desaList = DB::all("
            SELECT d.id, d.nama, k.nama AS kecamatan_nama
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY k.nama ASC, d.nama ASC
        ");

        $totalRekomendasi = (float)($summary['sum_rekomendasi'] ?? 0);
        $totalDisetor     = (float)($summary['sum_disetor'] ?? 0);
        $persenPulih      = $totalRekomendasi > 0 ? round(($totalDisetor / $totalRekomendasi) * 100, 1) : 100.0;

        view('tlhp/index', compact('list', 'summary', 'rekapDesa', 'desaList', 'tahun', 'desaId', 'persenPulih'));
    }

    /**
     * Auto-sinkronisasi kka_temuan ke kka_tindak_lanjut jika belum ada
     */
    private function syncTemuanToTlhp(int $tahun): void {
        $temuanList = DB::all("
            SELECT t.*, spt.tgl_spt
            FROM kka_temuan t
            LEFT JOIN kka_spt spt ON spt.id = t.spt_id
            WHERE t.tahun_anggaran = ?
        ", [$tahun]);

        foreach ($temuanList as $t) {
            $exists = DB::one("SELECT id FROM kka_tindak_lanjut WHERE temuan_id = ?", [$t['id']]);
            if (!$exists) {
                // Tentukan tanggal LHP default (tgl_spt + 14 hari atau tanggal dibuatnya temuan)
                $tglLhp = !empty($t['tgl_spt']) ? date('Y-m-d', strtotime($t['tgl_spt'] . ' +14 days')) : date('Y-m-d', strtotime($t['created_at']));
                // Batas waktu 60 hari kalender
                $batasWaktu = date('Y-m-d', strtotime($tglLhp . ' +60 days'));
                $nominal = (float)$t['nominal'];

                DB::insert('kka_tindak_lanjut', [
                    'temuan_id'           => $t['id'],
                    'desa_id'             => $t['desa_id'],
                    'tahun_anggaran'      => $t['tahun_anggaran'],
                    'status'              => 'BD',
                    'tgl_lhp'             => $tglLhp,
                    'batas_waktu_tl'      => $batasWaktu,
                    'rekomendasi_teks'    => $t['rekomendasi'],
                    'nominal_rekomendasi' => $nominal,
                    'nominal_disetor'     => 0.00,
                    'sisa_kerugian'       => $nominal,
                ]);
            }
        }
    }

    /**
     * Update Progres Tindak Lanjut & Validasi Bukti Setor
     */
    public function update(): void {
        only_post();
        csrf_check();

        $id                  = (int) input('id');
        $status              = trim((string) input('status', 'BD'));
        if ($status === 'TUNTAS') $status = 'S';
        if ($status === 'PROSES') $status = 'BS';
        if ($status === 'BELUM')  $status = 'BD';

        $uraianTindakLanjut  = trim((string) input('uraian_tindak_lanjut'));
        $nominalDisetor      = parse_money(input('nominal_disetor', 0));
        $noBuktiSetor        = trim((string) input('no_bukti_setor'));
        $tglSetor            = trim((string) input('tgl_setor')) ?: null;
        $verifikasiApip      = trim((string) input('verifikasi_apip', 'BELUM_VERIFIKASI'));
        $catatanApip         = trim((string) input('catatan_apip'));

        $row = DB::one("SELECT * FROM kka_tindak_lanjut WHERE id = ?", [$id]);
        if (!$row) {
            flash('error', 'Data Tindak Lanjut tidak ditemukan.');
            redirect('tlhp');
        }

        $nominalRekomendasi = (float)$row['nominal_rekomendasi'];
        $sisaKerugian = max(0.0, $nominalRekomendasi - $nominalDisetor);

        // Auto S jika nominal pulih 100% atau verifikasi sesuai
        if (($nominalRekomendasi > 0 && $sisaKerugian <= 0 && $verifikasiApip === 'SESUAI') || ($nominalRekomendasi == 0 && $verifikasiApip === 'SESUAI')) {
            $status = 'S';
        }

        $userId = $this->auth->user()['id'] ?? null;
        $tglVerif = in_array($verifikasiApip, ['SESUAI', 'BELUM_SESUAI', 'TDTD']) ? date('Y-m-d H:i:s') : null;

        DB::update('kka_tindak_lanjut', [
            'status'              => $status,
            'uraian_tindak_lanjut'=> $uraianTindakLanjut ?: null,
            'nominal_disetor'     => $nominalDisetor,
            'sisa_kerugian'       => $sisaKerugian,
            'no_bukti_setor'      => $noBuktiSetor ?: null,
            'tgl_setor'           => $tglSetor,
            'verifikasi_apip'     => $verifikasiApip,
            'catatan_apip'        => $catatanApip ?: null,
            'diverifikasi_oleh'   => $tglVerif ? $userId : $row['diverifikasi_oleh'],
            'tgl_verifikasi'      => $tglVerif ?: $row['tgl_verifikasi'],
        ], ['id' => $id]);

        flash('success', 'Data pemantauan tindak lanjut berhasil disimpan.');
        redirect('tlhp?tahun=' . $row['tahun_anggaran'] . '&desa_id=' . $row['desa_id']);
    }

    /**
     * Cetak Matriks Pemantauan Tindak Lanjut Hasil Pengawasan (Standar Kendali Mutu APIP A4 Landscape)
     */
    public function matriks(): void {
        $tahun  = (int) input('tahun', date('Y'));
        $desaId = (int) input('desa_id', 0);

        $params = [$tahun];
        $whereDesa = '';
        if ($desaId > 0) {
            $whereDesa = ' AND tl.desa_id = ?';
            $params[] = $desaId;
        }

        $list = DB::all("
            SELECT 
                tl.*,
                t.nomor_temuan, t.judul AS temuan_judul, t.nominal AS temuan_nominal,
                t.kondisi AS temuan_kondisi, t.rekomendasi AS temuan_rekomendasi,
                d.nama AS desa_nama, k.nama AS kecamatan_nama,
                u.nama AS verifikator_nama,
                DATEDIFF(tl.batas_waktu_tl, CURRENT_DATE) AS sisa_hari
            FROM kka_tindak_lanjut tl
            JOIN kka_temuan t ON t.id = tl.temuan_id
            JOIN kka_desa d ON d.id = tl.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_users u ON u.id = tl.diverifikasi_oleh
            WHERE tl.tahun_anggaran = ? $whereDesa
            ORDER BY d.nama ASC, tl.id ASC
        ", $params);

        $desa = null;
        if ($desaId > 0) {
            $desa = DB::one("SELECT d.*, k.nama AS kecamatan_nama FROM kka_desa d JOIN kka_kecamatan k ON k.id = d.kecamatan_id WHERE d.id = ?", [$desaId]);
        }

        $inspektur = DB::one("SELECT * FROM kka_users WHERE role = 'inspektur' OR username = 'inspektur' LIMIT 1") ?: [
            'nama' => 'H. SARMAN SYAHRONI, ST., M.IP., CGCAE',
            'nip'  => '19760810 200312 1 004',
        ];

        view('print/matriks_tlhp', compact('list', 'desa', 'tahun', 'inspektur'));
    }

    /**
     * Cetak Rekapitulasi Hasil Pengawasan & Pemantauan TLHP Se-Kabupaten Rokan Hilir (A4 Landscape)
     */
    public function rekap(): void {
        $tahun = (int) input('tahun', date('Y'));
        
        $this->syncTemuanToTlhp($tahun);

        $rekapDesa = DB::all("
            SELECT 
                d.id AS desa_id,
                d.nama AS desa_nama,
                k.nama AS kecamatan_nama,
                (SELECT COUNT(*) FROM kka_sesi s WHERE s.desa_id = d.id AND s.tahun_anggaran = ?) AS total_sesi_kka,
                (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS total_temuan,
                (SELECT COALESCE(SUM(t.nominal), 0) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS sum_temuan,
                COUNT(tl.id) AS total_rekomendasi,
                COALESCE(SUM(tl.nominal_rekomendasi), 0) AS sum_rekomendasi,
                COALESCE(SUM(tl.nominal_disetor), 0) AS sum_disetor,
                COALESCE(SUM(tl.sisa_kerugian), 0) AS sum_sisa,
                COUNT(CASE WHEN tl.status IN ('S', 'TUNTAS') THEN 1 END) AS count_s,
                COUNT(CASE WHEN tl.status IN ('BS', 'PROSES') THEN 1 END) AS count_bs,
                COUNT(CASE WHEN tl.status IN ('BD', 'BELUM') THEN 1 END) AS count_bd,
                COUNT(CASE WHEN tl.status = 'TDTD' THEN 1 END) AS count_tdtd
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_tindak_lanjut tl ON tl.desa_id = d.id AND tl.tahun_anggaran = ?
            GROUP BY d.id, d.nama, k.nama
            HAVING (total_sesi_kka > 0 OR total_temuan > 0 OR total_rekomendasi > 0)
            ORDER BY k.nama ASC, d.nama ASC
        ", [$tahun, $tahun, $tahun, $tahun]);

        $inspektur = DB::one("SELECT * FROM kka_users WHERE role = 'inspektur' OR username = 'inspektur' LIMIT 1") ?: [
            'nama' => 'H. SARMAN SYAHRONI, ST., M.IP., CGCAE',
            'nip'  => '19760810 200312 1 004',
        ];

        view('print/rekap_pengawasan', compact('rekapDesa', 'tahun', 'inspektur'));
    }
}
