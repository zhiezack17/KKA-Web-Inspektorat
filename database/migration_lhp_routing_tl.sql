-- ==========================================================
-- Migration: Status LHP, Routing Slip Kendali Mutu & Akun Bagian TL
-- Inspektorat Kabupaten Rokan Hilir
-- Standard: APIP Ruang Kerja Digital, Simondes & Permenpan-RB
-- ==========================================================

-- 1. Tambahkan kolom status pengesahan dan catatan berjenjang pada kka_lhp_narasi
SET @dbname = DATABASE();
SET @tablename = 'kka_lhp_narasi';

-- status_lhp
SET @columnname = 'status_lhp';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `status_lhp` ENUM(\'DRAFT\', \'REVIU_DALNIS\', \'TELAAH_IRBAN\', \'DISAHKAN_INSPEKTUR\') NOT NULL DEFAULT \'DRAFT\' AFTER `tahun_anggaran`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tgl_disahkan_inspektur
SET @columnname = 'tgl_disahkan_inspektur';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `tgl_disahkan_inspektur` DATETIME DEFAULT NULL AFTER `status_lhp`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- disahkan_oleh_nama
SET @columnname = 'disahkan_oleh_nama';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `disahkan_oleh_nama` VARCHAR(150) DEFAULT NULL AFTER `tgl_disahkan_inspektur`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- catatan_dalnis
SET @columnname = 'catatan_dalnis';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `catatan_dalnis` TEXT DEFAULT NULL AFTER `disahkan_oleh_nama`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tgl_reviu_dalnis
SET @columnname = 'tgl_reviu_dalnis';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `tgl_reviu_dalnis` DATETIME DEFAULT NULL AFTER `catatan_dalnis`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- dalnis_nama
SET @columnname = 'dalnis_nama';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `dalnis_nama` VARCHAR(150) DEFAULT NULL AFTER `tgl_reviu_dalnis`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- catatan_irban
SET @columnname = 'catatan_irban';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `catatan_irban` TEXT DEFAULT NULL AFTER `dalnis_nama`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tgl_reviu_irban
SET @columnname = 'tgl_reviu_irban';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `tgl_reviu_irban` DATETIME DEFAULT NULL AFTER `catatan_irban`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- irban_nama
SET @columnname = 'irban_nama';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `kka_lhp_narasi` ADD COLUMN `irban_nama` VARCHAR(150) DEFAULT NULL AFTER `tgl_reviu_irban`;'
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Modifikasi ENUM role di kka_users untuk mengikutsertakan 'operator_tl'
ALTER TABLE `kka_users` MODIFY COLUMN `role` ENUM('admin','auditor','dalnis','irban','inspektur','operator_spt','operator_tl') NOT NULL DEFAULT 'auditor';

-- 3. Tambah akun default untuk Bagian Tindak Lanjut (TLHP)
INSERT INTO `kka_users` (`nama`, `email`, `username`, `password_hash`, `role`, `nip`, `pangkat`, `jabatan`, `is_active`, `created_at`)
SELECT 'Bagian Tindak Lanjut (TLHP)', 'tlhp@arsipdigital-inspektorat.com', 'operator_tl', '$2y$10$zphS/Joo6QIES5croeZZv.cZs0mUp67Kz75/HqljPp8rMJFccXaay', 'operator_tl', '19850615 201001 1 012', 'Penata / III.c', 'Pengadministrasi Tindak Lanjut APIP', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `kka_users` WHERE `username` = 'operator_tl');
