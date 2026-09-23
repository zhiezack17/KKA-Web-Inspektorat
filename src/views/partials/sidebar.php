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
?>
<aside class="sidebar" data-testid="sidebar">
  <div class="brand">
    <div class="brand-logo">
      <img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil">
    </div>
    <div class="brand-text">
      <h1>KKA DIGITAL <span style="font-size:9px;background:rgba(245,158,11,0.2);color:#fef08a;padding:1px 5px;border-radius:4px;border:1px solid rgba(245,158,11,0.3);font-weight:700">v2.5</span></h1>
      <p>Inspektorat Rokan Hilir</p>
    </div>

    <button type="button" class="sidebar-collapse-btn hide-mobile" id="sidebarCollapseBtn" title="Sembunyikan Menu (Ctrl+B)" aria-label="Sembunyikan Menu">
      <i class="fa-solid fa-angles-left"></i>
    </button>

    <button class="sidebar-close" id="sidebarCloseBtn" aria-label="Tutup menu">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <nav class="nav">
    <a href="<?= url('dashboard') ?>" class="nav-item<?= nav_active('/dashboard', $current) ?>" data-testid="nav-dashboard">
      <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
    </a>

    <?php if (!$auth->isOperatorTl()): ?>
    <div class="nav-section">Pra-Audit &amp; Penugasan</div>
    <a href="<?= url('penugasan/nota-dinas') ?>" class="nav-item<?= nav_active('/penugasan/nota-dinas', $current) ?>" data-testid="nav-nota-dinas">
      <i class="fa-solid fa-envelope-open-text"></i><span>Nota Dinas (ND)</span>
      <?php if ($badgeDisposisi > 0): ?>
        <span class="badge" style="background:#ef4444;color:#fff;font-size:10px;padding:2px 6px;border-radius:10px;margin-left:auto"><?= $badgeDisposisi ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= url('penugasan/spt') ?>" class="nav-item<?= nav_active('/penugasan/spt', $current) ?>" data-testid="nav-spt">
      <i class="fa-solid fa-file-signature"></i><span>Surat Tugas (SPT)</span>
    </a>
    <a href="<?= url('penugasan/pka') ?>" class="nav-item<?= nav_active('/penugasan/pka', $current) ?>" data-testid="nav-pka">
      <i class="fa-solid fa-list-check"></i><span>Matriks PKA</span>
    </a>
    <?php endif; ?>

    <?php if ((!$auth->isOperatorSpt() && !$auth->isOperatorTl()) || $auth->isAdmin()): ?>
    <div class="nav-section">Pelaksanaan Audit (KKA ADTT)</div>
    <a href="<?= url('sesi') ?>" class="nav-item<?= nav_active('/sesi', $current) ?>" data-testid="nav-sesi">
      <i class="fa-solid fa-clipboard-list"></i><span>Kertas Kerja (KKA)</span>
    </a>
    <a href="<?= url('opname-kas') ?>" class="nav-item<?= nav_active('/opname-kas', $current) ?>" data-testid="nav-opname">
      <i class="fa-solid fa-money-bill-transfer" style="color:#34d399"></i><span>Pemeriksaan Kas (Opname)</span>
    </a>
    <a href="<?= url('aspek-keuangan') ?>" class="nav-item<?= nav_active('/aspek-keuangan', $current) ?>" data-testid="nav-aspek-keuangan">
      <i class="fa-solid fa-calculator" style="color:#38bdf8"></i><span>Aspek Keuangan &amp; Kas</span>
      <span class="badge" style="background:#0f766e;color:#a7f3d0;font-size:9px;padding:1px 5px;border-radius:10px;margin-left:auto;font-weight:700">Uji Kas</span>
    </a>
    <a href="<?= url('rekap') ?>" class="nav-item<?= nav_active('/rekap', $current) ?>" data-testid="nav-rekap">
      <i class="fa-solid fa-chart-column"></i><span>Rekap Belanja</span>
    </a>
    <a href="<?= url('master') ?>" class="nav-item<?= nav_active('/master', $current) ?>" data-testid="nav-master">
      <i class="fa-solid fa-folder-tree"></i><span>Master KKA Fisik</span>
    </a>
    <?php endif; ?>

    <div class="nav-section">Hasil Pengawasan &amp; Laporan</div>
    <?php if (!$auth->isOperatorTl() && (!$auth->isOperatorSpt() || $auth->isAdmin())): ?>
    <a href="<?= url('temuan') ?>" class="nav-item<?= nav_active('/temuan', $current) ?>" data-testid="nav-temuan">
      <i class="fa-solid fa-file-circle-exclamation" style="color:#fbbf24"></i><span>Konsep Temuan (KTP)</span>
    </a>
    <a href="<?= url('lhp') ?>" class="nav-item<?= nav_active('/lhp', $current) ?>" data-testid="nav-lhp">
      <i class="fa-solid fa-file-shield" style="color:#6ee7b7"></i><span>Laporan Hasil Audit (LHP)</span>
    </a>
    <a href="<?= url('routing-slip') ?>" class="nav-item<?= nav_active('/routing-slip', $current) ?>" data-testid="nav-routing-slip">
      <i class="fa-solid fa-folder-open" style="color:#f59e0b"></i><span>Routing Slip (Kendali Mutu)</span>
    </a>
    <?php endif; ?>
    <a href="<?= url('tlhp') ?>" class="nav-item<?= nav_active('/tlhp', $current) ?>" data-testid="nav-tlhp">
      <i class="fa-solid fa-clock-rotate-left" style="color:#34d399"></i><span>Tindak Lanjut (TLHP 60 Hari)</span>
    </a>

    <?php if (!$auth->isOperatorTl() && (!$auth->isOperatorSpt() || $auth->isAdmin())): ?>
    <div class="nav-section">Audit Ketaatan OPD</div>
    <a href="javascript:void(0)" onclick="alert('ℹ️ MODUL KKA AUDIT KETAATAN DINAS/OPD (SIAKAT)\n\nModul ini sedang disiapkan untuk integrasi kepatuhan dinas/OPD se-Kabupaten Rokan Hilir pada pembaruan tahap berikutnya.')" class="nav-item" style="opacity:0.85" title="Segera Hadir">
      <i class="fa-solid fa-scale-balanced" style="color:#60a5fa"></i><span>KKA Ketaatan OPD</span>
      <span class="badge" style="background:#2563eb;color:#fff;font-size:9px;padding:1px 6px;border-radius:10px;margin-left:auto;font-weight:700">Segera</span>
    </a>
    <?php endif; ?>

    <div class="nav-section">Panduan &amp; Pengaturan</div>
    <a href="<?= url('gdrive') ?>" class="nav-item<?= nav_active('/gdrive', $current) ?>" data-testid="nav-gdrive">
      <i class="fa-solid fa-cloud-arrow-up" style="color:#38bdf8"></i><span>Google Drive</span>
    </a>
    <a href="<?= url('panduan-workflow') ?>" class="nav-item<?= nav_active('/panduan-workflow', $current) ?>" data-testid="nav-workflow">
      <i class="fa-solid fa-diagram-project"></i><span>SOP &amp; Workflow</span>
    </a>
    <a href="<?= url('desa') ?>" class="nav-item<?= nav_active('/desa', $current) ?>" data-testid="nav-desa">
      <i class="fa-solid fa-building-columns"></i><span>Manajemen Desa</span>
    </a>
    <?php if ($auth->isAdmin()): ?>
      <a href="<?= url('users') ?>" class="nav-item<?= nav_active('/users', $current) ?>" data-testid="nav-users">
        <i class="fa-solid fa-users-gear"></i><span>Manajemen Pengguna</span>
      </a>
    <?php endif; ?>
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
    <select onchange="if(this.value) window.location.href='<?= url('switch-role?user=') ?>' + this.value" style="width:100%;font-size:11px;font-weight:700;padding:5px 7px;border-radius:6px;background:#022c22;color:#f8fafc;border:1px solid #1e3a8a;cursor:pointer;outline:none">
      <option value="">-- Ganti Akun Pejabat --</option>
      <option value="inspektur" <?= ($user['username'] ?? '') === 'inspektur' ? 'selected' : '' ?>>👤 Inspektur (H. Sarman Syahroni)</option>
      <option value="marwan" <?= ($user['username'] ?? '') === 'marwan' ? 'selected' : '' ?>>👤 Irban IV (Marwan, M.T)</option>
      <option value="operator_spt" <?= ($user['username'] ?? '') === 'operator_spt' ? 'selected' : '' ?>>👤 Bag. Perencanaan (SPT)</option>
      <option value="abubakar" <?= ($user['username'] ?? '') === 'abubakar' ? 'selected' : '' ?>>👤 Dalnis (Abu Bakar, SE)</option>
      <option value="amdattofa" <?= ($user['username'] ?? '') === 'amdattofa' ? 'selected' : '' ?>>👤 Ketua Tim (Amdat Tofa, SH)</option>
      <option value="budicahyadi" <?= ($user['username'] ?? '') === 'budicahyadi' ? 'selected' : '' ?>>👤 Ketua Tim (Budi Cahyadi)</option>
      <option value="fakhrurrazi" <?= ($user['username'] ?? '') === 'fakhrurrazi' ? 'selected' : '' ?>>👤 Anggota (Fakhrurrazi)</option>
      <option value="operator_tl" <?= ($user['username'] ?? '') === 'operator_tl' ? 'selected' : '' ?>>👤 Bag. Tindak Lanjut (TLHP)</option>
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