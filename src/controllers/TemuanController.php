<?php
declare(strict_types=1);

/**
 * Controller Konsep Temuan Pemeriksaan (KTP 5 Unsur BPKP/SPKN)
 * Inspektorat Kabupaten Rokan Hilir
 */
class TemuanController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $auth->require();
        if ($this->auth->isOperatorSpt()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Perencanaan (Operator SPT) tidak memiliki akses ke Konsep Temuan Pemeriksaan (KTP).');
            redirect('penugasan/spt');
        }
        if ($this->auth->isOperatorTl()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Tindak Lanjut (TLHP) difokuskan pada pemantauan hasil tindak lanjut rekomendasi (TLHP).');
            redirect('tlhp');
        }
    }

    public function index(): void {
        $desaId = (int) input('desa_id', 0);
        $tahun  = (int) input('tahun', date('Y'));
        $status = trim((string) input('status', ''));

        $where = 'WHERE 1=1';
        $params = [];

        if ($desaId > 0) {
            $where .= ' AND t.desa_id = ?';
            $params[] = $desaId;
        }
        if ($tahun > 0) {
            $where .= ' AND t.tahun_anggaran = ?';
            $params[] = $tahun;
        }
        if ($status !== '' && in_array($status, ['DRAFT', 'DIBAHAS', 'FINAL_LHP'])) {
            $where .= ' AND t.status = ?';
            $params[] = $status;
        }

        $daftarTemuan = DB::all("
            SELECT t.*, d.nama AS desa_nama, k.nama AS kecamatan_nama, u.nama AS pembuat_nama
            FROM kka_temuan t
            JOIN kka_desa d ON d.id = t.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_users u ON u.id = t.created_by
            $where
            ORDER BY t.desa_id ASC, t.tahun_anggaran DESC, t.id ASC
        ", $params);

        $daftarDesa = DB::all("SELECT d.id, d.nama, k.nama AS kecamatan FROM kka_desa d JOIN kka_kecamatan k ON k.id = d.kecamatan_id ORDER BY k.nama ASC, d.nama ASC");
        
        $totalNominal = 0;
        foreach ($daftarTemuan as $t) {
            $totalNominal += (float)$t['nominal'];
        }

        view('temuan/index', compact('daftarTemuan', 'daftarDesa', 'desaId', 'tahun', 'status', 'totalNominal'));
    }

    public function create(): void {
        $sesiId    = (int) input('sesi_id', 0);
        $rincianId = (int) input('rincian_id', 0);
        $desaId    = (int) input('desa_id', 0);
        $tahun     = (int) input('tahun', date('Y'));

        $rincian = null;
        $sesi    = null;
        $desa    = null;
        $autoKondisi = '';
        $autoKriteria = '';
        $autoSebab = '';
        $autoAkibat = '';
        $autoRekomendasi = '';
        $autoNominal = 0;
        $autoJudul = '';
        $bidangNama = '';

        if ($rincianId > 0) {
            $rincian = DB::one("
                SELECT r.*, s.desa_id, s.tahun_anggaran, s.objek_audit, b.nama AS bidang_nama, d.nama AS desa_nama, k.nama AS kecamatan_nama
                FROM kka_rincian r
                JOIN kka_sesi s ON s.id = r.sesi_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                WHERE r.id = ?
            ", [$rincianId]);

            if ($rincian) {
                $desaId = (int)$rincian['desa_id'];
                $tahun = (int)$rincian['tahun_anggaran'];
                $sesiId = (int)$rincian['sesi_id'];
                $bidangNama = (string)$rincian['bidang_nama'];

                $selisih = (float)$rincian['realisasi'] - (float)$rincian['biaya_dikwitansi'];

                // Kasus 1: Pajak Belum Disetor
                if (($rincian['status_pajak'] ?? '') === 'BELUM_SETOR') {
                    $totPajak = (float)$rincian['nominal_ppn'] + (float)$rincian['nominal_pph'];
                    $autoNominal = $totPajak > 0 ? $totPajak : (float)$rincian['realisasi'];
                    $autoJudul = 'Pajak Belanja Atas ' . $rincian['uraian'] . ' Belum Disetor ke Kas Negara';
                    $autoKondisi = "Berdasarkan hasil uji petik kepatuhan perpajakan atas Belanja {$rincian['uraian']} pada Kepenghuluan {$rincian['desa_nama']}, ditemukan bahwa kewajiban pajak (PPN/PPh) sebesar Rp " . number_format($autoNominal, 0, ',', '.') . " telah dipungut namun belum disetorkan ke Kas Negara dan tidak memiliki bukti NTPN yang sah.";
                    $autoKriteria = "1. Undang-Undang Nomor 7 Tahun 2021 tentang Harmonisasi Peraturan Perpajakan (HPP);\n2. Peraturan Menteri Keuangan tentang Tata Cara Pemotongan, Pemungutan, dan Penyetoran Pajak oleh Instansi Pemerintah Desa;\n3. Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa.";
                    $autoSebab = "Kaur Keuangan/Bendahara Desa lalai dan menunda penyetoran pajak yang telah dipungut dari rekanan/penyedia jasa belanja.";
                    $autoAkibat = "Penerimaan Kas Negara dari sektor perpajakan tertunda dan terdapat potensi sanksi denda keterlambatan bagi Kepenghuluan {$rincian['desa_nama']}.";
                    $autoRekomendasi = "1. Menginstruksikan Pj. Penghulu memberikan teguran kepada Kaur Keuangan/Bendahara;\n2. Memerintahkan Bendahara untuk segera membuat kode billing dan menyetorkan seluruh tunggakan pajak sebesar Rp " . number_format($autoNominal, 0, ',', '.') . " ke Kas Negara serta menyampaikan bukti NTPN ke Tim Audit Inspektorat.";
                }
                // Kasus 2: Selisih Realisasi vs Kuitansi (Kekurangan SPJ)
                elseif ($selisih < 0) {
                    $autoNominal = abs($selisih);
                    $autoJudul = 'Realisasi Belanja ' . $rincian['uraian'] . ' Sebesar Rp ' . number_format($autoNominal, 0, ',', '.') . ' Tidak Didukung Kuitansi Sah';
                    $autoKondisi = "Hasil pemeriksaan dokumen pertanggungjawaban (SPJ) pada Kepenghuluan {$rincian['desa_nama']} untuk kegiatan {$rincian['uraian']} menunjukkan realisasi anggaran sebesar Rp " . number_format((float)$rincian['realisasi'], 0, ',', '.') . ", namun bukti kuitansi yang dapat diverifikasi hanya sebesar Rp " . number_format((float)$rincian['biaya_dikwitansi'], 0, ',', '.') . ", sehingga terdapat selisih sebesar Rp " . number_format($autoNominal, 0, ',', '.') . " yang belum dipertanggungjawabkan.";
                    $autoKriteria = "Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa, Pasal 51 ayat (1) menyatakan bahwa setiap pengeluaran belanja atas beban APBDesa harus didukung dengan bukti yang lengkap dan sah.";
                    $autoSebab = "1. Kaur Keuangan/Pelaksana Kegiatan Anggaran (PKA) lalai mengarsipkan bukti belanja secara tertib;\n2. Pj. Penghulu kurang optimal dalam memverifikasi bukti pengeluaran sebelum menyetujui SPP/pembayaran.";
                    $autoAkibat = "Berindikasi menimbulkan potensi kerugian keuangan Kepenghuluan {$rincian['desa_nama']} sebesar Rp " . number_format($autoNominal, 0, ',', '.') . ".";
                    $autoRekomendasi = "1. Menginstruksikan Pelaksana Kegiatan dan Bendahara untuk melengkapi bukti kuitansi sah dalam batas waktu yang ditentukan;\n2. Apabila bukti sah tidak dapat dipenuhi, memerintahkan yang bersangkutan menyetorkan kembali dana sebesar Rp " . number_format($autoNominal, 0, ',', '.') . " ke Rekening Kas Desa (RKD).";
                }
                // Kasus 3: Umum
                else {
                    $autoNominal = (float)$rincian['realisasi'];
                    $autoJudul = 'Ketidaksesuaian Pertanggungjawaban Belanja ' . $rincian['uraian'];
                    $autoKondisi = "Pemeriksaan atas Belanja {$rincian['uraian']} pada Kepenghuluan {$rincian['desa_nama']} menemukan ketidaksesuaian administrasi/fisik.";
                    $autoKriteria = "Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa.";
                    $autoSebab = "Kurang cermatnya aparatur Kepenghuluan dalam melaksanakan ketentuan pengelolaan keuangan desa.";
                    $autoAkibat = "Pengelolaan keuangan desa menjadi kurang tertib dan akuntabel.";
                    $autoRekomendasi = "Memerintahkan Pj. Penghulu untuk memedomani regulasi dan memperbaiki administrasi belanja.";
                }
            }
        } elseif ($sesiId > 0) {
            $sesi = DB::one("SELECT s.*, b.nama AS bidang_nama, d.nama AS desa_nama FROM kka_sesi s JOIN kka_bidang b ON b.id = s.bidang_id JOIN kka_desa d ON d.id = s.desa_id WHERE s.id = ?", [$sesiId]);
            if ($sesi) {
                $desaId = (int)$sesi['desa_id'];
                $tahun = (int)$sesi['tahun_anggaran'];
                $bidangNama = (string)$sesi['bidang_nama'];
            }
        }

        $daftarDesa = DB::all("SELECT d.id, d.nama, k.nama AS kecamatan FROM kka_desa d JOIN kka_kecamatan k ON k.id = d.kecamatan_id ORDER BY k.nama ASC, d.nama ASC");
        
        // Auto nomor temuan: hitung temuan yang sudah ada untuk desa & tahun tsb
        $countTemuan = (int) DB::scalar("SELECT COUNT(*) FROM kka_temuan WHERE desa_id = ? AND tahun_anggaran = ?", [$desaId ?: 1, $tahun]);
        $nomorTemuan = sprintf("KTP-%02d", $countTemuan + 1);

        view('temuan/create', compact(
            'daftarDesa', 'desaId', 'tahun', 'sesiId', 'rincianId', 'nomorTemuan',
            'autoJudul', 'bidangNama', 'autoNominal', 'autoKondisi', 'autoKriteria',
            'autoSebab', 'autoAkibat', 'autoRekomendasi'
        ));
    }

    public function store(): void {
        only_post(); csrf_check();

        $desaId      = (int) input('desa_id');
        $tahun       = (int) input('tahun_anggaran', date('Y'));
        $nomorTemuan = trim((string) input('nomor_temuan'));
        $judul       = trim((string) input('judul'));
        $nominal     = parse_money(input('nominal', 0));
        $bidangNama  = trim((string) input('bidang_nama')) ?: null;
        $sesiId      = (int) input('sesi_id') ?: null;
        $rincianId   = (int) input('rincian_id') ?: null;
        $status      = trim((string) input('status', 'DRAFT'));

        $kondisi     = trim((string) input('kondisi'));
        $kriteria    = trim((string) input('kriteria'));
        $sebab       = trim((string) input('sebab'));
        $akibat      = trim((string) input('akibat'));
        $rekomendasi = trim((string) input('rekomendasi'));
        $tanggapan   = trim((string) input('tanggapan_auditi')) ?: null;

        if (!$desaId || $judul === '' || $kondisi === '' || $rekomendasi === '') {
            flash('error', 'Desa, Judul Temuan, Kondisi, dan Rekomendasi wajib diisi.');
            redirect('temuan/create?desa_id=' . $desaId . '&tahun=' . $tahun);
        }

        if ($nomorTemuan === '') {
            $count = (int) DB::scalar("SELECT COUNT(*) FROM kka_temuan WHERE desa_id = ? AND tahun_anggaran = ?", [$desaId, $tahun]);
            $nomorTemuan = sprintf("KTP-%02d", $count + 1);
        }

        // Ambil spt_id jika ada
        $sptId = (int) DB::scalar("SELECT id FROM kka_spt WHERE desa_id = ? AND tahun_anggaran = ? ORDER BY id DESC LIMIT 1", [$desaId, $tahun]) ?: null;

        DB::insert('kka_temuan', [
            'desa_id'         => $desaId,
            'tahun_anggaran'  => $tahun,
            'spt_id'          => $sptId,
            'sesi_id'         => $sesiId,
            'rincian_id'      => $rincianId,
            'nomor_temuan'    => $nomorTemuan,
            'judul'           => $judul,
            'bidang_nama'     => $bidangNama,
            'nominal'         => $nominal,
            'kondisi'         => $kondisi,
            'kriteria'        => $kriteria,
            'sebab'           => $sebab,
            'akibat'          => $akibat,
            'rekomendasi'     => $rekomendasi,
            'tanggapan_auditi'=> $tanggapan,
            'status'          => in_array($status, ['DRAFT', 'DIBAHAS', 'FINAL_LHP']) ? $status : 'DRAFT',
            'created_by'      => $this->auth->id(),
        ]);

        flash('success', "Konsep Temuan {$nomorTemuan} berhasil disimpan.");
        redirect('temuan?desa_id=' . $desaId . '&tahun=' . $tahun);
    }

    public function edit(): void {
        $id = (int) input('id');
        $temuan = DB::one("
            SELECT t.*, d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_temuan t
            JOIN kka_desa d ON d.id = t.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            WHERE t.id = ?
        ", [$id]);

        if (!$temuan) {
            flash('error', 'Temuan tidak ditemukan.');
            redirect('temuan');
        }

        $daftarDesa = DB::all("SELECT d.id, d.nama, k.nama AS kecamatan FROM kka_desa d JOIN kka_kecamatan k ON k.id = d.kecamatan_id ORDER BY k.nama ASC, d.nama ASC");

        view('temuan/edit', compact('temuan', 'daftarDesa'));
    }

    public function update(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $temuan = DB::one("SELECT * FROM kka_temuan WHERE id = ?", [$id]);
        if (!$temuan) {
            flash('error', 'Temuan tidak ditemukan.');
            redirect('temuan');
        }

        $desaId      = (int) input('desa_id', $temuan['desa_id']);
        $tahun       = (int) input('tahun_anggaran', $temuan['tahun_anggaran']);
        $nomorTemuan = trim((string) input('nomor_temuan', $temuan['nomor_temuan']));
        $judul       = trim((string) input('judul'));
        $nominal     = parse_money(input('nominal', 0));
        $bidangNama  = trim((string) input('bidang_nama')) ?: null;
        $status      = trim((string) input('status', 'DRAFT'));

        $kondisi     = trim((string) input('kondisi'));
        $kriteria    = trim((string) input('kriteria'));
        $sebab       = trim((string) input('sebab'));
        $akibat      = trim((string) input('akibat'));
        $rekomendasi = trim((string) input('rekomendasi'));
        $tanggapan   = trim((string) input('tanggapan_auditi')) ?: null;

        if ($judul === '' || $kondisi === '' || $rekomendasi === '') {
            flash('error', 'Judul Temuan, Kondisi, dan Rekomendasi wajib diisi.');
            redirect('temuan/edit?id=' . $id);
        }

        DB::update('kka_temuan', [
            'desa_id'         => $desaId,
            'tahun_anggaran'  => $tahun,
            'nomor_temuan'    => $nomorTemuan,
            'judul'           => $judul,
            'bidang_nama'     => $bidangNama,
            'nominal'         => $nominal,
            'kondisi'         => $kondisi,
            'kriteria'        => $kriteria,
            'sebab'           => $sebab,
            'akibat'          => $akibat,
            'rekomendasi'     => $rekomendasi,
            'tanggapan_auditi'=> $tanggapan,
            'status'          => in_array($status, ['DRAFT', 'DIBAHAS', 'FINAL_LHP']) ? $status : 'DRAFT',
        ], ['id' => $id]);

        flash('success', "Konsep Temuan {$nomorTemuan} diperbarui.");
        redirect('temuan?desa_id=' . $desaId . '&tahun=' . $tahun);
    }

    public function delete(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $temuan = DB::one("SELECT desa_id, tahun_anggaran, nomor_temuan FROM kka_temuan WHERE id = ?", [$id]);
        if (!$temuan) {
            flash('error', 'Temuan tidak ditemukan.');
            redirect('temuan');
        }

        DB::delete('kka_temuan', ['id' => $id]);
        flash('success', "Konsep Temuan {$temuan['nomor_temuan']} dihapus.");
        redirect('temuan?desa_id=' . $temuan['desa_id'] . '&tahun=' . $temuan['tahun_anggaran']);
    }

    public function matriks(): void {
        $desaId = (int) input('desa_id');
        $tahun  = (int) input('tahun', date('Y'));

        $desa = DB::one("SELECT d.*, k.nama AS kecamatan_nama FROM kka_desa d JOIN kka_kecamatan k ON k.id = d.kecamatan_id WHERE d.id = ?", [$desaId]);
        if (!$desa) {
            flash('error', 'Pilih desa untuk mencetak matriks temuan.');
            redirect('temuan');
        }

        $spt = DB::one("SELECT * FROM kka_spt WHERE desa_id = ? AND tahun_anggaran = ? ORDER BY id DESC LIMIT 1", [$desaId, $tahun]);

        $daftarTemuan = DB::all("
            SELECT * FROM kka_temuan 
            WHERE desa_id = ? AND tahun_anggaran = ?
            ORDER BY id ASC
        ", [$desaId, $tahun]);

        view('print/matriks_temuan', compact('desa', 'tahun', 'spt', 'daftarTemuan'));
    }
}
