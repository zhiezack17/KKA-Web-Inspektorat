<?php $title = 'Dashboard Eksekutif - KKA Digital Inspektorat'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-dashboard">
  <?php partial('topbar', ['title' => 'Dashboard Eksekutif Pengawasan', 'icon' => 'fa-solid fa-gauge-high']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <!-- 1. EXECUTIVE WELCOME HERO CARD -->
    <div class="page-head" style="margin-bottom:20px;background:linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);border:1px solid #e2e8f0;border-radius:14px;padding:16px 22px;box-shadow:0 1px 3px rgba(0,0,0,0.03);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        <?php $dashAvatar = user_avatar_url($auth->user()); ?>
        <?php if ($dashAvatar): ?>
          <div style="position:relative;flex-shrink:0">
            <img src="<?= $dashAvatar ?>" alt="Foto Resmi" style="width:54px;height:54px;border-radius:50%;object-fit:cover;border:2.5px solid <?= $auth->isInspektur() ? '#d97706' : '#059669' ?>;box-shadow:0 4px 10px rgba(0,0,0,0.12)">
            <span style="position:absolute;bottom:1px;right:1px;width:13px;height:13px;border-radius:50%;background:#22c55e;border:2px solid #fff" title="Online Aktif"></span>
          </div>
        <?php endif; ?>
        <div>
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <h2 style="font-size:20px;font-weight:800;letter-spacing:-0.3px;color:#0f172a;margin:0">
              Selamat Datang, <?= e(sapaan_nama($auth->user()['nama'], $auth->user()['role'])) ?> 👋
            </h2>
            <span class="badge" style="background:<?= $auth->isInspektur() ? '#fef3c7' : '#ecfdf5' ?>;color:<?= $auth->isInspektur() ? '#92400e' : '#065f46' ?>;border:1px solid <?= $auth->isInspektur() ? '#fde68a' : '#a7f3d0' ?>;font-size:11px;font-weight:700">
              <i class="fa-solid fa-shield-halved" style="margin-right:3px"></i> <?= e(ucwords(str_replace('_', ' ', $auth->user()['role']))) ?>
            </span>
          </div>
          <p style="color:#64748b;font-size:12.5px;margin-top:4px;margin-bottom:0">
            Sistem Pengawasan Terpadu Kertas Kerja Audit (KKA) Digital &mdash; Inspektorat Kabupaten Rokan Hilir.
          </p>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <?php if ($auth->isAdmin()): ?>
          <a href="<?= url('sesi') ?>" class="btn btn-primary" style="font-weight:700">
            <i class="fa-solid fa-clipboard-list"></i> Kertas Kerja (KKA)
          </a>
          <a href="<?= url('lhp') ?>" class="btn btn-outline" style="border-color:#0f766e;color:#0f766e;font-weight:700">
            <i class="fa-solid fa-file-shield"></i> Laporan Hasil (LHP)
          </a>
          <a href="<?= url('users') ?>" class="btn btn-outline" style="border-color:#6366f1;color:#6366f1;font-weight:700">
            <i class="fa-solid fa-users-gear"></i> Kelola Pengguna
          </a>
        <?php elseif ($auth->isInspektur()): ?>
          <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-primary" style="background:#b45309;border:none;font-weight:700;box-shadow:0 2px 4px rgba(180,83,9,0.25)">
            <i class="fa-solid fa-pen-nib"></i> Lembar Disposisi Inspektur
          </a>
          <a href="<?= url('lhp') ?>" class="btn btn-outline" style="border-color:#0f766e;color:#0f766e;font-weight:700">
            <i class="fa-solid fa-file-shield"></i> Laporan Hasil Audit (LHP)
          </a>
        <?php elseif ($auth->isOperatorSpt()): ?>
          <a href="<?= url('penugasan/spt') ?>" class="btn btn-primary" style="background:#059669;border:none;font-weight:700;box-shadow:0 2px 4px rgba(5,150,105,0.25)">
            <i class="fa-solid fa-file-signature"></i> Antrean Surat Tugas (SPT)
          </a>
        <?php else: ?>
          <a href="<?= url('penugasan/nota-dinas/create') ?>" class="btn btn-outline" style="border-color:#2563eb;color:#2563eb;font-weight:700">
            <i class="fa-solid fa-envelope-open-text"></i> Usulkan Tim (ND)
          </a>
          <a href="<?= url('sesi') ?>" class="btn btn-primary" style="font-weight:700">
            <i class="fa-solid fa-clipboard-list"></i> Kertas Kerja (KKA)
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Alert Khusus Pimpinan (Inspektur & Operator) -->
    <?php if ($auth->isInspektur()): ?>
      <?php 
        $pendingNd = (int) DB::val("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DIAJUKAN_INSPEKTUR'");
        $pendingSpt = (int) DB::val("SELECT COUNT(*) FROM kka_spt WHERE status = 'DRAFT'");
      ?>
      <?php if ($pendingNd > 0 || $pendingSpt > 0): ?>
        <div style="background:linear-gradient(135deg, #fffbeb, #fef3c7); border:1px solid #fde68a; border-left:5px solid #d97706; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:16px; box-shadow:0 2px 6px rgba(217,119,6,0.08);">
          <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:42px; height:42px; border-radius:50%; background:#fde68a; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fa-solid fa-bell" style="font-size:18px; color:#b45309;"></i>
            </div>
            <div>
              <div style="font-weight:700; color:#92400e; font-size:14px;">Agenda Tindakan Pimpinan:</div>
              <div style="font-size:13px; color:#78350f; margin-top:2px;">
                <?php if ($pendingNd > 0): ?>Ada <strong style="color:#b45309"><?= $pendingNd ?></strong> Nota Dinas menunggu persetujuan / disposisi Anda. <?php endif; ?>
                <?php if ($pendingSpt > 0): ?>Ada <strong style="color:#0f766e"><?= $pendingSpt ?></strong> Surat Tugas (SPT) menunggu pengesahan digital TTE. <?php endif; ?>
              </div>
            </div>
          </div>
          <div style="display:flex; gap:8px; flex-shrink:0;">
            <?php if ($pendingNd > 0): ?>
              <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-sm" style="background:#b45309; color:#fff; border:none; font-weight:700; box-shadow:0 2px 4px rgba(180,83,9,0.25);"><i class="fa-solid fa-pen-nib"></i> Buka Disposisi ND</a>
            <?php endif; ?>
            <?php if ($pendingSpt > 0): ?>
              <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm" style="background:#0f766e; color:#fff; border:none; font-weight:700; box-shadow:0 2px 4px rgba(15,118,110,0.25);"><i class="fa-solid fa-signature"></i> Sahkan SPT</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php elseif ($auth->isOperatorSpt()): ?>
      <?php 
        $readyNd = (int) DB::val("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DISETUJUI' AND id NOT IN (SELECT COALESCE(nota_dinas_id,0) FROM kka_spt)");
      ?>
      <?php if ($readyNd > 0): ?>
        <div style="background:linear-gradient(135deg, #ecfdf5, #d1fae5); border:1px solid #a7f3d0; border-left:5px solid #059669; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:16px; box-shadow:0 2px 6px rgba(5,150,105,0.08);">
          <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:42px; height:42px; border-radius:50%; background:#a7f3d0; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fa-solid fa-clipboard-check" style="font-size:18px; color:#047857;"></i>
            </div>
            <div>
              <div style="font-weight:700; color:#065f46; font-size:14px;">Agenda Penerbitan SPT:</div>
              <div style="font-size:13px; color:#047857; margin-top:2px;">Ada <strong><?= $readyNd ?></strong> Nota Dinas telah disetujui Inspektur dan siap diterbitkan Surat Tugas (SPT).</div>
            </div>
          </div>
          <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm" style="background:#059669; color:#fff; border:none; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(5,150,105,0.25);"><i class="fa-solid fa-stamp"></i> Buka Antrean Penerbitan</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- 2. PIPELINE SIKLUS AUDIT DIGITAL TERPADU (STEPPER RAMPING) -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:26px;height:26px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:12px">
            <i class="fa-solid fa-diagram-project"></i>
          </div>
          <div>
            <span style="font-size:12px;font-weight:800;color:#1e293b;text-transform:uppercase;letter-spacing:0.5px">
              Pipeline Siklus Pengawasan Digital
            </span>
            <span style="font-size:11px;color:#64748b;margin-left:6px">(Standar SPKN &amp; SAIPI Terpadu)</span>
          </div>
        </div>
        <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-size:10.5px;font-weight:700">
          <i class="fa-solid fa-circle-check" style="color:#22c55e;margin-right:4px"></i> Alur Aktif Hulu ke Hilir
        </span>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:10px">
        <!-- Tahap 1 -->
        <a href="<?= url('penugasan/nota-dinas') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9';this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#6366f1;text-transform:uppercase;background:#e0e7ff;padding:2px 6px;border-radius:4px">1. PRA-AUDIT</span>
            <i class="fa-solid fa-envelope-open-text" style="color:#6366f1;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#0f172a;margin-top:2px"><?= $stats['nd_total'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">Nota Dinas</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= $pipeline['nd_diajukan'] ?> Menunggu &bull; <?= $pipeline['nd_disetujui'] ?> Disetujui
          </div>
        </a>

        <!-- Tahap 2 -->
        <a href="<?= url('penugasan/spt') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9';this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#0f766e;text-transform:uppercase;background:#ccfbf1;padding:2px 6px;border-radius:4px">2. SURAT TUGAS</span>
            <i class="fa-solid fa-file-signature" style="color:#0f766e;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#0f766e;margin-top:2px"><?= $stats['spt_total'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">SPT Sah</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            Matriks PKA &amp; TTE Digital
          </div>
        </a>

        <!-- Tahap 3 -->
        <a href="<?= url('sesi') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9';this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#0284c7;text-transform:uppercase;background:#e0f2fe;padding:2px 6px;border-radius:4px">3. UJI LAPANGAN</span>
            <i class="fa-solid fa-clipboard-check" style="color:#0284c7;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#0284c7;margin-top:2px"><?= $stats['sesi'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">Sesi Audit</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            Fisik, Kwitansi &amp; Pajak
          </div>
        </a>

        <!-- Tahap 4 -->
        <a href="<?= url('temuan') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9';this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#d97706;text-transform:uppercase;background:#fef3c7;padding:2px 6px;border-radius:4px">4. KONSEP TEMUAN</span>
            <i class="fa-solid fa-file-circle-exclamation" style="color:#d97706;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#b45309;margin-top:2px"><?= $stats['temuan_total'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">Temuan</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            5 Unsur &amp; Rekomendasi
          </div>
        </a>

        <!-- Tahap 5 -->
        <a href="<?= url('lhp') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9';this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#16a34a;text-transform:uppercase;background:#dcfce7;padding:2px 6px;border-radius:4px">5. NASKAH LHP</span>
            <i class="fa-solid fa-file-shield" style="color:#16a34a;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#15803d;margin-top:2px"><?= max(1, $pipeline['lhp_desa']) ?> <span style="font-size:11px;font-weight:600;color:#64748b">Desa Siap</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            Format Standar Rohil
          </div>
        </a>
      </div>
    </div>

    <!-- 3. 4 KARTU INDIKATOR EKSEKUTIF UTAMA -->
    <div class="stats-grid" style="margin-bottom:22px;display:grid;grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));gap:14px">
      <!-- Card 1: Total Belanja Diaudit -->
      <div class="stat blue" data-testid="stat-anggaran" style="padding:16px 18px;border-left:4px solid #0284c7;border-radius:10px">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Total Belanja Diaudit</div>
          <div class="value money" style="font-size:21px;font-weight:800;color:#0f172a;margin:5px 0 2px"><?= rupiah($stats['dikwitansi']) ?></div>
          <div class="sub" style="font-size:11px;color:#64748b">Pagu: <?= rupiah($stats['anggaran']) ?> &bull; Realisasi Uji</div>
        </div>
        <div class="ico" style="background:#e0f2fe;color:#0284c7;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-money-bill-wave"></i></div>
      </div>

      <!-- Card 2: Potensi Pemulihan Kas Desa -->
      <div class="stat rose" data-testid="stat-pemulihan" style="padding:16px 18px;border-left:4px solid #e11d48;border-radius:10px">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Potensi Pemulihan Kas</div>
          <div class="value money" style="font-size:21px;font-weight:800;color:#e11d48;margin:5px 0 2px">
            <?= rupiah(max($stats['selisih'], $stats['temuan_nominal'])) ?>
          </div>
          <div class="sub" style="font-size:11px;color:#64748b">
            <?= $stats['temuan_total'] ?> Butir Temuan SPKN &bull; Uji Kwitansi
          </div>
        </div>
        <div class="ico" style="background:#ffe4e6;color:#e11d48;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-hand-holding-dollar"></i></div>
      </div>

      <!-- Card 3: Uji Kepatuhan Pajak Belanja -->
      <div class="stat amber" data-testid="stat-pajak" style="padding:16px 18px;border-left:4px solid #f59e0b;border-radius:10px">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Kepatuhan Pajak Belanja</div>
          <div class="value money" style="font-size:21px;font-weight:800;color:#d97706;margin:5px 0 2px"><?= rupiah($stats['pajak_sudah_setor'] + $stats['pajak_belum_setor']) ?></div>
          <div class="sub" style="font-size:11px;color:#64748b">
            <span style="color:#059669;font-weight:700">Setor: <?= rupiah($stats['pajak_sudah_setor']) ?></span> &bull; 
            <span style="color:#dc2626;font-weight:700">Belum: <?= rupiah($stats['pajak_belum_setor']) ?></span>
          </div>
        </div>
        <div class="ico" style="background:#fef3c7;color:#d97706;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-receipt"></i></div>
      </div>

      <!-- Card 4: Dokumen Pengawasan Sah -->
      <div class="stat" style="padding:16px 18px;border-left:4px solid #10b981;border-radius:10px" data-testid="stat-dokumen">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Cakupan Pengawasan Sah</div>
          <div class="value" style="font-size:21px;font-weight:800;color:#0f172a;margin:5px 0 2px"><?= $stats['spt_total'] ?> SPT &bull; <?= $stats['desa'] ?> Desa</div>
          <div class="sub" style="font-size:11px;color:#64748b"><?= $stats['kec'] ?> Kecamatan se-Kabupaten Rokan Hilir</div>
        </div>
        <div class="ico" style="background:#d1fae5;color:#059669;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-stamp"></i></div>
      </div>
    </div>

    <!-- 4. MAIN CONTENT: 2fr 1fr BALANCED GRID -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">
      <!-- LEFT: EXECUTIVE DATA TABLE DAFTAR KEPENGHULUAN (DESA) -->
      <div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f1f5f9">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-building-columns" style="color:#2563eb"></i>
              Register Kepenghuluan (Desa) Objek Pemeriksaan
            </h3>
            <p style="margin:3px 0 0;font-size:12px;color:#64748b">Ringkasan hasil audit fisik belanja, kepatuhan perpajakan, dan status temuan per desa.</p>
          </div>
          <a href="<?= url('sesi') ?>" class="btn btn-ghost btn-sm" style="font-size:12px;font-weight:700">
            Semua Sesi <i class="fa-solid fa-arrow-right" style="margin-left:4px"></i>
          </a>
        </div>

        <?php if (empty($perDesa)): ?>
          <div class="empty" style="padding:40px 20px;text-align:center">
            <i class="fa-regular fa-folder-open" style="font-size:36px;color:#94a3b8;margin-bottom:10px"></i>
            <h4 style="margin:0;font-size:15px;color:#334155">Belum ada sesi audit</h4>
            <p style="margin:4px 0 0;font-size:12.5px;color:#64748b">Mulai buat sesi audit pertama melalui menu Kertas Kerja (KKA).</p>
          </div>
        <?php else: ?>
          <div class="table-responsive" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
            <table class="table" style="width:100%;margin-bottom:0;font-size:12.5px;border-collapse:collapse">
              <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:11px;font-weight:700;text-transform:uppercase;color:#475569;letter-spacing:0.5px">
                <tr>
                  <th style="padding:10px 12px;text-align:center;width:36px">No</th>
                  <th style="padding:10px 12px;text-align:left">Kepenghuluan (Desa)</th>
                  <th style="padding:10px 12px;text-align:right">Pagu APBDes</th>
                  <th style="padding:10px 12px;text-align:center">Sesi Audit</th>
                  <th style="padding:10px 12px;text-align:center">Status Kepatuhan</th>
                  <th style="padding:10px 12px;text-align:center;width:90px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach (array_slice($perDesa, 0, 7) as $d): ?>
                  <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                    <td style="padding:10px 12px;text-align:center;color:#64748b;font-weight:600"><?= $no++ ?></td>
                    <td style="padding:10px 12px;text-align:left">
                      <div style="font-weight:700;color:#0f172a;font-size:13px">
                        <a href="<?= url('sesi?desa=' . $d['id']) ?>" style="color:inherit;text-decoration:none" class="hover:text-primary">
                          <?= e($d['desa']) ?>
                        </a>
                      </div>
                      <div style="font-size:11px;color:#64748b;margin-top:1px">
                        Kec. <?= e($d['kecamatan']) ?>
                      </div>
                    </td>
                    <td style="padding:10px 12px;text-align:right;font-weight:700;color:#1e293b;white-space:nowrap">
                      <?= rupiah($d['pagu']) ?>
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                      <span class="badge" style="background:#f1f5f9;color:#334155;font-size:10.5px;font-weight:600">
                        <?= (int)$d['jumlah'] ?> Sesi (TA <?= $d['tahun_terakhir'] ?>)
                      </span>
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                      <?php if ((float)$d['selisih_fisik'] > 0 || (int)$d['jml_temuan'] > 0): ?>
                        <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-size:10.5px;font-weight:700;display:inline-flex;align-items:center;gap:4px">
                          <i class="fa-solid fa-triangle-exclamation"></i> Ada Temuan: <?= rupiah(max((float)$d['selisih_fisik'], (float)$d['nominal_temuan'])) ?>
                        </span>
                      <?php else: ?>
                        <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:10.5px;font-weight:700;display:inline-flex;align-items:center;gap:4px">
                          <i class="fa-solid fa-check"></i> Tertib (Nihil)
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                      <div style="display:inline-flex;align-items:center;gap:4px">
                        <a href="<?= url('sesi?desa=' . $d['id']) ?>" class="btn btn-sm btn-ghost" title="Buka Kertas Kerja (KKA)" style="padding:4px 7px;font-size:11px">
                          <i class="fa-solid fa-clipboard-list" style="color:#2563eb"></i>
                        </a>
                        <a href="<?= url('lhp/show?desa_id=' . $d['id'] . '&tahun=' . $d['tahun_terakhir']) ?>" class="btn btn-sm btn-ghost" title="Buka Naskah LHP" style="padding:4px 7px;font-size:11px">
                          <i class="fa-solid fa-file-shield" style="color:#0f766e"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: KEPATUHAN PAJAK & QUICK ACCESS DOKUMEN -->
      <div style="display:flex;flex-direction:column;gap:18px">
        <!-- Widget 1: Kepatuhan Pajak Belanja -->
        <div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:7px">
              <i class="fa-solid fa-receipt" style="color:#d97706"></i> Kepatuhan Pajak Belanja
            </h3>
            <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:10px;font-weight:700">PPN &amp; PPh</span>
          </div>

          <?php 
            $totPajak = $stats['pajak_sudah_setor'] + $stats['pajak_belum_setor'];
            $pctSetor = $totPajak > 0 ? round(($stats['pajak_sudah_setor'] / $totPajak) * 100) : 100;
          ?>
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:6px">
              <span style="color:#64748b">Penyetoran Kas Negara (NTPN):</span>
              <strong style="color:#059669;font-size:12px"><?= $pctSetor ?>% Tertib</strong>
            </div>
            <div style="height:8px;background:#fee2e2;border-radius:99px;overflow:hidden;display:flex">
              <div style="width:<?= $pctSetor ?>%;background:#059669;height:100%;transition:width 0.3s"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:11.5px">
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:8px 10px">
              <div style="color:#166534;font-size:10px;font-weight:800;text-transform:uppercase">SUDAH SETOR (NTPN)</div>
              <div style="color:#059669;font-size:13px;font-weight:800;margin-top:2px"><?= rupiah($stats['pajak_sudah_setor']) ?></div>
            </div>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 10px">
              <div style="color:#991b1b;font-size:10px;font-weight:800;text-transform:uppercase">BELUM SETOR (TERUTANG)</div>
              <div style="color:#dc2626;font-size:13px;font-weight:800;margin-top:2px"><?= rupiah($stats['pajak_belum_setor']) ?></div>
            </div>
          </div>
        </div>

        <!-- Widget 2: Pintasan Dokumen & Cetak Resmi (Solid & Clean Card) -->
        <div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:7px">
              <i class="fa-solid fa-print" style="color:#2563eb"></i> Pintasan Dokumen Audit
            </h3>
            <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:10px;font-weight:700">Format Rohil</span>
          </div>

          <div style="display:flex;flex-direction:column;gap:6px">
            <a href="<?= url('penugasan/spt') ?>" class="btn btn-ghost btn-sm" style="justify-content:space-between;padding:8px 10px;font-size:12px;border:1px solid #f1f5f9;border-radius:8px">
              <span style="display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-file-signature" style="color:#0f766e;width:14px"></i>
                <span style="font-weight:600;color:#1e293b">Surat Tugas (SPT) Rohil</span>
              </span>
              <i class="fa-solid fa-chevron-right" style="font-size:10px;color:#94a3b8"></i>
            </a>

            <a href="<?= url('sesi') ?>" class="btn btn-ghost btn-sm" style="justify-content:space-between;padding:8px 10px;font-size:12px;border:1px solid #f1f5f9;border-radius:8px">
              <span style="display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-list-check" style="color:#2563eb;width:14px"></i>
                <span style="font-weight:600;color:#1e293b">Matriks PKA (KM.6 BPKP)</span>
              </span>
              <i class="fa-solid fa-chevron-right" style="font-size:10px;color:#94a3b8"></i>
            </a>

            <a href="<?= url('temuan') ?>" class="btn btn-ghost btn-sm" style="justify-content:space-between;padding:8px 10px;font-size:12px;border:1px solid #f1f5f9;border-radius:8px">
              <span style="display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-file-circle-exclamation" style="color:#d97706;width:14px"></i>
                <span style="font-weight:600;color:#1e293b">Matriks Temuan 5 Unsur</span>
              </span>
              <i class="fa-solid fa-chevron-right" style="font-size:10px;color:#94a3b8"></i>
            </a>

            <a href="<?= url('lhp') ?>" class="btn btn-ghost btn-sm" style="justify-content:space-between;padding:8px 10px;font-size:12px;border:1px solid #f1f5f9;border-radius:8px">
              <span style="display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-file-shield" style="color:#16a34a;width:14px"></i>
                <span style="font-weight:600;color:#1e293b">Laporan Hasil Audit (LHP)</span>
              </span>
              <i class="fa-solid fa-chevron-right" style="font-size:10px;color:#94a3b8"></i>
            </a>
          </div>
        </div>

        <!-- Widget 3: Sebaran Bidang APBDesa -->
        <div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:7px">
              <i class="fa-solid fa-chart-pie" style="color:#6366f1"></i> Sebaran Sesi per Bidang
            </h3>
            <span class="badge" style="background:#f1f5f9;color:#475569;font-size:10px;font-weight:600">APBDes</span>
          </div>
          <div style="display:flex;flex-direction:column;gap:10px">
            <?php
              $jmlArr = array_column($perBidang, 'jumlah');
              $maxJml = $jmlArr ? max(1, max($jmlArr)) : 1;
              foreach ($perBidang as $b):
                $pct = round(((int)$b['jumlah'] / $maxJml) * 100);
            ?>
              <div>
                <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px">
                  <span style="color:#475569;font-weight:600"><?= e(mb_strimwidth($b['nama'],0,34,'…')) ?></span>
                  <span style="color:#0f172a;font-weight:700"><?= (int)$b['jumlah'] ?> sesi</span>
                </div>
                <div style="height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden">
                  <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,#3b82f6,#10b981);border-radius:99px"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
