<?php
$tbTitle = $title ?? 'KKA Digital';
$tbIcon  = $icon ?? 'fa-solid fa-layer-group';
$curUser = $GLOBALS['auth']->user() ?? [];
$curUname = $curUser['username'] ?? '';
?>
<div class="topbar">
  <div class="crumb">
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">☰</button>
    <i class="<?= $tbIcon ?>"></i> <b><?= e($tbTitle) ?></b>
  </div>
  <div class="topbar-right" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <!-- Quick Demo Switcher Widget -->
    <div style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#f8fafc,#f1f5f9);border:1px solid #cbd5e1;border-radius:20px;padding:3px 10px 3px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.04)">
      <span style="font-size:11px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:5px">
        <i class="fa-solid fa-masks-theater" style="color:#d97706"></i> <span class="hide-mobile">Peran Demo:</span>
      </span>
      <select onchange="if(this.value) window.location.href='<?= url('switch-role?user=') ?>' + this.value" style="font-size:11.5px;font-weight:700;border:none;background:transparent;color:#1e293b;cursor:pointer;outline:none;padding:2px 0">
        <option value="inspektur" <?= $curUname === 'inspektur' ? 'selected' : '' ?>>👤 Inspektur Daerah (H. Sarman Syahroni)</option>
        <option value="marwan" <?= $curUname === 'marwan' ? 'selected' : '' ?>>👤 Irban IV (Marwan, M.T)</option>
        <option value="operator_spt" <?= $curUname === 'operator_spt' ? 'selected' : '' ?>>👤 Bag. Perencanaan (Operator SPT)</option>
        <option value="abubakar" <?= $curUname === 'abubakar' ? 'selected' : '' ?>>👤 Dalnis (Abu Bakar, SE)</option>
        <option value="amdattofa" <?= $curUname === 'amdattofa' ? 'selected' : '' ?>>👤 Ketua Tim (Amdat Tofa, SH)</option>
        <option value="budicahyadi" <?= $curUname === 'budicahyadi' ? 'selected' : '' ?>>👤 Ketua Tim (Budi Cahyadi, S.A.P)</option>
        <option value="fakhrurrazi" <?= $curUname === 'fakhrurrazi' ? 'selected' : '' ?>>👤 Anggota Tim (Fakhrurrazi, S.A.P)</option>
        <option value="admin" <?= $curUname === 'admin' ? 'selected' : '' ?>>⚡ Administrator Sistem</option>
      </select>
    </div>

    <span class="badge hide-mobile" style="background:#e0e7ff;color:#3730a3;font-size:11.5px;font-weight:600;padding:5px 10px;border-radius:20px">
      <i class="fa-solid fa-shield-halved"></i> Inspektorat Rokan Hilir
    </span>
  </div>
</div>
