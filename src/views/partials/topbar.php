<?php
$tbTitle = $title ?? 'KKA Digital';
$tbIcon  = $icon ?? 'fa-solid fa-layer-group';
$curUser = $GLOBALS['auth']->user() ?? [];
$curUname = $curUser['username'] ?? '';
$curRole  = $curUser['role'] ?? 'Auditor';

if (($curUser['role'] ?? '') === 'admin') {
    $_SESSION['is_admin_master'] = true;
}
$canSwitchRole = !empty($_SESSION['is_admin_master']);
?>
<div class="topbar">
  <div class="crumb">
    <button type="button" class="sidebar-toggle-btn hide-mobile" id="sidebarToggleBtn" title="Buka / Tutup Menu (Ctrl+B)" aria-label="Toggle Menu Samping">
      <i class="fa-solid fa-bars-staggered"></i>
    </button>
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Buka Menu">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div style="display:flex;align-items:center;gap:8px">
      <div style="width:32px;height:32px;border-radius:8px;background:#ecfdf5;color:#047857;display:grid;place-items:center;font-size:14px">
        <i class="<?= $tbIcon ?>"></i>
      </div>
      <div>
        <b style="font-size:14.5px;color:#0f172a;display:block;line-height:1.2"><?= e($tbTitle) ?></b>
        <span style="font-size:11px;color:#64748b;font-weight:500">Inspektorat Kabupaten Rokan Hilir</span>
      </div>
    </div>
  </div>

  <div class="topbar-right" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <?php if ($canSwitchRole): ?>
      <!-- Quick Demo Role Switcher Widget (Hanya Tampil di Akun Administrator) -->
      <div style="display:inline-flex;align-items:center;gap:6px;background:#ffffff;border:1px solid #cbd5e1;border-radius:30px;padding:3px 12px 3px 10px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
        <?php $topAvatar = user_avatar_url($curUser); ?>
        <?php if ($topAvatar): ?>
          <img src="<?= $topAvatar ?>" alt="Avatar" style="width:22px;height:22px;border-radius:50%;object-fit:cover;border:1.5px solid <?= $curUname === 'inspektur' ? '#d97706' : '#059669' ?>">
        <?php else: ?>
          <i class="fa-solid fa-user-tie" style="color:#059669;font-size:12px"></i>
        <?php endif; ?>

        <span class="hide-mobile" style="font-size:11px;font-weight:700;color:#0f172a">Peran Demo:</span>
        <select onchange="if(this.value) window.location.href='<?= url('switch-role?user=') ?>' + this.value" style="font-size:11.5px;font-weight:700;border:none;background:transparent;color:#1e293b;cursor:pointer;outline:none;padding:2px 0">
          <option value="inspektur" <?= $curUname === 'inspektur' ? 'selected' : '' ?>>👤 Inspektur (H. Sarman Syahroni)</option>
          <option value="marwan" <?= $curUname === 'marwan' ? 'selected' : '' ?>>👤 Irban IV (Marwan, M.T)</option>
          <option value="operator_spt" <?= $curUname === 'operator_spt' ? 'selected' : '' ?>>👤 Bag. Perencanaan (SPT)</option>
          <option value="abubakar" <?= $curUname === 'abubakar' ? 'selected' : '' ?>>👤 Dalnis (Abu Bakar, SE)</option>
          <option value="amdattofa" <?= $curUname === 'amdattofa' ? 'selected' : '' ?>>👤 Ketua Tim (Amdat Tofa, SH)</option>
          <option value="budicahyadi" <?= $curUname === 'budicahyadi' ? 'selected' : '' ?>>👤 Ketua Tim (Budi Cahyadi)</option>
          <option value="fakhrurrazi" <?= $curUname === 'fakhrurrazi' ? 'selected' : '' ?>>👤 Anggota (Fakhrurrazi)</option>
          <option value="admin" <?= $curUname === 'admin' ? 'selected' : '' ?>>⚡ Administrator</option>
        </select>
      </div>
    <?php else: ?>
      <!-- User Info Badge Resmi (Untuk Pengguna / Auditor Biasa di Laptop Masing-Masing) -->
      <div style="display:inline-flex;align-items:center;gap:8px;background:#ffffff;border:1px solid #cbd5e1;border-radius:30px;padding:3px 14px 3px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
        <?php $topAvatar = user_avatar_url($curUser); ?>
        <?php if ($topAvatar): ?>
          <img src="<?= $topAvatar ?>" alt="Avatar" style="width:24px;height:24px;border-radius:50%;object-fit:cover;border:1.5px solid #059669">
        <?php else: ?>
          <i class="fa-solid fa-user-check" style="color:#059669;font-size:13px"></i>
        <?php endif; ?>
        <span style="font-size:12px;font-weight:700;color:#0f172a"><?= e($curUser['nama'] ?? 'Pengguna') ?></span>
        <span style="font-size:10px;font-weight:800;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:1px 7px;border-radius:10px;text-transform:uppercase">
          <?= e($curRole) ?>
        </span>
      </div>
    <?php endif; ?>

    <!-- Quick Cloud Drive Link -->
    <a href="<?= url('gdrive') ?>" class="hide-mobile" style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:700;text-decoration:none" title="Google Drive Resmi Terhubung (teamirban4@gmail.com)">
      <i class="fa-brands fa-google-drive" style="color:#16a34a"></i>
      <span>Drive Terhubung</span>
    </a>
  </div>
</div>
