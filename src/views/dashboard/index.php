<?php 
$title = 'Dashboard Operasional Pengawasan - E-LHP Inspektorat'; 
$user = $auth->user();
?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-dashboard">
  <?php partial('topbar', ['title' => 'Dashboard Operasional Pengawasan Terpadu', 'icon' => 'fa-solid fa-gauge-high']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <!-- ========================================== -->
    <!-- 0. ROLE SWITCHER DEMO TOOLBAR (KHUSUS ADMIN) -->
    <!-- ========================================== -->
    <?php if ($auth->isAdmin()): ?>
      <div style="background:linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);color:#fff;border-radius:12px;padding:12px 18px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 4px 12px rgba(49,46,129,0.25)">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;color:#fbbf24">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
          </div>
          <div>
            <div style="font-size:12.5px;font-weight:800;letter-spacing:0.3px;color:#fff">
              PRATINJAU MODE PERAN (Role Preview Demo)
            </div>
            <div style="font-size:11px;color:#c7d2fe">
              Simulasi tampilan dan modul operasional untuk setiap peran pengguna. Mode aktif: <strong style="color:#fde047"><?= strtoupper(str_replace('_', ' ', $activeRole)) ?></strong>
            </div>
          </div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
          <?php 
            $rolesList = [
              'admin'        => ['Admin', 'fa-crown'],
              'inspektur'    => ['Inspektur', 'fa-award'],
              'irban'        => ['Irban', 'fa-user-tie'],
              'dalnis'       => ['Dalnis', 'fa-user-check'],
              'ketua'        => ['Ketua Tim', 'fa-users'],
              'auditor'      => ['Auditor', 'fa-user-pen'],
              'operator_spt' => ['Perencanaan', 'fa-file-signature'],
              'operator_tl'  => ['Evlap (TLHP)', 'fa-clock-rotate-left'],
            ];
            foreach ($rolesList as $rk => [$rlabel, $ricon]):
              $isActiveBtn = ($activeRole === $rk);
          ?>
            <a href="<?= url('dashboard?view_as=' . $rk . ($filterTahun ? '&tahun=' . $filterTahun : '') . ($filterSemester ? '&semester=' . $filterSemester : '') . ($filterKecamatan ? '&kecamatan_id=' . $filterKecamatan : '')) ?>" 
               class="btn btn-sm" 
               style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:6px;text-decoration:none;transition:all 0.15s;background:<?= $isActiveBtn ? '#fbbf24' : 'rgba(255,255,255,0.12)' ?>;color:<?= $isActiveBtn ? '#1e1b4b' : '#ffffff' ?>;border:1px solid <?= $isActiveBtn ? '#f59e0b' : 'rgba(255,255,255,0.2)' ?>">
              <i class="fa-solid <?= $ricon ?>" style="margin-right:4px"></i> <?= $rlabel ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========================================== -->
    <!-- 1. TOP HEADER & FILTER BAR TERPADU (TAHUN, SEMESTER, WILAYAH) -->
    <!-- ========================================== -->
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 20px;margin-bottom:18px;box-shadow:0 1px 4px rgba(0,0,0,0.03)">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:14px">
        <div style="display:flex;align-items:center;gap:14px">
          <?php $dashAvatar = user_avatar_url($user); ?>
          <?php if ($dashAvatar): ?>
            <div style="position:relative;flex-shrink:0">
              <img src="<?= $dashAvatar ?>" alt="Foto Resmi" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2.5px solid <?= $activeRole === 'inspektur' ? '#d97706' : '#059669' ?>;box-shadow:0 3px 8px rgba(0,0,0,0.1)">
              <span style="position:absolute;bottom:1px;right:1px;width:12px;height:12px;border-radius:50%;background:#22c55e;border:2px solid #fff" title="Online Aktif"></span>
            </div>
          <?php endif; ?>
          <div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
              <h2 style="font-size:19px;font-weight:800;letter-spacing:-0.3px;color:#0f172a;margin:0">
                Selamat Datang, <?= e(sapaan_nama($user['nama'] ?? 'Auditor', $activeRole)) ?> 👋
              </h2>
              <span class="badge" style="background:<?= $activeRole === 'inspektur' ? '#fef3c7' : '#ecfdf5' ?>;color:<?= $activeRole === 'inspektur' ? '#92400e' : '#065f46' ?>;border:1px solid <?= $activeRole === 'inspektur' ? '#fde68a' : '#a7f3d0' ?>;font-size:11px;font-weight:700">
                <i class="fa-solid fa-shield-halved" style="margin-right:3px"></i> <?= e(ucwords(str_replace('_', ' ', $activeRole))) ?>
              </span>
            </div>
            <p style="color:#64748b;font-size:12px;margin-top:3px;margin-bottom:0">
              Pusat Komando Operasional Pengawasan E-LHP &mdash; Inspektorat Kabupaten Rokan Hilir.
            </p>
          </div>
        </div>

        <!-- Quick Action Buttons -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <?php if ($activeRole === 'inspektur'): ?>
            <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-sm btn-primary" style="background:#b45309;border:none;font-weight:700">
              <i class="fa-solid fa-pen-nib"></i> Lembar Disposisi
            </a>
            <a href="<?= url('lhp') ?>" class="btn btn-sm btn-outline" style="border-color:#0f766e;color:#0f766e;font-weight:700">
              <i class="fa-solid fa-file-shield"></i> Laporan LHP
            </a>
          <?php elseif ($activeRole === 'operator_spt'): ?>
            <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm btn-primary" style="background:#059669;border:none;font-weight:700">
              <i class="fa-solid fa-file-signature"></i> Antrean SPT
            </a>
          <?php elseif ($activeRole === 'operator_tl'): ?>
            <a href="<?= url('tlhp') ?>" class="btn btn-sm btn-primary" style="background:#059669;border:none;font-weight:700">
              <i class="fa-solid fa-clock-rotate-left"></i> Monitoring TLHP
            </a>
          <?php else: ?>
            <a href="<?= url('sesi') ?>" class="btn btn-sm btn-primary" style="font-weight:700">
              <i class="fa-solid fa-clipboard-list"></i> Kertas Kerja (KKA)
            </a>
            <a href="<?= url('penugasan/nota-dinas/create') ?>" class="btn btn-sm btn-outline" style="border-color:#2563eb;color:#2563eb;font-weight:700">
              <i class="fa-solid fa-envelope-open-text"></i> Usul Tim (ND)
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- FILTER BAR: TAHUN, SEMESTER, KECAMATAN, DESA -->
      <form method="GET" action="<?= url('dashboard') ?>" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px">
        <?php if (!empty($viewAs)): ?>
          <input type="hidden" name="view_as" value="<?= e($viewAs) ?>">
        <?php endif; ?>

        <div style="display:flex;align-items:center;gap:6px">
          <i class="fa-solid fa-filter" style="color:#0284c7;font-size:13px"></i>
          <span style="font-size:12px;font-weight:700;color:#334155">Filter Pengawasan:</span>
        </div>

        <!-- Filter Tahun Anggaran -->
        <div style="display:flex;align-items:center;gap:4px">
          <label style="font-size:11.5px;color:#64748b;font-weight:600">Tahun:</label>
          <select name="tahun" style="font-size:12px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#0f172a;font-weight:600" onchange="this.form.submit()">
            <option value="">Semua Tahun</option>
            <?php foreach ($daftarTahun as $thn): ?>
              <option value="<?= $thn ?>" <?= ($filterTahun == $thn) ? 'selected' : '' ?>>TA <?= $thn ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Semester -->
        <div style="display:flex;align-items:center;gap:4px">
          <label style="font-size:11.5px;color:#64748b;font-weight:600">Semester:</label>
          <select name="semester" style="font-size:12px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#0f172a;font-weight:600" onchange="this.form.submit()">
            <option value="">Semua Semester</option>
            <option value="1" <?= ($filterSemester === '1') ? 'selected' : '' ?>>Semester I</option>
            <option value="2" <?= ($filterSemester === '2') ? 'selected' : '' ?>>Semester II</option>
          </select>
        </div>

        <!-- Filter Kecamatan -->
        <div style="display:flex;align-items:center;gap:4px">
          <label style="font-size:11.5px;color:#64748b;font-weight:600">Kecamatan:</label>
          <select name="kecamatan_id" style="font-size:12px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#0f172a;max-width:180px" onchange="this.form.submit()">
            <option value="0">Semua Kecamatan</option>
            <?php foreach ($daftarKecamatan as $kec): ?>
              <option value="<?= $kec['id'] ?>" <?= ($filterKecamatan == $kec['id']) ? 'selected' : '' ?>><?= e($kec['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Desa -->
        <div style="display:flex;align-items:center;gap:4px">
          <label style="font-size:11.5px;color:#64748b;font-weight:600">Desa:</label>
          <select name="desa_id" style="font-size:12px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#0f172a;max-width:180px" onchange="this.form.submit()">
            <option value="0">Semua Kepenghuluan</option>
            <?php foreach ($daftarDesa as $des): ?>
              <option value="<?= $des['id'] ?>" <?= ($filterDesa == $des['id']) ? 'selected' : '' ?>><?= e($des['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-sm" style="background:#0284c7;color:#fff;border:none;font-weight:700;padding:4px 10px;border-radius:6px">
          <i class="fa-solid fa-magnifying-glass"></i> Terapkan
        </button>

        <?php if ($filterTahun !== '' || $filterSemester !== '' || $filterKecamatan > 0 || $filterDesa > 0): ?>
          <a href="<?= url('dashboard' . (!empty($viewAs) ? '?view_as=' . $viewAs : '')) ?>" class="btn btn-sm" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-weight:600;padding:4px 8px;border-radius:6px;text-decoration:none">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </a>
        <?php endif; ?>
      </form>
    </div>

    <!-- ========================================== -->
    <!-- 2. MODUL OPERASIONAL SPESIFIK PERAN (HERO COMMAND SECTION) -->
    <!-- ========================================== -->

    <!-- A. MODUL OPERASIONAL: ANGGOTA TIM (AUDITOR) -> TUGAS SAYA HARI INI -->
    <?php if ($activeRole === 'auditor'): ?>
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #2563eb;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-list-check" style="color:#2563eb"></i>
              TUGAS SAYA HARI INI (Action Center Auditor)
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Fokus penyelesaian KKA aktif, verifikasi fisik lapangan, kuitansi belum lengkap, dan kepatuhan pajak.</p>
          </div>
          <a href="<?= url('sesi/create') ?>" class="btn btn-sm btn-primary" style="font-weight:700">
            <i class="fa-solid fa-plus"></i> Tambah Sesi KKA
          </a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:12px">
          <!-- Widget A1: Sesi Aktif Dikerjakan -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#1e40af;text-transform:uppercase">
                <i class="fa-solid fa-folder-open" style="margin-right:4px"></i> Sesi KKA Aktif
              </span>
              <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:10px"><?= count($auditorData['sesi_aktif'] ?? []) ?> Sesi</span>
            </div>
            <?php if (empty($auditorData['sesi_aktif'])): ?>
              <div style="font-size:12px;color:#94a3b8;padding:10px 0;text-align:center">Tidak ada sesi KKA aktif saat ini.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($auditorData['sesi_aktif'], 0, 3) as $sa): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($sa['desa_nama']) ?> &bull; <span style="font-weight:600;color:#475569"><?= e($sa['bidang_nama']) ?></span></div>
                      <div style="font-size:11px;color:#64748b"><?= (int)$sa['total_rincian'] ?> rincian belanja &bull; Status: <span style="color:#0284c7;font-weight:600"><?= e($sa['status']) ?></span></div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $sa['id']) ?>" class="btn btn-sm btn-outline" style="padding:2px 8px;font-size:11px;font-weight:700">Buka</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Widget A2: Belanja Butuh Uji Fisik Lapangan -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase">
                <i class="fa-solid fa-ruler-combined" style="margin-right:4px"></i> Belum Uji Fisik
              </span>
              <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px"><?= count($auditorData['belanja_belum_fisik'] ?? []) ?> Item</span>
            </div>
            <?php if (empty($auditorData['belanja_belum_fisik'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Semua uji fisik telah terverifikasi!</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($auditorData['belanja_belum_fisik'], 0, 3) as $bf): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div style="max-width:70%">
                      <div style="font-size:12px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($bf['uraian']) ?></div>
                      <div style="font-size:11px;color:#64748b"><?= e($bf['desa_nama']) ?> &bull; <?= rupiah($bf['biaya_dikwitansi']) ?></div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $bf['sesi_id']) ?>" class="btn btn-sm" style="background:#f59e0b;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">Periksa</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Widget A3: Pajak Belum Setor -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#dc2626;text-transform:uppercase">
                <i class="fa-solid fa-receipt" style="margin-right:4px"></i> Pajak Belum Disetor
              </span>
              <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:10px"><?= count($auditorData['pajak_belum_setor'] ?? []) ?> Item</span>
            </div>
            <?php if (empty($auditorData['pajak_belum_setor'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Seluruh pajak belanja sudah tuntas disetor!</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($auditorData['pajak_belum_setor'], 0, 3) as $pbs): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div style="max-width:70%">
                      <div style="font-size:12px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($pbs['uraian']) ?></div>
                      <div style="font-size:11px;color:#dc2626;font-weight:700"><?= rupiah($pbs['total_pajak']) ?> &bull; <?= e($pbs['desa_nama']) ?></div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $pbs['sesi_id']) ?>" class="btn btn-sm btn-outline" style="border-color:#dc2626;color:#dc2626;padding:2px 8px;font-size:11px;font-weight:700">Tagih</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- B. MODUL OPERASIONAL: INSPEKTUR -> PUSAT KOMANDO 5 IRBAN, ANTREAN DISPOSISI & PERINGATAN WAKTU -->
    <?php if ($activeRole === 'inspektur' || $activeRole === 'admin'): ?>
      
      <!-- 1. RADAR MONITORING 5 IRBAN (SESUAI STRUKTUR POHON PENGAWASAN) -->
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #d97706;border-radius:14px;padding:18px 20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px">
          <div>
            <h3 style="margin:0;font-size:16px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-sitemap" style="color:#d97706"></i>
              RADAR MONITORING 5 IRBAN &bull; KABUPATEN ROKAN HILIR
            </h3>
            <p style="margin:3px 0 0;font-size:12px;color:#64748b">
              Pemantauan berjenjang progres seluruh tim di bawah kendali Inspektur Pembantu I s.d. V secara langsung.
            </p>
          </div>
          <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
            <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:11px;font-weight:700">
              🟢 <?= (int)($warningCounts['aman'] ?? 0) ?> Tepat Waktu
            </span>
            <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:11px;font-weight:700">
              🟡 <?= (int)($warningCounts['waspada'] ?? 0) ?> Waspada (H-3)
            </span>
            <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-size:11px;font-weight:700">
              🔴 <?= (int)($warningCounts['overdue'] ?? 0) ?> Lewat Batas (Overdue)
            </span>
          </div>
        </div>

        <!-- GRID 5 IRBAN (Responsive 1-5 Kolom) -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:12px;margin-bottom:16px">
          <?php foreach ($irbanRadar as $irId => $ir): ?>
            <div style="background:<?= $ir['bg'] ?>;border:1.5px solid <?= $ir['border'] ?>;border-radius:10px;padding:12px 14px;display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.15s">
              <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                  <span style="font-size:12px;font-weight:800;color:<?= $ir['warna'] ?>;letter-spacing:0.5px">
                    <i class="fa-solid fa-user-shield"></i> IRBAN <?= $ir['no'] ?>
                  </span>
                  <span class="badge" style="background:#fff;color:<?= $ir['warna'] ?>;border:1px solid <?= $ir['border'] ?>;font-size:10px;font-weight:700">
                    <?= (int)$ir['total_spt'] ?> SPT
                  </span>
                </div>
                <div style="font-size:11.5px;font-weight:700;color:#0f172a;line-height:1.3;margin-bottom:2px">
                  <?= e($ir['nama']) ?>
                </div>
                <div style="font-size:10.5px;color:#64748b;margin-bottom:10px">
                  <?= e($ir['jabatan']) ?>
                </div>
              </div>

              <div style="border-top:1px dashed <?= $ir['border'] ?>;padding-top:8px">
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;margin-bottom:4px">
                  <span style="color:#64748b">Berjalan:</span>
                  <strong style="color:#0f172a"><?= (int)$ir['berjalan'] ?> Tim</strong>
                </div>
                <div style="display:flex;gap:4px;flex-wrap:wrap">
                  <?php if ($ir['overdue'] > 0): ?>
                    <span style="font-size:10px;background:#fee2e2;color:#991b1b;padding:1px 6px;border-radius:4px;font-weight:700">
                      🔴 <?= $ir['overdue'] ?> Terlambat
                    </span>
                  <?php endif; ?>
                  <?php if ($ir['waspada'] > 0): ?>
                    <span style="font-size:10px;background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:4px;font-weight:700">
                      🟡 <?= $ir['waspada'] ?> H-3
                    </span>
                  <?php endif; ?>
                  <?php if ($ir['overdue'] === 0 && $ir['waspada'] === 0): ?>
                    <span style="font-size:10px;background:#ecfdf5;color:#065f46;padding:1px 6px;border-radius:4px;font-weight:700">
                      🟢 Semua Aman
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. AGENDA KEPUTUSAN & DISPOSISI PIMPINAN (MEJA TTE INSPEKTUR) -->
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #0f766e;border-radius:14px;padding:18px 20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-stamp" style="color:#0f766e"></i>
              MEJA DISPOSISI &amp; TTE DIGITAL INSPEKTUR
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Nota Dinas usulan tim, pengesahan SPT penugasan, dan pengesahan naskah LHP definitif.</p>
          </div>
          <span class="badge" style="background:#ccfbf1;color:#0f766e;border:1px solid #99f6e4;font-size:11px;font-weight:700">
            <i class="fa-solid fa-pen-nib"></i> Otoritas Utama
          </span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:14px">
          <!-- Kolom 1: Usulan Nota Dinas Menunggu Disposisi -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase">
                <i class="fa-solid fa-envelope-open-text" style="margin-right:4px"></i> Usulan Nota Dinas
              </span>
              <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px"><?= count($inspekturData['pending_nd'] ?? []) ?> Menunggu</span>
            </div>
            <?php if (empty($inspekturData['pending_nd'])): ?>
              <div style="font-size:12px;color:#059669;padding:12px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Tidak ada Nota Dinas menunggu disposisi.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach ($inspekturData['pending_nd'] as $pnd): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($pnd['desa_nama']) ?> &bull; <?= e($pnd['kecamatan_nama']) ?></div>
                      <div style="font-size:11px;color:#64748b"><?= e($pnd['no_nd']) ?> &bull; Ketua: <?= e($pnd['ketua_tim_nama']) ?></div>
                    </div>
                    <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-sm" style="background:#b45309;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">Disposisi</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Kolom 2: Surat Tugas (SPT) Siap TTE -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#0f766e;text-transform:uppercase">
                <i class="fa-solid fa-file-signature" style="margin-right:4px"></i> SPT Menunggu Sahkan (TTE)
              </span>
              <span class="badge" style="background:#ccfbf1;color:#0f766e;font-size:10px"><?= count($inspekturData['pending_spt'] ?? []) ?> Menunggu</span>
            </div>
            <?php if (empty($inspekturData['pending_spt'])): ?>
              <div style="font-size:12px;color:#059669;padding:12px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Seluruh Surat Tugas telah disahkan digital.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach ($inspekturData['pending_spt'] as $pspt): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($pspt['desa_nama']) ?> &bull; <?= (int)$pspt['lama_hari'] ?> Hari</div>
                      <div style="font-size:11px;color:#64748b"><?= e($pspt['no_spt'] ?: 'DRAFT') ?> &bull; <?= e($pspt['ketua_tim_nama']) ?></div>
                    </div>
                    <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm" style="background:#0f766e;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">TTE</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Kolom 3: P2HP / BA Kesepakatan Menunggu Telaah Pra-Ekspose -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase">
                <i class="fa-solid fa-file-circle-check" style="margin-right:4px"></i> P2HP / BA Pra-Ekspose
              </span>
              <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:10px"><?= count($inspekturData['pending_nhp'] ?? []) ?> Usulan</span>
            </div>
            <?php if (empty($inspekturData['pending_nhp'])): ?>
              <div style="font-size:12px;color:#059669;padding:12px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Seluruh P2HP/BA telah ditelaah.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach ($inspekturData['pending_nhp'] as $pnhp): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($pnhp['desa_nama']) ?> (TA <?= (int)$pnhp['tahun_anggaran'] ?>)</div>
                      <div style="font-size:11px;color:#64748b"><?= (int)$pnhp['jml_temuan'] ?> Temuan &bull; <?= rupiah($pnhp['total_nominal']) ?></div>
                    </div>
                    <a href="<?= url('temuan?desa_id=' . $pnhp['desa_id'] . '&tahun=' . $pnhp['tahun_anggaran']) ?>" class="btn btn-sm" style="background:#0284c7;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">Telaah</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Kolom 4: Naskah LHP Siap Disahkan -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#15803d;text-transform:uppercase">
                <i class="fa-solid fa-file-shield" style="margin-right:4px"></i> Naskah LHP Siap Sah
              </span>
              <span class="badge" style="background:#dcfce7;color:#15803d;font-size:10px"><?= count($inspekturData['pending_lhp'] ?? []) ?> Naskah</span>
            </div>
            <?php if (empty($inspekturData['pending_lhp'])): ?>
              <div style="font-size:12px;color:#059669;padding:12px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Tidak ada naskah LHP tertunda.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach ($inspekturData['pending_lhp'] as $plhp): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($plhp['desa_nama']) ?> (TA <?= (int)$plhp['tahun_anggaran'] ?>)</div>
                      <div style="font-size:11px;color:#64748b">Dalnis: <?= e($plhp['dalnis_nama'] ?: '-') ?> &bull; Status: <?= e($plhp['status_lhp']) ?></div>
                    </div>
                    <a href="<?= url('lhp') ?>" class="btn btn-sm" style="background:#15803d;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">Sahkan</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- 3. RADAR COUNTDOWN PERINGATAN HARI PENUGASAN (PEMANTAUAN SELURUH TIM LAPANGAN - MOBILE FRIENDLY) -->
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:18px 20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-stopwatch" style="color:#2563eb"></i>
              RADAR STATUS HARI PENUGASAN &amp; PROGRESS TIM LAPANGAN
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">
              Monitoring sisa waktu hari kerja SPT dan peringatan keterlambatan (Overdue) seluruh tim lapangan se-Kabupaten.
            </p>
          </div>
          <span style="font-size:11px;color:#64748b;font-style:italic">
            Real-time per <?= tgl_id(date('Y-m-d')) ?>
          </span>
        </div>

        <?php if (empty($sptMonitoring)): ?>
          <div style="padding:24px;text-align:center;color:#64748b;font-size:13px">
            <i class="fa-solid fa-clipboard-check" style="font-size:32px;color:#cbd5e1;margin-bottom:8px;display:block"></i>
            Belum ada Surat Tugas penugasan yang diterbitkan.
          </div>
        <?php else: ?>
          <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
            <table class="table" style="width:100%;font-size:12.5px;border-collapse:collapse">
              <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;text-align:left">
                  <th style="padding:10px 12px;font-weight:700;color:#334155">No SPT &amp; Lokasi</th>
                  <th style="padding:10px 12px;font-weight:700;color:#334155">Susunan Tim (Irban / Dalnis / Ketua)</th>
                  <th style="padding:10px 12px;font-weight:700;color:#334155">Masa Tugas &amp; Peringatan</th>
                  <th style="padding:10px 12px;font-weight:700;color:#334155">Status Audit</th>
                  <th style="padding:10px 12px;font-weight:700;color:#334155;text-align:right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sptMonitoring as $sm): ?>
                  <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s">
                    <td style="padding:10px 12px;vertical-align:middle">
                      <div style="font-weight:800;color:#0f172a"><?= e($sm['desa_nama']) ?></div>
                      <div style="font-size:11px;color:#64748b">Kec. <?= e($sm['kecamatan_nama']) ?></div>
                      <div style="font-size:10.5px;color:#0284c7;font-weight:600;margin-top:2px"><?= e($sm['no_spt']) ?></div>
                      <?php if (!empty($sm['kendala_lapangan'])): ?>
                        <div style="margin-top:5px;font-size:11px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;padding:3px 6px;line-height:1.3" title="<?= e($sm['kendala_lapangan']) ?>">
                          <i class="fa-solid fa-triangle-exclamation"></i> <b>Kendala:</b> <?= e(mb_strimwidth($sm['kendala_lapangan'], 0, 45, '...')) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;vertical-align:middle">
                      <div style="font-size:12px;font-weight:700;color:#0f172a">
                        <i class="fa-solid fa-user-tie" style="color:#d97706;width:14px"></i> <?= e($sm['wakil_pj_nama'] ?: 'Irban') ?>
                      </div>
                      <div style="font-size:11px;color:#475569">
                        <i class="fa-solid fa-user-check" style="color:#0284c7;width:14px"></i> Dalnis: <?= e($sm['dalnis_nama'] ?: '-') ?>
                      </div>
                      <div style="font-size:11px;color:#64748b">
                        <i class="fa-solid fa-user-pen" style="color:#10b981;width:14px"></i> Ketua: <?= e($sm['ketua_tim_nama'] ?: '-') ?>
                      </div>
                    </td>
                    <td style="padding:10px 12px;vertical-align:middle">
                      <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                        <span class="badge" style="background:<?= $sm['badge_bg'] ?>;color:<?= $sm['badge_color'] ?>;font-weight:800;font-size:11px;padding:3px 8px;border-radius:6px">
                          <?= $sm['status_waktu'] === 'OVERDUE' ? '🚨 ' : ($sm['status_waktu'] === 'WASPADA' ? '⚠️ ' : '🟢 ') ?>
                          <?= e($sm['label_waktu']) ?>
                        </span>
                      </div>
                      <div style="font-size:11px;color:#64748b">
                        Tgl SPT: <?= !empty($sm['tgl_spt']) ? tgl_id($sm['tgl_spt']) : '-' ?> (<?= (int)$sm['lama_hari'] ?> Hari)
                      </div>
                    </td>
                    <td style="padding:10px 12px;vertical-align:middle">
                      <span class="badge" style="background:<?= $sm['status'] === 'SELESAI' ? '#ecfdf5' : '#eff6ff' ?>;color:<?= $sm['status'] === 'SELESAI' ? '#065f46' : '#1e40af' ?>;font-weight:700;font-size:11px">
                        <?= e($sm['status']) ?>
                      </span>
                      <div style="font-size:10.5px;color:#64748b;margin-top:2px">
                        <?= (int)$sm['jml_sesi_kka'] ?> Sesi KKA &bull; <?= (int)$sm['jml_temuan'] ?> Temuan
                      </div>
                    </td>
                    <td style="padding:10px 12px;vertical-align:middle;text-align:right">
                      <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm btn-outline-primary" style="font-size:11px;font-weight:700;padding:3px 8px">
                        <i class="fa-solid fa-eye"></i> Detail
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    <?php endif; ?>

    <!-- C. MODUL OPERASIONAL: DALNIS -> REVIEW KKA & SOP KENDALI MUTU -->
    <?php if ($activeRole === 'dalnis'): ?>
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #0284c7;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-user-check" style="color:#0284c7"></i>
              KENDALI MUTU PENGAWASAN &amp; REVIEW TEKNIS (Dalnis)
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Antrean review berjenjang, revisi terbuka, dan checklist kepatuhan SOP pemeriksaan fisik &amp; pajak.</p>
          </div>
          <div style="display:flex;gap:6px">
            <span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:700">
              <i class="fa-solid fa-list-ol"></i> SOP Standar SPKN
            </span>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:12px">
          <!-- Antrean Review Sesi KKA -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#0369a1;text-transform:uppercase">
                <i class="fa-solid fa-inbox" style="margin-right:4px"></i> Menunggu Review Dalnis
              </span>
              <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:10px"><?= count($dalnisData['antrean_review'] ?? []) ?> Sesi</span>
            </div>
            <?php if (empty($dalnisData['antrean_review'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Tidak ada antrean review saat ini.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($dalnisData['antrean_review'], 0, 3) as $ar): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($ar['desa_nama']) ?> &bull; <?= e($ar['bidang_nama']) ?></div>
                      <div style="font-size:11px;color:#64748b"><?= (int)$ar['total_rincian'] ?> rincian &bull; Oleh: <?= e($ar['dibuat_oleh'] ?: 'Auditor') ?></div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $ar['id']) ?>" class="btn btn-sm btn-primary" style="padding:2px 8px;font-size:11px;font-weight:700">Review</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Revisi Terbuka yang Dikembalikan ke Tim -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase">
                <i class="fa-solid fa-rotate" style="margin-right:4px"></i> Catatan Revisi Terbuka
              </span>
              <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px"><?= count($dalnisData['revisi_terbuka'] ?? []) ?> Perlu Revisi</span>
            </div>
            <?php if (empty($dalnisData['revisi_terbuka'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Semua revisi telah diselesaikan tim.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($dalnisData['revisi_terbuka'], 0, 3) as $rt): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div style="max-width:70%">
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($rt['desa_nama']) ?> &bull; <?= e($rt['bidang_nama']) ?></div>
                      <div style="font-size:11px;color:#b45309;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($rt['catatan_reviu_dalnis'] ?: 'Perbaiki bukti fisik dan rincian belanja') ?></div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $rt['id']) ?>" class="btn btn-sm btn-outline" style="border-color:#b45309;color:#b45309;padding:2px 8px;font-size:11px;font-weight:700">Cek</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- SOP QA Metrics Checklist -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <span style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;display:block;margin-bottom:8px">
              <i class="fa-solid fa-shield-halved" style="margin-right:4px"></i> Checklist Kendali Mutu
            </span>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:12px">
              <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dashed #e2e8f0">
                <span style="color:#64748b">Belanja tanpa kuitansi:</span>
                <strong style="color:<?= ($dalnisData['qa_checklist']['tanpa_kwitansi'] ?? 0) > 0 ? '#b45309' : '#059669' ?>">
                  <?= (int)($dalnisData['qa_checklist']['tanpa_kwitansi'] ?? 0) ?> item
                </strong>
              </div>
              <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dashed #e2e8f0">
                <span style="color:#64748b">Selisih fisik belum tuntas:</span>
                <strong style="color:<?= ($dalnisData['qa_checklist']['selisih_fisik'] ?? 0) > 0 ? '#dc2626' : '#059669' ?>">
                  <?= (int)($dalnisData['qa_checklist']['selisih_fisik'] ?? 0) ?> item
                </strong>
              </div>
              <div style="display:flex;justify-content:space-between;padding:4px 0">
                <span style="color:#64748b">Pajak belanja belum disetor:</span>
                <strong style="color:<?= ($dalnisData['qa_checklist']['pajak_nunggak'] ?? 0) > 0 ? '#dc2626' : '#059669' ?>">
                  <?= (int)($dalnisData['qa_checklist']['pajak_nunggak'] ?? 0) ?> item
                </strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- D. MODUL OPERASIONAL: KETUA TIM -> RUANG KENDALI TIM -->
    <?php if ($activeRole === 'ketua'): ?>
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #059669;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-users" style="color:#059669"></i>
              RUANG KENDALI TIM AUDIT (Ketua Tim)
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Monitoring progres KKA per Bidang (1-5), kesiapan pengajuan review ke Dalnis, dan supervisi anggota tim.</p>
          </div>
          <a href="<?= url('sesi/create') ?>" class="btn btn-sm btn-primary" style="background:#059669;border:none;font-weight:700">
            <i class="fa-solid fa-plus"></i> Sesi Tim Baru
          </a>
        </div>

        <?php 
          // Ambil SPT aktif ketua tim
          $ketuaSptAktif = null;
          foreach ($sptMonitoring as $sm) {
            if ($sm['status'] !== 'SELESAI') {
              $ketuaSptAktif = $sm;
              break;
            }
          }
          if ($ketuaSptAktif):
        ?>
          <!-- PERINGATAN MASA TUGAS LAPANGAN (COUNTDOWN ALERT) -->
          <div style="background:<?= $ketuaSptAktif['badge_bg'] ?>;border:1.5px solid <?= $ketuaSptAktif['badge_color'] ?>;border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div style="display:flex;align-items:center;gap:12px">
              <div style="font-size:24px">
                <?= $ketuaSptAktif['status_waktu'] === 'OVERDUE' ? '🚨' : ($ketuaSptAktif['status_waktu'] === 'WASPADA' ? '⚠️' : '🟢') ?>
              </div>
              <div>
                <div style="font-size:13px;font-weight:800;color:<?= $ketuaSptAktif['badge_color'] ?>">
                  <?= $ketuaSptAktif['status_waktu'] === 'OVERDUE' ? 'PERINGATAN KRITIS: MASA TUGAS LAPANGAN TELAH BERAKHIR' : ($ketuaSptAktif['status_waktu'] === 'WASPADA' ? 'PERINGATAN: MENDEKATI BATAS WAKTU PENUGASAN' : 'STATUS PENUGASAN LAPANGAN AKTIF') ?>
                </div>
                <div style="font-size:12px;color:#334155;margin-top:2px">
                  Surat Tugas: <b><?= e($ketuaSptAktif['no_spt']) ?></b> &bull; Desa <b><?= e($ketuaSptAktif['desa_nama']) ?></b> &bull;
                  <b><?= e($ketuaSptAktif['label_waktu']) ?></b> (Batas: <?= tgl_id($ketuaSptAktif['tgl_selesai_est']) ?>)
                </div>
              </div>
            </div>
            <div style="display:flex;gap:6px">
              <a href="<?= url('sesi') ?>" class="btn btn-sm" style="background:#fff;border:1px solid <?= $ketuaSptAktif['badge_color'] ?>;color:<?= $ketuaSptAktif['badge_color'] ?>;font-weight:700">
                <i class="fa-solid fa-folder-open"></i> Buka KKA
              </a>
              <a href="<?= url('temuan') ?>" class="btn btn-sm" style="background:<?= $ketuaSptAktif['badge_color'] ?>;color:#fff;font-weight:700;border:none">
                <i class="fa-solid fa-file-circle-exclamation"></i> Buat Temuan / BA
              </a>
            </div>
          </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:12px">
          <!-- Sesi Tim Sedang Berjalan -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#047857;text-transform:uppercase">
                <i class="fa-solid fa-list-check" style="margin-right:4px"></i> Progres Sesi Tim
              </span>
              <span class="badge" style="background:#d1fae5;color:#065f46;font-size:10px"><?= count($ketuaData['sesi_tim'] ?? []) ?> Sesi</span>
            </div>
            <?php if (empty($ketuaData['sesi_tim'])): ?>
              <div style="font-size:12px;color:#94a3b8;padding:10px 0;text-align:center">Belum ada sesi penugasan aktif tim.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($ketuaData['sesi_tim'], 0, 3) as $st): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($st['desa_nama']) ?> &bull; <?= e($st['bidang_nama']) ?></div>
                      <div style="font-size:11px;color:#64748b"><?= (int)$st['rincian_valid'] ?>/<?= (int)$st['total_rincian'] ?> uji fisik selesai</div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $st['id']) ?>" class="btn btn-sm btn-outline" style="padding:2px 8px;font-size:11px;font-weight:700">Supervisi</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Siap Diajukan ke Dalnis -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase">
                <i class="fa-solid fa-paper-plane" style="margin-right:4px"></i> Siap Ajukan ke Dalnis
              </span>
              <span class="badge" style="background:#e0f2fe;color:#0284c7;font-size:10px"><?= count($ketuaData['siap_review'] ?? []) ?> Siap</span>
            </div>
            <?php if (empty($ketuaData['siap_review'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Belum ada sesi tertunda untuk diajukan review.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($ketuaData['siap_review'], 0, 3) as $sr): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($sr['desa_nama']) ?> &bull; <?= e($sr['bidang_nama']) ?></div>
                      <div style="font-size:11px;color:#64748b">Lengkap rincian &bull; Siap kendali mutu</div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $sr['id']) ?>" class="btn btn-sm" style="background:#0284c7;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">Ajukan</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- E. MODUL OPERASIONAL: IRBAN -> PENGAWASAN WILAYAH & SESI STAGNAN -->
    <?php if ($activeRole === 'irban'): ?>
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #8b5cf6;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-user-tie" style="color:#8b5cf6"></i>
              SUPERVISI WILAYAH PENGAWASAN (Inspektur Pembantu)
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Deteksi KKA stagnan (> 7 hari tidak diperbarui), telaah LHP tingkat Irban, dan pengendalian mutu wilayah.</p>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:12px">
          <!-- Peringatan KKA Stagnan > 7 Hari -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#dc2626;text-transform:uppercase">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:4px"></i> Sesi KKA Stagnan (> 7 Hari)
              </span>
              <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:10px"><?= count($irbanData['sesi_stagnan'] ?? []) ?> Sesi</span>
            </div>
            <?php if (empty($irbanData['sesi_stagnan'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Luar biasa! Tidak ada sesi KKA yang terlantar.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($irbanData['sesi_stagnan'], 0, 3) as $ss): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($ss['desa_nama']) ?> &bull; <?= e($ss['bidang_nama']) ?></div>
                      <div style="font-size:11px;color:#dc2626;font-weight:700"><?= (int)$ss['hari_terhenti'] ?> hari tidak ada aktivitas</div>
                    </div>
                    <a href="<?= url('sesi/show?id=' . $ss['id']) ?>" class="btn btn-sm btn-outline" style="border-color:#dc2626;color:#dc2626;padding:2px 8px;font-size:11px;font-weight:700">Tegur</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Naskah LHP Menunggu Telaah Irban -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#6b21a8;text-transform:uppercase">
                <i class="fa-solid fa-file-contract" style="margin-right:4px"></i> Telaah Naskah LHP
              </span>
              <span class="badge" style="background:#f3e8ff;color:#6b21a8;font-size:10px"><?= count($irbanData['telaah_lhp'] ?? []) ?> Naskah</span>
            </div>
            <?php if (empty($irbanData['telaah_lhp'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Tidak ada naskah LHP menunggu telaah Irban.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($irbanData['telaah_lhp'], 0, 3) as $tl): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($tl['desa_nama']) ?> &bull; <?= e($tl['kecamatan_nama']) ?></div>
                      <div style="font-size:11px;color:#64748b">Siap paraf telaah sebelum diajukan ke Inspektur</div>
                    </div>
                    <a href="<?= url('lhp') ?>" class="btn btn-sm" style="background:#8b5cf6;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">Telaah</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- F. MODUL OPERASIONAL: OPERATOR SPT (PERENCANAAN) -->
    <?php if ($activeRole === 'operator_spt'): ?>
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #0f766e;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-file-signature" style="color:#0f766e"></i>
              PERENCANAAN &amp; PENERBITAN SURAT TUGAS (Operator SPT)
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Antrean penerbitan SPT dari Nota Dinas yang disetujui, kontrol nomor terakhir, dan jadwal audit.</p>
          </div>
          <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm" style="background:#0f766e;color:#fff;border:none;font-weight:700">
            <i class="fa-solid fa-stamp"></i> Antrean Lengkap SPT
          </a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:12px">
          <!-- Antrean Penerbitan SPT -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#0f766e;text-transform:uppercase">
                <i class="fa-solid fa-envelope-circle-check" style="margin-right:4px"></i> ND Disetujui &bull; Siap Terbit SPT
              </span>
              <span class="badge" style="background:#ccfbf1;color:#0f766e;font-size:10px"><?= count($sptData['ready_nd'] ?? []) ?> Menunggu</span>
            </div>
            <?php if (empty($sptData['ready_nd'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Semua Nota Dinas telah diterbitkan Surat Tugas.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($sptData['ready_nd'], 0, 3) as $rnd): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($rnd['desa_nama']) ?> &bull; <?= e($rnd['kecamatan_nama']) ?></div>
                      <div style="font-size:11px;color:#64748b"><?= e($rnd['no_nd']) ?> &bull; Ketua: <?= e($rnd['ketua_tim_nama']) ?></div>
                    </div>
                    <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm" style="background:#0f766e;color:#fff;border:none;padding:2px 8px;font-size:11px;font-weight:700">Terbitkan</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Kontrol Nomor SPT Terakhir -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <span style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;display:block;margin-bottom:8px">
              <i class="fa-solid fa-hashtag" style="margin-right:4px"></i> Kontrol Nomor SPT Terakhir (Anti-Ganda)
            </span>
            <?php if (!empty($sptData['last_spt'])): ?>
              <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px">
                <div style="font-size:13px;font-weight:800;color:#0f766e"><?= e($sptData['last_spt']['no_spt']) ?></div>
                <div style="font-size:11.5px;color:#64748b;margin-top:2px">
                  Tujuan: <strong><?= e($sptData['last_spt']['desa_nama']) ?></strong> &bull; Tgl: <?= date('d/m/Y', strtotime($sptData['last_spt']['tgl_spt'])) ?>
                </div>
              </div>
            <?php else: ?>
              <div style="font-size:12px;color:#94a3b8;text-align:center;padding:10px 0">Belum ada SPT terbit di sistem.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- G. MODUL OPERASIONAL: OPERATOR TLHP (EVLAP) -->
    <?php if ($activeRole === 'operator_tl'): ?>
      <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:5px solid #16a34a;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-clock-rotate-left" style="color:#16a34a"></i>
              MONITORING TINDAK LANJUT HASIL PENGAWASAN (TLHP 60 Hari)
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Pemantauan kepatuhan rekomendasi, batas 60 hari kalender, dan verifikasi bukti setor (STS Bank) pemulihan kas.</p>
          </div>
          <a href="<?= url('tlhp') ?>" class="btn btn-sm" style="background:#16a34a;color:#fff;border:none;font-weight:700">
            <i class="fa-solid fa-eye"></i> Buka Matriks TLHP
          </a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:12px">
          <!-- Rekap Status 60 Hari -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <span style="font-size:11px;font-weight:700;color:#15803d;text-transform:uppercase;display:block;margin-bottom:8px">
              <i class="fa-solid fa-chart-pie" style="margin-right:4px"></i> Status Rekomendasi
            </span>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;text-align:center">
              <div style="background:#dcfce7;border-radius:6px;padding:6px">
                <div style="font-size:16px;font-weight:800;color:#166534"><?= (int)($tlhpData['summary']['jml_tuntas'] ?? 0) ?></div>
                <div style="font-size:10px;font-weight:700;color:#166534">TUNTAS</div>
              </div>
              <div style="background:#fef3c7;border-radius:6px;padding:6px">
                <div style="font-size:16px;font-weight:800;color:#92400e"><?= (int)($tlhpData['summary']['jml_proses'] ?? 0) ?></div>
                <div style="font-size:10px;font-weight:700;color:#92400e">PROSES</div>
              </div>
              <div style="background:#fee2e2;border-radius:6px;padding:6px">
                <div style="font-size:16px;font-weight:800;color:#991b1b"><?= (int)($tlhpData['summary']['jml_belum'] ?? 0) ?></div>
                <div style="font-size:10px;font-weight:700;color:#991b1b">BELUM</div>
              </div>
            </div>
          </div>

          <!-- Peringatan Batas Waktu 60 Hari -->
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase">
                <i class="fa-solid fa-stopwatch" style="margin-right:4px"></i> Batas Waktu Kritis
              </span>
              <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px">
                <?= (int)($tlhpData['summary']['jml_kadaluarsa'] ?? 0) ?> Lewat Batas
              </span>
            </div>
            <?php if (empty($tlhpData['urgent_list'])): ?>
              <div style="font-size:12px;color:#059669;padding:10px 0;text-align:center"><i class="fa-solid fa-circle-check"></i> Tidak ada rekomendasi melewati batas waktu.</div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach (array_slice($tlhpData['urgent_list'], 0, 3) as $ul): ?>
                  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                      <div style="font-size:12px;font-weight:700;color:#0f172a"><?= e($ul['desa_nama']) ?> &bull; <?= rupiah($ul['sisa_kerugian']) ?></div>
                      <div style="font-size:11px;color:<?= (int)$ul['sisa_hari'] < 0 ? '#dc2626' : '#d97706' ?>;font-weight:700">
                        <?= (int)$ul['sisa_hari'] < 0 ? 'Lewat ' . abs((int)$ul['sisa_hari']) . ' hari' : 'Sisa ' . (int)$ul['sisa_hari'] . ' hari' ?>
                      </div>
                    </div>
                    <a href="<?= url('tlhp?desa_id=' . $ul['desa_id']) ?>" class="btn btn-sm btn-outline" style="border-color:#16a34a;color:#16a34a;padding:2px 8px;font-size:11px;font-weight:700">Tagih</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========================================== -->
    <!-- 3. PIPELINE SIKLUS AUDIT DIGITAL TERPADU (STEPPER HULU-HILIR) -->
    <!-- ========================================== -->
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
        <div style="display:flex;align-items:center;gap:8px">
          <button type="button" onclick="openPilarModal()" class="btn btn-sm" style="background:#f8fafc;border:1px solid #cbd5e1;color:#047857;font-size:11px;font-weight:700;padding:4px 10px;cursor:pointer;border-radius:6px;display:flex;align-items:center;gap:6px" title="Buka infografis 5 Pilar Siklus Pengawasan">
            <i class="fa-solid fa-circle-nodes" style="color:#059669"></i> Diagram 5 Pilar Siklus
          </button>
          <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-size:10.5px;font-weight:700">
            <i class="fa-solid fa-circle-check" style="color:#22c55e;margin-right:4px"></i> Alur Terkoneksi Penuh
          </span>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:10px">
        <!-- Tahap 1: PRA-AUDIT -->
        <a href="<?= url('penugasan/nota-dinas') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#6366f1;text-transform:uppercase;background:#e0e7ff;padding:2px 6px;border-radius:4px">1. PRA-AUDIT</span>
            <i class="fa-solid fa-envelope-open-text" style="color:#6366f1;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#0f172a;margin-top:2px"><?= $stats['nd_total'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">Nota Dinas</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= $pipeline['nd_diajukan'] ?> Menunggu &bull; <?= $pipeline['nd_disetujui'] ?> Disetujui
          </div>
        </a>

        <!-- Tahap 2: SURAT TUGAS -->
        <a href="<?= url('penugasan/spt') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#0f766e;text-transform:uppercase;background:#ccfbf1;padding:2px 6px;border-radius:4px">2. SURAT TUGAS</span>
            <i class="fa-solid fa-file-signature" style="color:#0f766e;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#0f766e;margin-top:2px"><?= $stats['spt_total'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">SPT Sah</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            TTE Digital &bull; Matriks PKA
          </div>
        </a>

        <!-- Tahap 3: UJI LAPANGAN -->
        <a href="<?= url('sesi') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#0284c7;text-transform:uppercase;background:#e0f2fe;padding:2px 6px;border-radius:4px">3. UJI LAPANGAN</span>
            <i class="fa-solid fa-clipboard-check" style="color:#0284c7;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#0284c7;margin-top:2px"><?= $stats['sesi_total'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">Sesi Audit</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            Fisik, Kwitansi &amp; Pajak
          </div>
        </a>

        <!-- Tahap 4: KONSEP TEMUAN -->
        <a href="<?= url('temuan') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#d97706;text-transform:uppercase;background:#fef3c7;padding:2px 6px;border-radius:4px">4. KONSEP TEMUAN</span>
            <i class="fa-solid fa-file-circle-exclamation" style="color:#d97706;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#b45309;margin-top:2px"><?= $stats['temuan_total'] ?> <span style="font-size:11px;font-weight:600;color:#64748b">Temuan</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            5 Unsur &amp; Rekomendasi
          </div>
        </a>

        <!-- Tahap 5: NASKAH LHP & TINDAK LANJUT -->
        <a href="<?= url('lhp') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;transition:all 0.2s ease">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:9.5px;font-weight:800;color:#16a34a;text-transform:uppercase;background:#dcfce7;padding:2px 6px;border-radius:4px">5. LHP &amp; TLHP</span>
            <i class="fa-solid fa-file-shield" style="color:#16a34a;font-size:12px"></i>
          </div>
          <div style="font-size:15px;font-weight:800;color:#15803d;margin-top:2px"><?= max(1, $pipeline['lhp_desa']) ?> <span style="font-size:11px;font-weight:600;color:#64748b">LHP Sah</span></div>
          <div style="font-size:10.5px;color:#64748b;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            Monitoring TLHP 60 Hari
          </div>
        </a>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 4. 4 KARTU INDIKATOR EKSEKUTIF BERBASIS KONTEKS -->
    <!-- ========================================== -->
    <div class="stats-grid" style="margin-bottom:22px;display:grid;grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));gap:14px">
      <!-- Card 1: Total Belanja Diaudit -->
      <div class="stat blue" data-testid="stat-anggaran" style="padding:16px 18px;border-left:4px solid #0284c7;border-radius:10px">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Total Belanja Diaudit</div>
          <div class="value money" style="font-size:21px;font-weight:800;color:#0f172a;margin:5px 0 2px"><?= rupiah($stats['dikwitansi']) ?></div>
          <div class="sub" style="font-size:11px;color:#64748b">
            Pagu: <?= rupiah($stats['pagu_total']) ?> &bull; Realisasi: <?= rupiah($stats['realisasi']) ?>
          </div>
        </div>
        <div class="ico" style="background:#e0f2fe;color:#0284c7;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-money-bill-wave"></i></div>
      </div>

      <!-- Card 2: Potensi Pemulihan Kas Desa -->
      <div class="stat rose" data-testid="stat-pemulihan" style="padding:16px 18px;border-left:4px solid #e11d48;border-radius:10px">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Potensi Pemulihan Kas</div>
          <div class="value money" style="font-size:21px;font-weight:800;color:#e11d48;margin:5px 0 2px">
            <?= rupiah($stats['potensi_pemulihan']) ?>
          </div>
          <div class="sub" style="font-size:11px;color:#64748b">
            <?= $stats['temuan_total'] ?> Butir Temuan &bull; Selisih Fisik: <?= rupiah($stats['selisih_fisik']) ?>
          </div>
        </div>
        <div class="ico" style="background:#ffe4e6;color:#e11d48;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-hand-holding-dollar"></i></div>
      </div>

      <!-- Card 3: Uji Kepatuhan Pajak Belanja -->
      <div class="stat amber" data-testid="stat-pajak" style="padding:16px 18px;border-left:4px solid #f59e0b;border-radius:10px">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Kepatuhan Pajak Belanja</div>
          <div class="value money" style="font-size:21px;font-weight:800;color:#d97706;margin:5px 0 2px"><?= rupiah($stats['pajak_setor'] + $stats['pajak_belum_setor']) ?></div>
          <div class="sub" style="font-size:11px;color:#64748b">
            <span style="color:#059669;font-weight:700">Setor: <?= rupiah($stats['pajak_setor']) ?></span> &bull; 
            <span style="color:#dc2626;font-weight:700">Belum: <?= rupiah($stats['pajak_belum_setor']) ?></span>
          </div>
        </div>
        <div class="ico" style="background:#fef3c7;color:#d97706;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-receipt"></i></div>
      </div>

      <!-- Card 4: Dokumen Pengawasan Sah & Cakupan Desa -->
      <div class="stat" style="padding:16px 18px;border-left:4px solid #10b981;border-radius:10px" data-testid="stat-dokumen">
        <div>
          <div class="label" style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px">Cakupan Pengawasan Sah</div>
          <div class="value" style="font-size:21px;font-weight:800;color:#0f172a;margin:5px 0 2px">
            <?= $stats['desa_diaudit'] ?> dari <?= $stats['desa_total'] ?> Desa
          </div>
          <div class="sub" style="font-size:11px;color:#64748b">
            <?= $stats['cakupan_persen'] ?>% Cakupan &bull; <?= $stats['kec_total'] ?> Kecamatan se-Rohil
          </div>
        </div>
        <div class="ico" style="background:#d1fae5;color:#059669;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px"><i class="fa-solid fa-stamp"></i></div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 5. MAIN CONTENT BALANCED GRID (REGISTER DESA & WIDGET PENDUKUNG) -->
    <!-- ========================================== -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">
      <!-- LEFT: REGISTER KEPENGHULUAN (DESA) OBJEK PEMERIKSAAN -->
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
            <h4 style="margin:0;font-size:15px;color:#334155">Belum ada sesi audit untuk filter ini</h4>
            <p style="margin:4px 0 0;font-size:12.5px;color:#64748b">Ubah filter tahun/wilayah atau mulai buat sesi audit baru.</p>
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
                  <th style="padding:10px 12px;text-align:center;width:100px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach (array_slice($perDesa, 0, 8) as $d): ?>
                  <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                    <td style="padding:10px 12px;text-align:center;color:#64748b;font-weight:600"><?= $no++ ?></td>
                    <td style="padding:10px 12px;text-align:left">
                      <div style="font-weight:700;color:#0f172a;font-size:13px">
                        <?= e($d['desa']) ?>
                      </div>
                      <div style="font-size:11px;color:#64748b">
                        Kec. <?= e($d['kecamatan']) ?> &bull; TA <?= (int)($d['tahun_terakhir'] ?: date('Y')) ?>
                      </div>
                    </td>
                    <td style="padding:10px 12px;text-align:right;font-weight:700;color:#0f172a">
                      <?= rupiah($d['pagu']) ?>
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                      <span class="badge" style="background:#eff6ff;color:#1e40af;font-weight:700;border:1px solid #bfdbfe;font-size:11px">
                        <?= (int)$d['jumlah'] ?> Sesi
                      </span>
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                      <?php if ((int)$d['jml_temuan'] > 0 || (float)$d['selisih_fisik'] > 0): ?>
                        <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-weight:700;font-size:10.5px">
                          <i class="fa-solid fa-triangle-exclamation" style="margin-right:2px"></i>
                          <?= (int)$d['jml_temuan'] ?> Temuan <?= (float)$d['selisih_fisik'] > 0 ? '&bull; Ada Selisih' : '' ?>
                        </span>
                      <?php else: ?>
                        <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-weight:700;font-size:10.5px">
                          <i class="fa-solid fa-circle-check" style="margin-right:2px"></i> Tertib Fisik
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                      <a href="<?= url('sesi?desa=' . $d['id']) ?>" class="btn btn-sm btn-outline" style="padding:3px 8px;font-size:11.5px;font-weight:700">
                        Buka KKA
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: WIDGET SEBARAN BIDANG APBDES & PINTASAN DOKUMEN -->
      <div style="display:flex;flex-direction:column;gap:18px">
        <!-- Widget 1: Sebaran Sesi Per Bidang APBDes -->
        <div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:7px">
              <i class="fa-solid fa-chart-pie" style="color:#6366f1"></i> Sebaran Sesi per Bidang
            </h3>
            <span class="badge" style="background:#f1f5f9;color:#475569;font-size:10px;font-weight:600">5 Bidang APBDes</span>
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

        <!-- Widget 2: Pintasan Dokumen Standar Rohil -->
        <div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <h3 style="margin:0;font-size:14px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:7px">
              <i class="fa-solid fa-print" style="color:#2563eb"></i> Pintasan Dokumen Pengawasan
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

            <a href="<?= url('tlhp') ?>" class="btn btn-ghost btn-sm" style="justify-content:space-between;padding:8px 10px;font-size:12px;border:1px solid #f1f5f9;border-radius:8px">
              <span style="display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-clock-rotate-left" style="color:#059669;width:14px"></i>
                <span style="font-weight:600;color:#1e293b">Monitoring Tindak Lanjut (TLHP)</span>
              </span>
              <i class="fa-solid fa-chevron-right" style="font-size:10px;color:#94a3b8"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- LIGHTBOX MODAL 5 PILAR HD -->
<div id="pilarModal" class="pilar-modal" onclick="if(event.target === this) closePilarModal()" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(2,44,34,0.88);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px">
  <div style="position:relative;max-width:1020px;width:100%;background:#064e3b;border:2px solid rgba(250,204,21,0.5);border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,0.6);overflow:hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;background:rgba(0,0,0,0.35);border-bottom:1px solid rgba(255,255,255,0.1)">
      <div style="color:#fde68a;font-weight:700;font-size:13.5px;display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-shield-halved" style="color:#fbbf24"></i> 5 Pilar Siklus Pengawasan Terpadu (End-to-End) — APIP Inspektorat Rohil 2026
      </div>
      <button type="button" onclick="closePilarModal()" style="background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:16px;width:32px;height:32px;border-radius:50%;cursor:pointer;display:grid;place-items:center;transition:background .2s" title="Tutup (ESC)">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div style="padding:14px;background:#022c22;display:grid;place-items:center">
      <img src="<?= asset('img/5_pilar_siklus_pengawasan.jpg') ?>" alt="5 Pilar Siklus Pengawasan Terpadu" style="max-width:100%;max-height:80vh;object-fit:contain;border-radius:8px;display:block;box-shadow:0 10px 30px rgba(0,0,0,0.5)">
    </div>
  </div>
</div>

<script>
function openPilarModal() {
  const m = document.getElementById('pilarModal');
  if (m) {
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}
function closePilarModal() {
  const m = document.getElementById('pilarModal');
  if (m) {
    m.style.display = 'none';
    document.body.style.overflow = '';
  }
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closePilarModal();
});
</script>

<?php partial('foot'); ?>
