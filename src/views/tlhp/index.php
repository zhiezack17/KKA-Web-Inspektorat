<?php
partial('head', ['title' => 'Pemantauan Tindak Lanjut LHP (TLHP 60 Hari) - Inspektorat Rokan Hilir']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar', ['title' => 'Pemantauan Tindak Lanjut (TLHP 60 Hari)', 'icon' => 'fa-solid fa-clock-rotate-left']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-clock-rotate-left" style="color:#d97706"></i>
          Pemantauan Tindak Lanjut LHP (TLHP 60 Hari) &amp; Rekap Pemulihan Kas
        </h2>
        <p>Instrumen pengawasan kepatuhan penyelesaian rekomendasi audit 60 hari kalender &bull; Rekonsiliasi setoran kas ke Bank Riau Kepri Syariah.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="<?= url('temuan') ?>" class="btn btn-outline" style="color:#0284c7;border-color:#0284c7">
          <i class="fa-solid fa-file-circle-plus"></i> Kelola / Input Temuan (KTP)
        </a>
        <a href="<?= url('print/matriks-tlhp?tahun=' . $tahun . ($desaId ? '&desa_id=' . $desaId : '')) ?>" target="_blank" class="btn btn-outline" style="color:#059669;border-color:#059669">
          <i class="fa-solid fa-print"></i> Cetak Matriks TLHP (A4 Landscape)
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding:14px 18px;margin-bottom:18px">
      <form method="GET" action="<?= url('tlhp') ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
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
          <a href="<?= url('tlhp?tahun=' . $tahun) ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Executive KPI Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;margin-bottom:20px">
      <div class="card" style="padding:14px 18px;border-left:4px solid #0284c7">
        <div style="font-size:11px;color:var(--slate-500);font-weight:700;text-transform:uppercase">TOTAL REKOMENDASI</div>
        <div style="font-size:22px;font-weight:800;color:var(--slate-800);margin-top:4px">
          <?= (int)($summary['total_rekomendasi'] ?? 0) ?> <span style="font-size:12.5px;font-weight:600;color:var(--slate-500)">Butir</span>
        </div>
        <div style="font-size:11px;color:var(--slate-500);margin-top:4px">
          Nilai: <b><?= rupiah($summary['sum_rekomendasi'] ?? 0) ?></b>
        </div>
      </div>

      <div class="card" style="padding:14px 18px;border-left:4px solid #16a34a">
        <div style="font-size:11px;color:var(--slate-500);font-weight:700;text-transform:uppercase">KAS DESA DIPULIHKAN</div>
        <div style="font-size:20px;font-weight:800;color:#15803d;margin-top:4px">
          <?= rupiah($summary['sum_disetor'] ?? 0) ?>
        </div>
        <div style="font-size:11px;color:#16a34a;margin-top:4px;font-weight:700">
          <i class="fa-solid fa-arrow-trend-up"></i> <?= $persenPulih ?>% Berhasil Disetor
        </div>
      </div>

      <div class="card" style="padding:14px 18px;border-left:4px solid <?= ((float)($summary['sum_sisa'] ?? 0) > 0) ? '#dc2626' : '#16a34a' ?>">
        <div style="font-size:11px;color:var(--slate-500);font-weight:700;text-transform:uppercase">SISA KERUGIAN BELUM DISETOR</div>
        <div style="font-size:20px;font-weight:800;color:<?= ((float)($summary['sum_sisa'] ?? 0) > 0) ? '#dc2626' : '#15803d' ?>;margin-top:4px">
          <?= rupiah($summary['sum_sisa'] ?? 0) ?>
        </div>
        <div style="font-size:11px;color:var(--slate-500);margin-top:4px">
          Tunggakan Kas Desa
        </div>
      </div>

      <div class="card" style="padding:14px 18px;border-left:4px solid #f59e0b">
        <div style="font-size:11px;color:var(--slate-500);font-weight:700;text-transform:uppercase">STATUS PENYELESAIAN</div>
        <div style="display:flex;gap:6px;align-items:center;margin-top:6px;flex-wrap:wrap">
          <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700"><?= (int)($summary['count_tuntas'] ?? 0) ?> Tuntas</span>
          <span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700"><?= (int)($summary['count_proses'] ?? 0) ?> Proses</span>
          <span class="badge" style="background:#fee2e2;color:#b91c1c;font-weight:700"><?= (int)($summary['count_belum'] ?? 0) ?> Belum</span>
        </div>
        <?php if ((int)($summary['count_terlambat'] ?? 0) > 0): ?>
          <div style="font-size:10.5px;color:#dc2626;font-weight:700;margin-top:6px">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= (int)$summary['count_terlambat'] ?> Terlambat Lewat 60 Hari
          </div>
        <?php else: ?>
          <div style="font-size:10.5px;color:#16a34a;margin-top:6px">
            <i class="fa-solid fa-clock"></i> Dalam Batas Waktu 60 Hari
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Progress Bar Pemulihan Kas Desa -->
    <div class="card" style="padding:16px 20px;margin-bottom:20px;background:#f8fafc">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
        <div style="font-size:12.5px;font-weight:700;color:var(--slate-800)">
          <i class="fa-solid fa-shield-halved" style="color:#059669;margin-right:6px"></i>
          Tingkat Pemulihan Kas Desa (Recovery Rate) TA <?= $tahun ?>
        </div>
        <div style="font-size:13px;font-weight:800;color:#059669">
          <?= $persenPulih ?>%
        </div>
      </div>
      <div style="width:100%;height:10px;background:#e2e8f0;border-radius:10px;overflow:hidden">
        <div style="width:<?= min(100, max(0, $persenPulih)) ?>%;height:100%;background:linear-gradient(90deg, #10b981, #059669);border-radius:10px;transition:width 0.4s"></div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:11px;color:var(--slate-500)">
        <span>Disetor: <?= rupiah($summary['sum_disetor'] ?? 0) ?></span>
        <span>Target: <?= rupiah($summary['sum_rekomendasi'] ?? 0) ?></span>
      </div>
    </div>

    <!-- Tabel Daftar Tindak Lanjut -->
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:14px 18px;border-bottom:1px solid var(--slate-200);display:flex;justify-content:space-between;align-items:center;background:#f8fafc">
        <h3 style="margin:0;font-size:14px;font-weight:700;color:var(--slate-800)">
          <i class="fa-solid fa-table-list" style="color:#d97706;margin-right:6px"></i>
          Matriks Pemantauan Tindak Lanjut Hasil Pengawasan (TA <?= $tahun ?>)
        </h3>
        <span style="font-size:12px;color:var(--slate-500)">Total: <?= count($list) ?> Rekomendasi</span>
      </div>

      <div class="table-responsive">
        <table class="table" style="margin:0;font-size:12px">
          <thead>
            <tr style="background:#f1f5f9;color:var(--slate-700)">
              <th style="width:30px;text-align:center">No</th>
              <th>Kepenghuluan</th>
              <th>Pokok Temuan &amp; Rekomendasi</th>
              <th class="num">Nilai Temuan</th>
              <th class="num">Nilai Disetor (STS)</th>
              <th class="num">Sisa Kerugian</th>
              <th style="text-align:center;width:130px">Countdown 60 Hari</th>
              <th style="text-align:center;width:110px">Status &amp; Verif</th>
              <th style="min-width:135px;text-align:center;padding-right:20px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($list)): ?>
              <tr>
                <td colspan="9" style="text-align:center;padding:40px;color:var(--slate-400)">
                  <i class="fa-solid fa-clipboard-check" style="font-size:32px;margin-bottom:8px;display:block"></i>
                  Tidak ada temuan audit atau rekomendasi yang perlu ditindaklanjuti untuk tahun <?= $tahun ?>.
                  <div style="margin-top:14px;display:flex;gap:10px;justify-content:center">
                    <a href="<?= url('tlhp?tahun=2025') ?>" class="btn btn-primary btn-sm" style="background:#0284c7;border-color:#0284c7">
                      <i class="fa-solid fa-clock-rotate-left"></i> Buka Data Sampel TA 2025
                    </a>
                    <a href="<?= url('temuan') ?>" class="btn btn-outline btn-sm">
                      <i class="fa-solid fa-plus"></i> Input Temuan di Modul Temuan (KTP)
                    </a>
                  </div>
                </td>
              </tr>
            <?php else: $no=1; foreach ($list as $item): 
              $sisaHari = (int)$item['sisa_hari'];
              $isTuntas = ($item['status'] === 'TUNTAS');
            ?>
              <tr>
                <td style="text-align:center"><?= $no++ ?></td>
                <td>
                  <div style="font-weight:700;color:#0f766e"><?= e($item['desa_nama']) ?></div>
                  <div style="font-size:11px;color:var(--slate-500)">Kec. <?= e($item['kecamatan_nama']) ?></div>
                </td>
                <td style="max-width:320px">
                  <div style="font-weight:700;color:var(--slate-900);margin-bottom:2px">
                    <span class="badge" style="background:#f1f5f9;color:#334155;margin-right:4px"><?= e($item['nomor_temuan']) ?></span>
                    <?= e($item['temuan_judul']) ?>
                  </div>
                  <div style="font-size:11px;color:#475569;margin-top:3px;line-height:1.4">
                    <b>Rekomendasi:</b> <?= e(mb_strimwidth($item['rekomendasi_teks'] ?: $item['temuan_rekomendasi'], 0, 140, '...')) ?>
                  </div>
                  <?php if (!empty($item['uraian_tindak_lanjut'])): ?>
                    <div style="font-size:10.5px;color:#059669;margin-top:4px;background:#f0fdf4;padding:3px 6px;border-radius:4px">
                      <b>Tindak Lanjut Auditi:</b> <?= e(mb_strimwidth($item['uraian_tindak_lanjut'], 0, 100, '...')) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="num" style="font-weight:600"><?= rupiah($item['nominal_rekomendasi']) ?></td>
                <td class="num" style="font-weight:700;color:#15803d">
                  <?= rupiah($item['nominal_disetor']) ?>
                  <?php if (!empty($item['no_bukti_setor'])): ?>
                    <div style="font-size:10px;color:var(--slate-500);font-weight:normal">STS: <?= e($item['no_bukti_setor']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="num" style="font-weight:700;color:<?= (float)$item['sisa_kerugian'] > 0 ? '#dc2626' : '#15803d' ?>">
                  <?= rupiah($item['sisa_kerugian']) ?>
                </td>
                <td style="text-align:center">
                  <?php if ($isTuntas): ?>
                    <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700">
                      <i class="fa-solid fa-circle-check"></i> Tuntas
                    </span>
                  <?php elseif ($sisaHari > 15): ?>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:700">
                      <i class="fa-solid fa-hourglass-half"></i> Sisa <?= $sisaHari ?> Hari
                    </span>
                    <div style="font-size:10px;color:var(--slate-500);margin-top:2px">Batas: <?= tgl_id($item['batas_waktu_tl']) ?></div>
                  <?php elseif ($sisaHari >= 0): ?>
                    <span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700">
                      <i class="fa-solid fa-triangle-exclamation"></i> Sisa <?= $sisaHari ?> Hari
                    </span>
                    <div style="font-size:10px;color:#b45309;margin-top:2px">Mendekati Batas!</div>
                  <?php else: ?>
                    <span class="badge" style="background:#fee2e2;color:#b91c1c;font-weight:800">
                      <i class="fa-solid fa-bell"></i> Terlambat <?= abs($sisaHari) ?> Hari
                    </span>
                    <div style="font-size:10px;color:#b91c1c;margin-top:2px">Jatuh Tempo Lewat!</div>
                  <?php endif; ?>
                </td>
                <td style="text-align:center">
                  <?php if ($item['status'] === 'TUNTAS'): ?>
                    <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700">Tuntas</span>
                  <?php elseif ($item['status'] === 'PROSES'): ?>
                    <span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700">Proses</span>
                  <?php else: ?>
                    <span class="badge" style="background:#fee2e2;color:#b91c1c;font-weight:700">Belum</span>
                  <?php endif; ?>

                  <div style="margin-top:4px">
                    <?php if ($item['verifikasi_apip'] === 'SESUAI'): ?>
                      <span style="font-size:9.5px;color:#15803d;font-weight:700"><i class="fa-solid fa-check-double"></i> Terverifikasi</span>
                    <?php elseif ($item['verifikasi_apip'] === 'BELUM_SESUAI'): ?>
                      <span style="font-size:9.5px;color:#dc2626;font-weight:700"><i class="fa-solid fa-xmark"></i> Belum Sesuai</span>
                    <?php else: ?>
                      <span style="font-size:9.5px;color:var(--slate-400)">Belum Verif</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td style="text-align:center;padding-right:20px;white-space:nowrap">
                  <button type="button" class="btn btn-primary btn-sm" style="padding:5px 10px;font-size:11.5px;background:#0284c7;border-color:#0284c7;font-weight:700;white-space:nowrap" onclick="openModalTl(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)">
                    <i class="fa-solid fa-pen-to-square"></i> Update TL
                  </button>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- MODAL UPDATE TINDAK LANJUT -->
<div id="modalTl" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);z-index:9999;align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:12px;max-width:620px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2)">
    
    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;background:#f8fafc">
      <h3 style="margin:0;font-size:14.5px;font-weight:700;color:#0f172a" id="modalTlTitle">
        <i class="fa-solid fa-file-pen" style="color:#0284c7;margin-right:6px"></i>
        Update Progres Tindak Lanjut Rekomendasi
      </h3>
      <button type="button" onclick="closeModalTl()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b">&times;</button>
    </div>

    <form method="POST" action="<?= url('tlhp/update') ?>" style="padding:20px">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="m_id" value="">

      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px;margin-bottom:16px;font-size:12px">
        <div><b>Kepenghuluan:</b> <span id="m_desa"></span></div>
        <div style="margin-top:2px"><b>Temuan:</b> <span id="m_temuan" style="color:#b45309;font-weight:700"></span></div>
        <div style="margin-top:2px"><b>Target Pemulihan Kas:</b> <span id="m_target" style="color:#dc2626;font-weight:700"></span></div>
      </div>

      <div style="margin-bottom:14px">
        <label class="form-label" style="font-weight:700">Status Tindak Lanjut Auditi <span style="color:#dc2626">*</span></label>
        <select name="status" id="m_status" class="form-control" required>
          <option value="BELUM">BELUM (Belum ada penyelesaian)</option>
          <option value="PROSES">PROSES (Dalam proses penyetoran/penyusunan bukti)</option>
          <option value="TUNTAS">TUNTAS (Rekomendasi selesai 100%)</option>
        </select>
      </div>

      <div style="margin-bottom:14px">
        <label class="form-label" style="font-weight:700">Uraian Tindak Lanjut yang Dilakukan Desa</label>
        <textarea name="uraian_tindak_lanjut" id="m_uraian" class="form-control" rows="3" placeholder="Jelaskan langkah konkret yang telah ditempuh oleh Penghulu/Bendahara desa..."></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
          <label class="form-label" style="font-weight:700">Nominal Disetor (Rp)</label>
          <input type="text" name="nominal_disetor" id="m_nominal_disetor" class="form-control" placeholder="0" oninput="formatMoneyInput(this)">
          <small style="color:var(--slate-500)">Nominal yang telah disetor ke kas desa</small>
        </div>

        <div>
          <label class="form-label" style="font-weight:700">Nomor Bukti Setor / STS / NTPN</label>
          <input type="text" name="no_bukti_setor" id="m_no_bukti" class="form-control" placeholder="Contoh: STS-012/RKD/2025">
        </div>
      </div>

      <div style="margin-bottom:14px">
        <label class="form-label" style="font-weight:700">Tanggal Penyetoran Kas</label>
        <input type="date" name="tgl_setor" id="m_tgl_setor" class="form-control">
      </div>

      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px;margin-bottom:16px">
        <div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:8px">
          <i class="fa-solid fa-user-check"></i> Verifikasi Pengawasan APIP (Inspektorat)
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:8px">
          <div>
            <label class="form-label" style="font-size:11.5px;font-weight:700">Hasil Verifikasi Dokumen</label>
            <select name="verifikasi_apip" id="m_verifikasi" class="form-control" style="font-size:12.5px">
              <option value="BELUM_VERIFIKASI">BELUM DIVERIFIKASI</option>
              <option value="SESUAI">SESUAI (Bukti Sah &amp; Valid)</option>
              <option value="BELUM_SESUAI">BELUM SESUAI (Perlu Dilengkapi)</option>
            </select>
          </div>

          <div>
            <label class="form-label" style="font-size:11.5px;font-weight:700">Catatan Verifikator APIP</label>
            <input type="text" name="catatan_apip" id="m_catatan_apip" class="form-control" style="font-size:12.5px" placeholder="Catatan Irban / Dalnis...">
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px">
        <button type="button" class="btn btn-outline" onclick="closeModalTl()">Batal</button>
        <button type="submit" class="btn btn-primary" style="background:#059669;border-color:#059669;font-weight:700">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Progres TL
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function formatMoneyInput(el) {
  let val = el.value.replace(/[^0-9]/g, '');
  let num = parseInt(val) || 0;
  el.value = num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function openModalTl(item) {
  document.getElementById('m_id').value = item.id;
  document.getElementById('m_desa').textContent = item.desa_nama + ' (Kec. ' + item.kecamatan_nama + ')';
  document.getElementById('m_temuan').textContent = item.nomor_temuan + ' - ' + item.temuan_judul;
  
  let targetNum = parseFloat(item.nominal_rekomendasi) || 0;
  document.getElementById('m_target').textContent = 'Rp ' + Math.round(targetNum).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

  document.getElementById('m_status').value = item.status || 'BELUM';
  document.getElementById('m_uraian').value = item.uraian_tindak_lanjut || '';

  let disetorNum = parseFloat(item.nominal_disetor) || 0;
  document.getElementById('m_nominal_disetor').value = disetorNum ? Math.round(disetorNum).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '0';

  document.getElementById('m_no_bukti').value = item.no_bukti_setor || '';
  document.getElementById('m_tgl_setor').value = item.tgl_setor || '';
  document.getElementById('m_verifikasi').value = item.verifikasi_apip || 'BELUM_VERIFIKASI';
  document.getElementById('m_catatan_apip').value = item.catatan_apip || '';

  document.getElementById('modalTl').style.display = 'flex';
}

function closeModalTl() {
  document.getElementById('modalTl').style.display = 'none';
}

// Close on backdrop click
window.addEventListener('click', function(e) {
  let modal = document.getElementById('modalTl');
  if (e.target === modal) {
    closeModalTl();
  }
});
</script>

<?php partial('foot'); ?>
