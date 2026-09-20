<?php $title = 'Program Kerja Audit (PKA) - KKA Digital'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-pka">
  <?php partial('topbar', ['title' => 'Program Kerja Audit (PKA)', 'icon' => 'fa-solid fa-list-check']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Program Kerja Audit (PKA)</h2>
        <p>Matriks alokasi prosedur audit dan pembagian ruang lingkup tugas Anggota Tim.</p>
      </div>
    </div>

    <!-- FILTER TAHUN -->
    <div class="card" style="margin-bottom:16px;padding:14px 20px">
      <form method="GET" action="<?= url('penugasan/pka') ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div style="min-width:160px">
          <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">TAHUN ANGGARAN</label>
          <select name="tahun" class="input" onchange="this.form.submit()" style="padding:7px 12px;font-size:13px;width:100%">
            <option value="">Semua Tahun</option>
            <?php for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun===$y?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div style="margin-top:18px">
          <a href="<?= url('penugasan/pka') ?>" class="btn btn-outline" style="padding:7px 14px;font-size:13px">Reset</a>
        </div>
      </form>
    </div>

    <!-- TABEL DAFTAR PKA -->
    <div class="card">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:40px">No</th>
              <th>Nomor &amp; Tanggal PKA</th>
              <th>Objek Penugasan</th>
              <th>Dasar SPT</th>
              <th>Ketua Tim &amp; Dalnis</th>
              <th>Progres Prosedur</th>
              <th>Status</th>
              <th style="text-align:right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($list)): ?>
              <tr>
                <td colspan="8" style="text-align:center;padding:32px;color:#94a3b8">
                  <i class="fa-solid fa-list-check" style="font-size:32px;display:block;margin-bottom:8px"></i>
                  Belum ada Program Kerja Audit (PKA). Terbitkan dan sahkan SPT terlebih dahulu.
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1; foreach ($list as $p): 
                $pct = ($p['total_langkah'] > 0) ? round(($p['selesai_langkah'] / $p['total_langkah']) * 100) : 0;
              ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td>
                    <div style="font-weight:700;font-size:13.5px;color:#0f172a"><?= e($p['no_pka']) ?></div>
                    <div style="font-size:11.5px;color:#64748b">Tanggal: <?= tgl_id($p['tgl_pka']) ?></div>
                  </td>
                  <td>
                    <div style="font-weight:700;font-size:13.5px"><?= e($p['desa_nama']) ?></div>
                    <div style="font-size:12px;color:#64748b">Kec. <?= e($p['kecamatan_nama']) ?></div>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;margin-top:4px">TA <?= (int)$p['tahun_anggaran'] ?></span>
                  </td>
                  <td>
                    <div style="font-weight:600;font-size:12px"><?= e($p['no_spt']) ?></div>
                    <div style="font-size:11px;color:#64748b"><?= tgl_id($p['tgl_mulai']) ?> s.d <?= tgl_id($p['tgl_selesai']) ?></div>
                  </td>
                  <td>
                    <div style="font-size:12px"><b>Ketua:</b> <?= e($p['ketua_tim_nama']) ?></div>
                    <div style="font-size:11.5px;color:#64748b"><b>Dalnis:</b> <?= e($p['dalnis_nama']) ?></div>
                  </td>
                  <td>
                    <div style="display:flex;align-items:center;gap:8px">
                      <div style="flex:1;background:#e2e8f0;height:6px;border-radius:3px;overflow:hidden">
                        <div style="background:#10b981;height:100%;width:<?= $pct ?>%"></div>
                      </div>
                      <span style="font-size:11px;font-weight:600"><?= $p['selesai_langkah'] ?>/<?= $p['total_langkah'] ?></span>
                    </div>
                  </td>
                  <td>
                    <?php if ($p['status'] === 'DRAFT'): ?>
                      <span class="badge badge-secondary">Penyusunan KT</span>
                    <?php elseif ($p['status'] === 'REVIEW_DALNIS'): ?>
                      <span class="badge" style="background:#fef3c7;color:#92400e">Reviu Dalnis</span>
                    <?php elseif ($p['status'] === 'DISETUJUI'): ?>
                      <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Disetujui Dalnis</span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:right">
                    <div style="display:inline-flex;gap:6px">
                      <a href="<?= url('penugasan/pka/show?id=' . $p['id']) ?>" class="btn btn-sm btn-primary" title="Buka Matriks Pembagian Tugas">
                        <i class="fa-solid fa-users-gear"></i> Matriks Tugas
                      </a>
                      <a href="<?= url('print/pka?id=' . $p['id']) ?>" target="_blank" class="btn btn-sm btn-outline" title="Cetak Dokumen PKA">
                        <i class="fa-solid fa-print"></i> Cetak
                      </a>
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
