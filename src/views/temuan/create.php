<?php
partial('head', ['title' => 'Buat Konsep Temuan Pemeriksaan (KTP 5 Unsur)']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-file-circle-plus" style="color:#d97706"></i>
          Penyusunan Konsep Temuan Pemeriksaan (KTP)
        </h2>
        <p>Pengisian 5 Unsur Standar SPKN BPK-RI / Kendali Mutu BPKP untuk Bahan Naskah Hasil Pengawasan (NHP) &amp; LHP.</p>
      </div>
      <a href="<?= url('temuan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Temuan
      </a>
    </div>

    <div class="card" style="max-width:980px;margin:0 auto">
      <form method="post" action="<?= url('temuan/store') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="sesi_id" value="<?= $sesiId ?>">
        <input type="hidden" name="rincian_id" value="<?= $rincianId ?>">

        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#92400e;display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-circle-info" style="font-size:18px"></i>
          <div>
            Format ini telah disesuaikan dengan <b>5 Unsur Standar Pemeriksaan Keuangan Negara (SPKN)</b>: <i>Kondisi, Kriteria, Sebab, Akibat, dan Rekomendasi</i>.
            Data ini akan otomatis dirangkum ke dalam Dokumen LHP Desa resmi.
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:14px;margin-bottom:14px">
          <div class="field">
            <label>Desa / Kepenghuluan Objek Pemeriksaan <span class="req">*</span></label>
            <select name="desa_id" required class="input">
              <option value="">-- Pilih Kepenghuluan --</option>
              <?php foreach ($daftarDesa as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $desaId == $d['id'] ? 'selected' : '' ?>>
                  <?= e($d['nama']) ?> (Kec. <?= e($d['kecamatan']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Tahun Anggaran <span class="req">*</span></label>
            <input type="number" name="tahun_anggaran" class="input" value="<?= $tahun ?>" required>
          </div>

          <div class="field">
            <label>Nomor / Kode KTP <span class="req">*</span></label>
            <input type="text" name="nomor_temuan" class="input" value="<?= e($nomorTemuan) ?>" required placeholder="KTP-01">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:14px;margin-bottom:18px">
          <div class="field">
            <label>Pokok / Judul Temuan <span class="req">*</span></label>
            <input type="text" name="judul" class="input" value="<?= e($autoJudul) ?>" required placeholder="Cth: Belanja Semenisasi Sebesar Rp... Tidak Didukung Bukti Kuitansi Sah">
          </div>

          <div class="field">
            <label>Bidang Belanja</label>
            <input type="text" name="bidang_nama" class="input" value="<?= e($bidangNama) ?>" placeholder="Cth: Bidang 2: Pembangunan Desa">
          </div>

          <div class="field">
            <label>Nilai Kerugian / Temuan (Rp)</label>
            <input type="text" name="nominal" class="input" data-money value="<?= number_format((float)$autoNominal, 0, ',', '.') ?>" placeholder="0">
          </div>
        </div>

        <div style="font-weight:700;color:var(--slate-800);font-size:14px;border-bottom:2px solid #f1f5f9;padding-bottom:6px;margin:24px 0 16px;text-transform:uppercase;letter-spacing:0.5px">
          5 UNSUR TEMUAN PEMERIKSAAN
        </div>

        <!-- 1. KONDISI -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">1</span>
            Kondisi (Fakta / Apa yang Terjadi di Lapangan) <span class="req">*</span>
          </label>
          <textarea name="kondisi" class="textarea" rows="4" required placeholder="Uraikan fakta hasil pemeriksaan fisik, dokumen SPJ, selisih kas, atau ketidaksesuaian yang ditemukan..."><?= e($autoKondisi) ?></textarea>
          <small style="color:var(--slate-500);font-size:11.5px">Paparkan angka realisasi, kuitansi, tanggal pengujian, dan pihak-pihak terkait secara objektif.</small>
        </div>

        <!-- 2. KRITERIA -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">2</span>
            Kriteria (Ketentuan Perundang-Undangan / Standar yang Dilanggar) <span class="req">*</span>
          </label>
          <textarea name="kriteria" class="textarea" rows="3" required placeholder="Sebutkan pasal dan undang-undang/peraturan yang dilanggar (cth: Permendagri 20/2018, Perbup Rohil, RAB, dsb)..."><?= e($autoKriteria ?: "1. Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa;\n2. Peraturan Bupati Rokan Hilir tentang Petunjuk Teknis Pengelolaan Keuangan Kepenghuluan.") ?></textarea>
        </div>

        <!-- 3. SEBAB -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">3</span>
            Sebab (Mengapa Kondisi Tersebut Terjadi / Kelemahan Sistem Pengendalian) <span class="req">*</span>
          </label>
          <textarea name="sebab" class="textarea" rows="3" required placeholder="Jelaskan alasan mengapa kondisi ini terjadi (cth: kelalaian bendahara, kurangnya pengendalian Penghulu, rekanan belum setor)..."><?= e($autoSebab ?: "1. Kelalaian Kaur Keuangan/Bendahara dalam melengkapi bukti pertanggungjawaban secara tertib;\n2. Kurangnya pengawasan dan pengendalian dari Pj. Penghulu atas pengeluaran kas desa.") ?></textarea>
        </div>

        <!-- 4. AKIBAT -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">4</span>
            Akibat (Dampak Finansial / Risiko Kerugian Kas Desa / Risiko Hukum) <span class="req">*</span>
          </label>
          <textarea name="akibat" class="textarea" rows="3" required placeholder="Dampak nyata atau potensi kerugian bagi keuangan kepenghuluan atau kepentingan masyarakat..."><?= e($autoAkibat) ?></textarea>
        </div>

        <!-- 5. REKOMENDASI -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">5</span>
            Rekomendasi (Perintah Tindak Lanjut Perbaikan / Pengembalian Kas) <span class="req">*</span>
          </label>
          <textarea name="rekomendasi" class="textarea" rows="4" required placeholder="Uraikan rekomendasi konkret kepada Bupati / Camat / Penghulu (cth: teguran tertulis, setor ke Rekening Kas Desa, perbaiki fisik)..."><?= e($autoRekomendasi ?: "1. Memerintahkan Pj. Penghulu untuk memberikan teguran tertulis kepada aparatur terkait;\n2. Menginstruksikan kepada pihak yang bertanggung jawab untuk segera menyetorkan kembali dana/selisih ke Rekening Kas Desa (RKD).") ?></textarea>
        </div>

        <!-- TANGGAPAN AUDITI & STATUS -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:20px">
          <div style="font-weight:700;color:var(--slate-700);font-size:13px;margin-bottom:12px">
            KLARIFIKASI &amp; STATUS PEMBAHASAN
          </div>
          <div class="field" style="margin-bottom:12px">
            <label>Tanggapan / Klarifikasi dari Pihak Auditi (Penghulu / Kaur Keuangan)</label>
            <textarea name="tanggapan_auditi" class="textarea" rows="3" placeholder="Masukkan penjelasan auditi saat pembahasan Konsep Temuan (bila sudah ada)..."></textarea>
          </div>
          <div class="field" style="margin:0;max-width:300px">
            <label>Status Temuan</label>
            <select name="status" class="input">
              <option value="DRAFT">DRAFT (Penyusunan Tim)</option>
              <option value="DIBAHAS">DIBAHAS (Sudah dikonfirmasi ke Desa)</option>
              <option value="FINAL_LHP">FINAL (Siap Masuk ke Naskah LHP)</option>
            </select>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px">
          <a href="<?= url('temuan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" class="btn btn-ghost">Batal</a>
          <button type="submit" class="btn btn-primary" style="background:#d97706;border-color:#d97706;padding:10px 24px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Konsep Temuan
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
