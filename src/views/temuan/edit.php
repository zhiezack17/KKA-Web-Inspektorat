<?php
partial('head', ['title' => 'Edit Konsep Temuan ' . $temuan['nomor_temuan']]);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-pen-to-square" style="color:#d97706"></i>
          Edit Konsep Temuan: <?= e($temuan['nomor_temuan']) ?>
        </h2>
        <p>Perbarui rincian 5 Unsur Temuan, Tanggapan Auditi, dan Status Pembahasan.</p>
      </div>
      <a href="<?= url('temuan?desa_id=' . $temuan['desa_id'] . '&tahun=' . $temuan['tahun_anggaran']) ?>" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Temuan
      </a>
    </div>

    <div class="card" style="max-width:980px;margin:0 auto">
      <form method="post" action="<?= url('temuan/update') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $temuan['id'] ?>">

        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:14px;margin-bottom:14px">
          <div class="field">
            <label>Desa / Kepenghuluan Objek Pemeriksaan <span class="req">*</span></label>
            <select name="desa_id" required class="input">
              <?php foreach ($daftarDesa as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $temuan['desa_id'] == $d['id'] ? 'selected' : '' ?>>
                  <?= e($d['nama']) ?> (Kec. <?= e($d['kecamatan']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Tahun Anggaran <span class="req">*</span></label>
            <input type="number" name="tahun_anggaran" class="input" value="<?= $temuan['tahun_anggaran'] ?>" required>
          </div>

          <div class="field">
            <label>Nomor / Kode KTP <span class="req">*</span></label>
            <input type="text" name="nomor_temuan" class="input" value="<?= e($temuan['nomor_temuan']) ?>" required>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:14px;margin-bottom:18px">
          <div class="field">
            <label>Pokok / Judul Temuan <span class="req">*</span></label>
            <input type="text" name="judul" class="input" value="<?= e($temuan['judul']) ?>" required>
          </div>

          <div class="field">
            <label>Bidang Belanja</label>
            <input type="text" name="bidang_nama" class="input" value="<?= e($temuan['bidang_nama'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Nilai Kerugian / Temuan (Rp)</label>
            <input type="text" name="nominal" class="input" data-money value="<?= number_format((float)$temuan['nominal'], 0, ',', '.') ?>">
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
          <textarea name="kondisi" class="textarea" rows="4" required><?= e($temuan['kondisi']) ?></textarea>
        </div>

        <!-- 2. KRITERIA -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">2</span>
            Kriteria (Ketentuan Perundang-Undangan / Standar yang Dilanggar) <span class="req">*</span>
          </label>
          <textarea name="kriteria" class="textarea" rows="3" required><?= e($temuan['kriteria']) ?></textarea>
        </div>

        <!-- 3. SEBAB -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">3</span>
            Sebab (Mengapa Kondisi Tersebut Terjadi / Kelemahan Sistem Pengendalian) <span class="req">*</span>
          </label>
          <textarea name="sebab" class="textarea" rows="3" required><?= e($temuan['sebab']) ?></textarea>
        </div>

        <!-- 4. AKIBAT -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">4</span>
            Akibat (Dampak Finansial / Risiko Kerugian Kas Desa / Risiko Hukum) <span class="req">*</span>
          </label>
          <textarea name="akibat" class="textarea" rows="3" required><?= e($temuan['akibat']) ?></textarea>
        </div>

        <!-- 5. REKOMENDASI -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">5</span>
            Rekomendasi (Perintah Tindak Lanjut Perbaikan / Pengembalian Kas) <span class="req">*</span>
          </label>
          <textarea name="rekomendasi" class="textarea" rows="4" required><?= e($temuan['rekomendasi']) ?></textarea>
        </div>

        <!-- TANGGAPAN AUDITI & STATUS -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:20px">
          <div style="font-weight:700;color:var(--slate-700);font-size:13px;margin-bottom:12px">
            KLARIFIKASI &amp; STATUS PEMBAHASAN
          </div>
          <div class="field" style="margin-bottom:12px">
            <label>Tanggapan / Klarifikasi dari Pihak Auditi (Penghulu / Kaur Keuangan)</label>
            <textarea name="tanggapan_auditi" class="textarea" rows="3" placeholder="Masukkan penjelasan auditi saat pembahasan Konsep Temuan..."><?= e($temuan['tanggapan_auditi'] ?? '') ?></textarea>
          </div>
          <div class="field" style="margin:0;max-width:300px">
            <label>Status Temuan</label>
            <select name="status" class="input">
              <option value="DRAFT" <?= $temuan['status'] === 'DRAFT' ? 'selected' : '' ?>>DRAFT (Penyusunan Tim)</option>
              <option value="DIBAHAS" <?= $temuan['status'] === 'DIBAHAS' ? 'selected' : '' ?>>DIBAHAS (Sudah dikonfirmasi ke Desa)</option>
              <option value="FINAL_LHP" <?= $temuan['status'] === 'FINAL_LHP' ? 'selected' : '' ?>>FINAL (Siap Masuk ke Naskah LHP)</option>
            </select>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px">
          <a href="<?= url('temuan?desa_id=' . $temuan['desa_id'] . '&tahun=' . $temuan['tahun_anggaran']) ?>" class="btn btn-ghost">Batal</a>
          <button type="submit" class="btn btn-primary" style="background:#d97706;border-color:#d97706;padding:10px 24px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
