<?php
partial('head', ['title' => 'Laporan Hasil Pengawasan (LHP) Otomatis Desa']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

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
      <div class="field" style="margin:0;width:140px">
        <form method="get" action="<?= url('lhp') ?>">
          <select name="tahun" class="input" onchange="this.form.submit()">
            <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>>Tahun <?= $y ?></option>
            <?php endfor; ?>
          </select>
        </form>
      </div>
    </div>

    <!-- Highlight Banner -->
    <div class="card" style="background:linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);border:1px solid #a7f3d0;padding:16px 20px;margin-bottom:20px">
      <div style="display:flex;align-items:center;gap:16px">
        <div style="width:48px;height:48px;border-radius:12px;background:#059669;color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
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
    <div class="card" style="padding:0">
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th>Desa / Kepenghuluan</th>
              <th>Surat Tugas (SPT)</th>
              <th class="num">Pagu Anggaran</th>
              <th class="num">Realisasi Uji</th>
              <th class="num">Selisih Belanja</th>
              <th style="text-align:center">Temuan (KTP)</th>
              <th style="width:160px;text-align:center">Aksi LHP</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarLhp)): ?>
              <tr>
                <td colspan="8" style="text-align:center;padding:40px;color:var(--slate-500)">
                  <i class="fa-solid fa-folder-open" style="font-size:36px;color:#cbd5e1;margin-bottom:10px;display:block"></i>
                  Belum ada sesi audit atau penugasan SPT untuk tahun anggaran <?= $tahun ?>.
                </td>
              </tr>
            <?php else: $no=1; foreach ($daftarLhp as $d): 
              $selisih = (float)$d['total_realisasi'] - (float)$d['total_kuitansi'];
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td>
                  <strong style="font-size:14px;color:var(--slate-800)"><?= e($d['desa_nama']) ?></strong>
                  <div style="font-size:12px;color:var(--slate-500)">Kecamatan <?= e($d['kecamatan_nama']) ?></div>
                  <div style="font-size:11.5px;color:var(--emerald-700);margin-top:2px">
                    <i class="fa-solid fa-clipboard-check"></i> <?= $d['sesi_final'] ?> dari <?= $d['total_sesi'] ?> sesi KKA Sah
                  </div>
                </td>
                <td>
                  <?php if (!empty($d['no_spt'])): ?>
                    <span style="font-weight:600;color:var(--slate-800)"><?= e($d['no_spt']) ?></span>
                    <div style="font-size:11.5px;color:var(--slate-500)">Tgl: <?= tgl_id($d['tgl_spt']) ?></div>
                    <div style="font-size:11px;color:#4f46e5">KT: <?= e($d['ketua_tim_nama']) ?></div>
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
                    <a href="<?= url('temuan?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" class="badge" style="background:#fee2e2;color:#dc2626;font-weight:700;text-decoration:none">
                      <?= $d['total_temuan'] ?> Butir (<?= rupiah($d['nominal_temuan']) ?>)
                    </a>
                  <?php else: ?>
                    <span class="badge" style="background:#f1f5f9;color:#64748b">Nihil Temuan</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;white-space:nowrap">
                  <a href="<?= url('lhp/show?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" class="btn btn-outline btn-sm" style="border-color:#059669;color:#059669;padding:6px 10px" title="Buka Pratinjau Naskah LHP">
                    <i class="fa-solid fa-eye"></i> Pratinjau LHP
                  </a>
                  <a href="<?= url('print/lhp?desa_id=' . $d['desa_id'] . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-primary btn-sm" style="background:#059669;border-color:#059669;padding:6px 10px" title="Cetak Resmi LHP">
                    <i class="fa-solid fa-print"></i>
                  </a>
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
