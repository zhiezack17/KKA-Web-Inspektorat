<?php $title = 'Penerbitan SPT - KKA Digital'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-spt-create">
  <div class="topbar">
    <div class="crumb">
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">☰</button>
      <i class="fa-solid fa-file-signature"></i> <b>Penerbitan Surat Perintah Tugas (SPT)</b>
    </div>
    <div class="topbar-right">
      <a href="<?= url('penugasan/spt') ?>" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar SPT
      </a>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Penerbitan Surat Perintah Tugas (SPT)</h2>
        <p>Penerbitan nomor SPT resmi berdasarkan Nota Dinas yang telah disetujui Inspektur Daerah.</p>
      </div>
    </div>

    <form method="POST" action="<?= url('penugasan/spt/store') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="nota_dinas_id" value="<?= $nd['id'] ?>">

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
        <!-- KOLOM KIRI: DATA NOTA DINAS & DASAR HUKUM -->
        <div style="display:flex;flex-direction:column;gap:16px">
          <!-- RINGKASAN DISPOSISI NOTA DINAS -->
          <div class="card" style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Dasar Nota Dinas Terverifikasi</span>
              <span style="font-size:12px;color:#166534">Disposisi: <?= tgl_id($nd['tgl_disposisi']) ?></span>
            </div>
            <div style="font-weight:700;font-size:15px;color:#14532d">Nota Dinas: <?= e($nd['no_nd']) ?> (Tgl: <?= tgl_id($nd['tgl_nd']) ?>)</div>
            <div style="font-size:13px;color:#166534;margin-top:4px">
              <b>Objek Audit:</b> Kepenghuluan <?= e($nd['desa_nama']) ?> &mdash; Kec. <?= e($nd['kecamatan_nama']) ?> (TA <?= (int)$nd['tahun_anggaran'] ?>)
            </div>
            <div style="font-size:12px;color:#15803d;margin-top:4px;font-style:italic">
              <b>Arahan Inspektur:</b> "<?= e($nd['catatan_inspektur'] ?: 'Disetujui. Proses SPT.') ?>"
            </div>
          </div>

          <!-- KARTU DATA NOMOR & DASAR HUKUM SPT -->
          <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 14px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;color:#0f172a">
              <i class="fa-solid fa-stamp" style="color:#0284c7"></i> Registrasi Nomor SPT &amp; Dasar Hukum
            </h3>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:14px">
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Nomor Surat Perintah Tugas (SPT) <span style="color:red">*</span></label>
                <input type="text" name="no_spt" class="input" value="<?= e($defaultNoSpt) ?>" required style="font-weight:700;font-size:13.5px;width:100%">
                <div style="font-size:11px;color:#64748b;margin-top:2px">Format baku SPT Pengawasan Keuangan Desa Inspektorat Rohil</div>
              </div>
              <div>
                <label class="form-label" style="font-weight:600;font-size:12px;color:#475569">Tanggal Terbit SPT <span style="color:red">*</span></label>
                <input type="date" name="tgl_spt" class="input" value="<?= date('Y-m-d') ?>" required style="width:100%">
              </div>
            </div>

            <!-- DASAR UTAMA (STANDAR PKPT) -->
            <div style="margin-bottom:12px;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0">
              <label class="form-label" style="font-weight:700;font-size:12px;color:#0f172a;margin-bottom:4px">
                1. Dasar Utama (Otomatis):
              </label>
              <div style="font-size:12.5px;color:#334155">
                &bull; Program Kerja Pengawasan Tahunan (PKPT) Tahun <?= (int)$nd['tahun_anggaran'] ?> Inspektorat Kabupaten Rokan Hilir.
              </div>
              <div style="font-size:11px;color:#64748b;margin-top:2px">
                <i>(Standar resmi: Nota Dinas internal tidak dibunyikan di Surat Tugas reguler PKPT).</i>
              </div>
            </div>

            <!-- DASAR TAMBAHAN / SURAT PERMINTAAN AUDIT -->
            <div>
              <label class="form-label" style="font-weight:700;font-size:12px;color:#0f172a;display:flex;justify-content:space-between">
                <span>2. Dasar Tambahan / Surat Permintaan Audit (Opsional):</span>
                <span style="font-weight:normal;color:#64748b;font-size:11.5px">Kosongkan jika audit reguler PKPT</span>
              </label>
              <textarea name="dasar_hukum" class="input" rows="3" placeholder="Kosongkan jika audit reguler PKPT.&#10;Ketikkan di sini jika ada surat permintaan audit khusus, contoh:&#10;Surat Permintaan Audit dari Datuk Penghulu [Nama Kepenghuluan] Nomor: ... Tanggal: ... Perihal: ...&#10;atau Surat Permintaan dari Kejaksaan Negeri / Kepolisian Resor Rokan Hilir..." style="font-size:12px;line-height:1.5;width:100%"><?= e($defaultDasarHukum) ?></textarea>
              <div style="font-size:11px;color:#0284c7;margin-top:4px">
                <i class="fa-solid fa-circle-info"></i> Jika kolom ini diisi, otomatis akan menjadi <b>Dasar Poin 2</b> (dan seterusnya) pada Surat Tugas. Jika kosong, Surat Tugas hanya mencantumkan 1 dasar (PKPT).
              </div>
            </div>
          </div>

          <!-- SUSUNAN TIM HASIL NOTA DINAS -->
          <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 14px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;color:#0f172a">
              <i class="fa-solid fa-users" style="color:#059669"></i> Susunan Tim Pemeriksa (Sesuai Disposisi)
            </h3>

            <table class="table" style="font-size:12.5px">
              <tbody>
                <tr>
                  <td style="width:180px;font-weight:600;color:#64748b">Penanggung Jawab:</td>
                  <td><b>H. SARMAN SYAHRONI, ST., M.IP</b> (Inspektur Daerah)</td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b">1. Wakil Penanggung Jawab:</td>
                  <td><b><?= e($nd['irban_nama']) ?></b> (Inspektur Pembantu)</td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b">2. Pengendali Teknis (Dalnis):</td>
                  <td><b><?= e($nd['dalnis_nama']) ?></b></td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b">3. Ketua Tim:</td>
                  <td><b><?= e($nd['ketua_tim_nama']) ?></b></td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b;vertical-align:top">4. Anggota Tim Pemeriksa:</td>
                  <td>
                    <ol style="margin:0;padding-left:18px">
                      <?php foreach ($anggotaList as $ag): ?>
                        <li><b><?= e($ag['nama']) ?></b> &mdash; <?= e($ag['jabatan']) ?></li>
                      <?php endforeach; ?>
                    </ol>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- KOLOM KANAN: WAKTU & SUBMIT -->
        <div style="display:flex;flex-direction:column;gap:16px">
          <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 14px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;color:#0f172a">
              <i class="fa-solid fa-calendar-check" style="color:#f59e0b"></i> Jangka Waktu Tugas
            </h3>

            <div style="font-size:13px;line-height:1.6;margin-bottom:16px;background:#f8fafc;padding:12px;border-radius:8px">
              <div><b>Tanggal Mulai:</b> <?= tgl_id($nd['tgl_mulai']) ?></div>
              <div><b>Tanggal Selesai:</b> <?= tgl_id($nd['tgl_selesai']) ?></div>
              <div><b>Lama Tugas:</b> <?= (int)$nd['lama_hari'] ?> Hari Kerja</div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px">
              <button type="submit" name="action" value="ajukan" class="btn btn-primary" style="justify-content:center;padding:10px;font-size:13.5px">
                <i class="fa-solid fa-paper-plane"></i> Ajukan Pengesahan ke Inspektur
              </button>
              <button type="submit" name="action" value="draft" class="btn btn-outline" style="justify-content:center;padding:8px;font-size:12.5px">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Draft SPT
              </button>
            </div>
          </div>

          <div class="card" style="background:#eff6ff;border-left:4px solid #3b82f6">
            <h4 style="margin:0 0 6px;font-size:13px;font-weight:700;color:#1d4ed8"><i class="fa-solid fa-bolt"></i> Otomasi Sistem:</h4>
            <p style="margin:0;font-size:11.5px;color:#1e40af;line-height:1.5">
              Ketika SPT ini disahkan oleh Inspektur Daerah:
              <br>&bull; Matriks <b>Program Kerja Audit (PKA)</b> otomatis dibuat.
              <br>&bull; Ketua Tim langsung dapat membagi penugasan Anggota 1 &amp; Anggota 2.
            </p>
          </div>
        </div>
      </div>
    </form>
  </div>
</main>
<?php partial('foot'); ?>
