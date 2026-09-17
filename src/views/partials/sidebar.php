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
      <h1>KKA DIGITAL</h1>
      <p>Inspektorat Rokan Hilir</p>
    </div>

    <button class="sidebar-close" id="sidebarCloseBtn" aria-label="Tutup menu">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <nav class="nav">
    <a href="<?= url('dashboard') ?>" class="nav-item<?= nav_active('/dashboard', $current) ?>" data-testid="nav-dashboard">
      <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
    </a>

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

    <div class="nav-section">Pelaksanaan Audit (KKA ADTT)</div>
    <a href="<?= url('sesi') ?>" class="nav-item<?= nav_active('/sesi', $current) ?>" data-testid="nav-sesi">
      <i class="fa-solid fa-clipboard-list"></i><span>Kertas Kerja (KKA)</span>
    </a>
    <a href="<?= url('rekap') ?>" class="nav-item<?= nav_active('/rekap', $current) ?>" data-testid="nav-rekap">
      <i class="fa-solid fa-chart-column"></i><span>Rekap Belanja</span>
    </a>
    <a href="<?= url('master') ?>" class="nav-item<?= nav_active('/master', $current) ?>" data-testid="nav-master">
      <i class="fa-solid fa-folder-tree"></i><span>Master KKA Fisik</span>
    </a>

    <div class="nav-section">Hasil Pengawasan &amp; Laporan</div>
    <a href="<?= url('temuan') ?>" class="nav-item<?= nav_active('/temuan', $current) ?>" data-testid="nav-temuan">
      <i class="fa-solid fa-file-circle-exclamation"></i><span>Konsep Temuan (KTP)</span>
    </a>
    <a href="<?= url('lhp') ?>" class="nav-item<?= nav_active('/lhp', $current) ?>" data-testid="nav-lhp">
      <i class="fa-solid fa-file-shield"></i><span>Laporan Hasil Audit (LHP)</span>
    </a>

    <div class="nav-section">Audit Ketaatan OPD</div>
    <a href="javascript:void(0)" onclick="alert('ℹ️ MODUL KKA AUDIT KETAATAN DINAS/OPD (SIAKAT)\n\nModul ini sedang disiapkan untuk integrasi kepatuhan dinas/OPD se-Kabupaten Rokan Hilir pada pembaruan tahap berikutnya.')" class="nav-item" style="opacity:0.85" title="Segera Hadir">
      <i class="fa-solid fa-scale-balanced" style="color:#60a5fa"></i><span>KKA Ketaatan OPD</span>
      <span class="badge" style="background:#2563eb;color:#fff;font-size:9.5px;padding:2px 6px;border-radius:10px;margin-left:auto;font-weight:700">Segera</span>
    </a>

    <div class="nav-section">Panduan &amp; Pengaturan</div>
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

  <!-- Quick Demo Role Switcher in Sidebar -->
  <div style="margin:8px 12px 12px;padding:8px 10px;background:rgba(30,41,59,0.75);border:1px dashed rgba(251,191,36,0.5);border-radius:8px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px">
      <span style="font-size:10px;font-weight:700;color:#fbbf24;text-transform:uppercase;letter-spacing:0.5px">
        <i class="fa-solid fa-masks-theater"></i> Simulasi Peran Demo
      </span>
      <span style="font-size:9px;background:#b45309;color:#fff;padding:1px 5px;border-radius:4px;font-weight:700">1-Klik</span>
    </div>
    <select onchange="if(this.value) window.location.href='<?= url('switch-role?user=') ?>' + this.value" style="width:100%;font-size:11px;font-weight:700;padding:5px 7px;border-radius:6px;background:#0f172a;color:#f8fafc;border:1px solid #334155;cursor:pointer">
      <option value="">-- Ganti Akun Pejabat --</option>
      <option value="inspektur" <?= ($user['username'] ?? '') === 'inspektur' ? 'selected' : '' ?>>👤 Inspektur (H. Sarman Syahroni)</option>
      <option value="marwan" <?= ($user['username'] ?? '') === 'marwan' ? 'selected' : '' ?>>👤 Irban IV (Marwan, M.T)</option>
      <option value="operator_spt" <?= ($user['username'] ?? '') === 'operator_spt' ? 'selected' : '' ?>>👤 Bag. Perencanaan (SPT)</option>
      <option value="abubakar" <?= ($user['username'] ?? '') === 'abubakar' ? 'selected' : '' ?>>👤 Dalnis (Abu Bakar, SE)</option>
      <option value="amdattofa" <?= ($user['username'] ?? '') === 'amdattofa' ? 'selected' : '' ?>>👤 Ketua Tim (Amdat Tofa, SH)</option>
      <option value="budicahyadi" <?= ($user['username'] ?? '') === 'budicahyadi' ? 'selected' : '' ?>>👤 Ketua Tim (Budi Cahyadi)</option>
      <option value="fakhrurrazi" <?= ($user['username'] ?? '') === 'fakhrurrazi' ? 'selected' : '' ?>>👤 Anggota (Fakhrurrazi)</option>
      <option value="admin" <?= ($user['username'] ?? '') === 'admin' ? 'selected' : '' ?>>⚡ Admin Sistem</option>
    </select>
  </div>

  <div class="sidebar-foot">
    <a href="<?= url('profile') ?>" class="userbox" data-testid="userbox">
      <div class="avatar"><?= e(strtoupper(mb_substr($user['nama'] ?? 'U', 0, 1))) ?></div>
      <div class="info">
        <div class="name"><?= e($user['nama']) ?></div>
        <div class="email" style="font-size:11px;color:#cbd5e1;text-transform:capitalize"><?= e($user['role']) ?> &bull; <?= e(mb_substr($user['jabatan'] ?? '', 0, 24)) ?></div>
      </div>
    </a>
    <a class="logout" href="<?= url('logout') ?>" data-testid="logout-btn">
      <i class="fa-solid fa-right-from-bracket"></i> Keluar
    </a>
  </div>
</aside>