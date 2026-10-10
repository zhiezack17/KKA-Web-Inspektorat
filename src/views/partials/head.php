<?php $title = $title ?? 'E-LHP - Inspektorat Rokan Hilir'; ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?></title>
<link rel="icon" href="<?= asset('img/logo-rohil.png') ?>" type="image/png">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=2026.2.11">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  /* === E-LHP AESTHETIC SHIMMER & KILATAN GLOW === */
  @keyframes elhpShimmer {
    0% { background-position: -200% center; }
    100% { background-position: 200% center; }
  }
  @keyframes elhpSparkle {
    0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.85; filter: drop-shadow(0 0 4px #facc15); }
    50% { transform: scale(1.3) rotate(20deg); opacity: 1; filter: drop-shadow(0 0 10px #fde047); }
  }
  .elhp-brand-wrap { display: flex; flex-direction: column; }
  .elhp-title {
    margin: 0;
    font-size: 19px !important;
    font-weight: 900 !important;
    letter-spacing: 1.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    line-height: 1.2;
  }
  .elhp-text {
    background: linear-gradient(110deg, #ffffff 15%, #fde047 35%, #ffffff 50%, #86efac 70%, #ffffff 85%);
    background-size: 200% auto;
    color: #fff;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: elhpShimmer 3.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 900;
  }
  .elhp-sparkle {
    color: #fde047;
    font-size: 13px;
    display: inline-block;
    animation: elhpSparkle 2.2s ease-in-out infinite;
  }
  .elhp-badge-pro {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.8px;
    padding: 1.5px 6px;
    border-radius: 99px;
    background: linear-gradient(135deg, rgba(245,158,11,0.3), rgba(16,185,129,0.3));
    color: #fef08a;
    border: 1px solid rgba(250,204,21,0.5);
    box-shadow: 0 0 8px rgba(250,204,21,0.25);
    text-transform: uppercase;
  }
  .sidebar {
    width: 276px;
  }
  @media (min-width: 993px) {
    body.sidebar-collapsed .sidebar,
    html.sidebar-collapsed .sidebar {
      margin-left: -276px;
    }
  }
  .sidebar .brand {
    gap: 10px !important;
    padding: 16px 14px 14px !important;
  }
  .sidebar .brand-logo {
    width: 38px !important;
    height: 42px !important;
    flex-shrink: 0 !important;
  }
  .sidebar .brand-text {
    flex: 1 !important;
    min-width: 0 !important;
    overflow: hidden !important;
  }
  .elhp-sub {
    margin: 3px 0 0 !important;
    font-size: 9.75px !important;
    color: #a7f3d0 !important;
    letter-spacing: 0.15px !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    text-transform: none !important;
    line-height: 1.2 !important;
  }
</style>
<script>
  try {
    if (localStorage.getItem('kka_sidebar_collapsed') === '1' && window.innerWidth > 992) {
      document.documentElement.classList.add('sidebar-collapsed');
      document.addEventListener('DOMContentLoaded', function() {
        if (document.body) document.body.classList.add('sidebar-collapsed');
      });
    }
  } catch (e) {}
</script>
</head>
<body class="<?= e($body_class ?? 'app') ?>">
<div class="mobile-backdrop" id="mobileBackdrop"></div>
