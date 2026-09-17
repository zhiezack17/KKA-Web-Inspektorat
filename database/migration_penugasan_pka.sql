-- ==========================================================
-- Migration: Alur Pra-Audit (Nota Dinas, SPT, & Matriks PKA)
-- Inspektorat Kabupaten Rokan Hilir
-- Menghubungkan Pengajuan Tim, Disposisi Inspektur, Penerbitan SPT,
-- dan Program Kerja Audit (PKA) dengan pembagian tugas Anggota 1 & 2
-- ==========================================================

-- 1. Perluas role kka_users
ALTER TABLE `kka_users` 
  MODIFY COLUMN `role` ENUM('admin','auditor','dalnis','irban','inspektur','operator_spt') NOT NULL DEFAULT 'auditor';

-- 2. Tambah akun Inspektur & Operator SPT jika belum ada
INSERT INTO `kka_users` (`nama`, `email`, `username`, `password_hash`, `role`, `nip`, `jabatan`, `is_active`)
SELECT 'H. SARMAN SYAHRONI, ST., M.IP', 'inspektur@rohilkab.go.id', 'inspektur', '$2y$10$RCoE5pZXXz4XOiJXTGRwUuKqumEKeA4uxDPKSQUTmT8JHlBZ2Azbi', 'inspektur', '19760810 200312 1 004', 'Inspektur Daerah Kabupaten Rokan Hilir', 1
WHERE NOT EXISTS (SELECT 1 FROM `kka_users` WHERE `username` = 'inspektur' OR `role` = 'inspektur');

INSERT INTO `kka_users` (`nama`, `email`, `username`, `password_hash`, `role`, `nip`, `jabatan`, `is_active`)
SELECT 'BAGIAN PERENCANAAN & EVALUASI (OPERATOR SPT)', 'spt@rohilkab.go.id', 'operator_spt', '$2y$10$RCoE5pZXXz4XOiJXTGRwUuKqumEKeA4uxDPKSQUTmT8JHlBZ2Azbi', 'operator_spt', '-', 'Pengelola Surat Perintah Tugas', 1
WHERE NOT EXISTS (SELECT 1 FROM `kka_users` WHERE `username` = 'operator_spt');

-- 3. Tabel kka_nota_dinas
CREATE TABLE IF NOT EXISTS `kka_nota_dinas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `no_nd` VARCHAR(100) NOT NULL,
  `tgl_nd` DATE NOT NULL,
  `irban_id` INT UNSIGNED NOT NULL,
  `irban_nama` VARCHAR(150) NOT NULL,
  `desa_id` INT UNSIGNED NOT NULL,
  `kecamatan_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `tujuan` VARCHAR(255) NOT NULL DEFAULT 'Pemeriksaan Reguler Ketaatan Pengelolaan Keuangan Kepenghuluan',
  `jenis_audit` VARCHAR(100) NOT NULL DEFAULT 'Audit Dengan Tujuan Tertentu (ADTT)',
  `tgl_mulai` DATE NOT NULL,
  `tgl_selesai` DATE NOT NULL,
  `lama_hari` INT NOT NULL DEFAULT 10,
  `dalnis_id` INT UNSIGNED NOT NULL,
  `dalnis_nama` VARCHAR(150) NOT NULL,
  `ketua_tim_id` INT UNSIGNED NOT NULL,
  `ketua_tim_nama` VARCHAR(150) NOT NULL,
  `anggota_data` TEXT DEFAULT NULL,
  `catatan_irban` TEXT DEFAULT NULL,
  `catatan_inspektur` TEXT DEFAULT NULL,
  `tgl_disposisi` DATETIME DEFAULT NULL,
  `status` ENUM('DRAFT', 'DIAJUKAN_INSPEKTUR', 'DISETUJUI', 'DITOLAK') NOT NULL DEFAULT 'DRAFT',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_nd_irban` (`irban_id`),
  INDEX `idx_nd_desa` (`desa_id`),
  INDEX `idx_nd_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel kka_spt
CREATE TABLE IF NOT EXISTS `kka_spt` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nota_dinas_id` INT UNSIGNED DEFAULT NULL,
  `no_spt` VARCHAR(100) NOT NULL,
  `tgl_spt` DATE NOT NULL,
  `tgl_mulai` DATE NOT NULL,
  `tgl_selesai` DATE NOT NULL,
  `lama_hari` INT NOT NULL DEFAULT 10,
  `desa_id` INT UNSIGNED NOT NULL,
  `kecamatan_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `tujuan` VARCHAR(255) NOT NULL DEFAULT 'Melakukan Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan Kepenghuluan',
  `dasar_hukum` TEXT DEFAULT NULL,
  `penanggung_jawab_nama` VARCHAR(150) NOT NULL DEFAULT 'H. SARMAN SYAHRONI, ST., M.IP',
  `penanggung_jawab_nip` VARCHAR(50) NOT NULL DEFAULT '19760810 200312 1 004',
  `wakil_pj_id` INT UNSIGNED DEFAULT NULL,
  `wakil_pj_nama` VARCHAR(150) DEFAULT NULL,
  `dalnis_id` INT UNSIGNED NOT NULL,
  `dalnis_nama` VARCHAR(150) NOT NULL,
  `ketua_tim_id` INT UNSIGNED NOT NULL,
  `ketua_tim_nama` VARCHAR(150) NOT NULL,
  `anggota_data` TEXT DEFAULT NULL,
  `status` ENUM('DRAFT', 'MENUNGGU_TTD', 'DITERBITKAN', 'DIBATALKAN') NOT NULL DEFAULT 'DRAFT',
  `catatan` TEXT DEFAULT NULL,
  `tgl_ttd_inspektur` DATETIME DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_spt_nd` (`nota_dinas_id`),
  INDEX `idx_spt_desa` (`desa_id`),
  INDEX `idx_spt_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel kka_pka
CREATE TABLE IF NOT EXISTS `kka_pka` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `spt_id` INT UNSIGNED NOT NULL,
  `no_pka` VARCHAR(100) NOT NULL,
  `tgl_pka` DATE NOT NULL,
  `desa_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `ketua_tim_id` INT UNSIGNED NOT NULL,
  `ketua_tim_nama` VARCHAR(150) NOT NULL,
  `dalnis_id` INT UNSIGNED NOT NULL,
  `dalnis_nama` VARCHAR(150) NOT NULL,
  `irban_id` INT UNSIGNED DEFAULT NULL,
  `irban_nama` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('DRAFT', 'REVIEW_DALNIS', 'DISETUJUI') NOT NULL DEFAULT 'DRAFT',
  `catatan_dalnis` TEXT DEFAULT NULL,
  `tgl_reviu_dalnis` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pka_spt` (`spt_id`),
  INDEX `idx_pka_desa` (`desa_id`),
  INDEX `idx_pka_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel kka_pka_langkah
CREATE TABLE IF NOT EXISTS `kka_pka_langkah` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pka_id` INT UNSIGNED NOT NULL,
  `bidang_kode` VARCHAR(50) DEFAULT NULL,
  `bidang_nama` VARCHAR(255) NOT NULL,
  `uraian_prosedur` TEXT NOT NULL,
  `tujuan_pengujian` TEXT DEFAULT NULL,
  `pelaksana_user_id` INT UNSIGNED DEFAULT NULL,
  `pelaksana_nama` VARCHAR(150) DEFAULT NULL,
  `waktu_rencana_jam` INT NOT NULL DEFAULT 8,
  `ref_kka_nomor` VARCHAR(100) DEFAULT NULL,
  `hasil_simpulan` TEXT DEFAULT NULL,
  `status_pelaksanaan` ENUM('BELUM', 'SEDANG', 'SELESAI') NOT NULL DEFAULT 'BELUM',
  `urutan` INT NOT NULL DEFAULT 1,
  INDEX `idx_langkah_pka` (`pka_id`),
  INDEX `idx_langkah_pelaksana` (`pelaksana_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Kolom relasi spt_id dan pka_id di kka_sesi
SET @dbname = DATABASE();
SET @col_spt = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'kka_sesi' AND COLUMN_NAME = 'spt_id');
SET @col_pka = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'kka_sesi' AND COLUMN_NAME = 'pka_id');

SET @stmt_spt = IF(@col_spt = 0, 'ALTER TABLE `kka_sesi` ADD COLUMN `spt_id` INT UNSIGNED DEFAULT NULL AFTER `ref_kka`, ADD INDEX `idx_sesi_spt` (`spt_id`);', 'SELECT 1;');
PREPARE stmt FROM @stmt_spt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @stmt_pka = IF(@col_pka = 0, 'ALTER TABLE `kka_sesi` ADD COLUMN `pka_id` INT UNSIGNED DEFAULT NULL AFTER `spt_id`, ADD INDEX `idx_sesi_pka` (`pka_id`);', 'SELECT 1;');
PREPARE stmt FROM @stmt_pka;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

