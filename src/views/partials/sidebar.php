<?php
$auth = $GLOBALS['auth'];
$user = $auth->user();
$current = $_SERVER['REQUEST_URI'] ?? '';
if (!function_exists('nav_active')) {
    function nav_active($needle, $current) {
        return str_contains($current, $needle) ? ' active' : '';
    }
}

// Cek jumlah antrean disposisi jika Inspektur login
$badgeDisposisi = 0;
if ($auth->isInspektur()) {
    $badgeDisposisi = (int) DB::val("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DIAJUKAN_INSPEKTUR'");
}

// Role flags
$isAdmin = $auth->isAdmin();
$isInspektur = $auth->isInspektur();
$isIrban = $auth->isIrban();
$isDalnis = $auth->isDalnis();
$isKetua = $auth->isKetua();
$isSpt = $auth->isOperatorSpt();
$isTl = $auth->isOperatorTl();

// Group Active Check Helpers (Auto-expand active submenu on page load)
$isPraAuditActive = str_contains($current, '/penugasan/') || str_contains($current, '/pia/');
$isKkaActive = (str_contains($current, '/sesi') && !str_contains($current, '/print/')) 
               || str_contains($current, '/opname-kas') 
               || str_contains($current, '/aspek-keuangan') 
               || str_contains($current, '/rekap') 
               || str_contains($current, '/master');
$isLhpActive = str_contains($current, '/temuan') 
               || (str_contains($current, '/lhp') && !str_contains($current, '/pia/lhp')) 
               || str_contains($current, '/routing-slip') 
               || str_contains($current, '/tlhp');
$isPengaturanActive = str_contains($current, '/panduan-workflow') 
                     || str_contains($current, '/desa') 
                     || str_contains($current, '/gdrive') 
                     || str_contains($current, '/users');
?>
<aside class="sidebar" data-testid="sidebar">
  <div class="brand">
    <div class="brand-logo">
      <img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil">
    </div>
    <div class="brand-text">
      <div class="elhp-brand-wrap">
        <h1 class="elhp-title">
          <span class="elhp-sparkle"><i class="fa-solid fa-sparkles"></i></span>
          <span class="elhp-text">E-LHP</span>
          <span class="elhp-badge-pro">DIGITAL</span>
        </h1>
        <p class="elhp-sub">Inspektorat Rokan Hilir</p>
      </div>
    </div>

    <button type="button" class="sidebar-collapse-btn hide-mobile" id="sidebarCollapseBtn" title="Sembunyikan Menu (Ctrl+B)" aria-label="Sembunyikan Menu">
      <i class="fa-solid fa-angles-left"></i>
    </button>

    <button class="sidebar-close" id="sidebarCloseBtn" aria-label="Tutup menu">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <nav class="nav">
    <!-- 1. DASHBOARD UTAMA -->
    <a href="<?= url('dashboard') ?>" class="nav-item<?= nav_active('/dashboard', $current) ?>" data-testid="nav-dashboard">
      <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
    </a>

    <!-- 2. TAHAP PERSIAPAN / PRA-AUDIT (Sesuai Diagram Alur Inspektorat) -->
    <?php if (!$isTl): ?>
      <div class="nav-group <?= $isPraAuditActive ? 'open' : '' ?>">
        <div class="nav-group-header" onclick="toggleNavGroup(this)">
          <div class="nav-group-title">
            <i class="fa-solid fa-file-signature" style="color:#6366f1"></i>
            <span>Tahap Persiapan (Pra-Audit)</span>
          </div>
          <div style="display:flex;align-items:center;gap:6px">
            <?php if ($badgeDisposisi > 0): ?>
              <span class="badge" style="background:#ef4444;color:#fff;font-size:9px;padding:1px 5px;border-radius:10px"><?= $badgeDisposisi ?></span>
            <?php endif; ?>
            <i class="fa-solid fa-chevron-down nav-group-chevron"></i>
          </div>
        </div>
        <div class="nav-submenu">
          <?php if (!$isSpt || $isAdmin): ?>
            <a href="<?= url('pia/nota-dinas') ?>" class="nav-subitem<?= nav_active('/pia/nota-dinas', $current) ?>" data-testid="nav-pia-nd">
              <i class="fa-solid fa-file-pen" style="color:#0ea5e9"></i><span>Nota Dinas PIA</span>
            </a>
            <a href="<?= url('pia/spt') ?>" class="nav-subitem<?= nav_active('/pia/spt', $current) ?>">
              <i class="fa-solid fa-signature" style="color:#0ea5e9"></i><span>SPT PIA</span>
            </a>
            <a href="<?= url('pia/lhp') ?>" class="nav-subitem<?= nav_active('/pia/lhp', $current) ?>">
              <i class="fa-solid fa-clipboard-check" style="color:#0ea5e9"></i><span>KKA &amp; DPPA PIA</span>
            </a>
          <?php endif; ?>
          <a href="<?= url('penugasan/nota-dinas') ?>" class="nav-subitem<?= nav_active('/penugasan/nota-dinas', $current) ?>" data-testid="nav-nota-dinas">
            <i class="fa-solid fa-envelope-open-text" style="color:#6366f1"></i><span>Nota Dinas ADTT</span>
            <?php if ($badgeDisposisi > 0): ?>
              <span class="badge" style="background:#ef4444;color:#fff;font-size:9px;padding:1px 5px;border-radius:8px;margin-left:auto"><?= $badgeDisposisi ?></span>
            <?php endif; ?>
          </a>
          <a href="<?= url('penugasan/spt') ?>" class="nav-subitem<?= nav_active('/penugasan/spt', $current) ?>" data-testid="nav-spt">
            <i class="fa-solid fa-stamp" style="color:#6366f1"></i><span>SPT ADTT</span>
          </a>
          <a href="<?= url('penugasan/pka') ?>" class="nav-subitem<?= nav_active('/penugasan/pka', $current) ?>" data-testid="nav-pka">
            <i class="fa-solid fa-list-check" style="color:#6366f1"></i><span>Matriks PKA ADTT</span>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- 3. TAHAP PELAKSANAAN / AUDIT (KKA, Kas, Aspek Keuangan, Temuan) -->
    <?php if ((!$isSpt && !$isTl) || $isAdmin): ?>
      <div class="nav-group <?= $isKkaActive ? 'open' : '' ?>">
        <div class="nav-group-header" onclick="toggleNavGroup(this)">
          <div class="nav-group-title">
            <i class="fa-solid fa-clipboard-list" style="color:#0284c7"></i>
            <span>Tahap Pelaksanaan (Audit)</span>
          </div>
          <i class="fa-solid fa-chevron-down nav-group-chevron"></i>
        </div>
        <div class="nav-submenu">
          <a href="<?= url('sesi') ?>" class="nav-subitem<?= (str_contains($current, '/sesi') && !str_contains($current, '/print/')) ? ' active' : '' ?>" data-testid="nav-sesi">
            <i class="fa-solid fa-folder-open"></i><span>Kertas Kerja (KKA)</span>
          </a>
          <a href="<?= url('opname-kas') ?>" class="nav-subitem<?= nav_active('/opname-kas', $current) ?>" data-testid="nav-opname">
            <i class="fa-solid fa-money-bill-transfer" style="color:#10b981"></i><span>Pemeriksaan Kas (Opname)</span>
          </a>
          <a href="<?= url('aspek-keuangan') ?>" class="nav-subitem<?= nav_active('/aspek-keuangan', $current) ?>" data-testid="nav-aspek-keuangan">
            <i class="fa-solid fa-calculator" style="color:#38bdf8"></i><span>Pengujian Aspek Keuangan</span>
          </a>
          <a href="<?= url('rekap') ?>" class="nav-subitem<?= nav_active('/rekap', $current) ?>" data-testid="nav-rekap">
            <i class="fa-solid fa-chart-column"></i><span>Rekapitulasi Belanja</span>
          </a>
          <a href="<?= url('temuan') ?>" class="nav-subitem<?= nav_active('/temuan', $current) ?>" data-testid="nav-temuan">
            <i class="fa-solid fa-file-circle-exclamation" style="color:#fbbf24"></i><span>Konsep Temuan (KTP)</span>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- 4. TAHAP PELAPORAN & TINDAK LANJUT (LHP, Routing Slip, TLHP) -->
    <div class="nav-group <?= $isLhpActive ? 'open' : '' ?>">
      <div class="nav-group-header" onclick="toggleNavGroup(this)">
        <div class="nav-group-title">
          <i class="fa-solid fa-file-shield" style="color:#10b981"></i>
          <span>Tahap Pelaporan &amp; TL</span>
        </div>
        <i class="fa-solid fa-chevron-down nav-group-chevron"></i>
      </div>
      <div class="nav-submenu">
        <?php if (!$isTl && (!$isSpt || $isAdmin)): ?>
          <a href="<?= url('lhp') ?>" class="nav-subitem<?= (str_contains($current, '/lhp') && !str_contains($current, '/pia/lhp')) ? ' active' : '' ?>" data-testid="nav-lhp">
            <i class="fa-solid fa-file-shield" style="color:#6ee7b7"></i><span>Laporan Hasil (LHP)</span>
          </a>
          <a href="<?= url('routing-slip') ?>" class="nav-subitem<?= nav_active('/routing-slip', $current) ?>" data-testid="nav-routing-slip">
            <i class="fa-solid fa-folder-tree" style="color:#f59e0b"></i><span>Routing Slip Kendali</span>
          </a>
        <?php endif; ?>
        <a href="<?= url('tlhp') ?>" class="nav-subitem<?= nav_active('/tlhp', $current) ?>" data-testid="nav-tlhp">
          <i class="fa-solid fa-clock-rotate-left" style="color:#34d399"></i><span>Tindak Lanjut (TLHP)</span>
        </a>
      </div>
    </div>

    <!-- 5. SUBMENU: PANDUAN & PENGATURAN SISTEM (All) -->
    <div class="nav-group <?= $isPengaturanActive ? 'open' : '' ?>">
      <div class="nav-group-header" onclick="toggleNavGroup(this)">
        <div class="nav-group-title">
          <i class="fa-solid fa-gear" style="color:#94a3b8"></i>
          <span>Pengaturan &amp; Data</span>
        </div>
        <i class="fa-solid fa-chevron-down nav-group-chevron"></i>
      </div>
      <div class="nav-submenu">
        <a href="<?= url('panduan-workflow') ?>" class="nav-subitem<?= nav_active('/panduan-workflow', $current) ?>" data-testid="nav-workflow">
          <i class="fa-solid fa-diagram-project"></i><span>SOP &amp; Workflow</span>
        </a>
        <a href="<?= asset('assets/docs/BUKU_PANDUAN_KKA_DIGITAL_2026.pdf') ?>" target="_blank" class="nav-subitem" data-testid="nav-buku-panduan" style="color:#fbbf24">
          <i class="fa-solid fa-book-bookmark" style="color:#fbbf24"></i><span>Buku Panduan (PDF)</span>
        </a>
        <a href="<?= url('desa') ?>" class="nav-subitem<?= nav_active('/desa', $current) ?>" data-testid="nav-desa">
          <i class="fa-solid fa-building-columns"></i><span>Master Wilayah Desa</span>
        </a>
        <?php if (!$isSpt && !$isTl): ?>
          <a href="<?= url('gdrive') ?>" class="nav-subitem<?= nav_active('/gdrive', $current) ?>" data-testid="nav-gdrive">
            <i class="fa-solid fa-cloud-arrow-up" style="color:#38bdf8"></i><span>Google Drive Sync</span>
          </a>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
          <a href="<?= url('users') ?>" class="nav-subitem<?= nav_active('/users', $current) ?>" data-testid="nav-users">
            <i class="fa-solid fa-users-gear" style="color:#fbbf24"></i><span>Manajemen Pengguna</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <?php if (!empty($_SESSION['is_admin_master'])): ?>
  <!-- Quick Demo Role Switcher in Sidebar (Khusus Administrator) -->
  <div style="margin:6px 12px 10px;padding:8px 10px;background:rgba(15,23,42,0.85);border:1px solid rgba(245,158,11,0.4);border-radius:8px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
      <span style="font-size:10px;font-weight:700;color:#fbbf24;text-transform:uppercase;letter-spacing:0.5px">
        <i class="fa-solid fa-masks-theater"></i> Simulasi Peran Demo
      </span>
      <span style="font-size:9px;background:#b45309;color:#fff;padding:1px 5px;border-radius:4px;font-weight:700">1-Klik</span>
    </div>
    <select onchange="if(this.value) window.location.href='<?= url('switch-role?csrf=' . csrf_token() . '&user=') ?>' + this.value" style="width:100%;font-size:11px;font-weight:700;padding:5px 7px;border-radius:6px;background:#022c22;color:#f8fafc;border:1px solid #1e3a8a;cursor:pointer;outline:none">
      <option value="">-- Ganti Akun Pejabat --</option>
      <option value="restore" style="font-weight:bold;color:#f59e0b">🔙 Akun Administrator Utama</option>
      <option value="inspektur" <?= ($user['username'] ?? '') === 'inspektur' ? 'selected' : '' ?>>👤 Inspektur (H. Sarman Syahroni)</option>
      <option value="marwan" <?= ($user['username'] ?? '') === 'marwan' ? 'selected' : '' ?>>👤 Irban IV (Marwan, M.T)</option>
      <option value="operator_spt" <?= ($user['username'] ?? '') === 'operator_spt' ? 'selected' : '' ?>>👤 Bag. Perencanaan (SPT)</option>
      <option value="abubakar" <?= ($user['username'] ?? '') === 'abubakar' ? 'selected' : '' ?>>👤 Dalnis (Abu Bakar, SE)</option>
      <option value="amdattofa" <?= ($user['username'] ?? '') === 'amdattofa' ? 'selected' : '' ?>>👤 Ketua Tim (Amdat Tofa, SH)</option>
      <option value="budicahyadi" <?= ($user['username'] ?? '') === 'budicahyadi' ? 'selected' : '' ?>>👤 Ketua Tim (Budi Cahyadi)</option>
      <option value="fakhrurrazi" <?= ($user['username'] ?? '') === 'fakhrurrazi' ? 'selected' : '' ?>>👤 Anggota (Fakhrurrazi)</option>
      <option value="operator_tl" <?= ($user['username'] ?? '') === 'operator_tl' ? 'selected' : '' ?>>👤 Bag. Evaluasi & TL</option>
      <option value="admin" <?= ($user['username'] ?? '') === 'admin' ? 'selected' : '' ?>>⚡ Administrator Sistem</option>
    </select>
  </div>
  <?php endif; ?>

  <div class="sidebar-foot">
    <a href="<?= url('profile') ?>" class="userbox" data-testid="userbox" style="display:flex;align-items:center;gap:10px;text-decoration:none;padding:6px;border-radius:8px">
      <?php $sideAvatar = user_avatar_url($user); ?>
      <?php if ($sideAvatar): ?>
        <img src="<?= $sideAvatar ?>" alt="<?= e($user['nama'] ?? '') ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid <?= ($user['username'] ?? '') === 'inspektur' ? '#fbbf24' : '#059669' ?>;flex-shrink:0">
      <?php else: ?>
        <div class="avatar" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--emerald-600),var(--emerald-800));color:#fff;display:grid;place-items:center;font-weight:700;font-size:13px"><?= e(strtoupper(mb_substr($user['nama'] ?? 'U', 0, 1))) ?></div>
      <?php endif; ?>
      <div class="info" style="min-width:0;flex:1">
        <div class="name" style="font-weight:700;color:#fff;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($user['nama']) ?></div>
        <div class="email" style="font-size:11px;color:#86efac;text-transform:capitalize;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($user['role']) ?> &bull; <?= e(mb_substr($user['jabatan'] ?? '', 0, 20)) ?></div>
      </div>
    </a>
    <a class="logout" href="<?= url('logout') ?>" data-testid="logout-btn" style="display:flex;align-items:center;gap:6px;padding:8px 10px;margin-top:6px;border-radius:6px;color:#fca5a5;font-size:12.5px;text-decoration:none">
      <i class="fa-solid fa-right-from-bracket"></i> <span>Keluar Sistem</span>
    </a>
  </div>
</aside>

<style>
/* ============ Collapsible Submenu Accordion ============ */
.nav-group {
  margin-bottom: 4px;
}
.nav-group-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 8px;
  color: #a7f3d0;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  cursor: pointer;
  user-select: none;
  transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}
.nav-group-header:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  transform: translateX(2px);
}
.nav-group-title {
  display: flex;
  align-items: center;
  gap: 10px;
}
.nav-group-title i {
  width: 16px;
  text-align: center;
  font-size: 13.5px;
}
.nav-group-chevron {
  font-size: 10px;
  transition: transform 0.22s ease, color 0.22s ease;
  color: #64748b;
}
.nav-group.open .nav-group-chevron {
  transform: rotate(180deg);
  color: #fbbf24;
}
.nav-group.open .nav-group-header {
  color: #ffffff;
}
.nav-submenu {
  display: none;
  flex-direction: column;
  gap: 2px;
  padding-left: 8px;
  margin: 2px 0 6px;
  border-left: 2px solid rgba(255, 255, 255, 0.12);
  margin-left: 18px;
}
.nav-group.open .nav-submenu {
  display: flex;
}
.nav-subitem {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 11px;
  border-radius: 6px;
  color: #bbf7d0;
  font-size: 12.5px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.15s ease;
}
.nav-subitem i {
  width: 15px;
  text-align: center;
  font-size: 12.5px;
  color: #86efac;
  transition: transform 0.15s ease, color 0.15s ease;
}
.nav-subitem:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  transform: translateX(3px);
}
.nav-subitem:hover i {
  color: #fef08a;
  transform: scale(1.15);
}
.nav-subitem.active {
  background: linear-gradient(90deg, rgba(245, 158, 11, 0.22) 0%, rgba(245, 158, 11, 0.06) 100%);
  color: #ffffff;
  font-weight: 700;
  border-left: 3px solid #fbbf24;
}
.nav-subitem.active i {
  color: #fbbf24;
  filter: drop-shadow(0 0 5px rgba(245, 158, 11, 0.5));
}
</style>

<script>
function toggleNavGroup(header) {
  const group = header.closest('.nav-group');
  if (group) {
    group.classList.toggle('open');
  }
}
</script>
