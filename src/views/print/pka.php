<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Program Kerja Audit (PKA) — <?= e($pka['no_pka']) ?></title>
  <style>
    @page {
      size: 330mm 215mm; /* Standar Folio Landscape / Lanskap */
      margin: 12mm 15mm 12mm 15mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Times New Roman", Times, serif;
      font-size: 10pt;
      line-height: 1.3;
      color: #000;
      background: #fff;
      margin: 0;
      padding: 0;
    }
    .no-print {
      background: #f8fafc;
      border-bottom: 1px solid #cbd5e1;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .btn-print {
      background: #0284c7;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-family: sans-serif;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: none;
    }
    .container {
      width: 100%;
      max-width: 1100px;
      margin: 0 auto;
      padding: 10px;
    }
    .kop {
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 3px double #000;
      padding-bottom: 6px;
      margin-bottom: 10px;
    }
    .kop-logo { width: 65px; text-align: center; }
    .kop-logo img { width: 60px; height: auto; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h2 { margin: 0; font-size: 12pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
    .kop-text h1 { margin: 1px 0; font-size: 15pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .kop-text p { margin: 0; font-size: 8pt; line-height: 1.2; }

    .doc-title {
      text-align: center;
      font-size: 12pt;
      font-weight: bold;
      text-decoration: underline;
      text-transform: uppercase;
      margin: 6px 0 2px;
      letter-spacing: 0.5px;
    }
    .doc-sub {
      text-align: center;
      font-size: 9.5pt;
      margin-bottom: 10px;
    }

    table.meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      font-size: 9.5pt;
    }
    table.meta-table td {
      padding: 2px 4px;
      vertical-align: top;
    }

    table.grid-table {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0;
      font-size: 9pt;
    }
    table.grid-table th, table.grid-table td {
      border: 1px solid #000;
      padding: 4px 6px;
      vertical-align: top;
    }
    table.grid-table th {
      background-color: #f1f5f9;
      text-align: center;
      font-weight: bold;
    }

    table.ttd-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
      font-size: 9.5pt;
      page-break-inside: avoid;
    }
    table.ttd-table td {
      vertical-align: top;
      text-align: center;
      padding: 0 10px;
    }

    @media print {
      .no-print { display: none !important; }
      body { margin: 0; background: #fff; }
      .container { padding: 0; width: 100%; max-width: 100%; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <div style="font-family:sans-serif;font-size:13px;color:#475569">
    <b>Preview Cetak:</b> Program Kerja Audit (PKA) Standar APIP — Matriks Pembagian Tugas Anggota 1 &amp; 2
  </div>
  <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="container">
  <!-- KOP SURAT -->
  <!-- KOP SURAT FORMULIR KM.6 -->
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px;border-bottom:2px solid #000;padding-bottom:6px">
    <div>
      <div style="font-size:11pt;font-weight:bold;letter-spacing:0.5px">INSPEKTORAT KABUPATEN ROKAN HILIR</div>
      <div style="font-size:9pt;color:#334155">Unit Kerja : Inspektorat Daerah Kabupaten Rokan Hilir</div>
    </div>
    <div style="border:1.5px solid #000;padding:3px 10px;font-weight:bold;font-size:10pt">
      Formulir KM. 6
    </div>
  </div>

  <div class="doc-title" style="margin:6px 0 2px">PROGRAM KERJA AUDIT (PKA)</div>
  <div class="doc-sub" style="margin-bottom:8px">Nomor PKA : <?= e($pka['no_pka']) ?></div>

  <!-- IDENTITAS PENUGASAN KM.6 -->
  <table class="meta-table">
    <tr>
      <td style="width:170px"><b>Nama Objek Pemeriksaan</b></td>
      <td style="width:10px">:</td>
      <td style="width:380px">Kepenghuluan <?= e($pka['desa_nama']) ?> (Kec. <?= e($pka['kecamatan_nama']) ?>)</td>
      <td style="width:150px"><b>Dasar Penugasan</b></td>
      <td style="width:10px">:</td>
      <td>Surat Perintah Tugas (SPT)</td>
    </tr>
    <tr>
      <td><b>Program Kegiatan</b></td>
      <td>:</td>
      <td>Audit Ketaatan / Reguler Pengelolaan Keuangan Desa</td>
      <td><b>Nomor / Tanggal SPT</b></td>
      <td>:</td>
      <td><?= e($pka['no_spt']) ?> / <?= tgl_id($pka['tgl_spt']) ?></td>
    </tr>
    <tr>
      <td><b>Periode yang Diawasi</b></td>
      <td>:</td>
      <td>Tahun Anggaran <?= (int)$pka['tahun_anggaran'] ?></td>
      <td><b>Lama Penugasan</b></td>
      <td>:</td>
      <td><?= (int)$pka['lama_hari'] ?> Hari (<?= tgl_id($pka['tgl_mulai']) ?> s.d <?= tgl_id($pka['tgl_selesai']) ?>)</td>
    </tr>
  </table>

  <!-- MATRIKS PROSEDUR AUDIT KM.6 (8 KOLOM RESMI DENGAN ALOKASI WAKTU) -->
  <table class="grid-table">
    <thead>
      <tr>
        <th rowspan="2" style="width:28px">NO</th>
        <th rowspan="2" style="width:32%">Tujuan dan Prosedur Pengawasan</th>
        <th colspan="2" style="width:22%">Rencana</th>
        <th colspan="2" style="width:22%">Realisasi</th>
        <th rowspan="2" style="width:10%">No KKP / Ref KKA</th>
        <th rowspan="2" style="width:10%">Keterangan</th>
      </tr>
      <tr>
        <th style="width:15%">Dilaksanakan oleh</th>
        <th style="width:7%">Waktu</th>
        <th style="width:15%">Dilaksanakan oleh</th>
        <th style="width:7%">Waktu</th>
      </tr>
      <tr style="background:#f8fafc;font-size:7.5pt">
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach ($langkah as $l): ?>
        <tr>
          <td align="center"><b><?= $no++ ?></b></td>
          <td>
            <b><?= e($l['bidang_nama']) ?></b>
            <div style="font-size:8.5pt;margin-top:2px"><?= e($l['uraian_prosedur']) ?></div>
            <?php if (!empty($l['tujuan_pengujian'])): ?>
              <div style="font-size:7.5pt;color:#334155;margin-top:2px"><i>Tujuan: <?= e($l['tujuan_pengujian']) ?></i></div>
            <?php endif; ?>
          </td>
          <td><b><?= e($l['pelaksana_nama'] ?: 'Ketua Tim / Bersama') ?></b></td>
          <td align="center"><?= (int)($l['waktu_rencana_hari'] ?: 1) ?> hari</td>
          <td><?= e($l['pelaksana_realisasi_nama'] ?: ($l['pelaksana_nama'] ?: 'Ketua Tim / Bersama')) ?></td>
          <td align="center"><?= (int)($l['waktu_realisasi_hari'] ?: ($l['waktu_rencana_hari'] ?: 1)) ?> hari</td>
          <td align="center"><b><?= e($l['ref_kka_nomor'] ?: '-') ?></b></td>
          <td align="center">
            <?php if ($l['status_pelaksanaan'] === 'SELESAI'): ?>
              <span style="font-weight:bold;color:#047857">&#10004; Teruji</span>
            <?php elseif ($l['status_pelaksanaan'] === 'SEDANG'): ?>
              <span style="color:#d97706">Proses</span>
            <?php else: ?>
              <span style="color:#64748b">Rencana</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <?php 
      $totRencana = 0;
      $totRealisasi = 0;
      foreach ($langkah as $l) {
        $totRencana += (int)($l['waktu_rencana_hari'] ?: 1);
        $totRealisasi += (int)($l['waktu_realisasi_hari'] ?: ($l['waktu_rencana_hari'] ?: 1));
      }
    ?>
    <tfoot>
      <tr style="background:#f1f5f9;font-weight:bold">
        <td colspan="3" align="right" style="padding:6px 10px;font-size:9.5pt">
          <b>JUMLAH ALOKASI WAKTU (TOTAL) :</b>
        </td>
        <td align="center" style="font-size:9.5pt;font-weight:bold;color:#0f172a">
          <?= $totRencana ?> hari
        </td>
        <td></td>
        <td align="center" style="font-size:9.5pt;font-weight:bold;color:#0f172a">
          <?= $totRealisasi ?> hari
        </td>
        <td colspan="2" align="center" style="font-size:8.5pt;color:#475569">
          Target SPT: <b><?= (int)$pka['lama_hari'] ?> hari kerja</b>
        </td>
      </tr>
    </tfoot>
  </table>

  <!-- TANGGAL DAN TIGA KOLOM TANDA TANGAN BERJENJANG STANDAR KM.6 -->
  <div style="text-align:right;font-size:9.5pt;margin:12px 10px 4px 0">
    Bagansiapiapi, <?= tgl_id($pka['tgl_pka']) ?>
  </div>

  <table class="ttd-table">
    <tr>
      <td style="width:33%">
        <b>MENYETUJUI:</b><br>
        PENGENDALI TEKNIS (DALNIS),
        <br><br><br><br>
        <u><b><?= e($pka['dalnis_nama']) ?></b></u><br>
        Auditor Ahli Madya
      </td>
      <td style="width:34%">
        <b>DISUSUN OLEH:</b><br>
        KETUA TIM PEMERIKSA,
        <br><br><br><br>
        <u><b><?= e($pka['ketua_tim_nama']) ?></b></u><br>
        Auditor Ahli Muda
      </td>
      <td style="width:33%">
        <b>MENGETAHUI:</b><br>
        WAKIL PENANGGUNG JAWAB,
        <br><br><br><br>
        <u><b><?= e($pka['irban_nama']) ?></b></u><br>
        Inspektur Pembantu
      </td>
    </tr>
  </table>
</div>

</body>
</html>

