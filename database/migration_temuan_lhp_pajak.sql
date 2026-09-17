-- ==========================================================
-- Migration: Modul Temuan (KTP 5 Unsur), LHP Otomatis & Uji Pajak
-- Inspektorat Kabupaten Rokan Hilir
-- Standard: SPKN BPK-RI & Pedoman Kendali Mutu BPKP
-- ==========================================================

-- 1. Buat Tabel kka_temuan (Konsep Temuan Pemeriksaan)
CREATE TABLE IF NOT EXISTS `kka_temuan` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `desa_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `spt_id` INT UNSIGNED DEFAULT NULL,
  `sesi_id` INT UNSIGNED DEFAULT NULL,
  `rincian_id` INT UNSIGNED DEFAULT NULL,
  `nomor_temuan` VARCHAR(50) NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `bidang_nama` VARCHAR(150) DEFAULT NULL,
  `nominal` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `kondisi` TEXT NOT NULL,
  `kriteria` TEXT NOT NULL,
  `sebab` TEXT NOT NULL,
  `akibat` TEXT NOT NULL,
  `rekomendasi` TEXT NOT NULL,
  `tanggapan_auditi` TEXT DEFAULT NULL,
  `status` ENUM('DRAFT', 'DIBAHAS', 'FINAL_LHP') NOT NULL DEFAULT 'DRAFT',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_temuan_desa` (`desa_id`),
  INDEX `idx_temuan_tahun` (`tahun_anggaran`),
  INDEX `idx_temuan_spt` (`spt_id`),
  INDEX `idx_temuan_sesi` (`sesi_id`),
  INDEX `idx_temuan_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tambah Kolom Pengujian Pajak Belanja pada kka_rincian (jika belum ada)
SET @dbname = DATABASE();
SET @tablename = 'kka_rincian';
SET @columnname = 'potong_ppn';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_rincian` 
     ADD COLUMN `potong_ppn` TINYINT(1) NOT NULL DEFAULT 0 AFTER `keterangan`,
     ADD COLUMN `nominal_ppn` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `potong_ppn`,
     ADD COLUMN `potong_pph` VARCHAR(20) DEFAULT NULL AFTER `nominal_ppn`,
     ADD COLUMN `nominal_pph` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `potong_pph`,
     ADD COLUMN `status_pajak` ENUM(\'TIDAK_TERUTANG\', \'SUDAH_SETOR\', \'BELUM_SETOR\') NOT NULL DEFAULT \'TIDAK_TERUTANG\' AFTER `nominal_pph`,
     ADD COLUMN `ntpn` VARCHAR(50) DEFAULT NULL AFTER `status_pajak`;'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
