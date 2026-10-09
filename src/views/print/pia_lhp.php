<?php $title = 'Cetak LHP & DPP PIA — ' . ($spt['no_spt'] ?? ''); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?= e($title) ?></title>
  <style>
    @page { size: A4 portrait; margin: 15mm 20mm 15mm 20mm; }
    * { box-sizing: border-box; }
    body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.4; color: #000; margin: 0; padding: 0; background: #fff; }
    .page { width: 100%; max-width: 210mm; margin: 0 auto; padding: 10px 0; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .kop {
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 3px double #000;
      padding-bottom: 8px;
      margin-bottom: 16px;
    }
    .kop-logo { width: 75px; text-align: center; }
    .kop-logo img { width: 70px; height: auto; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h2 { margin: 0; font-size: 13pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
    .kop-text h1 { margin: 2px 0; font-size: 16pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .kop-text p { margin: 0; font-size: 8.5pt; line-height: 1.25; }
    h3 { font-size: 11pt; margin-top: 16px; margin-bottom: 6px; text-decoration: underline; text-transform: uppercase; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .table-bordered th, .table-bordered td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; font-size: 10.5pt; }
    .table-bordered th { background: #f8fafc; font-weight: bold; }
    .signature { margin-top: 30px; width: 100%; }
    .signature td { width: 50%; vertical-align: top; }
    @media print {
      body { background: transparent; }
      .page { margin: 0; padding: 0; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;padding:10px 20px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:99">
    <a href="javascript:history.back()" style="color:#0284c7;text-decoration:none;font-family:sans-serif;font-size:13px;font-weight:600">&larr; Kembali</a>
    <button onclick="window.print()" style="background:#0284c7;color:white;border:none;padding:8px 16px;border-radius:4px;font-weight:600;cursor:pointer;font-size:13px;font-family:sans-serif">Cetak Dokumen (PDF)</button>
  </div>
  <div class="page">
    <div class="kop">
      <div class="kop-logo">
        <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rohil" onerror="this.style.display='none'">
      </div>
      <div class="kop-text">
        <h2>Pemerintah Kabupaten Rokan Hilir</h2>
        <h1>Inspektorat Daerah</h1>
        <p>Jalan Perkantoran No. 01, Kompleks Perkantoran Bagansiapiapi, Kab. Rokan Hilir, Riau<br>
        Posel: <code>inspektorat@rohilkab.go.id</code> &bull; Laman: <code>https://kka.arsipdigital-inspektorat.com</code></p>
      </div>
    </div>

    <div class="text-center font-bold" style="font-size:13pt;margin-bottom:16px">
      <u>LAPORAN HASIL PENELAAHAN (LHP) INFORMASI AWAL</u><br>
      <span style="font-size:11pt">DAN DESAIN PENUGASAN PENGAWASAN (DPP)</span>
    </div>

    <table style="width:100%;margin-bottom:16px;font-size:11pt">
      <tr><td style="width:160px;font-weight:bold">Dasar Penugasan</td><td style="width:10px">:</td><td>Surat Tugas PIA No. <?= e($spt['no_spt'] ?? '-') ?></td></tr>
      <tr><td style="font-weight:bold">Objek Pemeriksaan</td><td>:</td><td>Kepenghuluan <?= e($spt['desa_nama'] ?? '-') ?> (Kec. <?= e($spt['kecamatan_nama'] ?? '-') ?>)</td></tr>
      <tr><td style="font-weight:bold">Tahun Anggaran</td><td>:</td><td>TA <?= e($spt['tahun_anggaran'] ?? date('Y')) ?></td></tr>
    </table>

    <h3>A. KERTAS KERJA AUDIT (KKA) PIA</h3>
    <table class="table-bordered">
      <tr><th style="width:32%;text-align:left">Daftar Regulasi &amp; Juknis Terkait</th><td><?= nl2br(e($lhp['daftar_regulasi'] ?? '-')) ?></td></tr>
      <tr><th style="text-align:left">Indikasi Masalah / Informasi Awal</th><td><?= nl2br(e($lhp['indikasi_masalah'] ?? '-')) ?></td></tr>
      <tr><th style="text-align:left">Titik Kritis Risiko (Risk Points)</th><td><?= nl2br(e($lhp['titik_kritis_risiko'] ?? '-')) ?></td></tr>
      <tr><th style="text-align:left">Ceklis Pengumpulan Data Tambahan</th><td><?= nl2br(e($lhp['ceklis_data_tambahan'] ?? '-')) ?></td></tr>
    </table>

    <h3>B. LAPORAN HASIL PENELAAHAN &amp; DESAIN PENUGASAN (DPP)</h3>
    <table class="table-bordered">
      <tr><th style="width:32%;text-align:left">Hasil Penelaahan</th><td><?= nl2br(e($lhp['hasil_penelaahan'] ?? '-')) ?></td></tr>
      <tr><th style="text-align:left">Simpulan &amp; Rekomendasi PIA</th><td><?= nl2br(e($lhp['simpulan_rekomendasi'] ?? '-')) ?></td></tr>
      <tr><th style="text-align:left">Sasaran Pengawasan (Objective)</th><td><?= nl2br(e($lhp['dpp_sasaran'] ?? '-')) ?></td></tr>
      <tr><th style="text-align:left">Metodologi &amp; Langkah Kerja</th><td><?= nl2br(e($lhp['dpp_metodologi'] ?? '-')) ?></td></tr>
      <tr><th style="text-align:left">Keputusan Final Inspektur</th><td class="font-bold">
        <?php
          if(($lhp['keputusan_inspektur'] ?? '') === 'LAYAK_AUDIT') echo "LAYAK AUDIT / TERBITKAN ND ADTT (GO)";
          elseif(($lhp['keputusan_inspektur'] ?? '') === 'ARSIP') echo "TIDAK CUKUP BUKTI / ARSIPKAN (NO-GO)";
          else echo "BELUM DIPUTUSKAN";
        ?>
      </td></tr>
    </table>

    <table class="signature">
      <tr>
        <td style="width:50%"></td>
        <td class="text-center">
          Bagansiapiapi, <?= tgl_id(date('Y-m-d')) ?><br>
          <b>INSPEKTUR DAERAH KABUPATEN ROKAN HILIR</b>
          <br><br><br><br>
          <u><b>H. SARMAN SYAHRONI, ST., M.IP., CGCAE</b></u><br>
          Pembina Utama Muda / IV.c<br>
          NIP. 19760810 200312 1 004
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
