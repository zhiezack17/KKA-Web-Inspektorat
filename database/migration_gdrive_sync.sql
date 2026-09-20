-- Migration: Google Drive Synchronization Tracking Table
-- Inspektorat Kabupaten Rokan Hilir - KKA Digital

CREATE TABLE IF NOT EXISTS `kka_gdrive_sync` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tipe_dokumen` ENUM('PENUGASAN_ND', 'PENUGASAN_SPT', 'PKA', 'KKA_RINCIAN', 'OPNAME_KAS', 'LHP_FINAL', 'TLHP_BUKTI', 'LAMPIRAN') NOT NULL,
    `ref_id` INT UNSIGNED NOT NULL,
    `desa_id` INT UNSIGNED NOT NULL,
    `tahun_anggaran` SMALLINT NOT NULL,
    `irban_nama` VARCHAR(50) NOT NULL DEFAULT 'IRBAN IV',
    `file_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL DEFAULT 'text/html',
    `drive_file_id` VARCHAR(100) NOT NULL,
    `drive_folder_id` VARCHAR(100) NOT NULL,
    `web_view_link` VARCHAR(500) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('SYNCED', 'PENDING', 'FAILED') NOT NULL DEFAULT 'SYNCED',
    `error_message` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `synced_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_gdrive_tipe_ref` (`tipe_dokumen`, `ref_id`),
    INDEX `idx_gdrive_desa_ta` (`desa_id`, `tahun_anggaran`),
    INDEX `idx_gdrive_irban` (`irban_nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
