<?php $title = 'Edit / Koreksi Nota Dinas — ' . e($nd['no_nd']); ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-nd-edit">
  <?php partial('topbar', ['title' => 'Koreksi Nota Dinas: ' . $nd['no_nd'], 'icon' => 'fa-solid fa-pen-to-square']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <?php if (!empty($spt)): ?>
      <div class="alert alert-warning" style="margin-bottom:16px">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <b>Perhatian:</b> Nota Dinas ini sudah memiliki SPT resmi (<b><?= e($spt['no_spt']) ?></b>). Mengubah tim atau jadwal di sini akan disinkronkan.
      </div>
    <?php endif; ?>

    <div class="page-head">
      <div>
        <h2>Koreksi / Edit Nota Dinas Penugasan</h2>
        <p>Perbarui objek pemeriksaan, susunan tim (Irban, Dalnis, Ketua, Anggota), atau jadwal audit.</p>
      </div>
    </div>

    <form method="POST" action="<?= url('penugasan/nota-dinas/update') ?>" id="ndEditForm">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $nd['id'] ?>">

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
                    <option value="<?= $d['id'] ?>" <?= ((int)$nd['desa_id'] === (int)$d['id']) ? 'selected' : '' ?>>
                      <?= e($d['label']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tahun Anggaran <span style="color:red">*</span></label>
                <select name="tahun_anggaran" class="input" style="font-size:13.5px;width:100%">
                  <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= ((int)$nd['tahun_anggaran'] === $y) ? 'selected' : '' ?>><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="input" value="<?= e($nd['tgl_mulai']) ?>" required style="width:100%">
              </div>
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" class="input" value="<?= e($nd['tgl_selesai']) ?>" required style="width:100%">
              </div>
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Lama Waktu (Hari)</label>
                <input type="number" name="lama_hari" class="input" value="<?= (int)$nd['lama_hari'] ?>" min="1" max="60" required style="width:100%">
              </div>
            </div>

            <div style="margin-bottom:12px">
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tujuan / Keperluan Penugasan</label>
              <input type="text" name="tujuan" class="input" value="<?= e($nd['tujuan']) ?>" required style="width:100%">
            </div>

            <div>
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Jenis Pengawasan</label>
              <input type="text" name="jenis_audit" class="input" value="<?= e($nd['jenis_audit']) ?>" required style="width:100%">
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
                  $selected = ((int)$nd['irban_id'] === (int)$ir['id'] || (empty($nd['irban_id']) && str_contains($ir['nama'], $nd['irban_nama']))) ? 'selected' : '';
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
                  <?php foreach ($dalnis as $dn): 
                    $selected = ((int)$nd['dalnis_id'] === (int)$dn['id']) ? 'selected' : '';
                  ?>
                    <option value="<?= $dn['id'] ?>" <?= $selected ?>><?= e($dn['nama']) ?> (<?= e($dn['jabatan']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="form-label" style="font-weight:700;font-size:12px;color:#334155">3. Ketua Tim Pemeriksa <span style="color:red">*</span></label>
                <select name="ketua_tim_id" class="input" required style="font-size:13px;width:100%">
                  <option value="">-- Pilih Ketua Tim --</option>
                  <?php foreach ($ketua as $kt): 
                    $selected = ((int)$nd['ketua_tim_id'] === (int)$kt['id']) ? 'selected' : '';
                  ?>
                    <option value="<?= $kt['id'] ?>" <?= $selected ?>><?= e($kt['nama']) ?> (<?= e($kt['jabatan']) ?>)</option>
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
                <?php foreach ($anggota as $ag): 
                  $isChecked = in_array((int)$ag['id'], $selectedAnggotaIds, true);
                ?>
                  <label style="display:flex;align-items:center;gap:8px;background:#fff;padding:6px 10px;border-radius:6px;border:1px solid #e2e8f0;cursor:pointer;font-size:12px">
                    <input type="checkbox" name="anggota_ids[]" value="<?= $ag['id'] ?>" class="anggota-check" <?= $isChecked ? 'checked' : '' ?>>
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
              <input type="text" name="no_nd" class="input" value="<?= e($nd['no_nd']) ?>" required style="font-size:13px;width:100%">
              <div style="font-size:11px;color:#64748b;margin-top:2px">Format: 700/ND-IRBAN/BLN/THN/XXX</div>
            </div>

            <div style="margin-bottom:12px">
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tanggal Nota Dinas</label>
              <input type="date" name="tgl_nd" class="input" value="<?= e($nd['tgl_nd']) ?>" required style="width:100%">
            </div>

            <div style="margin-bottom:16px">
              <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Catatan Khusus Irban</label>
              <textarea name="catatan_irban" class="input" rows="3" placeholder="Tambahkan catatan pertimbangan audit jika ada..." style="font-size:12.5px;width:100%"><?= e($nd['catatan_irban'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px">
              <button type="submit" name="action" value="ajukan" class="btn btn-primary btn-block" style="padding:10px;font-size:13.5px">
                <i class="fa-solid fa-paper-plane"></i> Simpan &amp; Ajukan ke Inspektur
              </button>
              <button type="submit" name="action" value="draft" class="btn btn-outline btn-block" style="padding:8px;font-size:12.5px">
                <i class="fa-solid fa-floppy-disk"></i> Simpan sebagai Draft
              </button>
              <button type="submit" name="action" value="simpan" class="btn btn-secondary btn-block" style="padding:8px;font-size:12.5px">
                <i class="fa-solid fa-check"></i> Simpan Saja (Status Tetap <?= e($nd['status']) ?>)
              </button>
            </div>
          </div>

          <!-- STATUS SAAT INI -->
          <div class="card" style="background:#f8fafc;border-left:4px solid #0284c7">
            <h4 style="margin:0 0 6px;font-size:13px;font-weight:700;color:#0369a1"><i class="fa-solid fa-circle-info"></i> Status Saat Ini:</h4>
            <div style="font-size:12px;color:#475569;margin-bottom:8px">
              Status: <b><?= e($nd['status']) ?></b>
            </div>
            <?php if (!empty($nd['catatan_inspektur'])): ?>
              <div style="background:#fef2f2;border:1px solid #fecaca;padding:8px 10px;border-radius:6px;font-size:11.5px;color:#991b1b">
                <b>Catatan Inspektur:</b><br>
                <?= nl2br(e($nd['catatan_inspektur'])) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </form>
  </div>
</main>
<?php partial('foot'); ?>
