<?php
declare(strict_types=1);

/**
 * Controller Berita Acara Pemeriksaan Kas (Opname Kas Desa)
 * Inspektorat Kabupaten Rokan Hilir
 * Standar: Formulir Kendali Mutu APIP & Keputusan Bupati (Benchmark Simondes)
 */
class OpnameKasController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $auth->require();
        if ($this->auth->isOperatorSpt()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Perencanaan (Operator SPT) tidak memiliki akses ke Pemeriksaan Kas (Opname).');
            redirect('penugasan/spt');
        }
        if ($this->auth->isOperatorTl()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Tindak Lanjut (TLHP) tidak memiliki akses ke Pemeriksaan Kas (Opname).');
            redirect('tlhp');
        }
    }

    /**
     * Daftar Berita Acara Opname Kas per Desa
     */
    public function index(): void {
        $tahun  = (int) input('tahun', date('Y'));
        $desaId = (int) input('desa_id', 0);

        $params = [$tahun];
        $whereDesa = '';
        if ($desaId > 0) {
            $whereDesa = ' AND o.desa_id = ?';
            $params[] = $desaId;
        }

        $list = DB::all("
            SELECT o.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   spt.no_spt,
                   (SELECT web_view_link FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'OPNAME_KAS' AND g.ref_id = o.id ORDER BY g.id DESC LIMIT 1) AS gdrive_link,
                   (SELECT synced_at FROM kka_gdrive_sync g WHERE g.tipe_dokumen = 'OPNAME_KAS' AND g.ref_id = o.id ORDER BY g.id DESC LIMIT 1) AS gdrive_synced_at
            FROM kka_opname_kas o
            JOIN kka_desa d ON d.id = o.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_spt spt ON spt.id = o.spt_id
            WHERE o.tahun_anggaran = ? $whereDesa
            ORDER BY o.tgl_pemeriksaan DESC, o.id DESC
        ", $params);

        // Rekap ringkasan statistik
        $summary = DB::one("
            SELECT 
                COUNT(*) AS total_opname,
                COALESCE(SUM(total_kas_fisik), 0) AS sum_fisik,
                COALESCE(SUM(saldo_bank), 0) AS sum_bank,
                COALESCE(SUM(total_kas_riil), 0) AS sum_riil,
                COALESCE(SUM(saldo_bku), 0) AS sum_bku,
                COALESCE(SUM(selisih_kas), 0) AS sum_selisih,
                COUNT(CASE WHEN status_selisih = 'COCOK' THEN 1 END) AS count_cocok,
                COUNT(CASE WHEN status_selisih = 'KURANG' THEN 1 END) AS count_kurang,
                COUNT(CASE WHEN status_selisih = 'LEBIH' THEN 1 END) AS count_lebih
            FROM kka_opname_kas o
            WHERE o.tahun_anggaran = ? $whereDesa
        ", $params);

        $desaList = DB::all("
            SELECT d.id, d.nama, k.nama AS kecamatan_nama
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY k.nama ASC, d.nama ASC
        ");

        view('opname_kas/index', compact('list', 'summary', 'desaList', 'tahun', 'desaId'));
    }

    /**
     * Form Buat Berita Acara Opname Kas Baru
     */
    public function create(): void {
        $tahun  = (int) input('tahun', date('Y'));
        $desaId = (int) input('desa_id', 0);

        $desaList = DB::all("
            SELECT d.id, d.nama, k.nama AS kecamatan_nama
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY k.nama ASC, d.nama ASC
        ");

        // Cari SPT terkait desa (jika ada) untuk auto-fill ketua tim & anggota
        $spt = null;
        if ($desaId > 0) {
            $spt = DB::one("
                SELECT * FROM kka_spt 
                WHERE desa_id = ? AND tahun_anggaran = ? 
                ORDER BY id DESC LIMIT 1
            ", [$desaId, $tahun]);
        }

        // Generate nomor BAP default: 700/BAP-KAS/INSP/2025/001
        $countThisYear = (int) DB::val("SELECT COUNT(*) FROM kka_opname_kas WHERE tahun_anggaran = ?", [$tahun]) + 1;
        $defaultNoBap = sprintf("700/BAP-KAS/INSP/%d/%03d", $tahun, $countThisYear);

        view('opname_kas/create', compact('desaList', 'tahun', 'desaId', 'spt', 'defaultNoBap'));
    }

    /**
     * Simpan Berita Acara Opname Kas Baru
     */
    public function store(): void {
        only_post();
        csrf_check();

        $desaId          = (int) input('desa_id');
        $tahun           = (int) input('tahun_anggaran', date('Y'));
        $sptId           = (int) input('spt_id') ?: null;
        $noBap           = trim((string) input('no_bap'));
        $tglPemeriksaan  = trim((string) input('tgl_pemeriksaan')) ?: date('Y-m-d');
        $waktuPemeriksaan = trim((string) input('waktu_pemeriksaan')) ?: '09.30 WIB';
        $tempatPemeriksaan = trim((string) input('tempat_pemeriksaan')) ?: 'Kantor Kepenghuluan';

        $namaBendahara   = trim((string) input('nama_bendahara'));
        $nipBendahara    = trim((string) input('nip_bendahara'));
        $namaKepalaDesa  = trim((string) input('nama_kepala_desa'));
        $namaKetuaTim    = trim((string) input('nama_ketua_tim'));
        $nipKetuaTim     = trim((string) input('nip_ketua_tim'));
        $namaAnggota     = trim((string) input('nama_anggota'));

        // Pecahan Uang Kertas
        $kertasPecahan = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
        $rincianKertas = [];
        $totalKertas = 0.0;
        foreach ($kertasPecahan as $p) {
            $lembar = (int) input('kertas_' . $p, 0);
            $subtotal = $lembar * $p;
            $rincianKertas[$p] = [
                'lembar'   => $lembar,
                'subtotal' => $subtotal
            ];
            $totalKertas += $subtotal;
        }

        // Pecahan Uang Logam
        $logamPecahan = [1000, 500, 200, 100];
        $rincianLogam = [];
        $totalLogam = 0.0;
        foreach ($logamPecahan as $p) {
            $keping = (int) input('logam_' . $p, 0);
            $subtotal = $keping * $p;
            $rincianLogam[$p] = [
                'keping'   => $keping,
                'subtotal' => $subtotal
            ];
            $totalLogam += $subtotal;
        }

        $totalKasFisik = $totalKertas + $totalLogam;
        $saldoBank     = parse_money(input('saldo_bank', 0));
        $namaBank      = trim((string) input('nama_bank')) ?: 'Bank Riau Kepri Syariah';
        $noRekBank     = trim((string) input('no_rekening_bank'));
        $totalKasRiil  = $totalKasFisik + $saldoBank;
        $saldoBku      = parse_money(input('saldo_bku', 0));
        $selisihKas    = $totalKasRiil - $saldoBku;

        if (abs($selisihKas) < 0.01) {
            $statusSelisih = 'COCOK';
        } elseif ($selisihKas > 0) {
            $statusSelisih = 'LEBIH';
        } else {
            $statusSelisih = 'KURANG';
        }

        $penjelasanSelisih = trim((string) input('penjelasan_selisih'));
        $catatanPemeriksaan = trim((string) input('catatan_pemeriksaan'));
        $userId = $this->auth->user()['id'] ?? null;

        if ($desaId <= 0 || empty($noBap) || empty($namaBendahara) || empty($namaKetuaTim)) {
            flash('error', 'Kepenghuluan, Nomor BAP, Nama Bendahara, dan Nama Ketua Tim wajib diisi.');
            redirect('opname-kas/create?desa_id=' . $desaId . '&tahun=' . $tahun);
        }

        DB::insert('kka_opname_kas', [
            'desa_id'              => $desaId,
            'tahun_anggaran'       => $tahun,
            'spt_id'               => $sptId,
            'no_bap'               => $noBap,
            'tgl_pemeriksaan'      => $tglPemeriksaan,
            'waktu_pemeriksaan'    => $waktuPemeriksaan,
            'tempat_pemeriksaan'   => $tempatPemeriksaan,
            'nama_bendahara'       => $namaBendahara,
            'nip_bendahara'        => $nipBendahara ?: null,
            'nama_kepala_desa'     => $namaKepalaDesa,
            'nama_ketua_tim'       => $namaKetuaTim,
            'nip_ketua_tim'        => $nipKetuaTim ?: null,
            'nama_anggota'         => $namaAnggota ?: null,
            'rincian_uang_kertas'  => json_encode($rincianKertas),
            'total_kertas'         => $totalKertas,
            'rincian_uang_logam'   => json_encode($rincianLogam),
            'total_logam'          => $totalLogam,
            'total_kas_fisik'      => $totalKasFisik,
            'saldo_bank'           => $saldoBank,
            'nama_bank'            => $namaBank,
            'no_rekening_bank'     => $noRekBank ?: null,
            'total_kas_riil'       => $totalKasRiil,
            'saldo_bku'            => $saldoBku,
            'selisih_kas'          => $selisihKas,
            'status_selisih'       => $statusSelisih,
            'penjelasan_selisih'   => $penjelasanSelisih ?: null,
            'catatan_pemeriksaan'  => $catatanPemeriksaan ?: null,
            'created_by'           => $userId,
        ]);

        flash('success', 'Berita Acara Pemeriksaan Kas (Opname) berhasil disimpan.');
        redirect('opname-kas?tahun=' . $tahun . '&desa_id=' . $desaId);
    }

    /**
     * Form Edit Berita Acara Opname Kas
     */
    public function edit(): void {
        $id = (int) input('id');
        $row = DB::one("
            SELECT o.*, d.nama AS desa_nama, k.nama AS kecamatan_nama
            FROM kka_opname_kas o
            JOIN kka_desa d ON d.id = o.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            WHERE o.id = ?
        ", [$id]);

        if (!$row) {
            flash('error', 'Data Berita Acara Opname Kas tidak ditemukan.');
            redirect('opname-kas');
        }

        $rincianKertas = json_decode($row['rincian_uang_kertas'] ?? '[]', true) ?: [];
        $rincianLogam  = json_decode($row['rincian_uang_logam'] ?? '[]', true) ?: [];

        $desaList = DB::all("
            SELECT d.id, d.nama, k.nama AS kecamatan_nama
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY k.nama ASC, d.nama ASC
        ");

        view('opname_kas/edit', compact('row', 'rincianKertas', 'rincianLogam', 'desaList'));
    }

    /**
     * Update Berita Acara Opname Kas
     */
    public function update(): void {
        only_post();
        csrf_check();

        $id = (int) input('id');
        $row = DB::one("SELECT * FROM kka_opname_kas WHERE id = ?", [$id]);
        if (!$row) {
            flash('error', 'Data Berita Acara Opname Kas tidak ditemukan.');
            redirect('opname-kas');
        }

        $noBap           = trim((string) input('no_bap'));
        $tglPemeriksaan  = trim((string) input('tgl_pemeriksaan')) ?: $row['tgl_pemeriksaan'];
        $waktuPemeriksaan = trim((string) input('waktu_pemeriksaan')) ?: '09.30 WIB';
        $tempatPemeriksaan = trim((string) input('tempat_pemeriksaan')) ?: 'Kantor Kepenghuluan';

        $namaBendahara   = trim((string) input('nama_bendahara'));
        $nipBendahara    = trim((string) input('nip_bendahara'));
        $namaKepalaDesa  = trim((string) input('nama_kepala_desa'));
        $namaKetuaTim    = trim((string) input('nama_ketua_tim'));
        $nipKetuaTim     = trim((string) input('nip_ketua_tim'));
        $namaAnggota     = trim((string) input('nama_anggota'));

        // Pecahan Uang Kertas
        $kertasPecahan = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
        $rincianKertas = [];
        $totalKertas = 0.0;
        foreach ($kertasPecahan as $p) {
            $lembar = (int) input('kertas_' . $p, 0);
            $subtotal = $lembar * $p;
            $rincianKertas[$p] = [
                'lembar'   => $lembar,
                'subtotal' => $subtotal
            ];
            $totalKertas += $subtotal;
        }

        // Pecahan Uang Logam
        $logamPecahan = [1000, 500, 200, 100];
        $rincianLogam = [];
        $totalLogam = 0.0;
        foreach ($logamPecahan as $p) {
            $keping = (int) input('logam_' . $p, 0);
            $subtotal = $keping * $p;
            $rincianLogam[$p] = [
                'keping'   => $keping,
                'subtotal' => $subtotal
            ];
            $totalLogam += $subtotal;
        }

        $totalKasFisik = $totalKertas + $totalLogam;
        $saldoBank     = parse_money(input('saldo_bank', 0));
        $namaBank      = trim((string) input('nama_bank')) ?: 'Bank Riau Kepri Syariah';
        $noRekBank     = trim((string) input('no_rekening_bank'));
        $totalKasRiil  = $totalKasFisik + $saldoBank;
        $saldoBku      = parse_money(input('saldo_bku', 0));
        $selisihKas    = $totalKasRiil - $saldoBku;

        if (abs($selisihKas) < 0.01) {
            $statusSelisih = 'COCOK';
        } elseif ($selisihKas > 0) {
            $statusSelisih = 'LEBIH';
        } else {
            $statusSelisih = 'KURANG';
        }

        $penjelasanSelisih = trim((string) input('penjelasan_selisih'));
        $catatanPemeriksaan = trim((string) input('catatan_pemeriksaan'));

        DB::update('kka_opname_kas', [
            'no_bap'               => $noBap,
            'tgl_pemeriksaan'      => $tglPemeriksaan,
            'waktu_pemeriksaan'    => $waktuPemeriksaan,
            'tempat_pemeriksaan'   => $tempatPemeriksaan,
            'nama_bendahara'       => $namaBendahara,
            'nip_bendahara'        => $nipBendahara ?: null,
            'nama_kepala_desa'     => $namaKepalaDesa,
            'nama_ketua_tim'       => $namaKetuaTim,
            'nip_ketua_tim'        => $nipKetuaTim ?: null,
            'nama_anggota'         => $namaAnggota ?: null,
            'rincian_uang_kertas'  => json_encode($rincianKertas),
            'total_kertas'         => $totalKertas,
            'rincian_uang_logam'   => json_encode($rincianLogam),
            'total_logam'          => $totalLogam,
            'total_kas_fisik'      => $totalKasFisik,
            'saldo_bank'           => $saldoBank,
            'nama_bank'            => $namaBank,
            'no_rekening_bank'     => $noRekBank ?: null,
            'total_kas_riil'       => $totalKasRiil,
            'saldo_bku'            => $saldoBku,
            'selisih_kas'          => $selisihKas,
            'status_selisih'       => $statusSelisih,
            'penjelasan_selisih'   => $penjelasanSelisih ?: null,
            'catatan_pemeriksaan'  => $catatanPemeriksaan ?: null,
        ], ['id' => $id]);

        flash('success', 'Berita Acara Opname Kas berhasil diperbarui.');
        redirect('opname-kas?tahun=' . $row['tahun_anggaran'] . '&desa_id=' . $row['desa_id']);
    }

    /**
     * Hapus Berita Acara Opname Kas
     */
    public function delete(): void {
        only_post();
        csrf_check();

        $id = (int) input('id');
        $row = DB::one("SELECT * FROM kka_opname_kas WHERE id = ?", [$id]);
        if ($row) {
            DB::delete('kka_opname_kas', ['id' => $id]);
            flash('success', 'Berita Acara Opname Kas berhasil dihapus.');
            redirect('opname-kas?tahun=' . $row['tahun_anggaran']);
        }
        redirect('opname-kas');
    }

    /**
     * Cetak Berita Acara Pemeriksaan Kas (A4 Resmi Standar Pemkab Rohil)
     */
    public function print(): void {
        $id = (int) input('id');
        $row = DB::one("
            SELECT o.*, d.nama AS desa_nama,
                   k.nama AS kecamatan_nama, spt.no_spt, spt.tgl_spt
            FROM kka_opname_kas o
            JOIN kka_desa d ON d.id = o.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            LEFT JOIN kka_spt spt ON spt.id = o.spt_id
            WHERE o.id = ?
        ", [$id]);

        if (!$row) {
            exit('Data Berita Acara Pemeriksaan Kas tidak ditemukan.');
        }

        $rincianKertas = json_decode($row['rincian_uang_kertas'] ?? '[]', true) ?: [];
        $rincianLogam  = json_decode($row['rincian_uang_logam'] ?? '[]', true) ?: [];

        // Terbilang
        $terbilangKasFisik = terbilang_rupiah((float)$row['total_kas_fisik']);
        $terbilangKasRiil  = terbilang_rupiah((float)$row['total_kas_riil']);
        $terbilangBku      = terbilang_rupiah((float)$row['saldo_bku']);
        $terbilangSelisih  = terbilang_rupiah(abs((float)$row['selisih_kas']));

        view('print/opname_kas', compact('row', 'rincianKertas', 'rincianLogam', 'terbilangKasFisik', 'terbilangKasRiil', 'terbilangBku', 'terbilangSelisih'));
    }
}
