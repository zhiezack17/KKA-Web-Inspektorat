<?php
$auth = $GLOBALS['auth'];
$currUser = $auth->user();
$narasi = $data['narasi'] ?? ($narasi ?? []);

partial('head', ['title' => 'Review Sheet Routing Slip - Kepenghuluan ' . $desa['nama'] . ' TA ' . $tahun]);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar', ['title' => 'Review Sheet Kendali Mutu LHA / LHP', 'icon' => 'fa-solid fa-folder-open']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-folder-open" style="color:#d97706"></i>
          Lembar Catatan Hasil Reviu (Review Sheet Routing Slip)
        </h2>
        <p>Kendali Mutu Berjenjang Pengawasan Keuangan Kepenghuluan <?= e($desa['nama']) ?>, Kec. <?= e($desa['kecamatan_nama']) ?> TA <?= $tahun ?>.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="<?= url('routing-slip?tahun=' . $tahun) ?>" class="btn btn-outline">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Register
        </a>
        <a href="<?= url('lhp/show?desa_id=' . $desa['id'] . '&tahun=' . $tahun) ?>" class="btn btn-outline" style="border-color:#059669;color:#059669;font-weight:700">
          <i class="fa-solid fa-file-shield"></i> Pratinjau Naskah LHP
        </a>
        <a href="<?= url('print/routing-slip?desa_id=' . $desa['id'] . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-primary" style="background:#d97706;border-color:#d97706">
          <i class="fa-solid fa-print"></i> Cetak Routing Slip (Map Kuning F4)
        </a>
      </div>
    </div>

    <!-- Info Singkat Penugasan & Status -->
    <div class="card" style="background:#fff;border:1px solid #e2e8f0;padding:16px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
      <div style="display:flex;align-items:center;gap:16px">
        <div style="width:44px;height:44px;border-radius:10px;background:#fef3c7;color:#b45309;display:grid;place-items:center;font-size:20px;flex-shrink:0">
          <i class="fa-solid fa-file-contract"></i>
        </div>
        <div style="font-size:13px;line-height:1.5">
          <div><b>Kepenghuluan:</b> <?= e($desa['nama']) ?> &bull; <b>Kecamatan:</b> <?= e($desa['kecamatan_nama']) ?></div>
          <div style="color:var(--slate-600)"><b>Nomor SPT:</b> <?= e($spt['no_spt'] ?? 'SPT/....../INSP/2026') ?> &bull; <b>Tanggal SPT:</b> <?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '-' ?></div>
          <div style="color:#0369a1;font-weight:600"><b>Ketua Tim:</b> <?= e($spt['ketua_tim_nama'] ?? '-') ?> &bull; <b>Dalnis:</b> <?= e($spt['dalnis_nama'] ?? '-') ?> &bull; <b>Irban:</b> <?= e($spt['wakil_pj_nama'] ?? 'MARWAN, M.T') ?></div>
        </div>
      </div>
      <div>
        <?php $stLhp = $narasi['status_lhp'] ?? 'DRAFT'; ?>
        <?php if ($stLhp === 'DISAHKAN_INSPEKTUR'): ?>
          <span class="badge" style="background:#dcfce7;color:#15803d;font-size:12.5px;font-weight:700;padding:6px 12px;border:1px solid #86efac">
            <i class="fa-solid fa-stamp"></i> Telah Disahkan Inspektur Daerah
          </span>
          <div style="font-size:11px;color:#166534;margin-top:4px;text-align:right">
            Disahkan: <?= !empty($narasi['tgl_disahkan_inspektur']) ? date('d/m/Y H:i', strtotime($narasi['tgl_disahkan_inspektur'])) : '' ?> WIB
          </div>
        <?php elseif ($stLhp === 'TELAAH_IRBAN'): ?>
          <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:12.5px;font-weight:700;padding:6px 12px;border:1px solid #bfdbfe">
            <i class="fa-solid fa-user-tie"></i> Tahap Telaah Inspektur Pembantu (Irban)
          </span>
        <?php elseif ($stLhp === 'REVIU_DALNIS'): ?>
          <span class="badge" style="background:#fef3c7;color:#92400e;font-size:12.5px;font-weight:700;padding:6px 12px;border:1px solid #fde68a">
            <i class="fa-solid fa-glasses"></i> Tahap Reviu Pengendali Teknis (Dalnis)
          </span>
        <?php else: ?>
          <span class="badge" style="background:#f1f5f9;color:#475569;font-size:12.5px;font-weight:700;padding:6px 12px">
            <i class="fa-solid fa-pen"></i> Draf Konsep Naskah Tim
          </span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Stepper Alur Pengawasan Berjenjang (Simondes) -->
    <div class="card" style="padding:16px 20px;margin-bottom:24px;background:#f8fafc">
      <div style="font-size:12px;font-weight:700;color:var(--slate-500);text-transform:uppercase;margin-bottom:12px;letter-spacing:0.5px">
        <i class="fa-solid fa-route"></i> Alur Estafet Kendali Mutu LHA / LHP (Simondes)
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px">
        
        <!-- Step 1: Tim Pemeriksa -->
        <div style="padding:10px 12px;border-radius:8px;background:#fff;border:1px solid #cbd5e1;border-left:4px solid #059669">
          <div style="font-size:11px;color:#059669;font-weight:700">1. KETUA TIM</div>
          <div style="font-size:12px;font-weight:600;margin-top:2px;color:var(--slate-800)">Penyusunan KKA &amp; Temuan</div>
          <div style="font-size:11px;color:var(--slate-500);margin-top:2px">✓ Telah disusun</div>
        </div>

        <!-- Step 2: Dalnis -->
        <div style="padding:10px 12px;border-radius:8px;background:#fff;border:1px solid #cbd5e1;border-left:4px solid <?= in_array($stLhp, ['REVIU_DALNIS','TELAAH_IRBAN','DISAHKAN_INSPEKTUR']) ? '#d97706' : '#cbd5e1' ?>">
          <div style="font-size:11px;color:#d97706;font-weight:700">2. DALNIS</div>
          <div style="font-size:12px;font-weight:600;margin-top:2px;color:var(--slate-800)">Koreksi Teknis (Kotak A)</div>
          <div style="font-size:11px;color:var(--slate-500);margin-top:2px">
            <?= !empty($narasi['tgl_reviu_dalnis']) ? '✓ Selesai reviu (' . date('d/m/y', strtotime($narasi['tgl_reviu_dalnis'])) . ')' : 'Menunggu reviu' ?>
          </div>
        </div>

        <!-- Step 3: Irban -->
        <div style="padding:10px 12px;border-radius:8px;background:#fff;border:1px solid #cbd5e1;border-left:4px solid <?= in_array($stLhp, ['TELAAH_IRBAN','DISAHKAN_INSPEKTUR']) ? '#2563eb' : '#cbd5e1' ?>">
          <div style="font-size:11px;color:#2563eb;font-weight:700">3. IRBAN IV</div>
          <div style="font-size:12px;font-weight:600;margin-top:2px;color:var(--slate-800)">Telaah Strategis (Kotak B)</div>
          <div style="font-size:11px;color:var(--slate-500);margin-top:2px">
            <?= !empty($narasi['tgl_reviu_irban']) ? '✓ Selesai telaah (' . date('d/m/y', strtotime($narasi['tgl_reviu_irban'])) . ')' : 'Menunggu telaah' ?>
          </div>
        </div>

        <!-- Step 4: Inspektur -->
        <div style="padding:10px 12px;border-radius:8px;background:#fff;border:1px solid #cbd5e1;border-left:4px solid <?= ($stLhp === 'DISAHKAN_INSPEKTUR') ? '#16a34a' : '#cbd5e1' ?>">
          <div style="font-size:11px;color:#16a34a;font-weight:700">4. INSPEKTUR DAERAH</div>
          <div style="font-size:12px;font-weight:600;margin-top:2px;color:var(--slate-800)">Pengesahan LHP (Kotak C)</div>
          <div style="font-size:11px;color:var(--slate-500);margin-top:2px">
            <?= ($stLhp === 'DISAHKAN_INSPEKTUR') ? '✓ Sah (' . date('d/m/y', strtotime($narasi['tgl_disahkan_inspektur'])) . ')' : 'Menunggu pengesahan' ?>
          </div>
        </div>

      </div>
    </div>

    <!-- FORM KELOLA REVIEW SHEET -->
    <form method="POST" action="<?= url('routing-slip/update') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="desa_id" value="<?= $desa['id'] ?>">
      <input type="hidden" name="tahun_anggaran" value="<?= $tahun ?>">

      <!-- KOTAK A: CATATAN KOREKSI DALNIS -->
      <div class="card" style="padding:22px;margin-bottom:20px;border-left:5px solid #d97706">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-glasses" style="color:#d97706"></i>
              A. Catatan &amp; Arahan Koreksi Pengendali Teknis (Dalnis)
            </h3>
            <p style="margin:3px 0 0;font-size:12px;color:var(--slate-500)">
              Catatan evaluasi atas kepatuhan bukti belanja, kelayakan rekomendasi temuan 5 unsur, dan pengujian fisik di lapangan.
            </p>
          </div>
          <?php if (!empty($narasi['tgl_reviu_dalnis'])): ?>
            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:11.5px;font-weight:700">
              <i class="fa-solid fa-check"></i> Diparaf: <?= date('d/m/Y H:i', strtotime($narasi['tgl_reviu_dalnis'])) ?> WIB
            </span>
          <?php endif; ?>
        </div>

        <div style="margin-bottom:12px">
          <label class="form-label" style="font-weight:700;font-size:12.5px">
            Uraian Catatan Reviu Dalnis :
          </label>
          <textarea name="catatan_dalnis" class="form-control" rows="4" style="line-height:1.6;font-size:13px" placeholder="Contoh: Periksa kelengkapan kuitansi belanja tahap 2 dan pastikan bukti setor PPh/PPN telah dilampirkan sebelum naskah dimajukan ke Irban."><?= e($narasi['catatan_dalnis'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;background:#fffbeb;padding:10px 14px;border-radius:6px;font-size:12px;color:#92400e">
          <div>
            <b>Pejabat Pengendali Teknis:</b> <?= e(($spt['dalnis_nama'] ?? '') ?: ($narasi['dalnis_nama'] ?? 'Auditor Ahli Madya')) ?>
          </div>
          <?php if ($auth->isDalnis() || $auth->isAdmin()): ?>
            <button type="submit" class="btn btn-sm" style="background:#d97706;color:#fff;font-weight:700">
              <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan Dalnis
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- KOTAK B: CATATAN ARAHAN IRBAN -->
      <div class="card" style="padding:22px;margin-bottom:20px;border-left:5px solid #2563eb">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-user-tie" style="color:#2563eb"></i>
              B. Catatan &amp; Arahan Inspektur Pembantu (Irban IV / Wakil Penanggung Jawab)
            </h3>
            <p style="margin:3px 0 0;font-size:12px;color:var(--slate-500)">
              Telaah mutu komprehensif, kepatuhan hukum, dan rekomendasi strategis sebelum laporan diajukan kepada Inspektur Daerah.
            </p>
          </div>
          <?php if (!empty($narasi['tgl_reviu_irban'])): ?>
            <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:11.5px;font-weight:700">
              <i class="fa-solid fa-check"></i> Diparaf: <?= date('d/m/Y H:i', strtotime($narasi['tgl_reviu_irban'])) ?> WIB
            </span>
          <?php endif; ?>
        </div>

        <div style="margin-bottom:12px">
          <label class="form-label" style="font-weight:700;font-size:12.5px">
            Uraian Arahan Telaah Irban :
          </label>
          <textarea name="catatan_irban" class="form-control" rows="4" style="line-height:1.6;font-size:13px" placeholder="Contoh: Konsep naskah LHA dan rekomendasi temuan telah diteliti secara seksama dan memenuhi standar naskah dinas pengawasan APIP. Siap dimajukan ke meja Inspektur Daerah untuk pengesahan."><?= e($narasi['catatan_irban'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;background:#eff6ff;padding:10px 14px;border-radius:6px;font-size:12px;color:#1e40af">
          <div>
            <b>Inspektur Pembantu IV:</b> <?= e(($spt['wakil_pj_nama'] ?? '') ?: ($narasi['irban_nama'] ?? 'MARWAN, M.T')) ?>
          </div>
          <?php if ($auth->isIrban() || $auth->isAdmin()): ?>
            <button type="submit" class="btn btn-sm" style="background:#2563eb;color:#fff;font-weight:700">
              <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan Irban
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- KOTAK C: DISPOSISI & PENGESAHAN INSPEKTUR DAERAH -->
      <div class="card" style="padding:22px;margin-bottom:20px;border-left:5px solid #16a34a;background:<?= ($stLhp === 'DISAHKAN_INSPEKTUR') ? '#f0fdf4' : '#fff' ?>">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-shield-halved" style="color:#16a34a"></i>
              C. Arahan &amp; Disposisi Inspektur Daerah (Penanggung Jawab)
            </h3>
            <p style="margin:3px 0 0;font-size:12px;color:var(--slate-500)">
              Pernyataan resmi pengesahan naskah laporan hasil pengawasan dan perintah penyerahan kepada auditi serta pelaporan ke Bupati.
            </p>
          </div>
          <?php if ($stLhp === 'DISAHKAN_INSPEKTUR'): ?>
            <span class="badge" style="background:#dcfce7;color:#15803d;font-size:12px;font-weight:700;padding:4px 10px;border:1px solid #86efac">
              <i class="fa-solid fa-circle-check"></i> Sah &amp; Diterbitkan Resmi
            </span>
          <?php endif; ?>
        </div>

        <div style="background:#fff;border:1px solid #cbd5e1;padding:14px;border-radius:6px;margin-bottom:12px;font-size:13px;line-height:1.6;color:var(--slate-800)">
          <?php if ($stLhp === 'DISAHKAN_INSPEKTUR'): ?>
            <div style="font-weight:600;color:#15803d">
              <i class="fa-solid fa-quote-left" style="color:#86efac;margin-right:6px"></i>
              Disetujui untuk diterbitkan LHA / LHP resmi dan diserahkan kepada pihak auditi serta diarsipkan ke SIM HP &amp; Google Drive.
            </div>
            <div style="font-size:11.5px;color:#166534;margin-top:6px">
              Disahkan pada: <b><?= !empty($narasi['tgl_disahkan_inspektur']) ? tgl_id($narasi['tgl_disahkan_inspektur']) : '' ?></b> oleh <b><?= e($narasi['disahkan_oleh_nama'] ?? $inspektur['nama']) ?></b>
            </div>
          <?php else: ?>
            <div style="color:var(--slate-500);font-style:italic">
              Naskah LHP saat ini sedang dalam proses kendali mutu berjenjang. Pengesahan akhir dilakukan oleh Inspektur Daerah melalui tombol pengesahan di Pratinjau LHP.
            </div>
          <?php endif; ?>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#166534">
          <div>
            <b>Inspektur Daerah Kabupaten Rokan Hilir:</b> <?= e($inspektur['nama']) ?>
          </div>
          <?php if (($auth->isInspektur() || $auth->isAdmin()) && $stLhp !== 'DISAHKAN_INSPEKTUR'): ?>
            <a href="<?= url('lhp/show?desa_id=' . $desa['id'] . '&tahun=' . $tahun) ?>" class="btn btn-sm" style="background:#059669;color:#fff;font-weight:700">
              <i class="fa-solid fa-stamp"></i> Buka Naskah &amp; Sahkan LHP
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tombol Aksi Bawah -->
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
        <a href="<?= url('routing-slip?tahun=' . $tahun) ?>" class="btn btn-outline">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Register
        </a>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn btn-primary" style="font-weight:700">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan Reviu
          </button>
          <a href="<?= url('print/routing-slip?desa_id=' . $desa['id'] . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-primary" style="background:#d97706;border-color:#d97706;font-weight:700">
            <i class="fa-solid fa-print"></i> Cetak Routing Slip Resmi F4
          </a>
        </div>
      </div>

    </form>
  </div>
</main>

<?php partial('foot'); ?>
