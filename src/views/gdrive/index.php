<?php
partial('head', ['title' => 'Integrasi & Sinkronisasi Google Drive']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-cloud-arrow-up" style="color:#0284c7"></i>
          Penyimpanan & Cadangan Google Drive
        </h2>
        <p>Integrasi otomatis pencadangan dokumen pengawasan (LHP, Opname Kas, KKA, SPT) ke Google Drive Inspektorat per Irban dan Desa.</p>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <form method="get" action="<?= url('gdrive') ?>" style="margin:0">
          <select name="tahun" class="input" style="width:130px;margin:0" onchange="this.form.submit()">
            <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>>Tahun <?= $y ?></option>
            <?php endfor; ?>
          </select>
        </form>
        <a href="<?= url('gdrive/sync-all?tahun=' . $tahun) ?>" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin menyinkronkan seluruh LHP dan Berita Acara Opname Kas TA <?= $tahun ?> ke Google Drive? Proses memerlukan beberapa detik.')" style="background:#0284c7;border-color:#0284c7;display:flex;align-items:center;gap:6px">
          <i class="fa-solid fa-arrows-rotate"></i>
          <span>Sinkronkan Semua TA <?= $tahun ?></span>
        </a>
      </div>
    </div>

    <!-- Status Koneksi & Kuota Penyimpanan -->
    <div style="display:grid;grid-template-columns:1.8fr 1.2fr;gap:18px;margin-bottom:20px">
      <!-- Card Akun & Kuota -->
      <div class="card" style="padding:20px;border-left:4px solid #0284c7;margin:0">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
          <div style="display:flex;gap:14px;align-items:center">
            <div style="width:46px;height:46px;border-radius:12px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:22px">
              <i class="fa-brands fa-google-drive"></i>
            </div>
            <div>
              <div style="display:flex;align-items:center;gap:8px">
                <h4 style="margin:0;font-size:15px;font-weight:700">Akun Google Drive Terhubung</h4>
                <?php if ($connectionInfo && ($connectionInfo['success'] ?? false)): ?>
                  <span class="badge" style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600">
                    <i class="fa-solid fa-circle-check" style="margin-right:4px"></i>Terhubung
                  </span>
                <?php else: ?>
                  <span class="badge" style="background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:600">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right:4px"></i>Koneksi Bermasalah
                  </span>
                <?php endif; ?>
              </div>
              <p style="margin:2px 0 0;font-size:13px;color:#64748b">
                <b><?= e($connectionInfo['user']['displayName'] ?? 'Inspektorat Rohil') ?></b>
                (<?= e($connectionInfo['user']['emailAddress'] ?? 'teamirban4@gmail.com') ?>)
              </p>
            </div>
          </div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <a href="<?= url('gdrive/auth') ?>" class="btn btn-sm" style="background:#0284c7;color:#fff;font-size:12px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-weight:600" title="Hubungkan langsung dengan login Google akun teamirban4@gmail.com">
              <i class="fa-brands fa-google"></i>
              <span>Hubungkan Akun</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline" style="font-size:12px;display:inline-flex;align-items:center;gap:5px" onclick="document.getElementById('modal-manual-token').style.display='flex'">
              <i class="fa-solid fa-key"></i>
              <span>Input Token</span>
            </button>
            <button id="btn-test-conn" class="btn btn-sm btn-outline" style="font-size:12px;display:inline-flex;align-items:center;gap:5px" onclick="testConnection()">
              <i class="fa-solid fa-wifi"></i>
              <span>Tes Koneksi</span>
            </button>
          </div>
        </div>

        <!-- Quota Progress Bar -->
        <?php if ($connectionInfo && !empty($connectionInfo['quota'])): 
          $quota = $connectionInfo['quota'];
        ?>
          <div style="margin-top:12px">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#475569;margin-bottom:6px">
              <span><b>Kapasitas Terpakai:</b> <?= e($quota['usage_formatted']) ?> dari <?= e($quota['limit_formatted']) ?></span>
              <span style="font-weight:700;color:#0284c7"><?= $quota['percentage'] ?>%</span>
            </div>
            <div style="background:#f1f5f9;border-radius:999px;height:9px;overflow:hidden;width:100%">
              <div style="background:linear-gradient(90deg, #0284c7, #38bdf8);height:100%;width:<?= min(100, max(1, $quota['percentage'])) ?>%;border-radius:999px"></div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Folder Root Info -->
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;font-size:12.5px;flex-wrap:wrap;gap:10px">
          <div>
            <span style="color:#64748b">Folder Induk di Google Drive:</span>
            <a href="<?= e($parentFolderLink ?? 'https://drive.google.com/drive/folders/1QmpDbKANfhitTF69T4epmaD7rWsjMTem') ?>" target="_blank" style="font-weight:700;color:#0284c7;margin-left:6px;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
              <i class="fa-solid fa-folder-open" style="color:#f59e0b"></i>
              <span><?= e($parentFolderName ?? 'KKA DIGITAL INSPEKTORAT') ?></span>
              <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px"></i>
            </a>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:11.5px;color:#059669;background:#ecfdf5;padding:3px 8px;border-radius:6px;font-weight:600">
              Hierarki: IRBAN &rarr; TA &rarr; DESA
            </span>
            <a href="<?= e($parentFolderLink ?? 'https://drive.google.com/drive/folders/1QmpDbKANfhitTF69T4epmaD7rWsjMTem') ?>" target="_blank" class="btn btn-sm" style="background:#0284c7;color:#fff;font-size:11.5px;padding:4px 10px;display:inline-flex;align-items:center;gap:5px">
              <i class="fa-brands fa-google-drive"></i>
              <span>Buka Folder Induk</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Card Statistik Berkas -->
      <div class="card" style="padding:20px;margin:0;display:flex;flex-direction:column;justify-content:space-between">
        <div>
          <h4 style="margin:0 0 12px;font-size:14px;font-weight:700;color:#334155;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-chart-pie" style="color:#6366f1"></i>
            Statistik Dokumen Tercadangkan
          </h4>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div style="background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0;text-align:center">
              <div style="font-size:22px;font-weight:800;color:#0284c7"><?= number_format($totalSyncedCount, 0, ',', '.') ?></div>
              <div style="font-size:11px;color:#64748b;margin-top:2px">Dokumen Tersimpan</div>
            </div>
            <div style="background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0;text-align:center">
              <div style="font-size:22px;font-weight:800;color:#10b981">
                <?= $totalBytesSynced > 1048576 ? round($totalBytesSynced / 1048576, 1) . ' MB' : round($totalBytesSynced / 1024, 1) . ' KB' ?>
              </div>
              <div style="font-size:11px;color:#64748b;margin-top:2px">Ukuran Cadangan</div>
            </div>
          </div>
        </div>

        <!-- Distribusi Irban -->
        <div style="font-size:12px">
          <div style="color:#64748b;margin-bottom:6px;font-weight:600">Penyebaran Berkas per Irban:</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px">
            <?php foreach ($statsIrban as $si): ?>
              <span style="background:#f1f5f9;border:1px solid #cbd5e1;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600">
                <?= e($si['irban_nama']) ?>: <b style="color:#0284c7"><?= $si['total_files'] ?></b>
              </span>
            <?php endforeach; ?>
            <?php if (empty($statsIrban)): ?>
              <span style="color:#94a3b8;font-style:italic">Belum ada dokumen yang disinkronkan.</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Banner Informasi Konfigurasi Multi-Akun -->
    <div class="card" style="background:#f8fafc;border:1px dashed #cbd5e1;padding:14px 18px;margin-bottom:20px">
      <div style="display:flex;align-items:center;gap:12px">
        <i class="fa-solid fa-circle-info" style="font-size:18px;color:#0284c7;flex-shrink:0"></i>
        <div style="font-size:12.5px;color:#334155;line-height:1.5">
          <b>Fleksibilitas Pergantian Akun:</b> Apabila nantinya ingin mengalihkan penyimpanan ke akun resmi <b>Bagian Perencanaan</b> (contoh: <code>perencanaan.inspektorat@...</code>) atau akun Google Workspace dinas lainnya, cukup perbarui kredensial pada file <code>.env</code> server tanpa memerlukan perubahan pada kode sistem.
        </div>
      </div>
    </div>

    <!-- TABEL 1: Naskah LHP Final Desa -->
    <div class="card" style="padding:0;margin-bottom:24px">
      <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
        <div>
          <h3 style="margin:0;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-file-shield" style="color:#059669"></i>
            Naskah Laporan Hasil Pengawasan (LHP) Desa — TA <?= $tahun ?>
          </h3>
          <p style="margin:2px 0 0;font-size:12px;color:#64748b">
            Folder Tujuan: <code>KKA DIGITAL INSPEKTORAT / IRBAN [I-V] / TAHUN ANGGARAN <?= $tahun ?> / Kepenghuluan ... / 04_LHP_FINAL</code>
          </p>
        </div>
      </div>
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th>Desa / Kepenghuluan</th>
              <th>Kecamatan</th>
              <th style="text-align:center">Temuan Pemeriksaan</th>
              <th style="text-align:center">Status Google Drive</th>
              <th style="width:200px;text-align:center">Aksi Cadangan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarLhp)): ?>
              <tr>
                <td colspan="6" style="text-align:center;color:#94a3b8;padding:30px">
                  Tidak ada data LHP pada Tahun Anggaran <?= $tahun ?>.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftarLhp as $idx => $lhp): ?>
                <tr>
                  <td style="text-align:center"><?= $idx + 1 ?></td>
                  <td>
                    <b style="font-size:13.5px"><?= e($lhp['nama_desa']) ?></b>
                  </td>
                  <td><?= e($lhp['nama_kec']) ?></td>
                  <td style="text-align:center">
                    <?php if ($lhp['total_temuan'] > 0): ?>
                      <span class="badge" style="background:#fee2e2;color:#b91c1c;font-weight:700">
                        <?= $lhp['total_temuan'] ?> Temuan (<?= rupiah((float)$lhp['nominal_temuan']) ?>)
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background:#f1f5f9;color:#64748b">Nihil Temuan</span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:center">
                    <?php if (!empty($lhp['gdrive_link'])): ?>
                      <span class="badge" style="background:#dcfce7;color:#15803d;display:inline-flex;align-items:center;gap:4px">
                        <i class="fa-solid fa-cloud-check"></i>
                        <span>Tersimpan (<?= date('d/m/y H:i', strtotime($lhp['gdrive_synced_at'])) ?>)</span>
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0">
                        Belum Dicadangkan
                      </span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                      <?php if (!empty($lhp['gdrive_link'])): ?>
                        <a href="<?= e($lhp['gdrive_link']) ?>" target="_blank" class="btn btn-sm" style="background:#e0f2fe;color:#0284c7;border-color:#bae6fd;display:inline-flex;align-items:center;gap:4px">
                          <i class="fa-solid fa-arrow-up-right-from-square"></i>
                          <span>Buka di Drive</span>
                        </a>
                      <?php endif; ?>
                      <button class="btn btn-sm btn-outline btn-sync-lhp" 
                              data-desa-id="<?= $lhp['desa_id'] ?>" 
                              data-tahun="<?= $tahun ?>"
                              onclick="syncLhp(this, <?= $lhp['desa_id'] ?>, <?= $tahun ?>)"
                              style="display:inline-flex;align-items:center;gap:4px">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span><?= !empty($lhp['gdrive_link']) ? 'Sinkron Ulang' : 'Simpan ke Drive' ?></span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TABEL 2: Berita Acara Pemeriksaan Kas (Opname Kas) -->
    <div class="card" style="padding:0;margin-bottom:24px">
      <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
        <div>
          <h3 style="margin:0;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-money-bill-transfer" style="color:#0284c7"></i>
            Berita Acara Pemeriksaan Kas (Opname Kas)
          </h3>
          <p style="margin:2px 0 0;font-size:12px;color:#64748b">
            Folder Tujuan: <code>KKA DIGITAL INSPEKTORAT / IRBAN [I-V] / TAHUN ANGGARAN ... / Kepenghuluan ... / 03_OPNAME_KAS</code>
          </p>
        </div>
      </div>
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th>Nomor BAP & Tanggal</th>
              <th>Desa / Kepenghuluan</th>
              <th class="num">Total Kas Riil</th>
              <th class="num">Selisih Kas</th>
              <th style="text-align:center">Status Google Drive</th>
              <th style="width:200px;text-align:center">Aksi Cadangan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarKas)): ?>
              <tr>
                <td colspan="7" style="text-align:center;color:#94a3b8;padding:30px">
                  Belum ada dokumen Berita Acara Opname Kas yang terdaftar.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftarKas as $idx => $kas): ?>
                <tr>
                  <td style="text-align:center"><?= $idx + 1 ?></td>
                  <td>
                    <b><?= e($kas['no_bap']) ?></b>
                    <div style="font-size:11px;color:#64748b"><?= tgl_id($kas['tgl_pemeriksaan']) ?></div>
                  </td>
                  <td>
                    <b><?= e($kas['nama_desa']) ?></b>
                    <div style="font-size:11px;color:#64748b">Kec. <?= e($kas['nama_kec']) ?></div>
                  </td>
                  <td class="num font-mono"><?= rupiah((float)$kas['total_kas_riil']) ?></td>
                  <td class="num font-mono">
                    <?php if ((float)$kas['selisih_kas'] < 0): ?>
                      <span style="color:#dc2626;font-weight:700"><?= rupiah((float)$kas['selisih_kas']) ?></span>
                    <?php else: ?>
                      <span style="color:#16a34a"><?= rupiah((float)$kas['selisih_kas']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:center">
                    <?php if (!empty($kas['gdrive_link'])): ?>
                      <span class="badge" style="background:#dcfce7;color:#15803d;display:inline-flex;align-items:center;gap:4px">
                        <i class="fa-solid fa-cloud-check"></i>
                        <span>Tersimpan (<?= date('d/m/y H:i', strtotime($kas['gdrive_synced_at'])) ?>)</span>
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0">
                        Belum Dicadangkan
                      </span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                      <?php if (!empty($kas['gdrive_link'])): ?>
                        <a href="<?= e($kas['gdrive_link']) ?>" target="_blank" class="btn btn-sm" style="background:#e0f2fe;color:#0284c7;border-color:#bae6fd;display:inline-flex;align-items:center;gap:4px">
                          <i class="fa-solid fa-arrow-up-right-from-square"></i>
                          <span>Buka</span>
                        </a>
                      <?php endif; ?>
                      <button class="btn btn-sm btn-outline" 
                              onclick="syncOpnameKas(this, <?= $kas['id'] ?>)"
                              style="display:inline-flex;align-items:center;gap:4px">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span><?= !empty($kas['gdrive_link']) ? 'Sinkron Ulang' : 'Simpan' ?></span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TABEL 3: Riwayat Berkas Tersinkronisasi -->
    <div class="card" style="padding:0">
      <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0">
        <h3 style="margin:0;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-clock-rotate-left" style="color:#64748b"></i>
          Riwayat Pencadangan Google Drive (50 Berkas Terakhir)
        </h3>
      </div>
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th>Tipe Dokumen</th>
              <th>Nama Berkas</th>
              <th>Irban & Wilayah</th>
              <th>Waktu Sinkronisasi</th>
              <th style="width:140px;text-align:center">Akses Google Drive</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($syncedFiles)): ?>
              <tr>
                <td colspan="6" style="text-align:center;color:#94a3b8;padding:30px">
                  Belum ada riwayat berkas yang tersinkronisasi.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($syncedFiles as $idx => $f): ?>
                <tr>
                  <td style="text-align:center"><?= $idx + 1 ?></td>
                  <td>
                    <?php
                      $badgeStyle = 'background:#f1f5f9;color:#475569';
                      if ($f['tipe_dokumen'] === 'LHP_FINAL') $badgeStyle = 'background:#dcfce7;color:#15803d';
                      if ($f['tipe_dokumen'] === 'OPNAME_KAS') $badgeStyle = 'background:#e0f2fe;color:#0284c7';
                      if ($f['tipe_dokumen'] === 'KKA_RINCIAN') $badgeStyle = 'background:#fef3c7;color:#b45309';
                    ?>
                    <span class="badge" style="<?= $badgeStyle ?>;font-weight:700">
                      <?= e($f['tipe_dokumen']) ?>
                    </span>
                  </td>
                  <td>
                    <b><?= e($f['file_name']) ?></b>
                    <div style="font-size:11px;color:#64748b">Ukuran: <?= round($f['file_size'] / 1024, 1) ?> KB</div>
                  </td>
                  <td>
                    <span style="font-weight:600;color:#0284c7"><?= e($f['irban_nama']) ?></span>
                    <?php if (!empty($f['nama_desa'])): ?>
                      <div style="font-size:11px;color:#64748b">Kepenghuluan <?= e($f['nama_desa']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= date('d/m/Y H:i:s', strtotime($f['synced_at'])) ?>
                    <div style="font-size:11px;color:#64748b">Oleh: <?= e($f['uploader_nama'] ?? 'Sistem') ?></div>
                  </td>
                  <td style="text-align:center">
                    <a href="<?= e($f['web_view_link']) ?>" target="_blank" class="btn btn-sm" style="background:#0284c7;color:#fff;display:inline-flex;align-items:center;gap:4px">
                      <i class="fa-solid fa-arrow-up-right-from-square"></i>
                      <span>Lihat di Drive</span>
                    </a>
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

<script>
function testConnection() {
  const btn = document.getElementById('btn-test-conn');
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Menguji...</span>';

  fetch('<?= url('gdrive/test') ?>')
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = origHtml;
      if (data.success) {
        alert('✅ Koneksi Google Drive Berhasil!\n\nPengguna: ' + data.user.displayName + '\nEmail: ' + data.user.emailAddress + '\nKapasitas: ' + data.quota.usage_formatted + ' dari ' + data.quota.limit_formatted + ' (' + data.quota.percentage + '%)');
      } else {
        alert('❌ Koneksi Gagal:\n' + (data.message || 'Tidak dapat menghubungi server Google Drive.'));
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = origHtml;
      alert('❌ Terjadi kesalahan jaringan saat menguji koneksi: ' + err);
    });
}

function syncLhp(btn, desaId, tahun) {
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Menyimpan...</span>';

  fetch('<?= url('gdrive/sync-lhp') ?>?desa_id=' + desaId + '&tahun=' + tahun + '&ajax=1')
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = origHtml;
      if (data.success) {
        alert('✅ ' + data.message + '\n\nDokumen dapat langsung dilihat pada Google Drive.');
        window.location.reload();
      } else {
        alert('❌ Gagal sinkronisasi: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = origHtml;
      alert('❌ Kesalahan jaringan: ' + err);
    });
}

function syncOpnameKas(btn, id) {
  const origHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Menyimpan...</span>';

  fetch('<?= url('gdrive/sync-opname-kas') ?>?id=' + id + '&ajax=1')
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = origHtml;
      if (data.success) {
        alert('✅ ' + data.message);
        window.location.reload();
      } else {
        alert('❌ Gagal sinkronisasi: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = origHtml;
      alert('❌ Kesalahan jaringan: ' + err);
    });
}
</script>

<!-- Modal Input Manual Refresh Token -->
<div id="modal-manual-token" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(3px)">
  <div class="card" style="width:90%;max-width:520px;padding:24px;background:#fff;border-radius:12px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);margin:0">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;border-bottom:1px solid #f1f5f9;padding-bottom:10px">
      <h4 style="margin:0;display:flex;align-items:center;gap:8px;font-size:16px;color:#0f172a">
        <i class="fa-solid fa-key" style="color:#0284c7"></i>
        <span>Input Refresh Token Google Drive</span>
      </h4>
      <button type="button" onclick="document.getElementById('modal-manual-token').style.display='none'" style="background:none;border:none;font-size:22px;line-height:1;cursor:pointer;color:#94a3b8">&times;</button>
    </div>
    <form method="post" action="<?= url('gdrive/save-token') ?>">
      <?= csrf_field() ?>
      <p style="font-size:13px;color:#64748b;margin-bottom:12px;line-height:1.5">
        Jika Anda sudah meng-generate <b>Refresh Token</b> (misalnya lewat <i>Google OAuth Playground</i> untuk akun <b>teamirban4@gmail.com</b>), tempelkan kodenya di bawah ini:
      </p>
      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:6px">Refresh Token:</label>
        <textarea name="refresh_token" rows="3" class="input" style="width:100%;font-family:monospace;font-size:12px;box-sizing:border-box" placeholder="1//04... atau 1//0gyh..." required></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px">
        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('modal-manual-token').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm" style="background:#0284c7;border-color:#0284c7">Simpan & Hubungkan</button>
      </div>
    </form>
  </div>
</div>

<?php partial('foot'); ?>
