<?php
/**
 * Cetak Dokumen Notisi Hasil Pemeriksaan (NHP)
 * Inspektorat Daerah Kabupaten Rokan Hilir - Standar A4 Portrait
 */
$totalTemuan = 0;
foreach ($daftarTemuan as $t) {
    $totalTemuan += (float)$t['nominal'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Notisi Hasil Pemeriksaan (NHP) - Kepenghuluan <?= e($desa['nama']) ?> TA <?= $tahun ?></title>
  <style>
    @page { size: A4 portrait; margin: 15mm 20mm; }
    * { box-sizing: border-box; }
    body { font-family: "Bookman Old Style", Georgia, "Times New Roman", serif; font-size: 10.5pt; line-height: 1.4; color: #000; margin: 0; padding: 0; }
    
    .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 16px; position: relative; }
    .kop h1 { margin: 0; font-size: 13.5pt; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
    .kop h2 { margin: 2px 0 0; font-size: 12pt; text-transform: uppercase; font-weight: 700; }
    .kop p { margin: 3px 0 0; font-size: 8.5pt; font-family: Arial, sans-serif; line-height: 1.3; }
    
    .judul-doc { text-align: center; margin: 18px 0 16px; }
    .judul-doc h3 { margin: 0; font-size: 12pt; text-decoration: underline; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
    .judul-doc span { font-size: 10pt; font-weight: 700; }

    .meta-box { width: 100%; margin-bottom: 14px; font-size: 10pt; border-collapse: collapse; }
    .meta-box td { padding: 2px 4px; vertical-align: top; }

    .paragraf { text-align: justify; text-indent: 30px; margin-bottom: 12px; }

    table.notisi { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 9pt; }
    table.notisi th, table.notisi td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
    table.notisi th { background: #f2f2f2; text-align: center; font-weight: bold; }
    
    .num { text-align: right; white-space: nowrap; }
    .center { text-align: center; }

    .ttd-box { width: 100%; margin-top: 26px; page-break-inside: avoid; }
    .ttd-table { width: 100%; border-collapse: collapse; }
    .ttd-table td { width: 50%; text-align: center; vertical-align: top; font-size: 10pt; padding: 6px 14px; }

    .no-print { position: fixed; top: 12px; right: 12px; background: #fff; border: 1px solid #ccc; padding: 8px 14px; border-radius: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); font-family: sans-serif; z-index: 9999; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>

<div class="no-print">
  <button onclick="window.print()" style="background:#0284c7;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;font-weight:bold">🖨️ Cetak Dokumen NHP</button>
  <button onclick="window.close()" style="background:#64748b;color:#fff;border:none;padding:8px 12px;border-radius:4px;cursor:pointer;margin-left:6px">Tutup</button>
</div>

<!-- KOP RESMI INSPEKTORAT ROHIL -->
<div class="kop">
  <h1>PEMERINTAH KABUPATEN ROKAN HILIR</h1>
  <h2>INSPEKTORAT DAERAH</h2>
  <p>Komplek Perkantoran Batu Enam, Bagansiapiapi - Riau<br>Email: inspektorat@rohilkab.go.id | Website: www.rohilkab.go.id</p>
</div>

<div class="judul-doc">
  <h3>NOTISI HASIL PEMERIKSAAN (NHP)</h3>
  <span>Nomor: 700/NHP-ITKAB/<?= strtoupper(str_replace(' ', '-', e($desa['nama']))) ?>/<?= $tahun ?></span>
</div>

<p class="paragraf">
  Berdasarkan Surat Perintah Tugas Inspektur Kabupaten Rokan Hilir Nomor <b><?= e($spt['no_spt'] ?? '700.1.2.1/SPT/ITKAB-DESA/' . $tahun) ?></b> tanggal <b><?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : date('d F Y') ?></b>, Tim Pemeriksa APIP Inspektorat Kabupaten Rokan Hilir telah melaksanakan Pemeriksaan Ketaatan atas Pengelolaan Keuangan Kepenghuluan <b><?= e($desa['nama']) ?></b> Kecamatan <b><?= e($desa['kecamatan_nama']) ?></b> Tahun Anggaran <b><?= $tahun ?></b>.
</p>

<p class="paragraf" style="margin-top:-6px">
  Sehubungan dengan telah selesainya pengujian lapangan, bersama ini disampaikan pokok-pokok temuan pemeriksaan dan rekomendasi perbaikan untuk dimintakan tanggapan serta konfirmasi rencana tindak lanjut dari Pihak Auditi sebagai berikut:
</p>

<!-- TABEL TEMUAN & REKOMENDASI -->
<table class="notisi">
  <thead>
    <tr>
      <th style="width:28px">NO</th>
      <th style="width:70px">KODE KTP</th>
      <th style="width:160px">POKOK TEMUAN (KONDISI FAKTA)</th>
      <th style="width:90px">NILAI (RP)</th>
      <th>REKOMENDASI PEMERIKSA</th>
      <th style="width:130px">TANGGAPAN &amp; KOMITMEN AUDITI</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($daftarTemuan)): ?>
      <tr>
        <td colspan="6" class="center" style="padding:20px;font-style:italic">
          Tidak ditemukan adanya penyimpangan atau ketidakpatuhan material (Nihil Temuan).
        </td>
      </tr>
    <?php else: $no=1; foreach ($daftarTemuan as $t): ?>
      <tr>
        <td class="center"><?= $no++ ?></td>
        <td class="center" style="font-weight:700"><?= e($t['nomor_temuan']) ?></td>
        <td>
          <b><?= e($t['judul']) ?></b>
          <div style="margin-top:4px;font-size:8pt;color:#333;line-height:1.35">
            <?= nl2br(e($t['kondisi'])) ?>
          </div>
        </td>
        <td class="num" style="font-weight:700">
          <?= (float)$t['nominal'] > 0 ? number_format((float)$t['nominal'], 0, ',', '.') : '-' ?>
        </td>
        <td style="font-size:8.5pt;line-height:1.35">
          <?= nl2br(e($t['rekomendasi'])) ?>
        </td>
        <td style="font-size:8.5pt;line-height:1.35">
          <?= !empty($t['tanggapan_auditi']) ? nl2br(e($t['tanggapan_auditi'])) : '<i>Menindaklanjuti rekomendasi sesuai batas waktu 60 hari kalender.</i>' ?>
        </td>
      </tr>
    <?php endforeach; endif; ?>
  </tbody>
  <tfoot>
    <tr style="background:#f2f2f2;font-weight:700">
      <td colspan="3" class="center">TOTAL NILAI TEMUAN</td>
      <td class="num"><?= $totalTemuan > 0 ? 'Rp ' . number_format($totalTemuan, 0, ',', '.') : 'Rp 0' ?></td>
      <td colspan="2"></td>
    </tr>
  </tfoot>
</table>

<p style="font-size:9.5pt;line-height:1.4;margin:12px 0 0">
  <b>Catatan:</b><br>
  1. Notisi Hasil Pemeriksaan (NHP) ini merupakan lembar konfirmasi temuan lapangan sebelum diterbitkannya Laporan Hasil Pengawasan (LHP) Definitif.<br>
  2. Pihak Auditi (Pj. Penghulu / Kaur Keuangan) menyatakan menerima temuan ini dan berkomitmen menindaklanjuti rekomendasi selambat-lambatnya <b>60 (enam puluh) hari kalender</b> sejak naskah LHP diterima.
</p>

<!-- TANDA TANGAN BERSAMA -->
<div class="ttd-box">
  <table class="ttd-table">
    <tr>
      <td>
        Bagansiapiapi, <?= date('d F Y') ?><br>
        <b>Pihak Auditi,</b><br>
        Pj. Penghulu <?= e($desa['nama']) ?>
        <br><br><br><br><br>
        <b><u>( .................................................... )</u></b><br>
        NIP. -
      </td>
      <td>
        Bagansiapiapi, <?= date('d F Y') ?><br>
        <b>Tim Pemeriksa APIP,</b><br>
        Ketua Tim Pemeriksaan
        <br><br><br><br><br>
        <b><u><?= e($spt['ketua_tim_nama'] ?? 'Ketua Tim Pemeriksa') ?></u></b><br>
        NIP. <?= e($spt['ketua_tim_nip'] ?? '-') ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:20px;text-align:center">
        Mengetahui / Menyetujui,<br>
        <b>Pengendali Teknis (Dalnis)</b>
        <br><br><br><br><br>
        <b><u><?= e($spt['dalnis_nama'] ?? 'Pengendali Teknis') ?></u></b><br>
        NIP. <?= e($spt['dalnis_nip'] ?? '-') ?>
      </td>
    </tr>
  </table>
</div>

</body>
</html>
