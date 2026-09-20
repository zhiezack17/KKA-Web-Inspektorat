<?php
/**
 * Cetak Resmi Matriks Pemantauan Tindak Lanjut Hasil Pengawasan (TLHP 60 Hari)
 * Inspektorat Daerah Kabupaten Rokan Hilir
 * Standar: Formulir Kendali Mutu APIP & Keputusan Bupati (Benchmark Simondes)
 * Format: A4 Landscape
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Matriks TLHP — TA <?= $tahun ?> <?= $desa ? ('— ' . e($desa['nama'])) : '' ?></title>
  <style>
    @page {
      size: A4 landscape;
      margin: 12mm 15mm 12mm 15mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Bookman Old Style", Georgia, "Times New Roman", serif;
      font-size: 9pt;
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

    /* KOP */
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
      font-size: 11.5pt;
      font-weight: bold;
      text-decoration: underline;
      text-transform: uppercase;
      margin-top: 10px;
      margin-bottom: 2px;
    }
    .doc-subtitle {
      text-align: center;
      font-size: 9.5pt;
      margin-bottom: 14px;
      font-weight: bold;
    }

    /* TABEL MATRIKS */
    table.table-matriks {
      width: 100%;
      border-collapse: collapse;
      font-size: 8pt;
      margin-bottom: 16px;
    }
    table.table-matriks th, table.table-matriks td {
      border: 1px solid #000;
      padding: 4px 5px;
      vertical-align: top;
    }
    table.table-matriks th {
      background: #f1f1f1;
      font-weight: bold;
      text-align: center;
      vertical-align: middle;
    }
    .num { text-align: right; white-space: nowrap; }
    .center { text-align: center; }
    .bold { font-weight: bold; }

    @media print {
      .no-print { display: none !important; }
      .container { padding: 0; }
      body { font-size: 8.5pt; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <div>
      <b style="font-family:sans-serif;color:#0f172a">Matriks Pemantauan Tindak Lanjut Hasil Pengawasan (TLHP 60 Hari)</b>
      <div style="font-size:11px;color:#64748b;font-family:sans-serif">Standar Kendali Mutu APIP &bull; Rekap Pemulihan Kas Desa ke Bank Riau Kepri Syariah</div>
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

    <div class="doc-title">MATRIKS PEMANTAUAN TINDAK LANJUT HASIL PENGAWASAN (TLHP) 60 HARI KALENDER</div>
    <div class="doc-subtitle">
      PENGAWASAN PENGELOLAAN KEUANGAN DESA <?= $desa ? ('KEPENGHULUAN ' . strtoupper(e($desa['nama'])) . ' KEC. ' . strtoupper(e($desa['kecamatan_nama']))) : 'SE-KABUPATEN ROKAN HILIR' ?> TAHUN ANGGARAN <?= $tahun ?>
    </div>

    <table class="table-matriks">
      <thead>
        <tr>
          <th rowspan="2" style="width:25px">No</th>
          <th rowspan="2" style="width:95px">Kepenghuluan</th>
          <th rowspan="2" style="width:170px">Temuan Pemeriksaan</th>
          <th rowspan="2" style="width:160px">Rekomendasi APIP</th>
          <th rowspan="2" style="width:85px">Nilai Temuan (Rp)</th>
          <th colspan="3">Pelaksanaan Tindak Lanjut Auditi</th>
          <th rowspan="2" style="width:80px">Batas 60 Hari</th>
          <th rowspan="2" style="width:85px">Status &amp; Verif APIP</th>
        </tr>
        <tr>
          <th style="width:140px">Uraian Tindak Lanjut</th>
          <th style="width:85px">Nilai Setor (Rp)</th>
          <th style="width:85px">Sisa Kerugian (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $totRek = 0; $totSetor = 0; $totSisa = 0;
        if (empty($list)): 
        ?>
          <tr>
            <td colspan="10" class="center" style="padding:20px;font-style:italic">Tidak ada data rekomendasi tindak lanjut.</td>
          </tr>
        <?php else: $no=1; foreach ($list as $it): 
          $totRek   += (float)$it['nominal_rekomendasi'];
          $totSetor += (float)$it['nominal_disetor'];
          $totSisa  += (float)$it['sisa_kerugian'];
          $sisaHari  = (int)$it['sisa_hari'];
        ?>
          <tr>
            <td class="center"><?= $no++ ?></td>
            <td>
              <b><?= e($it['desa_nama']) ?></b><br>
              <span style="font-size:7.5pt;color:#333">Kec. <?= e($it['kecamatan_nama']) ?></span>
            </td>
            <td>
              <b><?= e($it['nomor_temuan']) ?></b><br>
              <?= e($it['temuan_judul']) ?>
            </td>
            <td>
              <?= nl2br(e($it['rekomendasi_teks'] ?: $it['temuan_rekomendasi'])) ?>
            </td>
            <td class="num bold"><?= rupiah($it['nominal_rekomendasi']) ?></td>
            <td>
              <?= !empty($it['uraian_tindak_lanjut']) ? nl2br(e($it['uraian_tindak_lanjut'])) : '<i style="color:#777">Belum ada laporan tindak lanjut</i>' ?>
              <?php if (!empty($it['no_bukti_setor'])): ?>
                <div style="font-size:7.5pt;margin-top:2px;font-weight:bold">Bukti: <?= e($it['no_bukti_setor']) ?> (<?= tgl_id($it['tgl_setor']) ?>)</div>
              <?php endif; ?>
            </td>
            <td class="num bold" style="color:#059669"><?= rupiah($it['nominal_disetor']) ?></td>
            <td class="num bold" style="color:<?= (float)$it['sisa_kerugian'] > 0 ? '#dc2626' : '#000' ?>"><?= rupiah($it['sisa_kerugian']) ?></td>
            <td class="center">
              <?= tgl_id($it['batas_waktu_tl']) ?><br>
              <?php if ($it['status'] === 'TUNTAS'): ?>
                <span style="color:#059669;font-weight:bold">[ Selesai ]</span>
              <?php elseif ($sisaHari >= 0): ?>
                <span style="color:#0284c7">[ Sisa <?= $sisaHari ?> hr ]</span>
              <?php else: ?>
                <span style="color:#dc2626;font-weight:bold">[ Telat <?= abs($sisaHari) ?> hr ]</span>
              <?php endif; ?>
            </td>
            <td class="center">
              <b><?= e($it['status']) ?></b><br>
              <span style="font-size:7.5pt">
                <?php if ($it['verifikasi_apip'] === 'SESUAI'): ?>
                  (Verif: Sesuai)
                <?php elseif ($it['verifikasi_apip'] === 'BELUM_SESUAI'): ?>
                  (Verif: Blm Sesuai)
                <?php else: ?>
                  (Blm Verif)
                <?php endif; ?>
              </span>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
      <tfoot>
        <tr style="background:#f2f2f2;font-weight:bold">
          <td colspan="4" class="center">TOTAL PEMULIHAN KAS DESA</td>
          <td class="num"><?= rupiah($totRek) ?></td>
          <td></td>
          <td class="num" style="color:#059669"><?= rupiah($totSetor) ?></td>
          <td class="num" style="color:#dc2626"><?= rupiah($totSisa) ?></td>
          <td colspan="2" class="center">
            <?php $persen = $totRek > 0 ? round(($totSetor / $totRek) * 100, 1) : 100; ?>
            Pemulihan: <?= $persen ?>%
          </td>
        </tr>
      </tfoot>
    </table>

    <!-- LEMBAR PENGESAHAN -->
    <table style="width:100%;font-size:9.5pt;margin-top:20px;border-collapse:collapse">
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
