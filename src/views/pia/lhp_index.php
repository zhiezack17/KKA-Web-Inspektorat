<?php $title = 'KKA & DPP PIA'; $current = '/pia/lhp'; partial('head', compact('title')); ?>
<?php partial('sidebar', compact('current')); ?>
<main class="main">
<?php partial('topbar', ['title' => 'KKA & DPP PIA', 'icon' => 'fa-solid fa-list-check']); ?>
<div class="content">
<?php partial('flash'); ?>
<div class="page-head">
  <div>
    <h2>KKA & DPP PIA</h2>
    <p>Pengisian Kertas Kerja dan Laporan Hasil Penelaahan PIA.</p>
  </div>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>NO</th>
          <th>NOMOR SPT PIA</th>
          <th>WILAYAH / OBJEK</th>
          <th>STATUS KKA/LHP</th>
          <th>AKSI</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($spts)): ?>
        <tr><td colspan="5" class=""><i class="fa-solid fa-inbox "></i> Belum ada SPT PIA yang diterbitkan.</td></tr>
        <?php else: ?>
        <?php foreach($spts as $i => $row): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><b><?= e($row['no_spt']) ?></b><br><small>Tgl: <?= tgl_id($row['tgl_spt']) ?></small></td>
          <td><b><?= e($row['desa_nama']) ?></b><br><small>Kec. <?= e($row['kecamatan_nama']) ?> - TA <?= e($row['tahun_anggaran']) ?></small></td>
          <td>
             <?php
               $lhp = DB::one('SELECT id, status, keputusan_inspektur FROM kka_pia_lhp WHERE spt_id = ?', [$row['id']]);
               if(!$lhp) echo '<span class="badge badge-secondary">Belum Diisi</span>';
               else if($lhp['status']=='DRAFT') echo '<span class="badge badge-warning">Draft</span>';
               else echo '<span class="badge badge-success">' . e($lhp['status']) . '</span><br><small><b>' . e(str_replace('_', ' ', $lhp['keputusan_inspektur'])) . '</b></small>';
             ?>
          </td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="<?= url('pia/lhp/edit?spt_id=' . $row['id']) ?>" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-pen-to-square"></i> Isi KKA/DPP
              </a>
              <?php if(!empty($lhp)): ?>
                <a href="<?= url('pia/print-lhp?spt_id=' . $row['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Dokumen">
                  <i class="fa-solid fa-print"></i>
                </a>
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
</div></main>
<?php partial('foot'); ?>
