<?php
/**
 * Cetak Matriks Konsep Temuan Pemeriksaan (KTP) & Rekomendasi
 * Inspektorat Kabupaten Rokan Hilir - Standar A4 Landscape
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Matriks Temuan - Kepenghuluan <?= e($desa['nama']) ?> TA <?= $tahun ?></title>
  <style>
    @page { size: A4 landscape; margin: 12mm 15mm; }
    * { box-sizing: border-box; }
    body { font-family: "Bookman Old Style", Georgia, "Times New Roman", serif; font-size: 11pt; line-height: 1.35; color: #000; margin: 0; padding: 0; }
    .header { text-align: center; border-bottom: 2.5px solid #000; padding-bottom: 8px; margin-bottom: 14px; }
    .header h2 { margin: 0; font-size: 13pt; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
    .header h3 { margin: 3px 0 0; font-size: 12pt; text-transform: uppercase; font-weight: 700; }
    .header p { margin: 2px 0 0; font-size: 10pt; font-style: italic; }
    
    .meta-table { width: 100%; margin-bottom: 12px; font-size: 10pt; border-collapse: collapse; }
    .meta-table td { padding: 2px 4px; vertical-align: top; }
    
    table.matriks { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9pt; }
    table.matriks th, table.matriks td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
    table.matriks th { background: #f2f2f2; text-align: center; font-weight: bold; }
    
    .num { text-align: right; white-space: nowrap; }
    .center { text-align: center; }
    
    .ttd-box { width: 100%; margin-top: 25px; page-break-inside: avoid; }
    .ttd-table { width: 100%; border-collapse: collapse; }
    .ttd-table td { width: 33.3%; text-align: center; vertical-align: top; font-size: 10pt; }
    
    .no-print { position: fixed; top: 12px; right: 12px; background: #fff; border: 1px solid #ccc; padding: 8px 14px; border-radius: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); font-family: sans-serif; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>

<div class="no-print">
  <button onclick="window.print()" style="background:#059669;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;font-weight:bold">🖨️ Cetak Matriks</button>
  <button onclick="window.close()" style="background:#64748b;color:#fff;border:none;padding:8px 12px;border-radius:4px;cursor:pointer;margin-left:6px">Tutup</button>
</div>

<div class="header">
  <h2>INSPEKTORAT DAERAH KABUPATEN ROKAN HILIR</h2>
  <h3>MATRIKS HASIL PEMERIKSAAN / KONSEP TEMUAN &amp; REKOMENDASI</h3>
  <p>Pemeriksaan Reguler Ketaatan Pengelolaan Keuangan Kepenghuluan (ADTT)</p>
</div>

<table class="meta-table">
  <tr>
    <td style="width:18%"><b>Objek Pengawasan</b></td>
    <td style="width:1%">:</td>
    <td style="width:45%">Kepenghuluan <?= e($desa['nama']) ?>, Kec. <?= e($desa['kecamatan_nama']) ?></td>
    <td style="width:18%"><b>Nomor SPT</b></td>
    <td style="width:1%">:</td>
    <td><?= e($spt['no_spt'] ?? '-') ?></td>
  </tr>
  <tr>
    <td><b>Tahun Anggaran</b></td>
    <td>:</td>
    <td><?= $tahun ?></td>
    <td><b>Tanggal SPT</b></td>
    <td>:</td>
    <td><?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '-' ?></td>
  </tr>
</table>

<table class="matriks">
  <thead>
    <tr>
      <th style="width:30px">NO</th>
      <th style="width:70px">KODE KTP</th>
      <th style="width:160px">POKOK TEMUAN (KONDISI)</th>
      <th style="width:150px">KRITERIA</th>
      <th style="width:120px">SEBAB</th>
      <th style="width:120px">AKIBAT</th>
      <th style="width:90px">NILAI (RP)</th>
      <th style="width:170px">REKOMENDASI</th>
      <th style="width:110px">TANGGAPAN AUDITI</th>
    </tr>
    <tr style="font-size:8pt;background:#fafafa">
      <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($daftarTemuan)): ?>
      <tr>
        <td colspan="9" style="text-align:center;padding:24px">Tidak terdapat temuan pemeriksaan yang terdata.</td>
      </tr>
    <?php else: $no=1; $tot=0; foreach ($daftarTemuan as $t): $tot += (float)$t['nominal']; ?>
      <tr>
        <td class="center"><?= $no++ ?></td>
        <td class="center"><b><?= e($t['nomor_temuan']) ?></b></td>
        <td>
          <b><?= e($t['judul']) ?></b><br>
          <span style="font-size:8.5pt"><?= nl2br(e($t['kondisi'])) ?></span>
        </td>
        <td style="font-size:8.5pt"><?= nl2br(e($t['kriteria'])) ?></td>
        <td style="font-size:8.5pt"><?= nl2br(e($t['sebab'])) ?></td>
        <td style="font-size:8.5pt"><?= nl2br(e($t['akibat'])) ?></td>
        <td class="num" style="font-weight:bold">
          <?= (float)$t['nominal'] > 0 ? number_format((float)$t['nominal'],0,',','.') : '-' ?>
        </td>
        <td style="font-size:8.5pt"><?= nl2br(e($t['rekomendasi'])) ?></td>
        <td style="font-size:8.5pt"><?= nl2br(e($t['tanggapan_auditi'] ?: '-')) ?></td>
      </tr>
    <?php endforeach; ?>
      <tr style="font-weight:bold;background:#f9f9f9">
        <td colspan="6" class="center">JUMLAH TOTAL NILAI TEMUAN</td>
        <td class="num"><?= rupiah($tot) ?></td>
        <td colspan="2"></td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<div class="ttd-box">
  <table class="ttd-table">
    <tr>
      <td>
        Mengetahui,<br>
        <b>Inspektur Pembantu (Irban)</b><br><br><br><br><br>
        <b><u><?= e($spt['wakil_pj_nama'] ?? '................................................') ?></u></b><br>
        NIP. <?= !empty($spt['wakil_pj_nip']) ? e($spt['wakil_pj_nip']) : '................................................' ?>
      </td>
      <td>
        Disetujui Oleh,<br>
        <b>Pengendali Teknis (Dalnis)</b><br><br><br><br><br>
        <b><u><?= e($spt['dalnis_nama'] ?? '................................................') ?></u></b><br>
        NIP. <?= !empty($spt['dalnis_nip']) ? e($spt['dalnis_nip']) : '................................................' ?>
      </td>
      <td>
        Bagansiapiapi, <?= tgl_id(date('Y-m-d')) ?><br>
        Disusun Oleh,<br>
        <b>Ketua Tim Pemeriksa</b><br><br><br><br><br>
        <b><u><?= e($spt['ketua_tim_nama'] ?? '................................................') ?></u></b><br>
        NIP. <?= !empty($spt['ketua_tim_nip']) ? e($spt['ketua_tim_nip']) : '................................................' ?>
      </td>
    </tr>
  </table>
</div>

</body>
</html>
