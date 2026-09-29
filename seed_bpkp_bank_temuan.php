<?php
/**
 * Seed Master Bank Temuan Standar BPKP & Inspektorat Kabupaten Rokan Hilir
 * Diadaptasi dari taksonomi resmi Siswaskeudes v2.0 (Ref_HA_Kondisi, Sebab, Rekomendasi)
 */
require_once __DIR__ . '/src/bootstrap.php';

$seeds = [
    [
        'kategori'    => 'PEKERJAAN FISIK',
        'kode'        => '0107',
        'judul'       => 'Kekurangan Volume dan/atau Mutu atas Pekerjaan Fisik Konstruksi di Lapangan',
        'kondisi'     => "Berdasarkan hasil pengujian fisik dan pengukuran bersama (joint inspection) di lapangan antara Tim Pemeriksa APIP Inspektorat Kabupaten Rokan Hilir dengan Tim Pelaksana Kegiatan (TPK) serta Pendamping Desa pada Kepenghuluan {DESA}, ditemukan kekurangan volume terpasang dan/atau ketidaksesuaian mutu bahan pada pekerjaan fisik dibandingkan dengan Rencana Anggaran Biaya (RAB) dan dokumen pertanggungjawaban (SPJ). Hal ini mengakibatkan terjadinya kelebihan pembayaran riil kepada penyedia/pelaksana kegiatan.",
        'kriteria'    => "1. Undang-Undang Nomor 6 Tahun 2014 tentang Desa;\n2. Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa, Pasal 51 ayat (1) bahwa setiap pengeluaran atas beban APBDesa harus didukung dengan bukti yang lengkap dan sah;\n3. Peraturan LKPP tentang Pedoman Tata Cara Pengadaan Barang/Jasa di Desa;\n4. Peraturan Bupati Rokan Hilir tentang Pengelolaan Keuangan Desa;\n5. Rencana Anggaran Biaya (RAB) dan Gambar Kerja Kegiatan.",
        'sebab'       => "1. Tim Pelaksana Kegiatan (TPK) dan Pj. Penghulu kurang cermat dalam mengawasi dan menguji volume pekerjaan sebelum menyetujui pembayaran 100%;\n2. Rekanan/penyedia tidak melaksanakan pekerjaan sesuai spesifikasi teknis dan volume dalam kontrak/RAB.",
        'akibat'      => "Terjadinya potensi kerugian keuangan Kepenghuluan {DESA} atas kelebihan pembayaran hasil pekerjaan yang tidak dapat dipertanggungjawabkan secara fisik.",
        'rekomendasi' => "1. Menginstruksikan Pj. Penghulu untuk menegur keras Ketua TPK agar lebih cermat dalam pengawasan mutu dan volume pekerjaan;\n2. Memerintahkan TPK dan rekanan/pelaksana kegiatan untuk menyetorkan kembali kelebihan pembayaran dana tersebut ke Rekening Kas Desa (RKD) dan bukti setor disampaikan kepada Tim Inspektorat Kabupaten Rokan Hilir."
    ],
    [
        'kategori'    => 'PEKERJAAN FISIK',
        'kode'        => '0211',
        'judul'       => 'Pekerjaan Fisik Terlambat Diselesaikan Melampaui Tahun Anggaran dan Belum Dikenakan Denda Keterlambatan',
        'kondisi'     => "Hasil pemeriksaan fisik di lapangan menunjukkan bahwa pelaksanaan pekerjaan pada Kepenghuluan {DESA} belum selesai 100% pada batas akhir tahun anggaran berjalan, namun dana kegiatan telah ditarik dan belum dikenakan sanksi denda keterlambatan kepada pihak pelaksana.",
        'kriteria'    => "1. Peraturan LKPP tentang Pengadaan Barang/Jasa di Desa;\n2. Ketentuan Perjanjian Kerja / Surat Perintah Kerja (SPK) tentang batas waktu pelaksanaan dan pengenaan denda keterlambatan 1/1000 per hari kelambatan.",
        'sebab'       => "1. TPK kurang optimal dalam mengendalikan jadwal pelaksanaan kegiatan fisik;\n2. Penghulu / PKA tidak memberlakukan klausul denda keterlambatan atas keterlambatan penyelesaian pekerjaan.",
        'akibat'      => "Azas manfaat kegiatan bagi masyarakat Kepenghuluan {DESA} terlambat dinikmati dan potensi penerimaan denda bagi kas desa belum terealisasi.",
        'rekomendasi' => "Memerintahkan Pj. Penghulu untuk memungut denda keterlambatan penyelesaian pekerjaan sesuai perhitungan hari keterlambatan dan menyetorkannya ke Rekening Kas Desa (RKD)."
    ],
    [
        'kategori'    => 'PENGADAAN & KEMAHALAN HARGA',
        'kode'        => '0104',
        'judul'       => 'Kemahalan Harga Pengadaan Barang/Jasa Melebihi Standar Satuan Harga (SSH) Kabupaten Rokan Hilir',
        'kondisi'     => "Pemeriksaan atas bukti pengadaan barang dan material pada Kepenghuluan {DESA} menemukan harga satuan barang/bahan yang dipertanggungjawabkan lebih tinggi secara tidak wajar dibandingkan Standar Satuan Harga (SSH) resmi Pemkab Rokan Hilir maupun harga pasar yang berlaku umum pada waktu yang sama.",
        'kriteria'    => "1. Peraturan Bupati Rokan Hilir tentang Standar Satuan Harga (SSH) dan Standar Biaya Masukan (SBM);\n2. Prinsip Efisiensi dan Efektivitas Pengelolaan Keuangan Desa sesuai Permendagri Nomor 20 Tahun 2018.",
        'sebab'       => "1. Tim Pelaksana Kegiatan (TPK) tidak melakukan survei harga pasar sebelum menetapkan rekanan toko/penyedia;\n2. Verifikator APBDesa tidak memedomani SSH dalam penetapan harga satuan.",
        'akibat'      => "Terjadi pemborosan dan kelebihan pembayaran belanja APBDesa yang membebani keuangan Kepenghuluan {DESA}.",
        'rekomendasi' => "Memerintahkan penyedia/pelaksana untuk menyetorkan kembali selisih kemahalan harga belanja tersebut ke Rekening Kas Desa (RKD)."
    ],
    [
        'kategori'    => 'KAS & KEUANGAN',
        'kode'        => '0101',
        'judul'       => 'Ketekoran Kas / Terdapat Selisih Kurang Saldo Kas Fisik pada Brankas Bendahara Desa',
        'kondisi'     => "Hasil pemeriksaan kas setempat (Cash Opname) dan perbandingan saldo Buku Kas Umum (BKU) dengan uang fisik riil pada brankas Kaur Keuangan Kepenghuluan {DESA} menunjukkan adanya selisih kas fisik yang tidak dapat dibuktikan keberadaannya, atau saldo kas tunai yang disimpan melebihi pagu simpan kas tunai yang diizinkan.",
        'kriteria'    => "1. Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa;\n2. Peraturan Bupati Rokan Hilir tentang Batas Maksimal Penyimpanan Uang Tunai pada Kas Bendahara Kepenghuluan.",
        'sebab'       => "1. Kaur Keuangan/Bendahara Desa tidak tertib dan disiplin dalam membukukan transaksi harian pada BKU;\n2. Dana kas desa digunakan terlebih dahulu untuk keperluan lain di luar peruntukan APBDesa.",
        'akibat'      => "Terjadinya kerugian keuangan kas desa (tekor kas) dan risiko penyalahgunaan uang negara/desa.",
        'rekomendasi' => "1. Memberikan teguran keras kepada Kaur Keuangan/Bendahara Desa atas kelalaian penatausahaan kas;\n2. Memerintahkan Bendahara untuk menyetorkan selisih kekurangan kas tersebut seketika kembali ke Rekening Kas Desa (RKD) dan melampirkan bukti Surat Tanda Setor (STS)."
    ],
    [
        'kategori'    => 'KAS & KEUANGAN',
        'kode'        => '0103',
        'judul'       => 'Realisasi Pengeluaran Kas Tanpa Bukti Kuitansi / Pengeluaran Fiktif',
        'kondisi'     => "Ditemukan transaksi pencatatan pengeluaran dana pada Buku Kas Umum (BKU) atau penarikan tunai dari Rekening Kas Desa pada Kepenghuluan {DESA} yang tidak dapat dibuktikan dengan dokumen kuitansi, nota, atau bukti fisik kegiatan apa pun.",
        'kriteria'    => "Pasal 51 Permendagri Nomor 20 Tahun 2018 bahwa setiap pengeluaran belanja atas beban APBDesa harus didukung dengan bukti yang lengkap dan sah.",
        'sebab'       => "Pencairan kas dilakukan tanpa pengajuan Surat Permintaan Pembayaran (SPP) dan tanpa verifikasi dokumen pertanggungjawaban yang memadai.",
        'akibat'      => "Terjadinya kerugian riil keuangan Kepenghuluan {DESA} atas pengeluaran yang tidak dapat dipertanggungjawabkan.",
        'rekomendasi' => "Memerintahkan pihak yang menarik dan menggunakan dana kas tersebut untuk menyetorkan kembali seluruh dana ke Rekening Kas Desa (RKD)."
    ],
    [
        'kategori'    => 'KAS & KEUANGAN',
        'kode'        => '0109',
        'judul'       => 'Uang Muka / Panjar Kegiatan Belum Dipertanggungjawabkan Melampaui Batas Waktu Berjalan',
        'kondisi'     => "Pemberian uang panjar kerja kepada Pelaksana Kegiatan Anggaran (PKA) atau Tim Kegiatan pada Kepenghuluan {DESA} belum dipertanggungjawabkan dengan SPJ definitif melampaui batas waktu yang ditentukan dan belum disetorkan kembali sisa kelebihannya.",
        'kriteria'    => "Peraturan Bupati Rokan Hilir tentang Batas Waktu Penyelesaian Uang Muka/Panjar Kerja Pengelolaan Keuangan Desa.",
        'sebab'       => "Kaur Keuangan dan Pj. Penghulu kurang tegas dalam menagih SPJ penyelesaian uang panjar kerja.",
        'akibat'      => "Saldo kas desa mengendap di tangan pihak pelaksana dan berisiko tidak tertagih.",
        'rekomendasi' => "Memerintahkan Pj. Penghulu untuk menarik kembali seluruh sisa panjar yang belum di-SPJ-kan dan menyetorkannya ke Rekening Kas Desa (RKD)."
    ],
    [
        'kategori'    => 'KAS & KEUANGAN',
        'kode'        => '0812',
        'judul'       => 'Rekonsiliasi Buku Kas Umum (BKU) dengan Rekening Koran Kas Desa Tidak Tertib Dilakukan',
        'kondisi'     => "Kaur Keuangan Kepenghuluan {DESA} tidak melakukan rekonsiliasi bulanan antara catatan mutasi Buku Kas Umum (BKU) dengan Rekening Koran Bank Riau Kepri Syariah, sehingga terdapat transaksi debet/kredit bank yang belum dibukukan.",
        'kriteria'    => "Permendagri Nomor 20 Tahun 2018 tentang Tata Cara Penatausahaan Keuangan Desa.",
        'sebab'       => "Kaur Keuangan belum menguasai teknik rekonsiliasi bank dan tidak rutin mencetak rekening koran setiap akhir bulan.",
        'akibat'      => "Saldo kas desa yang dilaporkan tidak mencerminkan posisi likuiditas riil yang dapat dipercaya.",
        'rekomendasi' => "Menginstruksikan Pj. Penghulu mewajibkan Kaur Keuangan membuat Berita Acara Rekonsiliasi Bank setiap akhir bulan bersama Sekretaris Desa."
    ],
    [
        'kategori'    => 'PERPAJAKAN',
        'kode'        => '0202',
        'judul'       => 'Pajak Pusat (PPN & PPh) Telah Dipungut Namun Belum Disetorkan ke Kas Negara',
        'kondisi'     => "Berdasarkan hasil uji petik dokumen pertanggungjawaban (SPJ) pada Kepenghuluan {DESA}, ditemukan pemotongan pajak Pajak Pertambahan Nilai (PPN) dan Pajak Penghasilan (PPh Pasal 21, 22, 23) yang telah dipotong dari rekanan belanja namun belum disetorkan ke Kas Negara dan tidak memiliki Bukti Penerimaan Negara (NTPN).",
        'kriteria'    => "1. Undang-Undang Nomor 7 Tahun 2021 tentang Harmonisasi Peraturan Perpajakan (HPP);\n2. Peraturan Menteri Keuangan Nomor 59/PMK.03/2022 tentang Tata Cara Pemotongan, Pemungutan, dan Penyetoran Pajak oleh Instansi Pemerintah;\n3. Surat Edaran Inspektorat Kabupaten Rokan Hilir tentang Kepatuhan Perpajakan Desa.",
        'sebab'       => "Kaur Keuangan/Bendahara Desa menunda-nunda pembuatan kode billing dan penyetoran pajak yang telah dipungut.",
        'akibat'      => "Penerimaan Kas Negara tertunda dan Kepenghuluan {DESA} terancam sanksi denda administrasi perpajakan.",
        'rekomendasi' => "1. Pj. Penghulu memberikan teguran tertulis kepada Kaur Keuangan/Bendahara;\n2. Memerintahkan Bendahara untuk segera menyetorkan seluruh tunggakan pajak tersebut ke Kas Negara serta menyerahkan salinan bukti NTPN yang sah kepada Tim Pemeriksa APIP."
    ],
    [
        'kategori'    => 'PERPAJAKAN',
        'kode'        => '0206',
        'judul'       => 'Pajak Daerah (PB1 Restoran dan Retribusi MBLB Galian C) Belum Dipungut dan Disetor ke Kas Daerah Rohil',
        'kondisi'     => "Atas belanja makan minum (konsumsi rapat/pelatihan) dan belanja material tanah timbun/pasir/kerikil (Galian C) pada Kepenghuluan {DESA}, belum dilakukan pemungutan Pajak Daerah (PB1 10% dan Pajak MBLB) untuk disetorkan ke Bapenda Kabupaten Rokan Hilir.",
        'kriteria'    => "1. Peraturan Daerah Kabupaten Rokan Hilir tentang Pajak Daerah dan Retribusi Daerah;\n2. Surat Edaran Bupati Rokan Hilir tentang Kewajiban Pemungutan Pajak Daerah atas Pengelolaan Keuangan Kepenghuluan.",
        'sebab'       => "Kaur Keuangan dan Pelaksana Kegiatan belum memahami kewajiban pemungutan pajak daerah atas belanja APBDesa.",
        'akibat'      => "Hilangnya potensi Pendapatan Asli Daerah (PAD) Kabupaten Rokan Hilir dari sektor Pajak Daerah.",
        'rekomendasi' => "Memerintahkan Bendahara Desa menghitung dan menyetorkan Pajak Daerah terutang ke Kas Daerah Kabupaten Rokan Hilir melalui rekening resmi Bapenda Rohil."
    ],
    [
        'kategori'    => 'PERTANGGUNGJAWABAN SPJ',
        'kode'        => '0813',
        'judul'       => 'Realisasi Belanja Tidak Didukung dengan Bukti Pertanggungjawaban (SPJ) yang Lengkap dan Sah',
        'kondisi'     => "Pemeriksaan atas Surat Pertanggungjawaban (SPJ) pengeluaran APBDesa pada Kepenghuluan {DESA} menemukan bukti pengeluaran yang tidak dilengkapi dokumen pendukung yang memadai, seperti kuitansi tanpa nota riil toko/rekanan, tidak bermaterai cukup, tanpa tanggal/cap toko, atau tanpa dokumentasi foto kegiatan.",
        'kriteria'    => "Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa, Pasal 51 ayat (1) yang mewajibkan setiap pengeluaran kas didukung dengan bukti yang lengkap dan sah atas beban APBDesa.",
        'sebab'       => "1. Kaur Keuangan dan Pelaksana Kegiatan Anggaran (PKA) tidak tertib dalam penatausahaan bukti belanja;\n2. Pj. Penghulu menyetujui Surat Permintaan Pembayaran (SPP) tanpa memverifikasi kelengkapan bukti pertanggungjawaban.",
        'akibat'      => "Kebenaran formil dan materiil atas pengeluaran belanja APBDesa diragukan dan berpotensi menimbulkan kerugian kas desa.",
        'rekomendasi' => "Memerintahkan Pj. Penghulu untuk menginstruksikan PKA dan Bendahara melengkapi seluruh bukti pertanggungjawaban belanja yang sah dalam batas waktu yang ditentukan."
    ],
    [
        'kategori'    => 'ASET DESA',
        'kode'        => '0303',
        'judul'       => 'Barang Hasil Pengadaan Aset Belum Dicatat dalam Buku Inventaris Aset Desa (KIB)',
        'kondisi'     => "Pengadaan aset tetap (seperti laptop, printer, mesin genset, tenda, sound system, tanah, dan bangunan) pada Kepenghuluan {DESA} belum dicatat ke dalam Buku Inventaris Aset Desa / Kartu Inventaris Barang (KIB) serta belum diberi kodefikasi label nomor register aset.",
        'kriteria'    => "Permendagri Nomor 1 Tahun 2016 tentang Pengelolaan Aset Desa, yang mewajibkan seluruh barang milik desa yang berasal dari pembelian APBDesa dicatat dalam inventaris aset desa.",
        'sebab'       => "Petugas pengurus barang desa tidak melaksanakan inventarisasi dan pembukuan aset desa secara berkala.",
        'akibat'      => "Status kepemilikan aset desa tidak tertib, rentan beralih penguasaan kepada pihak yang tidak berhak, dan risiko kehilangan aset milik desa.",
        'rekomendasi' => "Memerintahkan Pj. Penghulu untuk menugaskan Sekretaris Desa melakukan sensus aset, membukukan seluruh aset desa ke dalam KIB, serta memasang label kodefikasi barang."
    ],
    [
        'kategori'    => 'PENGANGGARAN & SILPA',
        'kode'        => '0501',
        'judul'       => 'Realisasi Belanja Dilaksanakan Melampaui Pagu Anggaran APBDesa yang Ditetapkan',
        'kondisi'     => "Realisasi belanja pada beberapa pos kegiatan pada Kepenghuluan {DESA} melampaui plafon pagu anggaran yang telah ditetapkan dalam Peraturan Desa tentang APBDesa tanpa melalui mekanisme perubahan APBDesa terlebih dahulu.",
        'kriteria'    => "Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa yang melarang pengeluaran belanja melebihi pagu yang telah ditetapkan dalam APBDesa.",
        'sebab'       => "Pelaksana Kegiatan Anggaran (PKA) tidak melakukan pengendalian penyerapan anggaran secara tertib.",
        'akibat'      => "Pelanggaran terhadap disiplin anggaran dan tidak tertibnya tata kelola keuangan desa.",
        'rekomendasi' => "Memberikan teguran kepada PKA dan memastikan seluruh pergeseran anggaran di masa mendatang wajib dituangkan dalam Perdes Perubahan APBDesa sebelum dana dibelanjakan."
    ],
    [
        'kategori'    => 'PENGANGGARAN & SILPA',
        'kode'        => '0502',
        'judul'       => 'Sisa Lebih Perhitungan Anggaran (SiLPA) Tahun Lalu Belum Dianggarkan Kembali dalam APBDesa',
        'kondisi'     => "Berdasarkan rekening koran per 31 Desember tahun sebelumnya, terdapat saldo kas desa (SiLPA) pada Kepenghuluan {DESA} yang belum dianggarkan kembali ke dalam pos Penerimaan Pembiayaan APBDesa tahun berjalan.",
        'kriteria'    => "Permendagri Nomor 20 Tahun 2018 Pasal 29 ayat (1) bahwa SiLPA tahun sebelumnya merupakan penerimaan pembiayaan yang wajib dianggarkan kembali.",
        'sebab'       => "Sekretaris Desa dan Penghulu tidak cermat dalam menyusun dokumen penganggaran APBDesa.",
        'akibat'      => "Saldo kas desa mengendap tidak termanfaatkan secara optimal untuk program pembangunan masyarakat desa.",
        'rekomendasi' => "Menginstruksikan Pj. Penghulu untuk memasukkan sisa dana SiLPA tersebut ke dalam dokumen Perubahan APBDesa sesuai prioritas kebutuhan masyarakat."
    ],
    [
        'kategori'    => 'BANTUAN SOSIAL & BLT',
        'kode'        => '0402',
        'judul'       => 'Penyaluran Bantuan Langsung Tunai (BLT Dana Desa) Kurang Tertib Administrasi dan Penerima',
        'kondisi'     => "Penyaluran dana BLT Dana Desa pada Kepenghuluan {DESA} tidak didukung dengan Berita Acara Perkades Penetapan KPM hasil Musdesus yang lengkap, atau terdapat ketidakcocokan daftar tanda terima keluarga penerima manfaat.",
        'kriteria'    => "Peraturan Menteri Desa PDTT dan Peraturan Menteri Keuangan tentang Petunjuk Teknis Pengalokasian dan Penyaluran Bantuan Langsung Tunai (BLT) Dana Desa.",
        'sebab'       => "Tim Verifikasi Desa tidak melakukan pemutakhiran data warga miskin/KPM secara berkala.",
        'akibat'      => "Penyaluran dana BLT berisiko tidak tepat sasaran dan menimbulkan komplain dari masyarakat.",
        'rekomendasi' => "Memerintahkan Pj. Penghulu untuk melakukan Musdesus ulang guna memvalidasi daftar KPM dan melengkapi seluruh administrasi penyaluran dana BLT."
    ]
];

$count = 0;
foreach ($seeds as $s) {
    $exists = DB::val("SELECT id FROM kka_bank_temuan WHERE kode = ?", [$s['kode']]);
    if ($exists) {
        DB::update('kka_bank_temuan', $s, ['id' => $exists]);
    } else {
        DB::insert('kka_bank_temuan', $s);
    }
    $count++;
}

echo "Successfully seeded/updated $count BPKP standard bank data temuan records.\n";
echo "Total records in kka_bank_temuan: " . DB::val("SELECT COUNT(*) FROM kka_bank_temuan") . "\n";
