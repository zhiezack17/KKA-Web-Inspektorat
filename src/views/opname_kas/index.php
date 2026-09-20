<?php
partial('head', ['title' => 'Pemeriksaan Kas (Opname Kas Desa) - Inspektorat Rokan Hilir']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar', ['title' => 'Pemeriksaan Kas (Opname Kas Desa)', 'icon' => 'fa-solid fa-money-bill-transfer']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-money-bill-transfer" style="color:#059669"></i>
          Berita Acara Pemeriksaan Kas (Opname Kas Desa)
        </h2>
        <p>Pengujian fisik kas tunai di brankas desa, rekonsiliasi rekening koran Bank Riau Kepri, dan pencocokan Buku Kas Umum (BKU).</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="<?= url('opname-kas/create?tahun=' . $tahun . ($desaId ? '&desa_id=' . $desaId : '')) ?>" class="btn btn-primary" style="background:#059669;border-color:#059669">
          <i class="fa-solid fa-plus"></i> Buat Berita Acara Kas Baru
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding:14px 18px;margin-bottom:18px">
      <form method="GET" action="<?= url('opname-kas') ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:8px">
          <label style="font-size:12px;font-weight:700;color:var(--slate-600)">TAHUN ANGGARAN:</label>
          <select name="tahun" class="form-control" style="width:110px;padding:6px 10px;font-size:13px" onchange="this.form.submit()">
            <?php for ($y = date('Y'); $y >= 2023; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div style="display:flex;align-items:center;gap:8px">
          <label style="font-size:12px;font-weight:700;color:var(--slate-600)">KEPENGHULUAN:</label>
          <select name="desa_id" class="form-control" style="width:240px;padding:6px 10px;font-size:13px" onchange="this.form.submit()">
            <option value="">-- Semua Kepenghuluan --</option>
            <?php foreach ($desaList as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $desaId == $d['id'] ? 'selected' : '' ?>><?= e($d['nama']) ?> (<?= e($d['kecamatan_nama']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if ($desaId > 0): ?>
          <a href="<?= url('opname-kas?tahun=' . $tahun) ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Ringkasan Statistik -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;margin-bottom:20px">
      <div class="card" style="padding:14px 18px;border-left:4px solid #059669">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:700;text-transform:uppercase">BERITA ACARA KAS</div>
        <div style="font-size:22px;font-weight:800;color:var(--slate-800);margin-top:4px">
          <?= (int)($summary['total_opname'] ?? 0) ?> <span style="font-size:13px;font-weight:600;color:var(--slate-500)">Dokumen</span>
        </div>
        <div style="font-size:11px;color:#059669;margin-top:4px">
          <i class="fa-solid fa-check-double"></i> <?= (int)($summary['count_cocok'] ?? 0) ?> Sesuai BKU
        </div>
      </div>

      <div class="card" style="padding:14px 18px;border-left:4px solid #0284c7">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:700;text-transform:uppercase">TOTAL KAS FISIK (BRANKAS)</div>
        <div style="font-size:18px;font-weight:800;color:#0369a1;margin-top:4px">
          <?= rupiah($summary['sum_fisik'] ?? 0) ?>
        </div>
        <div style="font-size:11px;color:var(--slate-500);margin-top:4px">Uang Tunai Kertas &amp; Logam</div>
      </div>

      <div class="card" style="padding:14px 18px;border-left:4px solid #7c3aed">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:700;text-transform:uppercase">SALDO REKENING BANK</div>
        <div style="font-size:18px;font-weight:800;color:#6d28d9;margin-top:4px">
          <?= rupiah($summary['sum_bank'] ?? 0) ?>
        </div>
        <div style="font-size:11px;color:var(--slate-500);margin-top:4px">Bank Riau Kepri Syariah</div>
      </div>

      <div class="card" style="padding:14px 18px;border-left:4px solid <?= ((float)($summary['sum_selisih'] ?? 0) < 0) ? '#dc2626' : '#16a34a' ?>">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:700;text-transform:uppercase">TOTAL SELISIH KAS</div>
        <div style="font-size:18px;font-weight:800;color:<?= ((float)($summary['sum_selisih'] ?? 0) < 0) ? '#dc2626' : '#15803d' ?>;margin-top:4px">
          <?= rupiah($summary['sum_selisih'] ?? 0) ?>
        </div>
        <div style="font-size:11px;margin-top:4px">
          <?php if ((int)($summary['count_kurang'] ?? 0) > 0): ?>
            <span style="color:#dc2626;font-weight:700"><i class="fa-solid fa-triangle-exclamation"></i> <?= (int)$summary['count_kurang'] ?> Kas Tekor (Kurang)</span>
          <?php else: ?>
            <span style="color:#16a34a;font-weight:700"><i class="fa-solid fa-circle-check"></i> Seluruh Kas Tertib</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Tabel Daftar Berita Acara -->
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:14px 18px;border-bottom:1px solid var(--slate-200);display:flex;justify-content:space-between;align-items:center;background:#f8fafc">
        <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--slate-800)">
          <i class="fa-solid fa-list-check" style="color:#059669;margin-right:6px"></i>
          Register Berita Acara Pemeriksaan Kas (TA <?= $tahun ?>)
        </h3>
        <span style="font-size:12px;color:var(--slate-500)">Total: <?= count($list) ?> Data</span>
      </div>

      <div class="table-responsive">
        <table class="table" style="margin:0;font-size:12.5px">
          <thead>
            <tr style="background:#f1f5f9;color:var(--slate-700)">
              <th style="width:40px;text-align:center">No</th>
              <th>Nomor BAP &amp; Tanggal</th>
              <th>Kepenghuluan</th>
              <th>Bendahara &amp; Penghulu</th>
              <th class="num">Kas Fisik (Tunai)</th>
              <th class="num">Saldo Bank</th>
              <th class="num">Kas Riil</th>
              <th class="num">Saldo BKU</th>
              <th class="num">Selisih</th>
              <th style="text-align:center">Status</th>
              <th style="min-width:160px;text-align:center;padding-right:20px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($list)): ?>
              <tr>
                <td colspan="11" style="text-align:center;padding:40px;color:var(--slate-400)">
                  <i class="fa-solid fa-inbox" style="font-size:32px;margin-bottom:8px;display:block"></i>
                  Belum ada Berita Acara Pemeriksaan Kas untuk tahun <?= $tahun ?>.
                  <div style="margin-top:10px">
                    <a href="<?= url('opname-kas/create?tahun=' . $tahun) ?>" class="btn btn-primary btn-sm">
                      <i class="fa-solid fa-plus"></i> Buat Berita Acara Pertama
                    </a>
                  </div>
                </td>
              </tr>
            <?php else: $no=1; foreach ($list as $item): 
              $selisih = (float)$item['selisih_kas'];
            ?>
              <tr>
                <td style="text-align:center"><?= $no++ ?></td>
                <td>
                  <div style="font-weight:700;color:var(--slate-900)"><?= e($item['no_bap']) ?></div>
                  <div style="font-size:11px;color:var(--slate-500)">
                    <i class="fa-regular fa-calendar"></i> <?= tgl_id($item['tgl_pemeriksaan']) ?> &bull; <?= e($item['waktu_pemeriksaan']) ?>
                  </div>
                </td>
                <td>
                  <div style="font-weight:700;color:#0f766e"><?= e($item['desa_nama']) ?></div>
                  <div style="font-size:11px;color:var(--slate-500)">Kec. <?= e($item['kecamatan_nama']) ?></div>
                </td>
                <td>
                  <div style="font-size:12px;font-weight:600">Bend: <?= e($item['nama_bendahara']) ?></div>
                  <div style="font-size:11px;color:var(--slate-500)">Pj: <?= e($item['nama_kepala_desa']) ?></div>
                </td>
                <td class="num" style="font-weight:600"><?= rupiah($item['total_kas_fisik']) ?></td>
                <td class="num" style="color:#6d28d9;font-weight:600"><?= rupiah($item['saldo_bank']) ?></td>
                <td class="num" style="font-weight:700;color:#0369a1"><?= rupiah($item['total_kas_riil']) ?></td>
                <td class="num" style="font-weight:600"><?= rupiah($item['saldo_bku']) ?></td>
                <td class="num" style="font-weight:800;color:<?= $selisih < 0 ? '#dc2626' : ($selisih > 0 ? '#d97706' : '#16a34a') ?>">
                  <?= rupiah($selisih) ?>
                </td>
                <td style="text-align:center">
                  <?php if ($item['status_selisih'] === 'COCOK'): ?>
                    <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700">
                      <i class="fa-solid fa-circle-check"></i> Cocok
                    </span>
                  <?php elseif ($item['status_selisih'] === 'KURANG'): ?>
                    <span class="badge" style="background:#fee2e2;color:#b91c1c;font-weight:700" title="Kas Tekor: Kas Riil lebih kecil dari Saldo BKU">
                      <i class="fa-solid fa-circle-xmark"></i> Kurang
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700">
                      <i class="fa-solid fa-triangle-exclamation"></i> Lebih
                    </span>
                  <?php endif; ?>

                  <?php if (!empty($item['gdrive_link'])): ?>
                    <div style="margin-top:4px">
                      <span class="badge" style="background:#f0fdf4;color:#16a34a;font-size:10px;padding:2px 6px;border:1px solid #bbf7d0" title="Tercadangkan di Google Drive: <?= e($item['gdrive_synced_at']) ?>">
                        <i class="fa-solid fa-cloud-check"></i> Drive OK
                      </span>
                    </div>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;padding-right:20px;white-space:nowrap">
                  <div style="display:inline-flex;gap:4px">
                    <a href="<?= url('print/opname-kas?id=' . $item['id']) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding:4px 8px;color:#059669;border-color:#059669" title="Cetak Naskah Berita Acara Resmi (A4)">
                      <i class="fa-solid fa-print"></i> Cetak
                    </a>
                    <?php if (!empty($item['gdrive_link'])): ?>
                      <a href="<?= e($item['gdrive_link']) ?>" target="_blank" class="btn btn-sm" style="padding:4px 8px;background:#e0f2fe;color:#0284c7;border:1px solid #bae6fd" title="Lihat Naskah BAP di Google Drive">
                        <i class="fa-brands fa-google-drive"></i>
                      </a>
                    <?php else: ?>
                      <a href="<?= url('gdrive/sync-opname-kas?id=' . $item['id']) ?>" class="btn btn-outline btn-sm" style="padding:4px 8px;color:#0284c7;border-color:#0284c7" title="Cadangkan ke Google Drive" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i>'">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                      </a>
                    <?php endif; ?>
                    <a href="<?= url('opname-kas/edit?id=' . $item['id']) ?>" class="btn btn-outline btn-sm" style="padding:4px 8px" title="Edit BAP Kas">
                      <i class="fa-solid fa-pencil"></i>
                    </a>
                    <form method="POST" action="<?= url('opname-kas/delete') ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Berita Acara Kas nomor <?= e($item['no_bap']) ?>?');" style="display:inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= $item['id'] ?>">
                      <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 8px;color:#dc2626;border-color:#fca5a5" title="Hapus">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
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
