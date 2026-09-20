<?php
partial('head', ['title' => 'Konsep Temuan Pemeriksaan (KTP 5 Unsur)']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar', ['title' => 'Konsep Temuan Pemeriksaan (KTP 5 Unsur)', 'icon' => 'fa-solid fa-file-circle-exclamation']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-file-circle-exclamation" style="color:#d97706"></i>
          Konsep Temuan Pemeriksaan (KTP 5 Unsur)
        </h2>
        <p>Matriks Daftar Temuan Berdasarkan Standar SPKN BPK-RI &amp; Kendali Mutu BPKP (Kondisi, Kriteria, Sebab, Akibat, Rekomendasi).</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php if ($desaId > 0): ?>
          <a href="<?= url('print/matriks-temuan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-outline" style="border-color:#d97706;color:#d97706">
            <i class="fa-solid fa-print"></i> Cetak Matriks Temuan
          </a>
        <?php endif; ?>
        <a href="<?= url('temuan/create' . ($desaId > 0 ? '?desa_id=' . $desaId . '&tahun=' . $tahun : '')) ?>" class="btn btn-primary" style="background:#d97706;border-color:#d97706">
          <i class="fa-solid fa-plus"></i> Buat Temuan Baru
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding:14px 18px;margin-bottom:18px">
      <form method="get" action="<?= url('temuan') ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
        <div class="field" style="margin:0;min-width:260px">
          <label style="font-size:12px;font-weight:600">Desa / Kepenghuluan</label>
          <select name="desa_id" class="input">
            <option value="0">-- Semua Desa / Kepenghuluan --</option>
            <?php foreach ($daftarDesa as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $desaId == $d['id'] ? 'selected' : '' ?>>
                <?= e($d['nama']) ?> (Kec. <?= e($d['kecamatan']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field" style="margin:0;width:120px">
          <label style="font-size:12px;font-weight:600">Tahun</label>
          <input type="number" name="tahun" class="input" value="<?= $tahun ?>">
        </div>

        <div class="field" style="margin:0;min-width:160px">
          <label style="font-size:12px;font-weight:600">Status Pembahasan</label>
          <select name="status" class="input">
            <option value="">Semua Status</option>
            <option value="DRAFT" <?= $status === 'DRAFT' ? 'selected' : '' ?>>Draft</option>
            <option value="DIBAHAS" <?= $status === 'DIBAHAS' ? 'selected' : '' ?>>Dibahas dgn Auditi</option>
            <option value="FINAL_LHP" <?= $status === 'FINAL_LHP' ? 'selected' : '' ?>>Masuk Final LHP</option>
          </select>
        </div>

        <button type="submit" class="btn btn-outline" style="height:38px"><i class="fa-solid fa-filter"></i> Filter</button>
        <?php if ($desaId > 0 || $status !== ''): ?>
          <a href="<?= url('temuan') ?>" class="btn btn-ghost" style="height:38px">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Summary Box -->
    <?php
      $countDraft = 0; $countDibahas = 0; $countFinal = 0;
      foreach ($daftarTemuan as $itemT) {
        if ($itemT['status'] === 'FINAL_LHP') $countFinal++;
        elseif ($itemT['status'] === 'DIBAHAS') $countDibahas++;
        else $countDraft++;
      }
    ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;margin-bottom:18px">
      <div class="card" style="padding:14px 18px;border-left:4px solid #d97706">
        <div style="font-size:12px;color:var(--slate-500);font-weight:700;text-transform:uppercase">TOTAL TEMUAN TERDATA</div>
        <div style="font-size:22px;font-weight:800;color:var(--slate-800);margin-top:4px"><?= count($daftarTemuan) ?> Butir</div>
        <div style="font-size:11px;color:var(--slate-500);margin-top:4px">Standar SPKN &bull; 5 Unsur Pemeriksaan</div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid #ef4444">
        <div style="font-size:12px;color:var(--slate-500);font-weight:700;text-transform:uppercase">TOTAL NILAI KERUGIAN / SELISIH</div>
        <div style="font-size:20px;font-weight:800;color:#dc2626;margin-top:4px"><?= rupiah($totalNominal) ?></div>
        <div style="font-size:11px;color:#dc2626;margin-top:4px;font-weight:600">Potensi Pemulihan Kas Desa</div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid #059669">
        <div style="font-size:12px;color:var(--slate-500);font-weight:700;text-transform:uppercase">STATUS PEMBAHASAN AUDITI</div>
        <div style="display:flex;gap:6px;align-items:center;margin-top:8px;flex-wrap:wrap">
          <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700"><?= $countFinal ?> Final LHP</span>
          <span class="badge" style="background:#e0e7ff;color:#4338ca;font-weight:700"><?= $countDibahas ?> Dibahas</span>
          <span class="badge" style="background:#f1f5f9;color:#475569;font-weight:700"><?= $countDraft ?> Draft</span>
        </div>
      </div>
    </div>

    <!-- Tabel Daftar Temuan -->
    <div class="card" style="padding:0">
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th style="width:90px">Kode KTP</th>
              <th>Kepenghuluan &amp; Bidang</th>
              <th>Pokok Temuan / Judul</th>
              <th class="num">Nilai Temuan</th>
              <th>Status</th>
              <th style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarTemuan)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--slate-500)">
                  <i class="fa-solid fa-clipboard-check" style="font-size:36px;color:#cbd5e1;margin-bottom:10px;display:block"></i>
                  Belum ada temuan pemeriksaan untuk filter ini.<br>
                  <span style="font-size:12px">Anda dapat menambahkan temuan baru melalui tombol di atas atau langsung dari baris rincian belanja pada Kertas Kerja Audit (KKA).</span>
                </td>
              </tr>
            <?php else: $no=1; foreach ($daftarTemuan as $t): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700"><?= e($t['nomor_temuan']) ?></span></td>
                <td>
                  <strong><?= e($t['desa_nama']) ?></strong>
                  <div style="font-size:11.5px;color:var(--slate-500)">Kec. <?= e($t['kecamatan_nama']) ?> &bull; TA <?= $t['tahun_anggaran'] ?></div>
                  <?php if ($t['bidang_nama']): ?>
                    <div style="font-size:11px;color:var(--emerald-700);font-weight:600"><i class="fa-solid fa-tag"></i> <?= e($t['bidang_nama']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color:var(--slate-800)"><?= e($t['judul']) ?></strong>
                  <div style="font-size:12px;color:var(--slate-600);margin-top:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                    <b>Kondisi:</b> <?= e($t['kondisi']) ?>
                  </div>
                </td>
                <td class="num" style="color:#dc2626;font-weight:700">
                  <?= (float)$t['nominal'] > 0 ? rupiah($t['nominal']) : '<span style="color:#64748b;font-weight:400">-</span>' ?>
                </td>
                <td>
                  <?php if ($t['status'] === 'FINAL_LHP'): ?>
                    <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700"><i class="fa-solid fa-check"></i> Final LHP</span>
                  <?php elseif ($t['status'] === 'DIBAHAS'): ?>
                    <span class="badge" style="background:#e0e7ff;color:#4338ca;font-weight:600">Dibahas Auditi</span>
                  <?php else: ?>
                    <span class="badge" style="background:#f1f5f9;color:#475569">Draft KTP</span>
                  <?php endif; ?>
                </td>
                <td style="white-space:nowrap">
                  <a href="<?= url('temuan/edit?id=' . $t['id']) ?>" class="btn btn-ghost btn-sm" style="color:var(--emerald-700);padding:5px 8px" title="Edit 5 Unsur"><i class="fa-solid fa-pen-to-square"></i></a>
                  <form method="post" action="<?= url('temuan/delete') ?>" onsubmit="return confirm('Hapus temuan <?= e($t['nomor_temuan']) ?> ini?')" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button class="btn btn-ghost btn-sm" style="color:#dc2626;padding:5px 8px" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                  </form>
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
