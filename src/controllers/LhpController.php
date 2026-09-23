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
        if ($this->auth->isOperatorSpt()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Perencanaan (Operator SPT) tidak memiliki akses ke Laporan Hasil Pemeriksaan (LHP).');
            redirect('penugasan/spt');
        }
        if ($this->auth->isOperatorTl()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Tindak Lanjut (TLHP) difokuskan pada pemantauan hasil tindak lanjut rekomendasi (TLHP).');
            redirect('tlhp');
        }
    }

    public function index(): void {
        $defaultYear = (int) DB::val("SELECT MAX(tahun_anggaran) FROM kka_temuan") ?: (int)date('Y');
        $tahun = (int) input('tahun', $defaultYear);

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
                (SELECT COALESCE(SUM(t.nominal), 0) FROM kka_temuan t WHERE t.desa_id = d.id AND t.tahun_anggaran = ?) AS nominal_temuan,
                COALESCE(n.status_lhp, 'DRAFT') AS status_lhp,
                n.tgl_disahkan_inspektur,
                n.disahkan_oleh_nama,
                (CASE WHEN n.id IS NOT NULL THEN 1 ELSE 0 END) AS is_custom_narasi,
                (SELECT web_view_link FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'LHP_FINAL' AND g.desa_id = d.id AND g.tahun_anggaran = ? ORDER BY id DESC LIMIT 1) AS gdrive_lhp_link,
                (SELECT synced_at FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'LHP_FINAL' AND g.desa_id = d.id AND g.tahun_anggaran = ? ORDER BY id DESC LIMIT 1) AS gdrive_lhp_synced_at
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_sesi s ON s.desa_id = d.id AND s.tahun_anggaran = ?
            LEFT JOIN kka_spt spt ON spt.desa_id = d.id AND spt.tahun_anggaran = ?
            LEFT JOIN kka_lhp_narasi n ON n.desa_id = d.id AND n.tahun_anggaran = ?
            GROUP BY d.id, d.nama, k.nama, spt.id, spt.no_spt, spt.tgl_spt, spt.status, spt.wakil_pj_nama, spt.dalnis_nama, spt.ketua_tim_nama, n.id, n.status_lhp, n.tgl_disahkan_inspektur, n.disahkan_oleh_nama
            HAVING total_sesi > 0 OR spt_id IS NOT NULL
            ORDER BY k.nama ASC, d.nama ASC
        ", [$tahun, $tahun, $tahun, $tahun, $tahun, $tahun, $tahun, $tahun, $tahun]);

        view('lhp/index', compact('daftarLhp', 'tahun'));
    }

    /** Helper mengambil paket data lengkap untuk satu LHP Desa */
    public function getLhpData(int $desaId, int $tahun): ?array {
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

        // Narasi Kustomisasi (Bab I s.d IV)
        $narasi = DB::one("SELECT * FROM kka_lhp_narasi WHERE desa_id = ? AND tahun_anggaran = ?", [$desaId, $tahun]);

        $defaultRingkasan = "Berdasarkan Surat Perintah Tugas Inspektur Daerah Kabupaten Rokan Hilir Nomor: " . ($spt['no_spt'] ?? '...........................') . " tanggal " . (!empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '..............') . ", Tim Pemeriksa telah melakukan Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan Kepenghuluan " . $desa['nama'] . " Kecamatan " . $desa['kecamatan_nama'] . " Tahun Anggaran " . $tahun . ".\n\nDari hasil pengujian terhadap bukti pertanggungjawaban (SPJ) dan verifikasi fisik di lapangan atas realisasi belanja sebesar " . rupiah($totalRealisasi) . ", Tim Pengawasan mengidentifikasi " . count($daftarTemuan) . " butir Pokok Temuan Pemeriksaan dengan total nilai ketidaksesuaian/indikasi kerugian kas desa sebesar " . rupiah($totalNominalTemuan) . ".";

        $defaultDasar = "1. Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Kabupaten Rokan Hilir Tahun " . $tahun . ";\n2. Surat Perintah Tugas Inspektur Daerah Kabupaten Rokan Hilir Nomor: " . ($spt['no_spt'] ?? '-') . " tanggal " . (!empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '-') . ";\n3. Peraturan Perundang-undangan mengenai Pengelolaan Keuangan Desa di Kabupaten Rokan Hilir.";

        $defaultTujuan = $spt['tujuan'] ?? ("Memberikan keyakinan memadai atas ketaatan, efisiensi, dan efektivitas pengelolaan keuangan serta kepatuhan administrasi belanja Kepenghuluan " . $desa['nama'] . " Tahun Anggaran " . $tahun . ".");

        $defaultRuangLingkup = "Pengujian aspek keuangan tertentu, kepatuhan perpajakan belanja desa, dan opname fisik pekerjaan pembangunan desa Tahun Anggaran " . $tahun . ".";

        $defaultBatasan = "Pemeriksaan ini didasarkan pada dokumen pertanggungjawaban (SPJ) dan keterangan yang diserahkan oleh pihak auditi. Tanggung jawab kebenaran material dokumen sepenuhnya berada pada Pj. Penghulu dan Bendahara Pengeluaran.";

        $defaultGambaranUmum = "Realisasi pengeluaran kas belanja APBDesa Kepenghuluan " . $desa['nama'] . " Tahun Anggaran " . $tahun . " yang dilakukan uji petik adalah sebagai berikut:";

        $defaultKesimpulan = "Demikian Laporan Hasil Pengawasan (LHP) Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan Kepenghuluan " . $desa['nama'] . " Kecamatan " . $desa['kecamatan_nama'] . " ini disusun sebagai bahan evaluasi dan perbaikan tata kelola keuangan desa. Diharapkan Pj. Penghulu beserta jajaran segera menindaklanjuti rekomendasi yang termuat dalam laporan ini selambat-lambatnya 60 (enam puluh) hari kalender sejak laporan ini diterima.";

        $defaultSaranPenutup = "1. Pj. Penghulu memerintahkan Bendahara Pengeluaran menyetorkan kembali ketekoran kas/kelebihan bayar ke Rekening Kas Desa;\n2. Pj. Penghulu dan Tim Pelaksana Kegiatan (TPK) menyelesaikan kelengkapan bukti administrasi dan pertanggungjawaban fisik pekerjaan sesuai ketentuan.";

        $narasiFinal = [
            'ringkasan_eksekutif' => $narasi['ringkasan_eksekutif'] ?? $defaultRingkasan,
            'dasar_penugasan'     => $narasi['dasar_penugasan'] ?? $defaultDasar,
            'tujuan_pengawasan'   => $narasi['tujuan_pengawasan'] ?? $defaultTujuan,
            'ruang_lingkup'       => $narasi['ruang_lingkup'] ?? $defaultRuangLingkup,
            'batasan_pengawasan'  => $narasi['batasan_pengawasan'] ?? $defaultBatasan,
            'gambaran_umum'       => $narasi['gambaran_umum'] ?? $defaultGambaranUmum,
            'kesimpulan'          => $narasi['kesimpulan'] ?? $defaultKesimpulan,
            'saran_penutup'       => $narasi['saran_penutup'] ?? $defaultSaranPenutup,
            'is_customized'       => !empty($narasi),
        ];

        $aspekKeuangan = AspekKeuanganService::getAnalisisDesa($desaId, $tahun);

        return compact(
            'desa', 'spt', 'notaDinas', 'tahun', 'anggotaList', 'sesiList',
            'rekapBidang', 'rekapPajak', 'daftarTemuan', 'totalPagu',
            'totalRealisasi', 'totalKuitansi', 'totalSelisih', 'totalNominalTemuan',
            'inspektur', 'narasiFinal', 'narasi', 'aspekKeuangan'
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

    /**
     * Form Kustomisasi Narasi LHP (Bab I s.d IV)
     */
    public function edit(): void {
        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun', date('Y'));

        $data = $this->getLhpData($desaId, $tahun);
        if (!$data) {
            flash('error', 'Data Kepenghuluan tidak ditemukan.');
            redirect('lhp');
        }

        view('lhp/edit', $data);
    }

    /**
     * Simpan Perubahan Narasi LHP
     */
    public function update(): void {
        only_post();
        csrf_check();

        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun_anggaran', date('Y'));

        $ringkasanEksekutif = trim((string) input('ringkasan_eksekutif'));
        $dasarPenugasan     = trim((string) input('dasar_penugasan'));
        $tujuanPengawasan   = trim((string) input('tujuan_pengawasan'));
        $ruangLingkup       = trim((string) input('ruang_lingkup'));
        $batasanPengawasan  = trim((string) input('batasan_pengawasan'));
        $gambaranUmum       = trim((string) input('gambaran_umum'));
        $kesimpulan         = trim((string) input('kesimpulan'));
        $saranPenutup       = trim((string) input('saran_penutup'));
        $userId = $this->auth->user()['id'] ?? null;

        $existing = DB::one("SELECT id FROM kka_lhp_narasi WHERE desa_id = ? AND tahun_anggaran = ?", [$desaId, $tahun]);
        if ($existing) {
            DB::update('kka_lhp_narasi', [
                'ringkasan_eksekutif' => $ringkasanEksekutif,
                'dasar_penugasan'     => $dasarPenugasan,
                'tujuan_pengawasan'   => $tujuanPengawasan,
                'ruang_lingkup'       => $ruangLingkup,
                'batasan_pengawasan'  => $batasanPengawasan,
                'gambaran_umum'       => $gambaranUmum,
                'kesimpulan'          => $kesimpulan,
                'saran_penutup'       => $saranPenutup,
                'updated_by'          => $userId,
            ], ['id' => $existing['id']]);
        } else {
            DB::insert('kka_lhp_narasi', [
                'desa_id'             => $desaId,
                'tahun_anggaran'      => $tahun,
                'ringkasan_eksekutif' => $ringkasanEksekutif,
                'dasar_penugasan'     => $dasarPenugasan,
                'tujuan_pengawasan'   => $tujuanPengawasan,
                'ruang_lingkup'       => $ruangLingkup,
                'batasan_pengawasan'  => $batasanPengawasan,
                'gambaran_umum'       => $gambaranUmum,
                'kesimpulan'          => $kesimpulan,
                'saran_penutup'       => $saranPenutup,
                'updated_by'          => $userId,
            ]);
        }

        flash('success', 'Narasi dan kalimat naskah LHP Kepenghuluan ' . $desaId . ' berhasil diperbarui.');
        redirect('lhp/show?desa_id=' . $desaId . '&tahun=' . $tahun);
    }

    /**
     * Pengesahan LHP 1-Klik oleh Inspektur Daerah
     */
    public function sahkan(): void {
        only_post();
        csrf_check();

        if (!$this->auth->isInspektur() && !$this->auth->isAdmin()) {
            http_response_code(403);
            exit('Hanya Inspektur Daerah atau Administrator yang berwenang mengesahkan LHP.');
        }

        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun_anggaran', date('Y'));
        $u      = $this->auth->user();
        $now    = date('Y-m-d H:i:s');

        $existing = DB::one("SELECT id FROM kka_lhp_narasi WHERE desa_id = ? AND tahun_anggaran = ?", [$desaId, $tahun]);
        if ($existing) {
            DB::update('kka_lhp_narasi', [
                'status_lhp'              => 'DISAHKAN_INSPEKTUR',
                'tgl_disahkan_inspektur'  => $now,
                'disahkan_oleh_nama'      => $u['nama'],
                'updated_by'              => $u['id'] ?? null,
            ], ['id' => $existing['id']]);
        } else {
            DB::insert('kka_lhp_narasi', [
                'desa_id'                 => $desaId,
                'tahun_anggaran'          => $tahun,
                'status_lhp'              => 'DISAHKAN_INSPEKTUR',
                'tgl_disahkan_inspektur'  => $now,
                'disahkan_oleh_nama'      => $u['nama'],
                'updated_by'              => $u['id'] ?? null,
            ]);
        }

        $desaNama = DB::val("SELECT nama FROM kka_desa WHERE id = ?", [$desaId]) ?: 'Desa';
        flash('success', 'Laporan Hasil Pengawasan (LHP) Kepenghuluan ' . $desaNama . ' TA ' . $tahun . ' telah resmi DISAHKAN oleh Inspektur Daerah pada ' . tgl_id($now) . ' pukul ' . date('H:i', strtotime($now)) . ' WIB. Notifikasi telah dikirimkan ke meja Ketua Tim untuk pencetakan naskah fisik.');
        redirect('lhp/show?desa_id=' . $desaId . '&tahun=' . $tahun);
    }

    /**
     * Ajukan LHP ke Jenjang Berikutnya (Ketua Tim -> Dalnis -> Irban -> Inspektur)
     */
    public function ajukan(): void {
        only_post();
        csrf_check();

        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun_anggaran', date('Y'));
        $tahap  = (string) input('tahap');
        $u      = $this->auth->user();

        $statusMap = [
            'dalnis'    => 'REVIU_DALNIS',
            'irban'     => 'TELAAH_IRBAN',
        ];
        $targetStatus = $statusMap[$tahap] ?? 'REVIU_DALNIS';

        $existing = DB::one("SELECT id FROM kka_lhp_narasi WHERE desa_id = ? AND tahun_anggaran = ?", [$desaId, $tahun]);
        if ($existing) {
            DB::update('kka_lhp_narasi', [
                'status_lhp' => $targetStatus,
                'updated_by' => $u['id'] ?? null,
            ], ['id' => $existing['id']]);
        } else {
            DB::insert('kka_lhp_narasi', [
                'desa_id'        => $desaId,
                'tahun_anggaran' => $tahun,
                'status_lhp'     => $targetStatus,
                'updated_by'     => $u['id'] ?? null,
            ]);
        }

        $labelMap = [
            'dalnis' => 'Pengendali Teknis (Dalnis)',
            'irban'  => 'Inspektur Pembantu (Irban)',
        ];
        flash('success', 'Naskah LHP & Routing Slip berhasil diajukan ke ' . ($labelMap[$tahap] ?? 'tahap berikutnya') . '.');
        redirect('lhp/show?desa_id=' . $desaId . '&tahun=' . $tahun);
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
