-- ==========================================================
-- Migration: Routing Slip Map Kuning LHA & Penugasan Irban
-- Inspektorat Kabupaten Rokan Hilir
-- Menambahkan kolom SPT, LHA, dan Wakil Penanggung Jawab (Irban)
-- ==========================================================

ALTER TABLE `kka_sesi`
  ADD COLUMN `no_spt` VARCHAR(100) DEFAULT NULL AFTER `ref_kka`,
  ADD COLUMN `tgl_spt` DATE DEFAULT NULL AFTER `no_spt`,
  ADD COLUMN `tgl_spt_selesai` DATE DEFAULT NULL AFTER `tgl_spt`,
  ADD COLUMN `alamat_objek` VARCHAR(255) DEFAULT NULL AFTER `tgl_spt_selesai`,
  ADD COLUMN `jenis_audit` VARCHAR(100) DEFAULT 'Audit Dengan Tujuan Tertentu (ADTT)' AFTER `alamat_objek`,
  ADD COLUMN `irban_id` INT UNSIGNED DEFAULT NULL AFTER `dalnis_id`,
  ADD COLUMN `irban_nama` VARCHAR(150) DEFAULT NULL AFTER `irban_id`,
  ADD COLUMN `catatan_reviu_irban` TEXT DEFAULT NULL AFTER `tgl_reviu_dalnis`,
  ADD COLUMN `tgl_reviu_irban` DATETIME DEFAULT NULL AFTER `catatan_reviu_irban`,
  ADD COLUMN `no_lha` VARCHAR(100) DEFAULT NULL AFTER `tgl_reviu_irban`,
  ADD COLUMN `tgl_lha` DATE DEFAULT NULL AFTER `no_lha`,
  ADD INDEX `idx_sesi_irban` (`irban_id`);

