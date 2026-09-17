-- ==========================================================
-- Migration: Lembar Verifikasi & Reviu Berjenjang KKA
-- Inspektorat Kabupaten Rokan Hilir
-- Alur: DRAFT -> REVIEW_KETUA -> REVIEW_DALNIS -> SELESAI_FINAL
-- ==========================================================

-- Tambah kolom status dan audit trail verifikasi pada kka_sesi
ALTER TABLE `kka_sesi`
  ADD COLUMN `status` ENUM('DRAFT', 'REVIEW_KETUA', 'REVIEW_DALNIS', 'PERLU_REVISI', 'SELESAI_FINAL') NOT NULL DEFAULT 'DRAFT' AFTER `sumber_data`,
  ADD COLUMN `catatan_reviu_ketua` TEXT DEFAULT NULL AFTER `status`,
  ADD COLUMN `catatan_reviu_dalnis` TEXT DEFAULT NULL AFTER `catatan_reviu_ketua`,
  ADD COLUMN `tgl_reviu_ketua` DATETIME DEFAULT NULL AFTER `catatan_reviu_dalnis`,
  ADD COLUMN `tgl_reviu_dalnis` DATETIME DEFAULT NULL AFTER `tgl_reviu_ketua`,
  ADD COLUMN `ketua_tim_id` INT UNSIGNED DEFAULT NULL AFTER `tgl_reviu_dalnis`,
  ADD COLUMN `dalnis_id` INT UNSIGNED DEFAULT NULL AFTER `ketua_tim_id`,
  ADD INDEX `idx_sesi_status` (`status`),
  ADD INDEX `idx_sesi_ketua` (`ketua_tim_id`),
  ADD INDEX `idx_sesi_dalnis` (`dalnis_id`);
