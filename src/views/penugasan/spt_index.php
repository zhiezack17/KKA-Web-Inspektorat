<?php $title = 'Surat Perintah Tugas (SPT) - KKA Digital'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-spt">
  <?php partial('topbar', ['title' => 'Surat Perintah Tugas (SPT)', 'icon' => 'fa-solid fa-file-signature']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Surat Perintah Tugas (SPT)</h2>
        <p>Pengelolaan dan penerbitan Surat Tugas Pengawasan Inspektorat Kabupaten Rokan Hilir.</p>
      </div>
    </div>

    <!-- ANTREAN NOTA DINAS YANG SUDAH DISETUJUI INSPEKTUR -->
    <?php if (!empty($pendingNd)): ?>
      <div class="card" style="border-left:4px solid #f59e0b;margin-bottom:20px;background:#fffdfa">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <h3 style="margin:0;font-size:15px;font-weight:700;color:#92400e;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-bell" style="color:#f59e0b"></i>
            Antrean Penerbitan SPT (<?= count($pendingNd) ?> Disposisi Disetujui)
          </h3>
          <span style="font-size:12px;color:#78350f">Nota Dinas telah disetujui Inspektur dan siap diterbitkan SPT</span>
        </div>

        <div class="table-wrap">
          <table class="table" style="font-size:12.5px">
            <thead>
              <tr style="background:#fef3c7;color:#92400e">
                <th>No. Nota Dinas</th>
                <th>Kepenghuluan</th>
                <th>Irban Pengusul</th>
                <th>Tanggal Disposisi</th>
                <th>Catatan Inspektur</th>
                <th style="text-align:right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingNd as $pnd): ?>
                <tr>
                  <td><b><?= e($pnd['no_nd']) ?></b></td>
                  <td><b><?= e($pnd['desa_nama']) ?></b> (Kec. <?= e($pnd['kecamatan_nama']) ?>)</td>
                  <td><?= e($pnd['irban_nama']) ?></td>
                  <td><?= tgl_id($pnd['tgl_disposisi']) ?></td>
                  <td style="font-style:italic;color:#475569"><?= e($pnd['catatan_inspektur'] ?: 'Disetujui untuk proses SPT.') ?></td>
                  <td style="text-align:right">
                    <?php if ($isOperatorSpt): ?>
                      <a href="<?= url('penugasan/spt/create?nd_id=' . $pnd['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-stamp"></i> Terbitkan SPT
                      </a>
                    <?php else: ?>
                      <span class="badge" style="background:#e2e8f0;color:#475569">Menunggu Bagian SPT</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
      <div class="card" style="border-left:4px solid #3b82f6;margin-bottom:20px;background:#f8fafc;padding:14px 18px">
        <div style="display:flex;align-items:center;gap:14px">
          <div style="width:38px;height:38px;border-radius:50%;background:#eff6ff;display:grid;place-items:center;flex-shrink:0">
            <i class="fa-solid fa-circle-info" style="font-size:18px;color:#2563eb"></i>
          </div>
          <div>
            <b style="font-size:13.5px;color:#1e293b">Alur Penerbitan Surat Perintah Tugas (SPT)</b>
            <p style="font-size:12px;color:#64748b;margin:2px 0 0;line-height:1.5">
              Saat ini belum ada antrean baru. Sesuai SOP pengawasan resmi APIP, berkas penugasan akan <b>otomatis masuk ke antrean Operator SPT</b> segera setelah <b>Nota Dinas diajukan oleh Irban Wilayah</b> dan <b>disetujui oleh Inspektur Daerah</b>.
            </p>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- FILTER TAHUN & STATUS SPT -->
    <div class="card" style="margin-bottom:16px;padding:14px 20px">
      <form method="GET" action="<?= url('penugasan/spt') ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div style="min-width:180px">
          <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">STATUS SPT</label>
          <select name="status" class="input" onchange="this.form.submit()" style="padding:7px 12px;font-size:13px;width:100%">
            <option value="">Semua Status</option>
            <option value="DRAFT" <?= $status==='DRAFT'?'selected':'' ?>>Draft Operator</option>
            <option value="MENUNGGU_TTD" <?= $status==='MENUNGGU_TTD'?'selected':'' ?>>Menunggu TTD Inspektur</option>
            <option value="DITERBITKAN" <?= $status==='DITERBITKAN'?'selected':'' ?>>Diterbitkan / Aktif</option>
          </select>
        </div>
        <div style="min-width:140px">
          <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">TAHUN ANGGARAN</label>
          <select name="tahun" class="input" onchange="this.form.submit()" style="padding:7px 12px;font-size:13px;width:100%">
            <option value="">Semua Tahun</option>
            <?php for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun===$y?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div style="margin-top:18px">
          <a href="<?= url('penugasan/spt') ?>" class="btn btn-outline" style="padding:7px 14px;font-size:13px">Reset</a>
        </div>
      </form>
    </div>

    <!-- TABEL DAFTAR SURAT PERINTAH TUGAS -->
    <div class="card">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:40px">No</th>
              <th>Nomor &amp; Tanggal SPT</th>
              <th>Kepenghuluan Objek</th>
              <th>Susunan Tim Pemeriksa</th>
              <th>Jangka Waktu</th>
              <th>Status &amp; PKA</th>
              <th style="text-align:right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($list)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8">
                  <i class="fa-solid fa-file-signature" style="font-size:32px;display:block;margin-bottom:8px"></i>
                  Belum ada Surat Perintah Tugas yang diterbitkan.
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1; foreach ($list as $s): 
                $anggotaList = json_decode($s['anggota_data'] ?? '[]', true) ?: [];
              ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td>
                    <div style="font-weight:700;font-size:13.5px;color:#0f172a"><?= e($s['no_spt']) ?></div>
                    <div style="font-size:11.5px;color:#64748b">Tanggal: <?= tgl_id($s['tgl_spt']) ?></div>
                    <?php if (!empty($s['no_nd'])): ?>
                      <div style="font-size:11px;color:#0369a1;margin-top:2px">Dasar ND: <?= e($s['no_nd']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div style="font-weight:700;font-size:13.5px"><?= e($s['desa_nama']) ?></div>
                    <div style="font-size:12px;color:#64748b">Kec. <?= e($s['kecamatan_nama']) ?></div>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;margin-top:4px">TA <?= (int)$s['tahun_anggaran'] ?></span>
                  </td>
                  <td>
                    <?php if (!empty($s['wakil_pj_nama'])): ?>
                      <div style="font-size:11.5px;color:#0369a1"><b>Wakil PJ:</b> <?= e($s['wakil_pj_nama']) ?></div>
                    <?php endif; ?>
                    <div style="font-size:11.5px"><b>Dalnis:</b> <?= e($s['dalnis_nama']) ?></div>
                    <div style="font-size:11.5px"><b>Ketua Tim:</b> <?= e($s['ketua_tim_nama']) ?></div>
                    <div style="font-size:11px;color:#64748b">
                      <b>Anggota (<?= count($anggotaList) ?>):</b> <?= e(implode(', ', array_column($anggotaList, 'nama'))) ?>
                    </div>
                  </td>
                  <td>
                    <div style="font-size:12px;font-weight:600"><?= tgl_id($s['tgl_mulai']) ?> s.d <?= tgl_id($s['tgl_selesai']) ?></div>
                    <div style="font-size:11px;color:#64748b"><?= (int)$s['lama_hari'] ?> Hari Kerja</div>
                  </td>
                  <td>
                    <?php if ($s['status'] === 'DRAFT'): ?>
                      <span class="badge badge-secondary">Draft</span>
                    <?php elseif ($s['status'] === 'MENUNGGU_TTD'): ?>
                      <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a">
                        <i class="fa-solid fa-signature"></i> Menunggu TTD Inspektur
                      </span>
                    <?php elseif ($s['status'] === 'DITERBITKAN'): ?>
                      <span class="badge badge-success"><i class="fa-solid fa-stamp"></i> Resmi Diterbitkan</span>
                      <?php if (!empty($s['pka_id'])): ?>
                        <div style="margin-top:6px">
                          <a href="<?= url('penugasan/pka/show?id=' . $s['pka_id']) ?>" class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;text-decoration:none">
                            <i class="fa-solid fa-list-check"></i> Matriks PKA &rarr;
                          </a>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:right">
                    <div style="display:inline-flex;gap:6px">
                      <!-- CETAK SPT -->
                      <a href="<?= url('print/spt?id=' . $s['id']) ?>" target="_blank" class="btn btn-sm btn-outline" title="Cetak Dokumen SPT">
                        <i class="fa-solid fa-print"></i> Cetak
                      </a>

                      <!-- AKSI TTD INSPEKTUR -->
                      <?php if (($isInspektur || $auth->isAdmin()) && $s['status'] === 'MENUNGGU_TTD'): ?>
                        <form method="POST" action="<?= url('penugasan/spt/sahkan') ?>" style="display:inline" onsubmit="return confirm('Sahkan Surat Perintah Tugas ini dan aktifkan Matriks PKA?')">
                          <?= csrf_field() ?>
                          <input type="hidden" name="id" value="<?= $s['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-file-signature"></i> Sahkan SPT
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<?php partial('foot'); ?>
