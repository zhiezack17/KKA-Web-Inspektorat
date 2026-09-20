<?php
partial('head', ['title' => 'Laporan Hasil Pengawasan (LHP) Otomatis Desa']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar', ['title' => 'Laporan Hasil Pengawasan (LHP) Desa', 'icon' => 'fa-solid fa-file-shield']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-file-shield" style="color:#059669"></i>
          Laporan Hasil Pengawasan (LHP) Otomatis Desa
        </h2>
        <p>Penyusunan Otomatis Dokumen LHP Desa (ADTT) Merangkum SPT, PKA, Seluruh Belanja KKA, Evaluasi Pajak, dan Temuan 5 Unsur.</p>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <form method="get" action="<?= url('lhp') ?>" style="margin:0">
          <select name="tahun" class="input" style="width:130px;margin:0" onchange="this.form.submit()">
            <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>>Tahun <?= $y ?></option>
            <?php endfor; ?>
          </select>
        </form>
        <a href="<?= url('gdrive') ?>" class="btn btn-outline" style="border-color:#0284c7;color:#0284c7">
          <i class="fa-brands fa-google-drive"></i> Cadangan Drive
        </a>
      </div>
    </div>

    <!-- Highlight Banner -->
    <div class="card" style="background:linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);border:1px solid #a7f3d0;padding:16px 20px;margin-bottom:20px">
      <div style="display:flex;align-items:center;gap:16px">
        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#059669,#047857);color:#fff;display:grid;place-items:center;font-size:22px;flex-shrink:0;box-shadow:0 4px 10px rgba(5,150,105,0.25)">
          <i class="fa-solid fa-award"></i>
        </div>
        <div>
          <h4 style="margin:0;color:#065f46;font-size:15px;font-weight:700">Integrasi Paripurna Pengawasan Desa (Siklus Hulu ke Hilir)</h4>
          <p style="margin:4px 0 0;font-size:12.5px;color:#047857">
            Sistem secara otomatis mengumpulkan seluruh data dari <b>Surat Tugas (SPT)</b>, <b>Kertas Kerja Audit (KKA)</b>, dan <b>Konsep Temuan (KTP 5 Unsur)</b> menjadi draf naskah resmi <b>Laporan Hasil Pengawasan (LHP)</b> siap cetak dan tanda tangan pimpinan tanpa perlu diketik ulang.
          </p>
        </div>
      </div>
    </div>

    <!-- Daftar Desa LHP -->
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:14px 18px;border-bottom:1px solid var(--slate-200);display:flex;justify-content:space-between;align-items:center;background:#f8fafc">
        <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--slate-800)">
          <i class="fa-solid fa-file-lines" style="color:#059669;margin-right:6px"></i>
          Register Naskah LHP Kepenghuluan (TA <?= $tahun ?>)
        </h3>
        <span style="font-size:12px;color:var(--slate-500)">Total: <?= count($daftarLhp) ?> Kepenghuluan</span>
      </div>

      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:40px;text-align:center">No</th>
              <th>Desa / Kepenghuluan</th>
              <th>Surat Tugas (SPT)</th>
              <th class="num">Pagu Anggaran</th>
              <th class="num">Realisasi Uji</th>
              <th class="num">Selisih Belanja</th>
              <th style="text-align:center">Temuan (KTP)</th>
              <th style="text-align:center">Google Drive</th>
              <th style="min-width:185px;text-align:center;padding-right:20px">Aksi LHP</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarLhp)): ?>
              <tr>
                <td colspan="9" style="text-align:center;padding:40px;color:var(--slate-500)">
                  <i class="fa-solid fa-folder-open" style="font-size:36px;color:#cbd5e1;margin-bottom:10px;display:block"></i>
                  Belum ada sesi audit atau penugasan SPT untuk tahun anggaran <?= $tahun ?>.
                </td>
              </tr>
            <?php else: $no=1; foreach ($daftarLhp as $d): 
              $selisih = (float)$d['total_realisasi'] - (float)$d['total_kuitansi'];
            ?>
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
                    <div style="font-size:11px;color:#4f46e5;font-weight:600">KT: <?= e($d['ketua_tim_nama']) ?></div>
                  <?php else: ?>
                    <span style="color:var(--slate-400);font-size:12px">Belum Terbit SPT</span>
                  <?php endif; ?>
                </td>
                <td class="num"><?= rupiah($d['total_pagu']) ?></td>
                <td class="num"><?= rupiah($d['total_realisasi']) ?></td>
                <td class="num" style="color:<?= $selisih < 0 ? '#dc2626' : 'var(--emerald-700)' ?>;font-weight:700">
                  <?= rupiah($selisih) ?>
                </td>
                <td style="text-align:center">
                  <?php if ((int)$d['total_temuan'] > 0): ?>
                    <a href="<?= url('temuan?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" class="badge badge-danger" style="text-decoration:none" title="Lihat rincian temuan">
                      <i class="fa-solid fa-triangle-exclamation"></i> <?= $d['total_temuan'] ?> Butir (<?= rupiah($d['nominal_temuan']) ?>)
                    </a>
                  <?php else: ?>
                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> Nihil Temuan</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center">
                  <?php if (!empty($d['gdrive_lhp_link'])): ?>
                    <a href="<?= e($d['gdrive_lhp_link']) ?>" target="_blank" class="badge badge-info" style="text-decoration:none;font-weight:700" title="Buka berkas PDF resmi di Google Drive teamirban4@gmail.com">
                      <i class="fa-brands fa-google-drive"></i> PDF Drive
                    </a>
                  <?php else: ?>
                    <a href="<?= url('gdrive/sync-lhp?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" class="badge badge-slate" style="text-decoration:none" title="Simpan naskah PDF ke Google Drive teamirban4@gmail.com" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Menyimpan...'">
                      <i class="fa-solid fa-cloud-arrow-up"></i> Cadangkan
                    </a>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;white-space:nowrap;padding-right:20px">
                  <div style="display:inline-flex;gap:6px">
                    <a href="<?= url('lhp/show?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" class="btn btn-outline btn-sm" style="border-color:#059669;color:#059669;padding:5px 10px" title="Buka Pratinjau Naskah LHP">
                      <i class="fa-solid fa-eye"></i> Pratinjau
                    </a>
                    <a href="<?= url('print/lhp?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-primary btn-sm" style="padding:5px 10px" title="Cetak Resmi LHP (Format Bookman Rohil)">
                      <i class="fa-solid fa-print"></i> Cetak
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
