<?php
partial('head', ['title' => 'Routing Slip Kendali Mutu LHA / LHP (Model Simondes)']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar', ['title' => 'Routing Slip Kendali Mutu Pengawasan', 'icon' => 'fa-solid fa-folder-open']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-folder-open" style="color:#d97706"></i>
          Lembar Kendali Mutu (Routing Slip LHA / LHP)
        </h2>
        <p>Pengawasan Berjenjang Terpadu (Model Simondes): Catatan Koreksi Dalnis, Telaah Irban, dan Pengesahan Inspektur Daerah.</p>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <form method="get" action="<?= url('routing-slip') ?>" style="margin:0">
          <select name="tahun" class="input" style="width:130px;margin:0" onchange="this.form.submit()">
            <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>>Tahun <?= $y ?></option>
            <?php endfor; ?>
          </select>
        </form>
        <a href="<?= url('lhp?tahun=' . $tahun) ?>" class="btn btn-outline" style="border-color:#059669;color:#059669">
          <i class="fa-solid fa-file-shield"></i> Daftar LHP Desa
        </a>
      </div>
    </div>

    <!-- Banner Penjelasan Model Simondes -->
    <div class="card" style="background:linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);border:1px solid #fde68a;padding:16px 20px;margin-bottom:20px">
      <div style="display:flex;align-items:center;gap:16px">
        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;display:grid;place-items:center;font-size:22px;flex-shrink:0;box-shadow:0 4px 10px rgba(217,119,6,0.25)">
          <i class="fa-solid fa-diagram-project"></i>
        </div>
        <div>
          <h4 style="margin:0;color:#92400e;font-size:15px;font-weight:700">Audit Trail Kendali Mutu LHA / LHP (Map Kuning F4)</h4>
          <p style="margin:4px 0 0;font-size:12.5px;color:#b45309">
            Setiap koreksi teknis dari <b>Pengendali Teknis (Dalnis)</b> dan arahan strategis dari <b>Inspektur Pembantu (Irban)</b> otomatis terekam ke lembar Routing Slip desa tanpa ketik ulang. Menjadi bukti sah pengawasan mutu sebelum naskah disahkan resmi oleh <b>Inspektur Daerah</b>.
          </p>
        </div>
      </div>
    </div>

    <!-- Tabel Daftar Routing Slip -->
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:14px 18px;border-bottom:1px solid var(--slate-200);display:flex;justify-content:space-between;align-items:center;background:#f8fafc">
        <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--slate-800)">
          <i class="fa-solid fa-table-list" style="color:#d97706;margin-right:6px"></i>
          Register Routing Slip Kepenghuluan (TA <?= $tahun ?>)
        </h3>
        <span style="font-size:12px;color:var(--slate-500)">Total: <?= count($daftar) ?> Kepenghuluan</span>
      </div>

      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:40px;text-align:center">No</th>
              <th>Desa / Kepenghuluan</th>
              <th>Surat Perintah Tugas (SPT)</th>
              <th>Susunan Tim Pengawasan</th>
              <th style="text-align:center">Status Kendali Mutu</th>
              <th style="min-width:210px;text-align:center;padding-right:20px">Aksi Routing Slip</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:var(--slate-500)">
                  <i class="fa-solid fa-folder-open" style="font-size:36px;color:#cbd5e1;margin-bottom:10px;display:block"></i>
                  Belum ada data penugasan SPT atau sesi audit untuk tahun anggaran <?= $tahun ?>.
                </td>
              </tr>
            <?php else: $no=1; foreach ($daftar as $d): ?>
              <tr>
                <td style="text-align:center"><?= $no++ ?></td>
                <td>
                  <strong style="font-size:14px;color:var(--slate-900)"><?= e($d['desa_nama']) ?></strong>
                  <div style="font-size:11.5px;color:var(--slate-500)">Kecamatan <?= e($d['kecamatan_nama']) ?></div>
                  <div style="font-size:11px;color:var(--emerald-700);margin-top:2px;font-weight:600">
                    <i class="fa-solid fa-clipboard-check"></i> <?= $d['sesi_final'] ?> dari <?= $d['total_sesi'] ?> sesi KKA Sah
                  </div>
                </td>
                <td>
                  <?php if (!empty($d['no_spt'])): ?>
                    <span style="font-weight:700;color:var(--slate-800);font-size:12.5px"><?= e($d['no_spt']) ?></span>
                    <div style="font-size:11px;color:var(--slate-500)">Tgl: <?= tgl_id($d['tgl_spt']) ?></div>
                    <span class="badge" style="background:#ecfdf5;color:#065f46;font-size:10.5px;margin-top:2px;font-weight:600">
                      <?= e($d['spt_status'] ?? 'DITERBITKAN') ?>
                    </span>
                  <?php else: ?>
                    <span style="color:var(--slate-400);font-size:12px">Belum Terbit SPT</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:12px;line-height:1.5">
                  <div><b>Irban:</b> <?= e($d['wakil_pj_nama'] ?: 'MARWAN, M.T') ?></div>
                  <div><b>Dalnis:</b> <?= e($d['dalnis_nama'] ?: '-') ?></div>
                  <div><b>Ketua:</b> <?= e($d['ketua_tim_nama'] ?: '-') ?></div>
                </td>
                <td style="text-align:center">
                  <?php if ($d['status_lhp'] === 'DISAHKAN_INSPEKTUR'): ?>
                    <span class="badge" style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;padding:4px 8px;border:1px solid #86efac">
                      <i class="fa-solid fa-stamp"></i> Disahkan Inspektur
                    </span>
                    <div style="font-size:10.5px;color:#166534;margin-top:2px">
                      <?= !empty($d['tgl_disahkan_inspektur']) ? date('d/m/Y', strtotime($d['tgl_disahkan_inspektur'])) : '' ?>
                    </div>
                  <?php elseif ($d['status_lhp'] === 'TELAAH_IRBAN'): ?>
                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700;padding:4px 8px;border:1px solid #bfdbfe">
                      <i class="fa-solid fa-user-tie"></i> Telaah Irban
                    </span>
                  <?php elseif ($d['status_lhp'] === 'REVIU_DALNIS'): ?>
                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;padding:4px 8px;border:1px solid #fde68a">
                      <i class="fa-solid fa-glasses"></i> Reviu Dalnis
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background:#f1f5f9;color:#475569;font-size:11px;font-weight:600;padding:4px 8px">
                      <i class="fa-solid fa-pen"></i> Draf Konsep LHA
                    </span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;white-space:nowrap;padding-right:20px">
                  <div style="display:inline-flex;gap:6px">
                    <a href="<?= url('routing-slip/show?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" class="btn btn-outline btn-sm" style="border-color:#d97706;color:#b45309;padding:5px 10px;font-weight:600" title="Kelola Catatan Review Sheet">
                      <i class="fa-solid fa-file-pen"></i> Catatan Reviu
                    </a>
                    <a href="<?= url('print/routing-slip?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-primary btn-sm" style="background:#d97706;border-color:#d97706;padding:5px 10px" title="Cetak Resmi Routing Slip (Map Kuning F4)">
                      <i class="fa-solid fa-print"></i> Cetak F4
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
