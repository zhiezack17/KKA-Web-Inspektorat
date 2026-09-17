<?php $title = 'Buat Nota Dinas Penugasan - KKA Digital'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-nd-create">
  <div class="topbar">
    <div class="crumb">
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">☰</button>
      <i class="fa-solid fa-envelope-open-text"></i> <b>Penyusunan Nota Dinas Penugasan</b>
    </div>
    <div class="topbar-right">
      <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar ND
      </a>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Penyusunan Nota Dinas Penugasan</h2>
        <p>Permohonan penugasan audit dari Inspektur Pembantu kepada Inspektur Daerah.</p>
      </div>
    </div>

    <form method="POST" action="<?= url('penugasan/nota-dinas/store') ?>" id="ndForm">
      <?= csrf_field() ?>

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
        <!-- KOLOM KIRI: DATA UTAMA & TIM -->
        <div style="display:flex;flex-direction:column;gap:16px">
          <!-- KARTU 1: OBJEK & JADWAL AUDIT -->
          <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 14px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;color:#0f172a">
              <i class="fa-solid fa-building-flag" style="color:#0284c7"></i> 1. Objek Pemeriksaan &amp; Jadwal
            </h3>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px">
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Kepenghuluan / Desa Objek Audit <span style="color:red">*</span></label>
                <select name="desa_id" id="desa_id" class="input" required style="font-size:13.5px;width:100%">
                  <option value="">-- Pilih Kepenghuluan --</option>
                  <?php foreach ($desa as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= e($d['label']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tahun Anggaran <span style="color:red">*</span></label>
                <select name="tahun_anggaran" class="input" style="font-size:13.5px;width:100%">
                  <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="input" value="<?= date('Y-m-d') ?>" required style="width:100%">
              </div>
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" class="input" value="<?= date('Y-m-d', strtotime('+10 days')) ?>" required style="width:100%">
              </div>
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Lama Waktu (Hari)</label>
                <input type="number" name="lama_hari" class="input" value="10" min="1" max="60" required style="width:100%">
              </div>
            </div>

            <div style="margin-bottom:12px">
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tujuan / Keperluan Penugasan</label>
              <input type="text" name="tujuan" class="input" value="Pemeriksaan Reguler Ketaatan Pengelolaan Keuangan Kepenghuluan" required style="width:100%">
            </div>

            <div>
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Jenis Pengawasan</label>
              <input type="text" name="jenis_audit" class="input" value="Audit Dengan Tujuan Tertentu (ADTT)" required style="width:100%">
            </div>
          </div>

          <!-- KARTU 2: SUSUNAN TIM PEMERIKSA (HIERARKI RESMI) -->
          <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 14px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;color:#0f172a">
              <i class="fa-solid fa-users-gear" style="color:#059669"></i> 2. Usulan Susunan Tim Penugasan (Urutan Resmi)
            </h3>

            <!-- 1. WAKIL PENANGGUNGJAWAB (IRBAN) -->
            <div style="margin-bottom:14px;background:#f0fdf4;padding:12px;border-radius:8px;border:1px solid #bbf7d0">
              <label class="form-label" style="font-weight:700;font-size:12.5px;color:#166534">
                1. Wakil Penanggungjawab (Inspektur Pembantu) <span style="color:red">*</span>
              </label>
              <select name="irban_id" id="irban_id" class="input" required style="font-size:13.5px;width:100%;font-weight:600">
                <option value="">-- Pilih Inspektur Pembantu --</option>
                <?php foreach ($irbans as $ir): 
                  $selected = ($user['id'] === (int)$ir['id'] || ($user['role'] === 'admin' && str_contains($ir['jabatan'], 'IV')) || ($user['role'] === 'irban' && str_contains($ir['nama'], $user['nama']))) ? 'selected' : '';
                ?>
                  <option value="<?= $ir['id'] ?>" <?= $selected ?>>
                    <?= e($ir['jabatan']) ?> &mdash; <?= e($ir['nama']) ?> (NIP. <?= e(format_nip($ir['nip'])) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div style="font-size:11px;color:#15803d;margin-top:4px">
                <i class="fa-solid fa-circle-check"></i> Pada Nota Dinas, kolom <b>Dari</b> otomatis mencantumkan nama jabatan Irban ini.
              </div>
            </div>

            <!-- 2 & 3. DALNIS & KETUA TIM -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
              <div>
                <label class="form-label" style="font-weight:700;font-size:12px;color:#334155">2. Pengendali Teknis (Dalnis) <span style="color:red">*</span></label>
                <select name="dalnis_id" class="input" required style="font-size:13px;width:100%">
                  <option value="">-- Pilih Pengendali Teknis --</option>
                  <?php foreach ($dalnis as $dn): ?>
                    <option value="<?= $dn['id'] ?>"><?= e($dn['nama']) ?> (<?= e($dn['jabatan']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="form-label" style="font-weight:700;font-size:12px;color:#334155">3. Ketua Tim Pemeriksa <span style="color:red">*</span></label>
                <select name="ketua_tim_id" class="input" required style="font-size:13px;width:100%">
                  <option value="">-- Pilih Ketua Tim --</option>
                  <?php foreach ($ketua as $kt): ?>
                    <option value="<?= $kt['id'] ?>"><?= e($kt['nama']) ?> (<?= e($kt['jabatan']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- 4. ANGGOTA TIM PEMERIKSA (MULTIPLE AUDITOR) -->
            <div>
              <label class="form-label" style="font-weight:700;font-size:12px;color:#334155;display:flex;justify-content:space-between">
                <span>4. Anggota Tim Pemeriksa (Pilih 1 s.d 4 Anggota):</span>
                <span style="font-weight:normal;color:#64748b;font-size:11.5px">Centang auditor pelaksana lapangan</span>
              </label>
              <div style="max-height:220px;overflow-y:auto;border:1px solid #cbd5e1;border-radius:8px;padding:8px;background:#f8fafc;display:grid;grid-template-columns:1fr 1fr;gap:6px">
                <?php foreach ($anggota as $ag): ?>
                  <label style="display:flex;align-items:center;gap:8px;background:#fff;padding:6px 10px;border-radius:6px;border:1px solid #e2e8f0;cursor:pointer;font-size:12px">
                    <input type="checkbox" name="anggota_ids[]" value="<?= $ag['id'] ?>" class="anggota-check">
                    <div>
                      <div style="font-weight:600;color:#1e293b"><?= e($ag['nama']) ?></div>
                      <div style="font-size:10.5px;color:#64748b"><?= e($ag['jabatan']) ?></div>
                    </div>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- KOLOM KANAN: IDENTITAS NOTA DINAS & AKSI SUBMIT -->
        <div style="display:flex;flex-direction:column;gap:16px">
          <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 14px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;color:#0f172a">
              <i class="fa-solid fa-file-invoice" style="color:#f59e0b"></i> Identitas Nota Dinas
            </h3>

            <div style="margin-bottom:12px">
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Nomor Nota Dinas</label>
              <input type="text" name="no_nd" class="input" placeholder="Otomatis digenerate jika kosong" style="font-size:13px;width:100%">
              <div style="font-size:11px;color:#64748b;margin-top:2px">Format: 700/ND-IRBAN/BLN/THN/XXX</div>
            </div>

            <div style="margin-bottom:12px">
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tanggal Nota Dinas</label>
              <input type="date" name="tgl_nd" class="input" value="<?= date('Y-m-d') ?>" required style="width:100%">
            </div>

            <div style="margin-bottom:16px">
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Catatan Khusus Irban</label>
              <textarea name="catatan_irban" class="input" rows="3" placeholder="Tambahkan catatan pertimbangan audit jika ada..." style="font-size:12.5px;width:100%"></textarea>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px">
              <button type="submit" name="action" value="ajukan" class="btn btn-primary btn-block" style="padding:10px;font-size:13.5px">
                <i class="fa-solid fa-paper-plane"></i> Simpan &amp; Ajukan ke Inspektur
              </button>
              <button type="submit" name="action" value="draft" class="btn btn-outline btn-block" style="padding:8px;font-size:12.5px">
                <i class="fa-solid fa-floppy-disk"></i> Simpan sebagai Draft
              </button>
            </div>
          </div>

          <!-- INFO ALUR PRA-AUDIT -->
          <div class="card" style="background:#f8fafc;border-left:4px solid #0284c7">
            <h4 style="margin:0 0 6px;font-size:13px;font-weight:700;color:#0369a1"><i class="fa-solid fa-circle-info"></i> Alur Setelah Diajukan:</h4>
            <ol style="margin:0;padding-left:18px;font-size:11.5px;color:#475569;line-height:1.5">
              <li>Nota Dinas masuk ke kotak disposisi <b>Inspektur Daerah</b>.</li>
              <li>Setelah Inspektur menyetujui, otomatis diteruskan ke <b>Bagian Perencanaan/SPT</b>.</li>
              <li>Bagian SPT menerbitkan nomor Surat Perintah Tugas (SPT).</li>
              <li>Inspektur mengesahkan SPT &rarr; <b>Matriks PKA</b> otomatis terbentuk!</li>
            </ol>
          </div>
        </div>
      </div>
    </form>
  </div>
</main>
<?php partial('foot'); ?>
