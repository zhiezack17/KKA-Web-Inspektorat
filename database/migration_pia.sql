-- Tabel Nota Dinas PIA
CREATE TABLE IF NOT EXISTS `kka_pia_nd` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `no_nd` VARCHAR(100) NOT NULL,
  `tgl_nd` DATE NOT NULL,
  `irban_id` INT UNSIGNED NOT NULL,
  `irban_nama` VARCHAR(150) NOT NULL,
  `desa_id` INT UNSIGNED NOT NULL,
  `kecamatan_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `tujuan` VARCHAR(255) NOT NULL DEFAULT 'Pengembangan Informasi Awal (PIA)',
  `tgl_mulai` DATE NOT NULL,
  `tgl_selesai` DATE NOT NULL,
  `lama_hari` INT NOT NULL DEFAULT 3,
  `dalnis_id` INT UNSIGNED NOT NULL,
  `dalnis_nama` VARCHAR(150) NOT NULL,
  `ketua_tim_id` INT UNSIGNED NOT NULL,
  `ketua_tim_nama` VARCHAR(150) NOT NULL,
  `anggota_data` TEXT DEFAULT NULL,
  `sumber_informasi` TEXT DEFAULT NULL,
  `catatan_irban` TEXT DEFAULT NULL,
  `catatan_inspektur` TEXT DEFAULT NULL,
  `tgl_disposisi` DATETIME DEFAULT NULL,
  `status` ENUM('DRAFT', 'DIAJUKAN_INSPEKTUR', 'DISETUJUI', 'DITOLAK') NOT NULL DEFAULT 'DRAFT',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Surat Tugas PIA
CREATE TABLE IF NOT EXISTS `kka_pia_spt` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pia_nd_id` INT UNSIGNED DEFAULT NULL,
  `no_spt` VARCHAR(100) NOT NULL,
  `tgl_spt` DATE NOT NULL,
  `tgl_mulai` DATE NOT NULL,
  `tgl_selesai` DATE NOT NULL,
  `lama_hari` INT NOT NULL DEFAULT 3,
  `dasar_spt` TEXT DEFAULT NULL,
  `tujuan_spt` TEXT DEFAULT NULL,
  `status` ENUM('DRAFT', 'DITERBITKAN', 'SELESAI') NOT NULL DEFAULT 'DRAFT',
  `file_spt` VARCHAR(255) DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel LHP PIA dan DPP (Desain Penugasan Pengawasan)
CREATE TABLE IF NOT EXISTS `kka_pia_lhp` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pia_spt_id` INT UNSIGNED NOT NULL,
  `desa_id` INT UNSIGNED NOT NULL,
  `tahun_anggaran` SMALLINT NOT NULL,
  `daftar_regulasi` TEXT DEFAULT NULL,
  `panduan_pengumpulan_data` TEXT DEFAULT NULL,
  `matriks_risiko` TEXT DEFAULT NULL,
  `hasil_penelaahan` TEXT DEFAULT NULL,
  `simpulan` TEXT DEFAULT NULL,
  `rekomendasi` TEXT DEFAULT NULL,
  `dpp_konteks_strategis` TEXT DEFAULT NULL,
  `dpp_pengembangan_hipotesis` TEXT DEFAULT NULL,
  `dpp_matriks_metodologi` TEXT DEFAULT NULL,
  `dpp_kebutuhan_sumber_daya` TEXT DEFAULT NULL,
  `status` ENUM('DRAFT', 'DIAJUKAN_DALNIS', 'DIAJUKAN_IRBAN', 'FINAL', 'REVISI') NOT NULL DEFAULT 'DRAFT',
  `keputusan_inspektur` ENUM('BELUM_ADA', 'LAYAK_AUDIT', 'TIDAK_LAYAK_AUDIT') NOT NULL DEFAULT 'BELUM_ADA',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
