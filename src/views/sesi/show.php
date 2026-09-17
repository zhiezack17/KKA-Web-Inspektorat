<?php $title = 'Detail Sesi - ' . $sesi['objek_audit'] . ' - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-sesi-show">
  <div class="topbar">
    <div class="crumb">
      <i class="fa-solid fa-clipboard-list"></i>
      <a href="<?= url('sesi') ?>" style="color:var(--slate-500)">Sesi Audit</a> /
      <b><?= e(mb_strimwidth($sesi['objek_audit'],0,42,'…')) ?></b>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="<?= url('sesi') ?>" class="btn btn-ghost btn-sm" data-testid="btn-back-sesi"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
      <a href="<?= url('print/routing-slip?id='.$sesi['id']) ?>" class="btn btn-outline btn-sm" target="_blank" style="border-color:#f59e0b;color:#b45309;background:#fffbeb;font-weight:700" data-testid="btn-print-routing-slip"><i class="fa-solid fa-folder-open"></i> Routing Slip LHA</a>
      <button type="button" class="btn btn-outline btn-sm" id="btnBukaRoutingSlip" style="border-color:#d97706;color:#b45309;background:#fff;font-weight:600" data-testid="btn-edit-routing-slip"><i class="fa-solid fa-pen-to-square"></i> Kelola Routing Slip</button>
      <a href="<?= url('print/reviu?id='.$sesi['id']) ?>" class="btn btn-outline btn-sm" target="_blank" style="border-color:#6366f1;color:#4f46e5;font-weight:600" data-testid="btn-print-reviu"><i class="fa-solid fa-clipboard-check"></i> Lembar Reviu KKA</a>
      <a href="<?= url('print/sesi?id='.$sesi['id']) ?>" class="btn btn-outline btn-sm" target="_blank" data-testid="btn-print"><i class="fa-solid fa-print"></i> Cetak / Preview</a>
      <a href="<?= url('export/sesi?id='.$sesi['id']) ?>" class="btn btn-accent btn-sm" data-testid="btn-export"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <?php
      $currUid   = (int) $auth->id();
      $currUser  = $auth->user();
      $currNama  = trim($currUser['nama'] ?? '');
      $currNip   = preg_replace('/\s+/', '', $currUser['nip'] ?? '');
      $isAdmin   = $auth->isAdmin();
      $statusKka = $sesi['status'] ?? 'DRAFT';

      // Cek apakah user adalah Ketua Tim (berdasarkan ID, nama, NIP, atau peran)
      $matchKetuaName = ($currNama !== '' && !empty($sesi['direview_oleh']) && (
          stripos($sesi['direview_oleh'], $currNama) !== false ||
          stripos($currNama, trim($sesi['direview_oleh'])) !== false ||
          (!empty($currNip) && strpos(preg_replace('/\s+/', '', $sesi['direview_oleh']), $currNip) !== false)
      ));
      $isKetua   = $isAdmin 
          || ((int)($sesi['ketua_tim_id'] ?? 0) === $currUid) 
          || $matchKetuaName 
          || (empty($sesi['ketua_tim_id']) && $auth->isKetua());

      // Cek apakah user adalah Dalnis (berdasarkan ID, nama, NIP, atau peran)
      $matchDalnisName = ($currNama !== '' && !empty($sesi['dievaluasi_oleh']) && (
          stripos($sesi['dievaluasi_oleh'], $currNama) !== false ||
          stripos($currNama, trim($sesi['dievaluasi_oleh'])) !== false ||
          (!empty($currNip) && strpos(preg_replace('/\s+/', '', $sesi['dievaluasi_oleh']), $currNip) !== false)
      ));
      $isDalnis  = $isAdmin 
          || ((int)($sesi['dalnis_id'] ?? 0) === $currUid) 
          || $matchDalnisName 
          || (empty($sesi['dalnis_id']) && $auth->isDalnis());

      // Hak mengajukan KKA ke Ketua Tim:
      // Hanya Penyusun/Auditor pembuat KKA atau Admin.
      // Jika akun yang login adalah Ketua Tim pada sesi ini (dan bukan pembuatnya),
      // maka dia tidak melihat tombol ajukan ke dirinya sendiri.
      $isPenyusun = ($isAdmin || ((int)($sesi['created_by'] ?? 0) === $currUid) || ($currNama !== '' && stripos($sesi['dibuat_oleh'] ?? '', $currNama) !== false));
      $canAjukan  = $isAdmin || ($isPenyusun && !$isKetua) || ($isPenyusun && (int)($sesi['created_by'] ?? 0) === $currUid);
    ?>

    <div class="page-head">
      <div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
          <h2 style="margin:0"><?= e($sesi['objek_audit']) ?></h2>
          <?= kka_status_badge($statusKka) ?>
        </div>
        <p style="margin-top:6px"><?= e($sesi['desa_nama']) ?> · Kec. <?= e($sesi['kecamatan_nama']) ?> · Semester <?= (int)$sesi['semester'] ?> / <?= (int)$sesi['tahun_anggaran'] ?></p>
      </div>
      <a href="<?= url('sesi/edit?id='.$sesi['id']) ?>" class="btn btn-outline" data-testid="btn-edit-sesi"><i class="fa-solid fa-pen"></i> Edit Identitas</a>
    </div>

    <!-- NOTIFIKASI CATATAN REVISI / REVIU -->
    <?php if ($statusKka === 'PERLU_REVISI' || !empty($sesi['catatan_reviu_dalnis']) || !empty($sesi['catatan_reviu_ketua'])): ?>
    <div class="card" style="background:#fff1f2;border:1px solid #fecdd3;border-left:5px solid #e11d48;margin-bottom:18px">
      <div style="display:flex;align-items:flex-start;gap:12px">
        <i class="fa-solid fa-triangle-exclamation" style="color:#e11d48;font-size:22px;margin-top:2px"></i>
        <div style="flex:1">
          <h4 style="margin:0 0 6px;color:#9f1239;font-size:15px;font-weight:700">
            <?= $statusKka === 'PERLU_REVISI' ? 'Perhatian: KKA Memerlukan Perbaikan / Revisi' : 'Catatan Hasil Reviu Berjenjang' ?>
          </h4>
          <?php if (!empty($sesi['catatan_reviu_dalnis'])): ?>
            <div style="background:#fff;border:1px solid #fecdd3;border-radius:8px;padding:10px 14px;margin-bottom:8px">
              <strong style="color:#9f1239;font-size:13px;display:flex;align-items:center;gap:6px">
                <i class="fa-solid fa-user-shield" style="color:#e11d48"></i> Catatan Pengendali Teknis (Dalnis) — <?= e($sesi['dalnis_nama'] ?: ($sesi['dievaluasi_oleh'] ?: 'Dalnis')) ?>
                <?php if (!empty($sesi['tgl_reviu_dalnis'])): ?><small style="color:var(--slate-500);font-weight:400">(<?= tgl_id($sesi['tgl_reviu_dalnis']) ?>)</small><?php endif; ?>
              </strong>
              <div style="color:#334155;font-size:13px;margin-top:6px;white-space:pre-wrap;line-height:1.5"><?= e($sesi['catatan_reviu_dalnis']) ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($sesi['catatan_reviu_ketua'])): ?>
            <div style="background:#fff;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px;margin-bottom:8px">
              <strong style="color:#9a3412;font-size:13px;display:flex;align-items:center;gap:6px">
                <i class="fa-solid fa-user-check" style="color:#d97706"></i> Catatan Ketua Tim — <?= e($sesi['ketua_nama'] ?: ($sesi['direview_oleh'] ?: 'Ketua Tim')) ?>
                <?php if (!empty($sesi['tgl_reviu_ketua'])): ?><small style="color:var(--slate-500);font-weight:400">(<?= tgl_id($sesi['tgl_reviu_ketua']) ?>)</small><?php endif; ?>
              </strong>
              <div style="color:#334155;font-size:13px;margin-top:6px;white-space:pre-wrap;line-height:1.5"><?= e($sesi['catatan_reviu_ketua']) ?></div>
            </div>
          <?php endif; ?>
          <?php if ($statusKka === 'PERLU_REVISI'): ?>
            <p style="margin:6px 0 0;font-size:12px;color:#be123c;font-style:italic">
              Silakan lakukan perbaikan pada tabel rincian belanja atau dokumen lampiran di bawah, kemudian klik <b>"Ajukan ke Ketua Tim"</b> kembali.
            </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- KARTU KENDALI VERIFIKASI & REVIU BERJENJANG -->
    <div class="card" style="margin-bottom:18px;border-top:4px solid #4f46e5;background:linear-gradient(180deg,#fafafa,#ffffff)">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
        <h3 style="margin:0;font-size:15px;font-weight:800;color:var(--slate-700);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-diagram-project" style="color:#4f46e5"></i>
          Alur Verifikasi &amp; Reviu Berjenjang (Quality Assurance)
        </h3>
        <a href="<?= url('print/reviu?id='.$sesi['id']) ?>" target="_blank" class="btn btn-outline btn-sm" style="border-color:#c7d2fe;color:#4338ca">
          <i class="fa-solid fa-print"></i> Cetak Lembar Reviu
        </a>
      </div>

      <!-- TIMELINE STEPPER -->
      <div class="timeline-stepper" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px">
        <!-- STEP 1: AUDITOR -->
        <?php
          $s1Done = in_array($statusKka, ['REVIEW_KETUA', 'REVIEW_DALNIS', 'SELESAI_FINAL']);
          $s1Active = in_array($statusKka, ['DRAFT', 'PERLU_REVISI']);
        ?>
        <div style="padding:12px;border-radius:10px;border:1px solid <?= $s1Active ? '#93c5fd' : ($s1Done ? '#bbf7d0' : '#e2e8f0') ?>;background:<?= $s1Active ? '#eff6ff' : ($s1Done ? '#f0fdf4' : '#f8fafc') ?>">
          <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:13px;color:<?= $s1Active ? '#1d4ed8' : ($s1Done ? '#15803d' : '#64748b') ?>">
            <i class="fa-solid <?= $s1Done ? 'fa-circle-check' : ($s1Active ? 'fa-circle-dot' : 'fa-circle') ?>"></i>
            1. Penyusunan (Auditor)
          </div>
          <div style="font-size:12px;color:var(--slate-600);margin-top:6px">
            <div><b>Penyusun:</b> <?= e($sesi['dibuat_oleh'] ?: 'Auditor') ?></div>
            <div><b>Tgl:</b> <?= tgl_id($sesi['tanggal_dibuat']) ?></div>
          </div>
        </div>

        <!-- STEP 2: KETUA TIM -->
        <?php
          $s2Done = in_array($statusKka, ['REVIEW_DALNIS', 'SELESAI_FINAL']);
          $s2Active = ($statusKka === 'REVIEW_KETUA');
        ?>
        <div style="padding:12px;border-radius:10px;border:1px solid <?= $s2Active ? '#fde68a' : ($s2Done ? '#bbf7d0' : '#e2e8f0') ?>;background:<?= $s2Active ? '#fffbeb' : ($s2Done ? '#f0fdf4' : '#f8fafc') ?>">
          <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:13px;color:<?= $s2Active ? '#b45309' : ($s2Done ? '#15803d' : '#64748b') ?>">
            <i class="fa-solid <?= $s2Done ? 'fa-circle-check' : ($s2Active ? 'fa-clock' : 'fa-circle') ?>"></i>
            2. Reviu Ketua Tim
          </div>
          <div style="font-size:12px;color:var(--slate-600);margin-top:6px">
            <div><b>Ketua:</b> <?= e($sesi['ketua_nama'] ?: ($sesi['direview_oleh'] ?: 'Belum ditunjuk')) ?></div>
            <div><b>Tgl:</b> <?= !empty($sesi['tgl_reviu_ketua']) ? tgl_id($sesi['tgl_reviu_ketua']) : ($s2Active ? '<span style="color:#d97706;font-weight:600">Sedang Reviu</span>' : '-') ?></div>
          </div>
        </div>

        <!-- STEP 3: DALNIS -->
        <?php
          $s3Done = ($statusKka === 'SELESAI_FINAL');
          $s3Active = ($statusKka === 'REVIEW_DALNIS');
        ?>
        <div style="padding:12px;border-radius:10px;border:1px solid <?= $s3Active ? '#c7d2fe' : ($s3Done ? '#bbf7d0' : '#e2e8f0') ?>;background:<?= $s3Active ? '#eef2ff' : ($s3Done ? '#f0fdf4' : '#f8fafc') ?>">
          <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:13px;color:<?= $s3Active ? '#4338ca' : ($s3Done ? '#15803d' : '#64748b') ?>">
            <i class="fa-solid <?= $s3Done ? 'fa-circle-check' : ($s3Active ? 'fa-clock' : 'fa-circle') ?>"></i>
            3. Pengesahan Dalnis
          </div>
          <div style="font-size:12px;color:var(--slate-600);margin-top:6px">
            <div><b>Dalnis:</b> <?= e($sesi['dalnis_nama'] ?: ($sesi['dievaluasi_oleh'] ?: 'Belum ditunjuk')) ?></div>
            <div><b>Tgl:</b> <?= !empty($sesi['tgl_reviu_dalnis']) ? tgl_id($sesi['tgl_reviu_dalnis']) : ($s3Active ? '<span style="color:#4f46e5;font-weight:600">Menunggu Sah</span>' : '-') ?></div>
          </div>
        </div>

        <!-- STEP 4: SELESAI FINAL -->
        <?php $s4Done = ($statusKka === 'SELESAI_FINAL'); ?>
        <div style="padding:12px;border-radius:10px;border:1px solid <?= $s4Done ? '#86efac' : '#e2e8f0' ?>;background:<?= $s4Done ? '#dcfce7' : '#f8fafc' ?>">
          <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:13px;color:<?= $s4Done ? '#15803d' : '#64748b' ?>">
            <i class="fa-solid <?= $s4Done ? 'fa-shield-halved' : 'fa-circle' ?>"></i>
            4. KKA Sah / Final
          </div>
          <div style="font-size:12px;color:var(--slate-600);margin-top:6px">
            <div><b>Status:</b> <?= $s4Done ? '<span style="color:#15803d;font-weight:700">Sah &amp; Siap LHP</span>' : 'Belum Sah' ?></div>
            <div><b>Output:</b> Lembar Reviu &amp; KKA</div>
          </div>
        </div>
      </div>

      <!-- KOTAK AKSI PENGGUNA BERDASARKAN STATUS & ROLE -->
      <div style="padding:14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
        <?php if ($statusKka === 'DRAFT' || $statusKka === 'PERLU_REVISI'): ?>
          <?php if ($canAjukan): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
              <div>
                <strong style="color:var(--slate-800);font-size:13px;display:block">Tahap Penyusunan KKA oleh Tim Pemeriksa / Auditor</strong>
                <span style="font-size:12px;color:var(--slate-500)">Setelah rincian belanja dan lampiran lengkap, ajukan ke Ketua Tim untuk mulai proses reviu mutu.</span>
              </div>
              <form method="post" action="<?= url('sesi/ajukan') ?>" onsubmit="return confirm('Ajukan KKA ini ke Ketua Tim untuk diverifikasi?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$sesi['id'] ?>">
                <button type="submit" class="btn btn-primary" data-testid="btn-ajukan-reviu">
                  <i class="fa-solid fa-paper-plane"></i> Ajukan ke Ketua Tim
                </button>
              </form>
            </div>
          <?php else: ?>
            <div style="display:flex;align-items:center;gap:12px;color:var(--slate-700)">
              <div style="width:36px;height:36px;border-radius:50%;background:#e0f2fe;display:flex;align-items:center;justify-content:center;color:#0284c7;font-size:16px;flex-shrink:0">
                <i class="fa-solid fa-pen-ruler"></i>
              </div>
              <div>
                <strong style="font-size:13.5px;color:var(--slate-800)">Tahap Penyusunan KKA oleh Tim Pemeriksa / Auditor</strong>
                <div style="font-size:12px;color:var(--slate-600);margin-top:2px">
                  KKA saat ini sedang disusun oleh <b><?= e($sesi['dibuat_oleh'] ?: 'Auditor') ?></b>. Tombol aksi reviu akan otomatis aktif untuk Anda setelah KKA diajukan ke Ketua Tim.
                </div>
              </div>
            </div>
          <?php endif; ?>

        <?php elseif ($statusKka === 'REVIEW_KETUA'): ?>
          <?php if ($isKetua): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
              <div>
                <strong style="color:#b45309;font-size:14px;display:flex;align-items:center;gap:6px">
                  <i class="fa-solid fa-user-check"></i> Hak Reviu: Anda bertindak sebagai Ketua Tim
                </strong>
                <span style="font-size:12px;color:var(--slate-600)">Periksa kecukupan bukti dan perhitungan belanja KKA sebelum diteruskan ke Dalnis.</span>
              </div>
              <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="button" class="btn btn-outline" style="color:#dc2626;border-color:#fca5a5" id="btnBukaRevisiKetua">
                  <i class="fa-solid fa-rotate-left"></i> Kembalikan (Perlu Revisi)
                </button>
                <button type="button" class="btn btn-primary" style="background:#059669;border-color:#059669" id="btnBukaSetujuKetua">
                  <i class="fa-solid fa-check"></i> Setujui &amp; Teruskan ke Dalnis
                </button>
              </div>
            </div>
          <?php else: ?>
            <div style="display:flex;align-items:center;gap:10px;color:#b45309">
              <i class="fa-solid fa-hourglass-half" style="font-size:20px"></i>
              <div>
                <strong>Sedang Menunggu Reviu Ketua Tim</strong>
                <div style="font-size:12px;color:var(--slate-600)">KKA sedang ditelaah oleh <b><?= e($sesi['ketua_nama'] ?: ($sesi['direview_oleh'] ?: 'Ketua Tim')) ?></b>. Tombol aksi reviu hanya aktif untuk akun Ketua Tim atau Admin.</div>
              </div>
            </div>
          <?php endif; ?>

        <?php elseif ($statusKka === 'REVIEW_DALNIS'): ?>
          <?php if ($isDalnis): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
              <div>
                <strong style="color:#4338ca;font-size:14px;display:flex;align-items:center;gap:6px">
                  <i class="fa-solid fa-user-shield"></i> Hak Pengesahan: Anda bertindak sebagai Pengendali Teknis (Dalnis)
                </strong>
                <span style="font-size:12px;color:var(--slate-600)">Ketua Tim telah menyetujui KKA ini. Silakan sahkan sebagai KKA Final atau kembalikan dengan arahan.</span>
              </div>
              <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button type="button" class="btn btn-outline" style="color:#dc2626;border-color:#fca5a5" id="btnBukaRevisiDalnis">
                  <i class="fa-solid fa-rotate-left"></i> Kembalikan (Perlu Revisi)
                </button>
                <button type="button" class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5" id="btnBukaSahkanDalnis">
                  <i class="fa-solid fa-stamp"></i> Sahkan KKA (Final)
                </button>
              </div>
            </div>
          <?php else: ?>
            <div style="display:flex;align-items:center;gap:10px;color:#4338ca">
              <i class="fa-solid fa-hourglass-half" style="font-size:20px"></i>
              <div>
                <strong>Sedang Menunggu Pengesahan Akhir dari Dalnis</strong>
                <div style="font-size:12px;color:var(--slate-600)">KKA telah disetujui Ketua Tim dan sedang dalam proses pengesahan oleh <b><?= e($sesi['dalnis_nama'] ?: ($sesi['dievaluasi_oleh'] ?: 'Pengendali Teknis')) ?></b>.</div>
              </div>
            </div>
          <?php endif; ?>

        <?php elseif ($statusKka === 'SELESAI_FINAL'): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:38px;height:38px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;color:#15803d;font-size:18px">
                <i class="fa-solid fa-check-double"></i>
              </div>
              <div>
                <strong style="color:#15803d;font-size:14px;display:block">KKA Telah Sah Disahkan oleh Pengendali Teknis (Dalnis)</strong>
                <span style="font-size:12px;color:var(--slate-600)">Seluruh proses reviu berjenjang selesai. Lembar reviu dan KKA ini siap dilampirkan dalam LHP resmi.</span>
              </div>
            </div>
            <div style="display:flex;gap:8px">
              <a href="<?= url('print/reviu?id='.$sesi['id']) ?>" target="_blank" class="btn btn-outline btn-sm" style="border-color:#6366f1;color:#4f46e5;font-weight:600">
                <i class="fa-solid fa-clipboard-check"></i> Cetak Lembar Reviu
              </a>
              <a href="<?= url('print/sesi?id='.$sesi['id']) ?>" target="_blank" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-print"></i> Cetak KKA Lengkap
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
      <div class="card">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px">Identitas KKA</h3>
        <dl class="kv">
          <dt>No. KKA</dt>     <dd><?= e($sesi['no_kka'] ?: '-') ?></dd>
          <dt>Ref. PKA</dt>    <dd><?= e($sesi['ref_kka'] ?: '-') ?></dd>
          <dt>Bidang</dt>      <dd><?= e($sesi['bidang_nama']) ?></dd>
          <dt>Sub Bidang</dt>  <dd><?= e($sesi['sub_bidang_nama'] ?: '-') ?></dd>
          <dt>Kegiatan</dt>    <dd><?= e($sesi['kegiatan'] ?: '-') ?></dd>
          <dt>Pagu Anggaran</dt><dd style="font-weight:700;color:var(--emerald-700)" data-testid="sesi-pagu"><?= rupiah($sesi['pagu_anggaran']) ?></dd>
        </dl>
      </div>
      <div class="card">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px">Tanda Tangan &amp; Tim Audit</h3>
        <dl class="kv">
          <?php 
            [$purePenyusun] = split_nama_nip($sesi['dibuat_oleh'] ?? '');
            [$pureKetua]    = split_nama_nip($sesi['ketua_nama'] ?: ($sesi['direview_oleh'] ?? ''));
            [$pureDalnis]   = split_nama_nip($sesi['dalnis_nama'] ?: ($sesi['dievaluasi_oleh'] ?? ''));
            [$pureIrban]    = split_nama_nip($sesi['irban_pejabat_nama'] ?: ($sesi['irban_nama'] ?? ''));
          ?>
          <dt>Penyusun (Auditor)</dt> <dd><?= !empty($purePenyusun) ? e($purePenyusun) : '-' ?></dd>
          <dt>Tanggal Dibuat</dt> <dd><?= tgl_id($sesi['tanggal_dibuat']) ?></dd>
          <dt>Ketua Tim</dt> <dd><?= !empty($pureKetua) ? e($pureKetua) : '-' ?></dd>
          <dt>Tanggal Reviu</dt> <dd><?= !empty($sesi['tgl_reviu_ketua']) ? tgl_id($sesi['tgl_reviu_ketua']) : (!empty($sesi['tanggal_review']) ? tgl_id($sesi['tanggal_review']) : '-') ?></dd>
          <dt>Pengendali Teknis (Dalnis)</dt> <dd><?= !empty($pureDalnis) ? e($pureDalnis) : '-' ?></dd>
          <dt>Tanggal Disahkan</dt> <dd><?= !empty($sesi['tgl_reviu_dalnis']) ? tgl_id($sesi['tgl_reviu_dalnis']) : (!empty($sesi['tanggal_evaluasi']) ? tgl_id($sesi['tanggal_evaluasi']) : '-') ?></dd>
          <dt>Wakil Penanggung Jawab (Irban)</dt> <dd><?= !empty($pureIrban) ? e($pureIrban) : '-' ?><?php if (!empty($sesi['irban_jabatan'])): ?> <small style="color:var(--slate-500)">(<?= e($sesi['irban_jabatan']) ?>)</small><?php endif; ?></dd>
          <?php if (!empty($sesi['no_lha'])): ?>
            <dt>Nomor LHA</dt> <dd><b><?= e($sesi['no_lha']) ?></b></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <?php if (!empty($sharedWith)): ?>
    <div class="card" style="margin-bottom:18px">
      <h3 style="margin:0 0 10px;font-size:14px;font-weight:800;color:var(--slate-600);text-transform:uppercase;letter-spacing:1px"><i class="fa-solid fa-users"></i> Dibagikan Kepada</h3>
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($sharedWith as $sw): ?>
          <span style="background:#f1f5f9;border:1px solid var(--slate-200);padding:4px 12px;border-radius:999px;font-size:13px;font-weight:500"><i class="fa-solid fa-user" style="color:var(--slate-400)"></i> <?= e($sw['nama']) ?><?php if (!empty($sw['jabatan'])): ?> <small style="color:var(--slate-500)">— <?= e($sw['jabatan']) ?></small><?php endif; ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- RINCIAN -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin:20px 0 10px;flex-wrap:wrap;gap:10px">
      <div class="section-title" style="margin:0"><i class="fa-solid fa-list"></i> Rincian Belanja</div>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <a href="<?= url('rincian/template?sesi_id='.$sesi['id']) ?>" class="btn btn-outline btn-sm" title="Unduh template Excel/CSV untuk diisi secara offline di lapangan" style="border-color:#10b981;color:#047857;background:#f0fdf4">
          <i class="fa-solid fa-file-excel"></i> Unduh Template Excel
        </a>
        <?php if (!in_array($statusKka, ['REVIEW_KETUA', 'REVIEW_DALNIS', 'SELESAI_FINAL'])): ?>
          <button type="button" class="btn btn-primary btn-sm" id="btnBukaImportExcel" style="background:#059669;border-color:#059669" data-testid="btn-import-excel">
            <i class="fa-solid fa-file-import"></i> Impor dari Excel
          </button>
        <?php else: ?>
          <button type="button" class="btn btn-ghost btn-sm" style="color:var(--slate-400);cursor:not-allowed" title="Data rincian dikunci selama proses reviu / setelah selesai">
            <i class="fa-solid fa-lock"></i> Impor Dikunci
          </button>
        <?php endif; ?>
      </div>
    </div>
    <div class="card" style="padding:0">
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th>Uraian / Rincian Belanja</th>
              <th class="num">Pagu Anggaran</th>
              <th class="num">Realisasi</th>
              <th class="num">Biaya Dikwitansi</th>
              <th class="num">Selisih</th>
              <th style="text-align:center">Uji Pajak</th>
              <th>Penerima</th>
              <th>Keterangan</th>
              <th style="width:110px;text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody data-testid="tbody-rincian">
            <?php if (empty($rincian)): ?>
              <tr><td colspan="10" style="text-align:center;color:var(--slate-500);padding:36px">Belum ada rincian. Tambahkan di formulir bawah.</td></tr>
            <?php else: $no=1; $totPagu=0; foreach ($rincian as $r): 
              $sel = (float)$r['realisasi'] - (float)$r['biaya_dikwitansi']; 
              $totPagu += (float)$r['pagu_anggaran']; 
              
              // Badge Pajak
              $pajakHtml = '<span style="color:#94a3b8;font-size:11px">-</span>';
              $stPajak = $r['status_pajak'] ?? 'TIDAK_TERUTANG';
              $totNomPajak = (float)($r['nominal_ppn'] ?? 0) + (float)($r['nominal_pph'] ?? 0);
              if ($stPajak === 'BELUM_SETOR') {
                  $pajakHtml = '<span class="badge" style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;padding:2px 6px;border-radius:4px" title="Kewajiban pajak belum disetor">' .
                               '<i class="fa-solid fa-triangle-exclamation"></i> Belum Setor ' . ($totNomPajak > 0 ? rupiah($totNomPajak) : '') . '</span>';
              } elseif ($stPajak === 'SUDAH_SETOR') {
                  $pajakHtml = '<span class="badge" style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:2px 6px;border-radius:4px" title="NTPN: ' . e((string)($r['ntpn'] ?? '-')) . '">' .
                               '<i class="fa-solid fa-circle-check"></i> Setor (' . ($r['potong_pph'] ?: 'PPN') . ')</span>';
              }
            ?>
              <tr data-testid="rincian-row-<?= $r['id'] ?>"
                  data-rincian-id="<?= (int)$r['id'] ?>"
                  data-uraian="<?= e($r['uraian']) ?>"
                  data-pagu="<?= (float)$r['pagu_anggaran'] ?>"
                  data-kwi="<?= (float)$r['biaya_dikwitansi'] ?>"
                  data-real="<?= (float)$r['realisasi'] ?>"
                  data-penerima="<?= e((string)$r['penerima']) ?>"
                  data-keterangan="<?= e((string)$r['keterangan']) ?>"
                  data-potong-ppn="<?= (int)($r['potong_ppn'] ?? 0) ?>"
                  data-nominal-ppn="<?= (float)($r['nominal_ppn'] ?? 0) ?>"
                  data-potong-pph="<?= e((string)($r['potong_pph'] ?? '')) ?>"
                  data-nominal-pph="<?= (float)($r['nominal_pph'] ?? 0) ?>"
                  data-status-pajak="<?= e((string)($r['status_pajak'] ?? 'TIDAK_TERUTANG')) ?>"
                  data-ntpn="<?= e((string)($r['ntpn'] ?? '')) ?>">
                <td><?= $no++ ?></td>
                <td>
                  <strong><?= e($r['uraian']) ?></strong>
                  <?php if (!empty($r['potong_pph']) || !empty($r['potong_ppn'])): ?>
                    <div style="font-size:11px;color:var(--slate-500)">
                      <?= !empty($r['potong_ppn']) ? 'PPN: ' . rupiah($r['nominal_ppn']) : '' ?>
                      <?= !empty($r['potong_pph']) ? ' &bull; ' . e($r['potong_pph']) . ': ' . rupiah($r['nominal_pph']) : '' ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="num"><?= rupiah($r['pagu_anggaran']) ?></td>
                <td class="num"><?= rupiah($r['realisasi']) ?></td>
                <td class="num"><?= rupiah($r['biaya_dikwitansi']) ?></td>
                <td class="num" style="color:<?= $sel<0?'var(--red-600)':'var(--emerald-700)' ?>;font-weight:700"><?= rupiah($sel) ?></td>
                <td style="text-align:center"><?= $pajakHtml ?></td>
                <td><?= e($r['penerima'] ?: '-') ?></td>
                <td><?= e($r['keterangan'] ?: '-') ?></td>
                <td style="white-space:nowrap;text-align:center">
                  <a href="<?= url('temuan/create?sesi_id=' . $sesi['id'] . '&rincian_id=' . $r['id']) ?>" class="btn btn-ghost btn-sm" style="color:#d97706;padding:5px 7px" title="Jadikan Konsep Temuan (KTP)"><i class="fa-solid fa-file-circle-exclamation"></i></a>
                  <button type="button" class="btn btn-ghost btn-sm js-edit-rincian" style="color:var(--emerald-700);padding:5px 7px" title="Edit" data-testid="edit-rincian-<?= $r['id'] ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                  <form method="post" action="<?= url('rincian/delete') ?>" onsubmit="return confirm('Hapus rincian ini?')" style="display:inline">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
                    <button class="btn btn-ghost btn-sm" style="color:var(--red-600);padding:5px 7px" title="Hapus" data-testid="del-rincian-<?= $r['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if (!empty($rincian)): $selTot = (float)$totals['realisasi']-(float)$totals['dikwitansi']; ?>
          <tfoot>
            <tr>
              <td colspan="2">JUMLAH <span style="font-weight:500;color:var(--slate-500);font-size:12px">(Pagu Sesi: <?= rupiah($sesi['pagu_anggaran']) ?>)</span></td>
              <td class="num"><?= rupiah($totPagu ?? 0) ?></td>
              <td class="num"><?= rupiah($totals['realisasi']) ?></td>
              <td class="num"><?= rupiah($totals['dikwitansi']) ?></td>
              <td class="num" style="color:<?= $selTot<0?'var(--red-600)':'var(--emerald-700)' ?>"><?= rupiah($selTot) ?></td>
              <td colspan="4"></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>

      <!-- Form tambah rincian dengan Uji Pajak -->
      <form method="post" action="<?= url('rincian/store') ?>" style="padding:16px 18px;border-top:1px solid var(--slate-200);background:var(--slate-50)" data-testid="form-rincian">
        <?= csrf_field() ?>
        <input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
        
        <div style="display:grid;grid-template-columns:2.5fr 1fr 1fr 1fr 1.2fr auto;gap:8px;align-items:end;margin-bottom:10px">
          <div class="field" style="margin:0"><label>Uraian Belanja <span class="req">*</span></label><input type="text" name="uraian" required class="input" placeholder="cth: Pembayaran honor / belanja semen" data-testid="r-uraian"></div>
          <div class="field" style="margin:0"><label>Pagu (Rp)</label><input type="text" name="pagu_anggaran" class="input" data-money placeholder="0" data-testid="r-pagu"></div>
          <div class="field" style="margin:0"><label>Realisasi (Rp)</label><input type="text" name="realisasi" class="input" data-money placeholder="0" data-testid="r-realisasi"></div>
          <div class="field" style="margin:0"><label>Kuitansi (Rp)</label><input type="text" name="biaya_dikwitansi" class="input" data-money placeholder="0"></div>
          <div class="field" style="margin:0"><label>Penerima</label><input type="text" name="penerima" class="input" placeholder="Nama penerima"></div>
          <button class="btn btn-primary" type="submit" data-testid="btn-tambah-rincian"><i class="fa-solid fa-plus"></i> Tambah</button>
        </div>

        <!-- Baris Uji Kepatuhan Pajak -->
        <div style="display:grid;grid-template-columns:1.2fr 1fr 1.2fr 1fr 1.2fr 1.2fr 1.5fr;gap:8px;align-items:end;background:#f1f5f9;padding:8px 12px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px">
          <div class="field" style="margin:0">
            <label style="font-size:11px;font-weight:700;color:var(--slate-700)">Status Setor Pajak</label>
            <select name="status_pajak" class="input" style="font-size:12px;padding:4px 8px">
              <option value="TIDAK_TERUTANG">Tidak Terutang</option>
              <option value="BELUM_SETOR">⚠️ Belum Disetor</option>
              <option value="SUDAH_SETOR">✓ Sudah Disetor</option>
            </select>
          </div>
          <div class="field" style="margin:0">
            <label style="font-size:11px;font-weight:700;color:var(--slate-700)">Potong PPN?</label>
            <label style="display:flex;align-items:center;gap:4px;height:34px;font-size:12px;cursor:pointer">
              <input type="checkbox" name="potong_ppn" value="1"> Ya (11%)
            </label>
          </div>
          <div class="field" style="margin:0">
            <label style="font-size:11px;font-weight:700;color:var(--slate-700)">Nominal PPN (Rp)</label>
            <input type="text" name="nominal_ppn" class="input" data-money placeholder="0" style="font-size:12px;padding:4px 8px">
          </div>
          <div class="field" style="margin:0">
            <label style="font-size:11px;font-weight:700;color:var(--slate-700)">Jenis PPh</label>
            <select name="potong_pph" class="input" style="font-size:12px;padding:4px 8px">
              <option value="">- Tanpa PPh -</option>
              <option value="PPh 21">PPh 21 (Honor/Upah)</option>
              <option value="PPh 22">PPh 22 (Barang)</option>
              <option value="PPh 23">PPh 23 (Sewa/Jasa)</option>
              <option value="PPh 4(2)">PPh Final 4(2) (Konstruksi)</option>
            </select>
          </div>
          <div class="field" style="margin:0">
            <label style="font-size:11px;font-weight:700;color:var(--slate-700)">Nominal PPh (Rp)</label>
            <input type="text" name="nominal_pph" class="input" data-money placeholder="0" style="font-size:12px;padding:4px 8px">
          </div>
          <div class="field" style="margin:0">
            <label style="font-size:11px;font-weight:700;color:var(--slate-700)">NTPN / Biling</label>
            <input type="text" name="ntpn" class="input" placeholder="No. Biling/NTPN" style="font-size:12px;padding:4px 8px">
          </div>
          <div class="field" style="margin:0">
            <label style="font-size:11px;font-weight:700;color:var(--slate-700)">Keterangan Tambahan</label>
            <input type="text" name="keterangan" class="input" placeholder="Catatan bukti SPJ" style="font-size:12px;padding:4px 8px">
          </div>
        </div>
      </form>
    </div>

    <!-- Kesimpulan & Sumber Data -->
    <div class="section-title"><i class="fa-solid fa-pen-to-square"></i> Kesimpulan & Sumber Data</div>
    <div class="card">
      <form method="post" action="<?= url('sesi/update') ?>" data-testid="form-kesimpulan">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $sesi['id'] ?>">
        <input type="hidden" name="desa_id" value="<?= $sesi['desa_id'] ?>">
        <input type="hidden" name="bidang_id" value="<?= $sesi['bidang_id'] ?>">
        <input type="hidden" name="sub_bidang_id" value="<?= e((string)$sesi['sub_bidang_id']) ?>">
        <input type="hidden" name="objek_audit" value="<?= e($sesi['objek_audit']) ?>">
        <input type="hidden" name="kegiatan" value="<?= e((string)$sesi['kegiatan']) ?>">
        <input type="hidden" name="pagu_anggaran" value="<?= number_format((float)$sesi['pagu_anggaran'],0,',','.') ?>">
        <input type="hidden" name="semester" value="<?= (int)$sesi['semester'] ?>">
        <input type="hidden" name="tahun_anggaran" value="<?= (int)$sesi['tahun_anggaran'] ?>">
        <input type="hidden" name="no_kka" value="<?= e((string)$sesi['no_kka']) ?>">
        <input type="hidden" name="ref_kka" value="<?= e((string)$sesi['ref_kka']) ?>">
        <input type="hidden" name="dibuat_oleh" value="<?= e((string)$sesi['dibuat_oleh']) ?>">
        <input type="hidden" name="tanggal_dibuat" value="<?= e((string)$sesi['tanggal_dibuat']) ?>">
        <input type="hidden" name="direview_oleh" value="<?= e((string)$sesi['direview_oleh']) ?>">
        <input type="hidden" name="tanggal_review" value="<?= e((string)$sesi['tanggal_review']) ?>">
        <input type="hidden" name="dievaluasi_oleh" value="<?= e((string)$sesi['dievaluasi_oleh']) ?>">
        <input type="hidden" name="tanggal_evaluasi" value="<?= e((string)$sesi['tanggal_evaluasi']) ?>">
        <div class="row">
          <div class="field">
            <label>Kesimpulan Audit</label>
            <textarea name="kesimpulan" class="textarea" placeholder="Tuliskan kesimpulan audit..." data-testid="f-kesimpulan"><?= e((string)$sesi['kesimpulan']) ?></textarea>
          </div>
          <div class="field">
            <label>Sumber Data</label>
            <textarea name="sumber_data" class="textarea" placeholder="cth: SPP, kwitansi, daftar hadir..."><?= e((string)$sesi['sumber_data']) ?></textarea>
          </div>
        </div>
        <div style="text-align:right"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan Kesimpulan</button></div>
      </form>
    </div>

    <!-- Lampiran -->
    <div class="section-title"><i class="fa-solid fa-paperclip"></i> Lampiran (PDF / Excel / Gambar)</div>
    <div class="card">
      <form method="post" enctype="multipart/form-data" action="<?= url('lampiran/upload') ?>" style="display:flex;gap:8px;align-items:end;margin-bottom:14px" data-testid="form-lampiran">
        <?= csrf_field() ?>
        <input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
        <div class="field" style="flex:1;margin:0"><label>Pilih File (maks 10 MB)</label><input type="file" name="file" required class="input" accept=".pdf,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif" data-testid="lamp-file"></div>
        <div class="field" style="flex:1;margin:0"><label>Keterangan</label><input type="text" name="keterangan" class="input" placeholder="cth: Kwitansi honor januari"></div>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-upload"></i> Upload</button>
      </form>
      <?php if (empty($lampiran)): ?>
        <div style="text-align:center;color:var(--slate-500);padding:18px 0;font-size:13px">Belum ada lampiran.</div>
      <?php else: ?>
        <div class="list-grid">
          <?php foreach ($lampiran as $l): ?>
            <div class="list-card">
              <div class="ico"><i class="fa-solid <?= str_contains((string)$l['mime_type'],'pdf')?'fa-file-pdf':(str_contains((string)$l['mime_type'],'image')?'fa-file-image':'fa-file-excel') ?>"></i></div>
              <div class="main">
                <div class="title"><?= e($l['nama_asli']) ?></div>
                <div class="meta">
                  <span><i class="fa-regular fa-calendar"></i> <?= tgl_id($l['created_at']) ?></span>
                  <span><i class="fa-solid fa-weight-hanging"></i> <?= number_format($l['ukuran']/1024, 1) ?> KB</span>
                  <?php if ($l['keterangan']): ?><span><i class="fa-solid fa-comment"></i> <?= e($l['keterangan']) ?></span><?php endif; ?>
                </div>
              </div>
              <div class="actions">
                <a class="btn btn-outline btn-sm" href="<?= url('lampiran/download?id='.$l['id']) ?>" target="_blank"><i class="fa-solid fa-download"></i></a>
                <form method="post" action="<?= url('lampiran/delete') ?>" onsubmit="return confirm('Hapus lampiran ini?')" style="display:inline">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $l['id'] ?>"><input type="hidden" name="sesi_id" value="<?= $sesi['id'] ?>">
                  <button class="btn btn-outline btn-sm" style="color:var(--red-600);border-color:#fecaca"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Modal Edit Rincian -->
<div id="modalEditRincian" class="kka-modal" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box">
    <div class="kka-modal__head">
      <h3 id="modalEditTitle"><i class="fa-solid fa-pen-to-square"></i> Edit Rincian Belanja</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="<?= url('rincian/update') ?>" id="formEditRincian">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="er-id">
      <input type="hidden" name="sesi_id" value="<?= (int)$sesi['id'] ?>">
      <div class="kka-modal__body">
        <div class="field">
          <label>Uraian Belanja <span class="req">*</span></label>
          <input type="text" name="uraian" id="er-uraian" required class="input" data-testid="er-uraian">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
          <div class="field">
            <label>Pagu Anggaran (Rp)</label>
            <input type="text" name="pagu_anggaran" id="er-pagu" class="input" data-money placeholder="0" data-testid="er-pagu">
          </div>
          <div class="field">
            <label>Realisasi (Rp)</label>
            <input type="text" name="realisasi" id="er-real" class="input" data-money placeholder="0" data-testid="er-real">
          </div>
          <div class="field">
            <label>Biaya Dikwitansi (Rp)</label>
            <input type="text" name="biaya_dikwitansi" id="er-kwi" class="input" data-money placeholder="0" data-testid="er-kwi">
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="field">
            <label>Penerima</label>
            <input type="text" name="penerima" id="er-penerima" class="input" placeholder="Nama penerima">
          </div>
          <div class="field">
            <label>Keterangan</label>
            <input type="text" name="keterangan" id="er-keterangan" class="input" placeholder="-">
          </div>
        </div>

        <div style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:8px">
          <div style="font-weight:700;font-size:12px;color:var(--slate-700);margin-bottom:8px">
            <i class="fa-solid fa-receipt" style="color:#059669"></i> Uji Kepatuhan Pajak Belanja
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:8px">
            <div class="field" style="margin:0">
              <label style="font-size:11.5px">Status Setor Pajak</label>
              <select name="status_pajak" id="er-status-pajak" class="input">
                <option value="TIDAK_TERUTANG">Tidak Terutang</option>
                <option value="BELUM_SETOR">⚠️ Belum Disetor</option>
                <option value="SUDAH_SETOR">✓ Sudah Disetor</option>
              </select>
            </div>
            <div class="field" style="margin:0">
              <label style="font-size:11.5px">Nomor NTPN / Biling</label>
              <input type="text" name="ntpn" id="er-ntpn" class="input" placeholder="No. NTPN">
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1.2fr 1.2fr 1.2fr;gap:8px;align-items:end">
            <div class="field" style="margin:0">
              <label style="display:flex;align-items:center;gap:4px;font-size:12px;cursor:pointer">
                <input type="checkbox" name="potong_ppn" id="er-potong-ppn" value="1"> PPN (11%)
              </label>
            </div>
            <div class="field" style="margin:0">
              <label style="font-size:11px">Nominal PPN (Rp)</label>
              <input type="text" name="nominal_ppn" id="er-nom-ppn" class="input" data-money placeholder="0">
            </div>
            <div class="field" style="margin:0">
              <label style="font-size:11px">Jenis PPh</label>
              <select name="potong_pph" id="er-potong-pph" class="input">
                <option value="">- Tanpa PPh -</option>
                <option value="PPh 21">PPh 21 (Honor)</option>
                <option value="PPh 22">PPh 22 (Barang)</option>
                <option value="PPh 23">PPh 23 (Sewa/Jasa)</option>
                <option value="PPh 4(2)">PPh Final 4(2)</option>
              </select>
            </div>
            <div class="field" style="margin:0">
              <label style="font-size:11px">Nominal PPh (Rp)</label>
              <input type="text" name="nominal_pph" id="er-nom-pph" class="input" data-money placeholder="0">
            </div>
          </div>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" data-testid="er-save"><i class="fa-solid fa-check"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Revisi Ketua Tim -->
<div id="modalRevisiKetua" class="kka-modal" role="dialog" aria-modal="true" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box">
    <div class="kka-modal__head" style="background:#fff1f2">
      <h3 style="color:#b91c1c"><i class="fa-solid fa-rotate-left"></i> Kembalikan KKA (Perlu Revisi) - Ketua Tim</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="<?= url('sesi/reviu-ketua') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$sesi['id'] ?>">
      <input type="hidden" name="aksi" value="revisi">
      <div class="kka-modal__body">
        <div class="field">
          <label>Catatan Reviu / Arahan Revisi untuk Auditor <span class="req">*</span></label>
          <textarea name="catatan_reviu" class="textarea" rows="5" required placeholder="Tuliskan catatan perbaikan, rincian yang perlu dicek ulang, bukti/dokumen yang kurang lengkap, dsb..."></textarea>
          <small style="color:var(--slate-500);font-size:12px">Catatan ini akan langsung tampil di KKA auditor agar segera diperbaiki.</small>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" style="background:#dc2626;border-color:#dc2626"><i class="fa-solid fa-paper-plane"></i> Kirim Catatan &amp; Kembalikan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Setuju Ketua Tim -->
<div id="modalSetujuKetua" class="kka-modal" role="dialog" aria-modal="true" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box">
    <div class="kka-modal__head" style="background:#f0fdf4">
      <h3 style="color:#15803d"><i class="fa-solid fa-circle-check"></i> Setujui &amp; Teruskan ke Dalnis - Ketua Tim</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="<?= url('sesi/reviu-ketua') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$sesi['id'] ?>">
      <input type="hidden" name="aksi" value="setuju">
      <div class="kka-modal__body">
        <div class="field">
          <label>Catatan Reviu / Kepatuhan (Opsional)</label>
          <textarea name="catatan_reviu" class="textarea" rows="4" placeholder="Cth: Telah direviu dan diuji sampling bukti kwitansi, perhitungan sesuai. Diteruskan ke Dalnis."></textarea>
          <small style="color:var(--slate-500);font-size:12px">Catatan ini akan dicatat dan tampil di Lembar Reviu KKA.</small>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" style="background:#059669;border-color:#059669"><i class="fa-solid fa-check"></i> Setujui &amp; Teruskan ke Dalnis</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Revisi Dalnis -->
<div id="modalRevisiDalnis" class="kka-modal" role="dialog" aria-modal="true" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box">
    <div class="kka-modal__head" style="background:#fff1f2">
      <h3 style="color:#b91c1c"><i class="fa-solid fa-rotate-left"></i> Kembalikan KKA (Perlu Perbaikan) - Dalnis</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="<?= url('sesi/reviu-dalnis') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$sesi['id'] ?>">
      <input type="hidden" name="aksi" value="revisi">
      <div class="kka-modal__body">
        <div class="field">
          <label>Arahan Perbaikan / Catatan Dalnis <span class="req">*</span></label>
          <textarea name="catatan_reviu" class="textarea" rows="5" required placeholder="Tuliskan arahan perbaikan teknis / substansi KKA dari Pengendali Teknis..."></textarea>
          <small style="color:var(--slate-500);font-size:12px">KKA akan berstatus Perlu Revisi dan catatan ini langsung tampil ke Tim Pemeriksa.</small>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" style="background:#dc2626;border-color:#dc2626"><i class="fa-solid fa-paper-plane"></i> Kembalikan untuk Revisi</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Sahkan Dalnis -->
<div id="modalSahkanDalnis" class="kka-modal" role="dialog" aria-modal="true" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box">
    <div class="kka-modal__head" style="background:#eef2ff">
      <h3 style="color:#4338ca"><i class="fa-solid fa-stamp"></i> Sahkan KKA (Final) - Pengendali Teknis</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="<?= url('sesi/reviu-dalnis') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$sesi['id'] ?>">
      <input type="hidden" name="aksi" value="sahkan">
      <div class="kka-modal__body">
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:14px;color:#15803d;font-size:13px">
          <i class="fa-solid fa-shield-halved"></i> Anda akan mengesahkan KKA ini secara final. Dokumen ini akan menjadi KKA resmi Inspektorat yang sah untuk penyusunan LHP.
        </div>
        <div class="field">
          <label>Catatan Pengesahan Dalnis (Opsional)</label>
          <textarea name="catatan_reviu" class="textarea" rows="3" placeholder="Cth: KKA telah diperiksa dan disetujui untuk diterbitkan sebagai lampiran LHP."></textarea>
          <small style="color:var(--slate-500);font-size:12px">Catatan pengesahan ini akan tercantum di Lembar Reviu KKA.</small>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5"><i class="fa-solid fa-stamp"></i> Sahkan KKA Sekarang</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Impor Excel -->
<div id="modalImportExcel" class="kka-modal" role="dialog" aria-modal="true" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box" style="max-width:540px">
    <div class="kka-modal__head" style="background:#ecfdf5">
      <h3 style="color:#065f46"><i class="fa-solid fa-file-excel"></i> Impor Rincian Belanja dari Excel / CSV</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" enctype="multipart/form-data" action="<?= url('rincian/import') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="sesi_id" value="<?= (int)$sesi['id'] ?>">
      <div class="kka-modal__body">
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:14px;color:#166534;font-size:12.5px;line-height:1.5">
          <i class="fa-solid fa-circle-info"></i> <b>Solusi Audit Offline di Desa:</b><br>
          Gunakan fitur ini jika pemeriksaan dilakukan di lokasi tanpa internet. Isi data SPJ &amp; Kwitansi di file Excel/CSV secara offline, lalu unggah file tersebut di sini saat sudah online.
        </div>

        <div class="field">
          <label>Pilih File Excel (.xlsx) atau CSV <span class="req">*</span></label>
          <input type="file" name="file_excel" accept=".xlsx, .csv, .xls" required class="input" style="padding:8px" id="inputExcelFile">
          <small style="color:var(--slate-500);font-size:11.5px;margin-top:4px;display:block">
            Mendukung file Microsoft Excel <code>.xlsx</code> dan <code>.csv</code>. Belum punya formatnya? <a href="<?= url('rincian/template?sesi_id='.$sesi['id']) ?>" style="color:#059669;font-weight:600"><i class="fa-solid fa-download"></i> Unduh Template di sini</a>.
          </small>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-top:12px">
          <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;margin:0;font-size:13px;color:var(--slate-700)">
            <input type="checkbox" name="mode_replace" value="1" style="margin-top:3px;accent-color:#059669">
            <div>
              <strong>Timpa / Ganti data rincian yang sudah ada</strong>
              <div style="font-size:11.5px;color:var(--slate-500);margin-top:2px">Jika dicentang, rincian belanja lama pada sesi ini akan dihapus dan diganti dengan isi file Excel baru. Jika tidak dicentang, data baru akan ditambahkan (append).</div>
            </div>
          </label>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" style="background:#059669;border-color:#059669"><i class="fa-solid fa-upload"></i> Mulai Impor Data</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Kelola Routing Slip LHA -->
<div id="modalEditRoutingSlip" class="kka-modal" role="dialog" aria-modal="true" hidden>
  <div class="kka-modal__backdrop" data-close-modal></div>
  <div class="kka-modal__box" style="max-width:720px">
    <div class="kka-modal__head" style="background:#fffbeb">
      <h3 style="color:#b45309"><i class="fa-solid fa-folder-open"></i> Kelola Data &amp; Catatan Routing Slip LHA</h3>
      <button type="button" class="kka-modal__x" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="post" action="<?= url('sesi/update-routing-slip') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$sesi['id'] ?>">
      <div class="kka-modal__body" style="max-height:75vh">
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px;margin-bottom:14px;color:#92400e;font-size:12.5px;line-height:1.5">
          <i class="fa-solid fa-circle-info"></i> <b>Routing Slip Kendali Mutu LHA:</b><br>
          Data SPT, penomoran LHA, dan lembar catatan hasil reviu (Review Sheet) berjenjang Dalnis &amp; Irban tersimpan di sini dan dicetak pada dokumen Routing Slip resmi.
        </div>

        <div style="font-weight:700;color:var(--slate-700);font-size:13px;border-bottom:1px solid #e2e8f0;padding-bottom:4px;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px">
          1. Data SPT &amp; Penugasan
        </div>

        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:10px">
          <div class="field">
            <label>Nomor SPT</label>
            <input type="text" name="no_spt" class="input" value="<?= e($sesi['no_spt'] ?? '') ?>" placeholder="Cth: 700/SPT-INSP/2026">
          </div>
          <div class="field">
            <label>Tgl. Mulai SPT</label>
            <input type="date" name="tgl_spt" class="input" value="<?= e($sesi['tgl_spt'] ?? '') ?>">
          </div>
          <div class="field">
            <label>SPT s.d. Tanggal</label>
            <input type="date" name="tgl_spt_selesai" class="input" value="<?= e($sesi['tgl_spt_selesai'] ?? '') ?>">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:10px;margin-top:6px">
          <div class="field">
            <label>Alamat Objek / Telepon</label>
            <input type="text" name="alamat_objek" class="input" value="<?= e($sesi['alamat_objek'] ?? '') ?>" placeholder="Cth: Kepenghuluan <?= e($sesi['desa_nama']) ?>, Kec. <?= e($sesi['kecamatan_nama']) ?>">
          </div>
          <div class="field">
            <label>Jenis Audit</label>
            <input type="text" name="jenis_audit" class="input" value="<?= e($sesi['jenis_audit'] ?: 'Audit Dengan Tujuan Tertentu (ADTT)') ?>" placeholder="Audit Dengan Tujuan Tertentu (ADTT)">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1.2fr 1fr 1.2fr;gap:10px;margin-top:6px">
          <div class="field">
            <label>Nomor LHA</label>
            <input type="text" name="no_lha" class="input" value="<?= e($sesi['no_lha'] ?? '') ?>" placeholder="Cth: 700/LHA-INSP/<?= $sesi['tahun_anggaran'] ?>">
          </div>
          <div class="field">
            <label>Tanggal LHA</label>
            <input type="date" name="tgl_lha" class="input" value="<?= e($sesi['tgl_lha'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Nama Irban (Wakil PJ)</label>
            <input type="text" name="irban_nama" class="input" value="<?= e($sesi['irban_nama'] ?? '') ?>" placeholder="Cth: Irban Wilayah I">
          </div>
        </div>

        <div style="font-weight:700;color:var(--slate-700);font-size:13px;border-bottom:1px solid #e2e8f0;padding-bottom:4px;margin:18px 0 12px;text-transform:uppercase;letter-spacing:0.5px">
          2. Catatan Hasil Reviu Pemeriksaan (Review Sheet)
        </div>

        <div class="field">
          <label style="font-weight:600;color:#1e293b"><i class="fa-solid fa-user-shield" style="color:#4f46e5"></i> A. Catatan / Arahan Koreksi Pengendali Teknis (PT / Dalnis)</label>
          <textarea name="catatan_reviu_dalnis" class="textarea" rows="3" placeholder="Tuliskan catatan atau instruksi perbaikan dari Pengendali Teknis..."><?= e($sesi['catatan_reviu_dalnis'] ?? '') ?></textarea>
          <small style="color:var(--slate-500);font-size:11.5px">Ditampilkan pada kotak A Review Sheet Routing Slip LHA.</small>
        </div>

        <div class="field" style="margin-top:10px">
          <label style="font-weight:600;color:#1e293b"><i class="fa-solid fa-user-tie" style="color:#0891b2"></i> B. Catatan / Arahan Inspektur Pembantu (Wakil Penanggung Jawab)</label>
          <textarea name="catatan_reviu_irban" class="textarea" rows="3" placeholder="Tuliskan catatan atau telaah dari Inspektur Pembantu (Irban)..."><?= e($sesi['catatan_reviu_irban'] ?? '') ?></textarea>
          <small style="color:var(--slate-500);font-size:11.5px">Ditampilkan pada kotak B Review Sheet Routing Slip LHA.</small>
        </div>
      </div>
      <div class="kka-modal__foot">
        <button type="button" class="btn btn-ghost" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary" style="background:#d97706;border-color:#d97706"><i class="fa-solid fa-floppy-disk"></i> Simpan Data &amp; Catatan</button>
      </div>
    </form>
  </div>
</div>

<style>
.kka-modal{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px}
.kka-modal[hidden]{display:none}
.kka-modal__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)}
.kka-modal__box{position:relative;background:#fff;border-radius:14px;box-shadow:0 30px 60px -20px rgba(0,0,0,.4);width:100%;max-width:640px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column}
.kka-modal__head{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid #e2e8f0;background:linear-gradient(90deg,#f0fdf4,#ecfeff)}
.kka-modal__head h3{margin:0;font-size:16px;color:var(--emerald-700);font-weight:700}
.kka-modal__x{background:transparent;border:0;cursor:pointer;font-size:18px;color:#64748b;padding:6px}
.kka-modal__x:hover{color:#0f172a}
.kka-modal__body{padding:20px 22px;overflow-y:auto}
.kka-modal__foot{display:flex;justify-content:flex-end;gap:8px;padding:14px 22px;border-top:1px solid #e2e8f0;background:#f8fafc}
@media (max-width: 992px){ .timeline-stepper{ grid-template-columns: repeat(2, 1fr) !important; } }
@media (max-width: 540px){ .timeline-stepper{ grid-template-columns: 1fr !important; } }
</style>

<script>
(function(){
  var modal = document.getElementById('modalEditRincian');
  if (!modal) return;
  function openModal(){ modal.hidden = false; document.body.style.overflow='hidden'; }
  function closeModal(){ modal.hidden = true; document.body.style.overflow=''; }
  function showModal(id){
    var el = document.getElementById(id);
    if (!el) return;
    el.hidden = false;
    document.body.style.overflow = 'hidden';
    var ta = el.querySelector('textarea, input[type="text"]');
    if (ta) setTimeout(function(){ ta.focus(); }, 60);
  }
  function hideModal(el){
    if (!el) return;
    el.hidden = true;
    if (!document.querySelector('.kka-modal:not([hidden])')) {
      document.body.style.overflow = '';
    }
  }

  // Bind close buttons on all modals
  document.querySelectorAll('.kka-modal [data-close-modal]').forEach(function(btn){
    btn.addEventListener('click', function(){
      hideModal(btn.closest('.kka-modal'));
    });
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      document.querySelectorAll('.kka-modal:not([hidden])').forEach(function(m){ hideModal(m); });
    }
  });

  // Action buttons for review
  var bRevisiKetua = document.getElementById('btnBukaRevisiKetua');
  if (bRevisiKetua) bRevisiKetua.addEventListener('click', function(){ showModal('modalRevisiKetua'); });

  var bSetujuKetua = document.getElementById('btnBukaSetujuKetua');
  if (bSetujuKetua) bSetujuKetua.addEventListener('click', function(){ showModal('modalSetujuKetua'); });

  var bRevisiDalnis = document.getElementById('btnBukaRevisiDalnis');
  if (bRevisiDalnis) bRevisiDalnis.addEventListener('click', function(){ showModal('modalRevisiDalnis'); });

  var bSahkanDalnis = document.getElementById('btnBukaSahkanDalnis');
  if (bSahkanDalnis) bSahkanDalnis.addEventListener('click', function(){ showModal('modalSahkanDalnis'); });

  var bImportExcel = document.getElementById('btnBukaImportExcel');
  if (bImportExcel) bImportExcel.addEventListener('click', function(){ showModal('modalImportExcel'); });

  var bRoutingSlip = document.getElementById('btnBukaRoutingSlip');
  if (bRoutingSlip) bRoutingSlip.addEventListener('click', function(){ showModal('modalEditRoutingSlip'); });

  // Format angka jadi format ID (titik ribuan)
  function fmt(v){ v = Math.round(Number(v)||0); return v.toLocaleString('id-ID'); }

  document.querySelectorAll('.js-edit-rincian').forEach(function(btn){
    btn.addEventListener('click', function(){
      var tr = btn.closest('tr');
      if (!tr) return;
      document.getElementById('er-id').value        = tr.dataset.rincianId || '';
      document.getElementById('er-uraian').value    = tr.dataset.uraian || '';
      document.getElementById('er-pagu').value      = fmt(tr.dataset.pagu);
      document.getElementById('er-kwi').value       = fmt(tr.dataset.kwi);
      document.getElementById('er-real').value      = fmt(tr.dataset.real);
      document.getElementById('er-penerima').value  = tr.dataset.penerima || '';
      document.getElementById('er-keterangan').value= tr.dataset.keterangan || '';
      
      var elStatusPajak = document.getElementById('er-status-pajak');
      if (elStatusPajak) elStatusPajak.value = tr.dataset.statusPajak || 'TIDAK_TERUTANG';
      var elNtpn = document.getElementById('er-ntpn');
      if (elNtpn) elNtpn.value = tr.dataset.ntpn || '';
      var elPotongPpn = document.getElementById('er-potong-ppn');
      if (elPotongPpn) elPotongPpn.checked = (tr.dataset.potongPpn === '1');
      var elNomPpn = document.getElementById('er-nom-ppn');
      if (elNomPpn) elNomPpn.value = fmt(tr.dataset.nominalPpn);
      var elPotongPph = document.getElementById('er-potong-pph');
      if (elPotongPph) elPotongPph.value = tr.dataset.potongPph || '';
      var elNomPph = document.getElementById('er-nom-pph');
      if (elNomPph) elNomPph.value = fmt(tr.dataset.nominalPph);

      openModal();
      setTimeout(function(){ document.getElementById('er-uraian').focus(); }, 50);
      showModal('modalEditRincian');
    });
  });

  modal.querySelectorAll('[data-close-modal]').forEach(function(el){
    el.addEventListener('click', closeModal);
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });
})();
</script>

<?php partial('foot'); ?>
