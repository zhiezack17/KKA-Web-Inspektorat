<?php
/**
 * Cetak Resmi Rekapitulasi Hasil Pengawasan dan Pemantauan Tindak Lanjut (TLHP)
 * Standar Pengawasan BPKP / Siswaskeudes & APIP Daerah
 * Format: A4 Landscape
 * Inspektorat Daerah Kabupaten Rokan Hilir
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekapitulasi Pengawasan Dana Desa TA <?= $tahun ?> — Inspektorat Kab. Rokan Hilir</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 12mm 15mm 12mm 15mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Bookman Old Style", Georgia, "Times New Roman", serif;
      font-size: 8.5pt;
      line-height: 1.35;
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
      background: #059669;
      color: #fff;
      padding: 7px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-family: sans-serif;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      border: none;
    }
    .container {
      width: 100%;
      padding: 10px 15px;
    }

    /* KOP SURAT RESMI */
    .kop {
      display: flex;
      align-items: center;
      gap: 14px;
      border-bottom: 3px double #000;
      padding-bottom: 6px;
      margin-bottom: 12px;
    }
    .kop-logo { width: 60px; text-align: center; }
    .kop-logo img { width: 55px; height: auto; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h3 { margin: 0; font-size: 11pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
    .kop-text h2 { margin: 2px 0; font-size: 13pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .kop-text p { margin: 0; font-size: 8pt; line-height: 1.2; font-family: Arial, sans-serif; }

    .doc-title {
      text-align: center;
      font-size: 11pt;
      font-weight: bold;
      text-decoration: underline;
      text-transform: uppercase;
      margin-top: 10px;
      margin-bottom: 2px;
    }
    .doc-subtitle {
      text-align: center;
      font-size: 9pt;
      margin-bottom: 14px;
      font-weight: bold;
    }

    /* TABEL REKAPITULASI */
    table.table-rekap {
      width: 100%;
      border-collapse: collapse;
      font-size: 8pt;
      margin-bottom: 16px;
    }
    table.table-rekap th, table.table-rekap td {
      border: 1px solid #000;
      padding: 4px 6px;
      vertical-align: middle;
    }
    table.table-rekap th {
      background: #f1f5f9;
      font-weight: bold;
      text-align: center;
    }
    .num { text-align: right; white-space: nowrap; }
    .center { text-align: center; }
    .bold { font-weight: bold; }

    @media print {
      .no-print { display: none !important; }
      .container { padding: 0; }
      body { font-size: 8pt; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <div>
      <b style="font-family:sans-serif;color:#0f172a">Rekapitulasi Pengawasan Dana Desa TA <?= $tahun ?></b>
      <div style="font-size:11px;color:#64748b;font-family:sans-serif">Format Eksekutif Standar BPKP / APIP &bull; Inspektorat Daerah Kab. Rokan Hilir</div>
    </div>
    <div style="display:flex;gap:8px">
      <button class="btn-print" onclick="window.print()">
        🖨️ Cetak / Ekspor PDF (A4 Landscape)
      </button>
      <button class="btn-print" style="background:#475569" onclick="window.close()">
        Tutup
      </button>
    </div>
  </div>

  <div class="container">
    <!-- KOP -->
    <div class="kop">
      <div class="kop-logo">
        <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rohil">
      </div>
      <div class="kop-text">
        <h3>PEMERINTAH KABUPATEN ROKAN HILIR</h3>
        <h2>INSPEKTORAT DAERAH</h2>
        <p>Jalan Komplek Perkantoran Bagansiapiapi, Rokan Hilir, Riau 28912 &bull; Email: inspektorat@rohilkab.go.id</p>
      </div>
    </div>

    <div class="doc-title">REKAPITULASI HASIL PENGAWASAN DAN PEMANTAUAN TINDAK LANJUT DANA DESA (APBDES)</div>
    <div class="doc-subtitle">
      SE-KABUPATEN ROKAN HILIR TAHUN ANGGARAN <?= $tahun ?>
    </div>

    <table class="table-rekap">
      <thead>
        <tr>
          <th rowspan="2" style="width:25px">No</th>
          <th rowspan="2" style="width:140px">Kepenghuluan</th>
          <th rowspan="2" style="width:100px">Kecamatan</th>
          <th rowspan="2" style="width:45px">KKA Diuji</th>
          <th colspan="2">Temuan Audit</th>
          <th colspan="4">Status Tindak Lanjut Rekomendasi (BPKP)</th>
          <th colspan="2">Pemulihan Kas Desa</th>
          <th rowspan="2" style="width:55px">% Pulih</th>
        </tr>
        <tr>
          <th style="width:45px">Butir</th>
          <th style="width:85px">Nilai (Rp)</th>
          <th style="width:35px" title="Sesuai">S</th>
          <th style="width:35px" title="Belum Sesuai">BS</th>
          <th style="width:35px" title="Belum Ditindaklanjuti">BD</th>
          <th style="width:40px" title="Tidak Dapat Ditindaklanjuti">TDTD</th>
          <th style="width:85px">Disetor (Rp)</th>
          <th style="width:85px">Sisa (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $tKka = 0; $tTemuan = 0; $tNilaiTem = 0;
        $tS = 0; $tBs = 0; $tBd = 0; $tTdtd = 0;
        $tSetor = 0; $tSisa = 0;

        if (empty($rekapDesa)): 
        ?>
          <tr>
            <td colspan="13" class="center" style="padding:24px;font-style:italic">Belum ada data kegiatan pengawasan atau temuan pada tahun anggaran <?= $tahun ?>.</td>
          </tr>
        <?php else: $no=1; foreach ($rekapDesa as $row): 
          $tKka      += (int)$row['total_sesi_kka'];
          $tTemuan   += (int)$row['total_temuan'];
          $tNilaiTem += (float)$row['sum_temuan'];
          $tS        += (int)$row['count_s'];
          $tBs       += (int)$row['count_bs'];
          $tBd       += (int)$row['count_bd'];
          $tTdtd     += (int)$row['count_tdtd'];
          $tSetor    += (float)$row['sum_disetor'];
          $tSisa     += (float)$row['sum_sisa'];

          $rekVal = (float)$row['sum_rekomendasi'];
          $setVal = (float)$row['sum_disetor'];
          $persen = $rekVal > 0 ? round(($setVal / $rekVal) * 100, 1) : 100.0;
        ?>
          <tr>
            <td class="center"><?= $no++ ?></td>
            <td><b><?= e($row['desa_nama']) ?></b></td>
            <td><?= e($row['kecamatan_nama']) ?></td>
            <td class="center"><?= (int)$row['total_sesi_kka'] ?></td>
            <td class="center bold"><?= (int)$row['total_temuan'] ?></td>
            <td class="num"><?= rupiah($row['sum_temuan']) ?></td>
            <td class="center bold" style="color:#15803d;background:#f0fdf4"><?= (int)$row['count_s'] ?></td>
            <td class="center bold" style="color:#b45309;background:#fefce8"><?= (int)$row['count_bs'] ?></td>
            <td class="center bold" style="color:#b91c1c;background:#fef2f2"><?= (int)$row['count_bd'] ?></td>
            <td class="center" style="color:#64748b"><?= (int)$row['count_tdtd'] ?></td>
            <td class="num bold" style="color:#15803d"><?= rupiah($row['sum_disetor']) ?></td>
            <td class="num bold" style="color:<?= (float)$row['sum_sisa'] > 0 ? '#dc2626' : '#000' ?>"><?= rupiah($row['sum_sisa']) ?></td>
            <td class="center bold" style="color:<?= $persen >= 100 ? '#15803d' : ($persen > 50 ? '#b45309' : '#dc2626') ?>">
              <?= $persen ?>%
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
      <tfoot>
        <tr style="background:#e2e8f0;font-weight:bold">
          <td colspan="3" class="center">JUMLAH TOTAL SE-KABUPATEN</td>
          <td class="center"><?= $tKka ?></td>
          <td class="center"><?= $tTemuan ?></td>
          <td class="num"><?= rupiah($tNilaiTem) ?></td>
          <td class="center" style="color:#15803d"><?= $tS ?></td>
          <td class="center" style="color:#b45309"><?= $tBs ?></td>
          <td class="center" style="color:#b91c1c"><?= $tBd ?></td>
          <td class="center"><?= $tTdtd ?></td>
          <td class="num" style="color:#15803d"><?= rupiah($tSetor) ?></td>
          <td class="num" style="color:#dc2626"><?= rupiah($tSisa) ?></td>
          <td class="center">
            <?php 
              $totRekSum = (float)$tSetor + (float)$tSisa;
              echo $totRekSum > 0 ? round(($tSetor / $totRekSum) * 100, 1) . '%' : '100%';
            ?>
          </td>
        </tr>
      </tfoot>
    </table>

    <div style="font-size:7.5pt;color:#475569;margin-bottom:18px;line-height:1.4">
      <b>Keterangan Status Tindak Lanjut Rekomendasi (Standar APIP / BPKP):</b><br>
      <b>[S] Sesuai</b> : Rekomendasi telah selesai dan tuntas ditindaklanjuti secara fisik/administrasi/setoran kas 100%. &bull; 
      <b>[BS] Belum Sesuai</b> : Tindak lanjut telah dimulai/disetor sebagian namun belum tuntas. &bull; 
      <b>[BD] Belum Ditindaklanjuti</b> : Belum ada upaya atau bukti tindak lanjut. &bull; 
      <b>[TDTD] Tidak Dapat Ditindaklanjuti</b> : Rekomendasi tidak dapat dilaksanakan karena alasan hukum/force majeure yang sah.
    </div>

    <!-- TANDA TANGAN -->
    <table style="width:100%;font-size:9pt;margin-top:20px;border-collapse:collapse">
      <tr>
        <td style="width:50%;text-align:center;vertical-align:top;padding-bottom:50px">
          Mengetahui,<br>
          <b>Inspektur Pembantu (Irban) Wilayah IV</b><br><br><br><br>
          <b><u>MARWAN, M.T</u></b><br>
          NIP. 19770727 200212 1 005
        </td>
        <td style="width:50%;text-align:center;vertical-align:top;padding-bottom:50px">
          Bagansiapiapi, <?= tgl_id(date('Y-m-d')) ?><br>
          Mengesahkan,<br>
          <b>INSPEKTUR DAERAH KABUPATEN ROKAN HILIR</b><br><br><br><br>
          <b><u><?= e($inspektur['nama']) ?></u></b><br>
          NIP. <?= e($inspektur['nip']) ?>
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
