<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

echo "=== SEEDING DATA PENUGASAN RIIL KEPENGHULUAN SUNGAI SIALANG HULU TA 2025 ===\n";

$pdo = DB::pdo();

// 1. Pastikan kolom pj_penghulu, sekdes, kaur_keuangan ada di kka_spt
try {
    $cols = DB::all("SHOW COLUMNS FROM kka_spt LIKE 'pj_penghulu'");
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE kka_spt ADD COLUMN pj_penghulu VARCHAR(150) NULL AFTER anggota_data");
        echo "✓ Kolom pj_penghulu ditambahkan ke kka_spt\n";
    }
    $cols = DB::all("SHOW COLUMNS FROM kka_spt LIKE 'sekdes'");
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE kka_spt ADD COLUMN sekdes VARCHAR(150) NULL AFTER pj_penghulu");
        echo "✓ Kolom sekdes ditambahkan ke kka_spt\n";
    }
    $cols = DB::all("SHOW COLUMNS FROM kka_spt LIKE 'kaur_keuangan'");
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE kka_spt ADD COLUMN kaur_keuangan VARCHAR(150) NULL AFTER sekdes");
        echo "✓ Kolom kaur_keuangan ditambahkan ke kka_spt\n";
    }
} catch (Throwable $e) {
    echo "! Catatan kolom kka_spt: " . $e->getMessage() . "\n";
}

$desaId = 69; // Sei Sialang Hulu
$kecId  = 6;  // Batu Hampar
$tahun  = 2025;

// Bersihkan data transaksi untuk desa_id = 69 TA 2025 jika ada
DB::q("DELETE FROM kka_tindak_lanjut WHERE desa_id = ?", [$desaId]);
DB::q("DELETE FROM kka_temuan WHERE desa_id = ?", [$desaId]);
DB::q("DELETE FROM kka_lhp_narasi WHERE desa_id = ?", [$desaId]);
$sesiRows = DB::all("SELECT id FROM kka_sesi WHERE desa_id = ?", [$desaId]);
foreach ($sesiRows as $s) {
    DB::q("DELETE FROM kka_rincian WHERE sesi_id = ?", [$s['id']]);
    DB::q("DELETE FROM kka_sesi_share WHERE sesi_id = ?", [$s['id']]);
}
DB::q("DELETE FROM kka_sesi WHERE desa_id = ?", [$desaId]);
$sptRows = DB::all("SELECT id FROM kka_spt WHERE desa_id = ?", [$desaId]);
foreach ($sptRows as $sp) {
    $pkaRows = DB::all("SELECT id FROM kka_pka WHERE spt_id = ?", [$sp['id']]);
    foreach ($pkaRows as $pk) {
        DB::q("DELETE FROM kka_pka_langkah WHERE pka_id = ?", [$pk['id']]);
    }
    DB::q("DELETE FROM kka_pka WHERE spt_id = ?", [$sp['id']]);
}
DB::q("DELETE FROM kka_spt WHERE desa_id = ?", [$desaId]);
DB::q("DELETE FROM kka_nota_dinas WHERE desa_id = ?", [$desaId]);

echo "✓ Pembersihan awal desa selesai.\n";

// Anggota tim JSON
$anggotaData = json_encode([
    [
        'id'      => 5,
        'nama'    => 'FAKHRURRAZI, S.A.P',
        'nip'     => '19820817 200502 1 001',
        'pangkat' => 'Penata / III.c',
        'jabatan' => 'Auditor Ahli Pertama'
    ],
    [
        'id'      => 4,
        'nama'    => 'HENDRY SAPUTRA, S.AK',
        'nip'     => '19850922 201503 1 001',
        'pangkat' => 'Penata Muda Tk. I / III.b',
        'jabatan' => 'Auditor Ahli Pertama'
    ]
], JSON_UNESCAPED_UNICODE);

// 2. Insert Nota Dinas (ND)
$ndId = DB::insert('kka_nota_dinas', [
    'no_nd'          => '35/INSP/ND/IRBAN-IV/IV/2026',
    'tgl_nd'         => '2026-06-22',
    'irban_id'       => 42,
    'irban_nama'     => 'MARWAN, M.T',
    'desa_id'        => $desaId,
    'kecamatan_id'   => $kecId,
    'tahun_anggaran' => (string)$tahun,
    'tujuan'         => 'Usulan Penugasan Melakukan Entry Meeting / Pengembangan Informasi Awal (PIA) Audit Tujuan Tertentu pada Kepenghuluan Sungai Sialang Hulu Kabupaten Rokan Hilir Tahun Anggaran 2025',
    'jenis_audit'    => 'Audit Dengan Tujuan Tertentu (ADTT)',
    'tgl_mulai'      => '2026-06-22',
    'tgl_selesai'    => '2026-06-24',
    'lama_hari'      => 3,
    'dalnis_id'      => 9,
    'dalnis_nama'    => 'ABU BAKAR, SE',
    'ketua_tim_id'   => 8,
    'ketua_tim_nama' => 'AMDAT TOFA, SH',
    'anggota_data'   => $anggotaData,
    'catatan_irban'  => 'Dasar: Keputusan Bupati Rokan Hilir No 10/INSP/2026 dan Surat GEMPAR-ROHIL No 31/LP/GEMPAR-ROHIL/1/2026.',
    'status'         => 'DISETUJUI',
    'catatan_inspektur' => 'Disetujui. Teruskan ke Bagian Perencanaan/SPT untuk penerbitan Surat Perintah Tugas (SPT).',
    'tgl_disposisi'  => '2026-06-22 09:15:00',
    'created_by'     => 42,
    'created_at'     => '2026-06-22 08:30:00'
]);
echo "✓ 1. Nota Dinas tersimpan (ID: $ndId, No: 35/INSP/ND/IRBAN-IV/IV/2026)\n";

// 3. Insert Surat Perintah Tugas (SPT)
$dasarHukum = "1. Peraturan Menteri Dalam Negeri Nomor 2 Tahun 2025 tentang Perencanaan Pembinaan dan Pengawasan Penyelenggaraan Pemerintah Daerah Tahun 2025;\n2. Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Kabupaten Rokan Hilir Tahun Anggaran 2026;\n3. Surat Perintah Inspektur Daerah Kabupaten Rokan Hilir Nomor: 700.1.2/ST-ADTT/INSP/PD/2026/18 tanggal 29 Juni 2026.";

$sptId = DB::insert('kka_spt', [
    'nota_dinas_id'        => $ndId,
    'no_spt'               => '700.1.2/ST-ADTT/INSP/PD/2026/18',
    'tgl_spt'              => '2026-06-29',
    'tgl_mulai'            => '2026-06-29',
    'tgl_selesai'          => '2026-07-07',
    'lama_hari'            => 7,
    'desa_id'              => $desaId,
    'kecamatan_id'         => $kecId,
    'tahun_anggaran'       => (string)$tahun,
    'tujuan'               => 'Melakukan Audit Dengan Tujuan Tertentu pada Kepenghuluan Sungai Sialang Hulu Kecamatan Batu Hampar Kabupaten Rokan Hilir Tahun Anggaran 2025',
    'dasar_hukum'          => $dasarHukum,
    'penanggung_jawab_nama'=> 'H. SARMAN SYAHRONI, ST., M.IP',
    'penanggung_jawab_nip' => '19760810 200312 1 004',
    'wakil_pj_id'          => 42,
    'wakil_pj_nama'        => 'MARWAN, M.T',
    'dalnis_id'            => 9,
    'dalnis_nama'          => 'ABU BAKAR, SE',
    'ketua_tim_id'         => 8,
    'ketua_tim_nama'       => 'AMDAT TOFA, SH',
    'anggota_data'         => $anggotaData,
    'pj_penghulu'          => 'RUDIYANTO',
    'sekdes'               => 'MASWIN ISMAIL',
    'kaur_keuangan'        => 'NURSUSILA',
    'status'               => 'DITERBITKAN',
    'tgl_ttd_inspektur'    => '2026-06-29 10:00:00',
    'status_nhp'           => 'DISETUJUI_EKSPOSE',
    'tgl_pengajuan_nhp'    => '2026-07-07',
    'diajukan_oleh_nhp'    => 'AMDAT TOFA, SH (Ketua Tim)',
    'tgl_disetujui_nhp'    => '2026-07-07',
    'disetujui_oleh_nhp'   => 'H. SARMAN SYAHRONI, ST., M.IP',
    'catatan_inspektur_nhp'=> 'P2HP dan Berita Acara Temuan disetujui untuk diekspose kepada auditan Pemerintah Kepenghuluan Sungai Sialang Hulu. Lanjutkan pembuatan Berita Acara Kesepakatan Tindak Lanjut.',
    'tte_barcode_nhp'      => 'TTE-INSP-20260707-SSH001',
    'created_by'           => 45,
    'created_at'           => '2026-06-29 09:00:00'
]);
echo "✓ 2. SPT tersimpan & Disahkan (ID: $sptId, No: 700.1.2/ST-ADTT/INSP/PD/2026/18)\n";

// 4. Insert PKA (Program Kerja Audit)
$pkaId = DB::insert('kka_pka', [
    'spt_id'         => $sptId,
    'no_pka'         => 'PKA-700-1-2-ST-ADTT-INSP-PD-2026-18',
    'tgl_pka'        => '2026-06-29',
    'desa_id'        => $desaId,
    'tahun_anggaran' => (string)$tahun,
    'ketua_tim_id'   => 8,
    'ketua_tim_nama' => 'AMDAT TOFA, SH',
    'dalnis_id'      => 9,
    'dalnis_nama'    => 'ABU BAKAR, SE',
    'irban_id'       => 42,
    'irban_nama'     => 'MARWAN, M.T',
    'status'         => 'DISETUJUI',
    'created_at'     => '2026-06-29 10:30:00'
]);

$langkahList = [
    [
        'bidang_kode'         => 'BID.1',
        'bidang_nama'         => 'Bidang 1: Penyelenggaraan Pemerintahan Desa',
        'uraian_prosedur'     => 'Pengujian kepatuhan pembayaran Penghasilan Tetap (Siltap), Tunjangan BPD, Operasional Perkantoran, serta belanja sarana prasarana perlengkapan mebelair kantor desa.',
        'tujuan_pengujian'    => 'Memastikan seluruh belanja operasional didukung bukti pengeluaran yang sah dan lengkap sesuai Perbup.',
        'pelaksana_user_id'   => 5,
        'pelaksana_nama'      => 'FAKHRURRAZI, S.A.P',
        'ref_kka_nomor'       => 'KKA-B.1.1',
        'waktu_rencana_hari'  => 3,
        'status_pelaksanaan'  => 'SELESAI',
        'urutan'              => 1
    ],
    [
        'bidang_kode'         => 'BID.2',
        'bidang_nama'         => 'Bidang 2: Pelaksanaan Pembangunan Desa (Fisik Lapangan)',
        'uraian_prosedur'     => 'Pemeriksaan pekerjaan fisik/konstruksi: Penimbunan Lapangan Bola dan Peningkatan Posyandu Desa. Cek volume (P x L x T) dan pengujian administrasi RAB.',
        'tujuan_pengujian'    => 'Menguji kesesuaian realisasi fisik dengan RAB, gambar kerja, dan kwitansi SPJ.',
        'pelaksana_user_id'   => 4,
        'pelaksana_nama'      => 'HENDRY SAPUTRA, S.AK',
        'ref_kka_nomor'       => 'KKA-B.2.1',
        'waktu_rencana_hari'  => 4,
        'status_pelaksanaan'  => 'SELESAI',
        'urutan'              => 2
    ]
];
foreach ($langkahList as $l) {
    DB::insert('kka_pka_langkah', array_merge($l, ['pka_id' => $pkaId]));
}
echo "✓ 3. PKA & Langkah Kerja terbentuk (ID: $pkaId)\n";

// 5. Insert Sesi KKA
$sesiId = DB::insert('kka_sesi', [
    'desa_id'        => $desaId,
    'bidang_id'      => 1,
    'sub_bidang_id'  => 2, // Penyediaan Sarana Prasarana Pemerintahan Desa
    'objek_audit'    => 'Penyediaan Sarana (Aset Tetap) Perkantoran / Pemerintahan Desa',
    'kegiatan'       => 'Pengadaan Peralatan Mebelair dan Aksesoris Ruangan Kantor Kepenghuluan',
    'pagu_anggaran'  => 13540000.00,
    'semester'       => 1,
    'tahun_anggaran' => $tahun,
    'no_kka'         => 'KKA.1.02.01/SSH/2025',
    'ref_kka'        => 'KKA-B.1.1',
    'dibuat_oleh'    => 'FAKHRURRAZI, S.A.P',
    'tanggal_dibuat' => '2026-06-30',
    'ketua_tim_id'   => 8,
    'direview_oleh'  => 'AMDAT TOFA, SH',
    'tanggal_review' => '2026-07-02',
    'dalnis_id'      => 9,
    'dievaluasi_oleh'=> 'ABU BAKAR, SE',
    'tanggal_evaluasi'=> '2026-07-04',
    'irban_id'       => 42,
    'irban_nama'     => 'MARWAN, M.T',
    'no_lha'         => '700.1.2.1/R/ADTT/INSP/2026/12',
    'kesimpulan'     => 'Terdapat pengeluaran belanja modal peralatan mebelair kantor sebesar Rp13.540.000,00 yang tidak dilengkapi bukti sah (bon faktur tanpa cap stempel rekanan).',
    'status'         => 'SELESAI_FINAL',
    'created_by'     => 5,
    'created_at'     => '2026-06-30 11:00:00'
]);
echo "✓ 4. Sesi KKA Belanja Riil terbentuk (ID: $sesiId, No KKA: KKA.1.02.01/SSH/2025)\n";

// 6. Insert Rincian KKA
$rincianId = DB::insert('kka_rincian', [
    'sesi_id'         => $sesiId,
    'urutan'          => 1,
    'uraian'          => 'Belanja Modal Peralatan Mebelair dan Aksesoris Ruangan (Meja, Kursi Kerja, dan Lemari Arsip Kantor Kepenghuluan)',
    'pagu_anggaran'   => 13540000.00,
    'biaya_dikwitansi'=> 13540000.00,
    'realisasi'       => 13540000.00,
    'penerima'        => 'Toko Mebel Sumber Berkah',
    'keterangan'      => 'Bukti bon faktur pembelian tidak dibubuhi tanda tangan dan cap stempel resmi toko rekanan.'
]);
echo "✓ 5. Rincian SPJ Belanja tersimpan (ID: $rincianId, Nilai: Rp13.540.000,00)\n";

// 7. Insert Temuan KTP 5 Unsur
$temuanId = DB::insert('kka_temuan', [
    'desa_id'        => $desaId,
    'spt_id'         => $sptId,
    'sesi_id'        => $sesiId,
    'rincian_id'     => $rincianId,
    'tahun_anggaran' => $tahun,
    'nomor_temuan'   => '01/KTP-ADTT/SSH/2026',
    'judul'          => 'Terdapat Pengeluaran dari Beban APBKep Tidak Didukung dengan Pertanggungjawaban yang Lengkap dan Sah Sebesar Rp13.540.000,00',
    'bidang_nama'    => 'Bidang Penyelenggaraan Pemerintahan Desa',
    'kondisi'        => 'Berdasarkan hasil audit atas penatausahaan dan pertanggungjawaban keuangan Kepenghuluan Sungai Sialang Hulu Tahun Anggaran 2025, ditemukan pengeluaran dari beban APBKep yang dikeluarkan namun tidak didukung pertanggungjawaban yang lengkap dan/atau sah sebesar Rp13.540.000,00 atas Belanja Modal Peralatan Mebelair dan Aksesoris Ruangan (bon faktur tanpa tanda tangan dan cap stempel toko rekanan).',
    'kriteria'       => 'Peraturan Bupati Rokan Hilir Nomor 31 Tahun 2020 tentang Pengelolaan Keuangan Kepenghuluan Pasal 55 ayat (2): "Setiap pengeluaran sebagaimana dimaksud pada ayat (1) didukung dengan bukti yang lengkap dan sah".',
    'sebab'          => 'Sekretaris Kepenghuluan, Kepala Urusan Keuangan dan Pelaksana Kegiatan Anggaran (PKA) lalai dalam menjalankan tugasnya serta kurang cermat dalam memverifikasi kelengkapan SPJ sebelum pembayaran dilakukan.',
    'akibat'         => 'Pengeluaran keuangan dari beban APBKep Sungai Sialang Hulu Tahun Anggaran 2025 sebesar Rp13.540.000,00 belum dapat diyakini kebenarannya dan berisiko menimbulkan kerugian keuangan Kepenghuluan.',
    'rekomendasi'    => "1. Membuat surat teguran tertulis kepada Sekretaris Kepenghuluan, Kaur Keuangan dan Pelaksana Kegiatan atas kelalaiannya dalam mematuhi peraturan yang berlaku;\n2. Menginstruksikan kepada Kaur Keuangan dan Pelaksana Kegiatan untuk melengkapi dokumen pertanggungjawaban pengeluaran keuangan yang kurang didukung surat pertanggungjawaban yang lengkap dan/atau sah sebesar Rp13.540.000,00 atau menyetorkan kembali ke Rekening Kas Kepenghuluan.",
    'nominal'        => 13540000.00,
    'tanggapan_auditi'=> 'Pemerintah Kepenghuluan Sungai Sialang Hulu menerima hasil pemeriksaan dan berkomitmen segera melengkapi bukti kwitansi serta stempel toko yang bersangkutan dalam masa tindak lanjut 60 hari.',
    'status'         => 'FINAL_LHP',
    'created_by'     => 5,
    'created_at'     => '2026-07-05 14:00:00'
]);
echo "✓ 6. Konsep Temuan Pemeriksaan 5 Unsur tersimpan (ID: $temuanId, Rp13.540.000)\n";

// 8. Insert Narasi LHP Resmi
DB::insert('kka_lhp_narasi', [
    'desa_id'               => $desaId,
    'tahun_anggaran'        => $tahun,
    'status_lhp'            => 'DISAHKAN_INSPEKTUR',
    'tgl_disahkan_inspektur'=> '2026-07-07 16:45:00',
    'disahkan_oleh_nama'    => 'H. SARMAN SYAHRONI, ST., M.IP',
    'dalnis_nama'           => 'ABU BAKAR, SE',
    'irban_nama'            => 'MARWAN, M.T',
    'ringkasan_eksekutif'   => 'Laporan Hasil Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan APBKep Kepenghuluan Sungai Sialang Hulu Kecamatan Batu Hampar TA 2025.',
    'dasar_penugasan'       => $dasarHukum,
    'tujuan_pengawasan'     => 'Memberikan keyakinan memadai bahwa pengelolaan Dana Kepenghuluan dan Alokasi Dana Kepenghuluan telah dilaksanakan sesuai ketentuan perundang-undangan.',
    'ruang_lingkup'         => 'Pertanggungjawaban pendapatan dan belanja APBKep Sungai Sialang Hulu TA 2025.',
    'kesimpulan'            => 'Terdapat temuan administrasi pertanggungjawaban belanja belum lengkap dan sah sebesar Rp13.540.000,00 yang telah disepakati untuk ditindaklanjuti dalam 60 hari.',
    'created_at'            => '2026-07-07 16:45:00'
]);
echo "✓ 7. Laporan Hasil Audit (LHP) tersimpan & Disahkan\n";

echo "\n=== SELESAI! DATA RIIL BERHASIL DIINPUT SECARA LENGKAP ===\n";
