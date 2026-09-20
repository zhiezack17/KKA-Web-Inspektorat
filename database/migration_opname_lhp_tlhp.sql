-- ==========================================================
-- Migration: Berita Acara Opname Kas, Kustomisasi Narasi LHP, & TLHP 60 Hari
-- Inspektorat Kabupaten Rokan Hilir
-- Standard: Pedoman Kendali Mutu Pengawasan APIP & Permenpan RB
-- ==========================================================

-- 1. Tabel Berita Acara Pemeriksaan Kas (Opname Kas Desa)
CREATE TABLE IF NOT EXISTS `kka_opname_kas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `desa_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `spt_id` INT UNSIGNED DEFAULT NULL,
  `no_bap` VARCHAR(100) NOT NULL,
  `tgl_pemeriksaan` DATE NOT NULL,
  `waktu_pemeriksaan` VARCHAR(50) DEFAULT '09.30 WIB',
  `tempat_pemeriksaan` VARCHAR(255) DEFAULT 'Kantor Kepenghuluan',
  `nama_bendahara` VARCHAR(150) NOT NULL,
  `nip_bendahara` VARCHAR(50) DEFAULT NULL,
  `nama_kepala_desa` VARCHAR(150) NOT NULL,
  `nama_ketua_tim` VARCHAR(150) NOT NULL,
  `nip_ketua_tim` VARCHAR(50) DEFAULT NULL,
  `nama_anggota` VARCHAR(255) DEFAULT NULL,
  `rincian_uang_kertas` TEXT DEFAULT NULL,
  `total_kertas` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `rincian_uang_logam` TEXT DEFAULT NULL,
  `total_logam` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `total_kas_fisik` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `saldo_bank` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `nama_bank` VARCHAR(100) DEFAULT 'Bank Riau Kepri Syariah',
  `no_rekening_bank` VARCHAR(100) DEFAULT NULL,
  `total_kas_riil` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `saldo_bku` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `selisih_kas` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `status_selisih` ENUM('COCOK', 'LEBIH', 'KURANG') NOT NULL DEFAULT 'COCOK',
  `penjelasan_selisih` TEXT DEFAULT NULL,
  `catatan_pemeriksaan` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_opname_desa` (`desa_id`),
  INDEX `idx_opname_tahun` (`tahun_anggaran`),
  INDEX `idx_opname_spt` (`spt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Kustomisasi Narasi LHP (Bab I s.d IV)
CREATE TABLE IF NOT EXISTS `kka_lhp_narasi` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `desa_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `ringkasan_eksekutif` TEXT DEFAULT NULL,
  `dasar_penugasan` TEXT DEFAULT NULL,
  `tujuan_pengawasan` TEXT DEFAULT NULL,
  `ruang_lingkup` TEXT DEFAULT NULL,
  `batasan_pengawasan` TEXT DEFAULT NULL,
  `gambaran_umum` TEXT DEFAULT NULL,
  `kesimpulan` TEXT DEFAULT NULL,
  `saran_penutup` TEXT DEFAULT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_lhp_desa_tahun` (`desa_id`, `tahun_anggaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Pemantauan Tindak Lanjut Hasil Pengawasan (TLHP 60 Hari) & Rekap Pemulihan
CREATE TABLE IF NOT EXISTS `kka_tindak_lanjut` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `temuan_id` INT UNSIGNED NOT NULL,
  `desa_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `status` ENUM('BELUM', 'PROSES', 'TUNTAS') NOT NULL DEFAULT 'BELUM',
  `tgl_lhp` DATE DEFAULT NULL,
  `batas_waktu_tl` DATE DEFAULT NULL,
  `rekomendasi_teks` TEXT DEFAULT NULL,
  `uraian_tindak_lanjut` TEXT DEFAULT NULL,
  `nominal_rekomendasi` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `nominal_disetor` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `sisa_kerugian` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `no_bukti_setor` VARCHAR(150) DEFAULT NULL,
  `tgl_setor` DATE DEFAULT NULL,
  `dokumen_bukti` VARCHAR(255) DEFAULT NULL,
  `verifikasi_apip` ENUM('BELUM_VERIFIKASI', 'SESUAI', 'BELUM_SESUAI') NOT NULL DEFAULT 'BELUM_VERIFIKASI',
  `catatan_apip` TEXT DEFAULT NULL,
  `diverifikasi_oleh` INT UNSIGNED DEFAULT NULL,
  `tgl_verifikasi` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tl_desa` (`desa_id`),
  INDEX `idx_tl_tahun` (`tahun_anggaran`),
  INDEX `idx_tl_temuan` (`temuan_id`),
  INDEX `idx_tl_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
