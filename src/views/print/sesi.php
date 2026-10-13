<?php $title = 'KKA - ' . $sesi['desa_nama']; ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= e($title) ?></title>
<style>
  @page { size: A4 portrait; margin: 12mm 12mm 14mm 12mm; }
  *{box-sizing:border-box}
  body{font-family:'Times New Roman',serif;color:#000;font-size:10pt;margin:0;background:#f1f5f9}
  .page{
    background:#fff;
    width:210mm; min-height:297mm;
    margin:14px auto;
    padding:12mm 12mm 20mm 12mm;
    box-shadow:0 8px 24px rgba(0,0,0,.12);
    position:relative;
    page-break-after: always;
  }
  .page:last-child{
    page-break-after: auto;
  }
  .kop{
    display:flex;gap:10px;align-items:flex-start;
    border-bottom:3px double #000;
    padding-bottom:6px;margin-bottom:8px;
    min-height:68px;
  }
  .kop .logo{width:60px;height:60px;display:grid;place-items:center;flex-shrink:0;margin-top:-2px}
  .kop .logo img{max-width:100%;max-height:100%;object-fit:contain;display:block}
  .kop .center{flex:1;text-align:center;line-height:1.25;padding-top:2px}
  .kop .center .l1{font-size:11pt;font-weight:bold;letter-spacing:.3px}
  .kop .center .l2{font-size:15pt;font-weight:bold;letter-spacing:1px;margin:1px 0}
  .kop .center .l3{font-size:8.5pt}
  h1{text-align:center;font-size:12.5pt;margin:4px 0 2px;text-decoration:underline}
  h2{text-align:center;font-size:10.5pt;margin:0 0 8px;font-weight:normal}
  .id-table{width:100%;border-collapse:collapse;margin-bottom:6px;font-size:9pt}
  .id-table td{padding:1px 3px;vertical-align:top;line-height:1.25}
  .id-table td.lbl{width:32mm;font-weight:bold;white-space:nowrap}
  .id-table td.sep{width:5px}
  .id-table td.val-left{width:70mm}
  .id-table td.lbl-r{width:30mm;font-weight:bold;white-space:nowrap;padding-left:4px}
  .id-table td.val-right{width:auto;padding-left:2px}
  .id-table .sub-r{font-weight:normal;font-size:8pt;color:#333}
  table.data{width:100%;border-collapse:collapse;margin-top:4px;font-size:8.5pt}
  table.data th, table.data td{border:1px solid #000;padding:3px 4px;vertical-align:top}
  table.data thead th{background:#dcfce7;text-align:center;font-weight:bold;font-size:8.5pt}
  table.data .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
  table.data tfoot td{font-weight:bold;background:#f3f4f6}
  .sec{margin-top:8px;font-size:9pt}
  .sec .ttl{font-weight:bold;text-decoration:underline;margin-bottom:2px;font-size:9pt}
  .sec .isi{min-height:16px;text-align:justify;line-height:1.3}
  .page-footer{
    position:absolute;
    left:12mm;right:12mm;bottom:7mm;
    padding-top:4px;
    border-top:1px dashed #999;
    display:flex;justify-content:space-between;
    color:#666;font-size:7.5pt;font-style:italic;
  }
  .toolbar{
    position:sticky;top:0;z-index:10;
    background:linear-gradient(180deg,#064e3b 0%,#047857 100%);color:#fff;
    padding:10px 22px;display:flex;justify-content:space-between;align-items:center;
    font-family:'Plus Jakarta Sans',sans-serif;
  }
  .toolbar .info{font-size:13px;color:#bbf7d0}
  .toolbar .info b{color:#fff}
  .toolbar button, .toolbar a{padding:8px 16px;border-radius:8px;background:#facc15;color:#022c22;border:0;font-weight:700;cursor:pointer;text-decoration:none;font-size:13px;margin-left:6px}
  .toolbar a.alt{background:#1e293b;color:#fff}
  .toolbar a.alt:hover{background:#0f172a}
  @media print{
    .toolbar{display:none}
    body{background:#fff}
    .page{box-shadow:none;margin:0;width:auto;min-height:0;padding:0;page-break-after: always !important}
    .page:last-child{page-break-after: auto !important}
    .page-footer{position:fixed;bottom:6mm;left:12mm;right:12mm}
  }
</style>
</head>
<body>
<div class="toolbar no-print">
  <div class="info">
    Preview Cetak KKA · Total <b><?= count($rincian) ?> Lembar Rekening Belanja</b> · Tekan tombol <b>Cetak</b> untuk print fisik / simpan PDF.
  </div>
  <div>
    <a class="alt" href="<?= url('sesi/show?id='.$sesi['id']) ?>">← Kembali ke Sesi</a>
    <button onclick="window.print()">🖨 Cetak KKA (<?= count($rincian) ?> Lembar)</button>
  </div>
</div>

<?php
  $tgl_buat   = !empty($sesi['tanggal_dibuat'])  ? date('d/m/y', strtotime($sesi['tanggal_dibuat']))  : '..../..../....';
  $tgl_review = !empty($sesi['tanggal_review']) ? date('d/m/y', strtotime($sesi['tanggal_review'])) : '..../..../....';
  [$pureBuat]   = split_nama_nip($sesi['dibuat_oleh'] ?? '');
  [$pureReview] = split_nama_nip($sesi['direview_oleh'] ?? '');
  $nama_buat   = $pureBuat ?: '...........................';
  $nama_review = $pureReview ?: '...........................';
  $displayObjek = !empty($sesi['objek_audit']) ? trim($sesi['objek_audit']) : ('Kepenghuluan ' . $sesi['desa_nama']);
  if (stripos($displayObjek, 'Pemeriksaan Kepatuhan') !== false) {
      $displayObjek = 'Kepenghuluan ' . $sesi['desa_nama'];
  }
  $cleanKegiatan = !empty($sesi['kegiatan']) ? trim($sesi['kegiatan'], " \t\n\r\0\x0B)-;") : '-';

  $totalLembar = count($rincian);
  if ($totalLembar === 0):
?>
  <div class="page">
    <div style="text-align:center;padding:50px 20px;color:#666">
      <h3>Belum ada data rekening belanja pada sesi ini.</h3>
    </div>
  </div>
<?php
  else:
    foreach ($rincian as $rIndex => $rek):
      $subItems = !empty($rek['children']) ? $rek['children'] : [$rek];
      $paguRekening = (float)($rek['pagu_anggaran'] ?? 0);
?>
<div class="page">
  <div class="kop">
    <div class="logo"><img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil"></div>
    <div class="center">
      <div class="l1">PEMERINTAH KABUPATEN ROKAN HILIR</div>
      <div class="l2">INSPEKTORAT</div>
      <div class="l3">Komplek Perkantoran Batu 6 Jl. Lintas Pesisir Sungai Rokan, Kec. Bangko - Bagansiapiapi</div>
      <div class="l3">Telp. (0767) 2700270 · Email: inspektorat@rohilkab.go.id</div>
    </div>
    <div class="logo"><img src="<?= asset('img/logo-inspektorat.png') ?>" alt="Inspektorat"></div>
  </div>
  <h1>KERTAS KERJA AUDIT (KKA)</h1>
  <h2>PENGELUARAN KEUANGAN KEPENGHULUAN — Tahun Anggaran <?= e($sesi['tahun_anggaran']) ?></h2>

  <table class="id-table">
    <tr>
      <td class="lbl">Kepenghuluan / Desa</td><td class="sep">:</td><td class="val-left"><?= e($sesi['desa_nama']) ?> (Kec. <?= e($sesi['kecamatan_nama']) ?>)</td>
      <td class="lbl-r">No. KKA</td><td class="sep">:</td><td class="val-right"><?= e($sesi['no_kka'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Objek Audit</td><td class="sep">:</td><td class="val-left"><?= e($displayObjek) ?></td>
      <td class="lbl-r">Ref. PKA</td><td class="sep">:</td><td class="val-right"><?= e($sesi['ref_kka'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Masa Audit</td><td class="sep">:</td><td class="val-left">Semester <?= (int)$sesi['semester'] ?> Tahun <?= e($sesi['tahun_anggaran']) ?></td>
      <td class="lbl-r">Disusun oleh <span class="sub-r">(Auditor)</span></td><td class="sep">:</td><td class="val-right"><?= e($nama_buat) ?></td>
    </tr>
    <tr>
      <td class="lbl">Bidang</td><td class="sep">:</td><td class="val-left" style="font-size:8.5pt"><?= e($sesi['bidang_nama']) ?></td>
      <td class="lbl-r">Tgl/Paraf</td><td class="sep">:</td><td class="val-right"><?= e($tgl_buat) ?></td>
    </tr>
    <tr>
      <td class="lbl">Sub Bidang</td><td class="sep">:</td><td class="val-left" style="font-size:8.5pt"><?= e($sesi['sub_bidang_nama'] ?: '-') ?></td>
      <td class="lbl-r">Direview oleh <span class="sub-r">(Ketua Tim)</span></td><td class="sep">:</td><td class="val-right"><?= e($nama_review) ?></td>
    </tr>
    <tr>
      <td class="lbl">Kegiatan</td><td class="sep">:</td><td class="val-left"><?= e($cleanKegiatan) ?></td>
      <td class="lbl-r">Tgl/Paraf</td><td class="sep">:</td><td class="val-right"><?= e($tgl_review) ?></td>
    </tr>
    <tr>
      <td class="lbl" style="color:#047857">Sub Kegiatan / Rekening</td><td class="sep">:</td>
      <td class="val-left" style="font-weight:bold;color:#047857;font-size:9pt"><?= e($rek['uraian']) ?></td>
      <td class="lbl-r">Pagu Rekening</td><td class="sep">:</td>
      <td class="val-right" style="font-weight:bold">Rp <?= number_format($paguRekening,0,',','.') ?></td>
    </tr>
  </table>

  <table class="data">
    <thead>
      <tr>
        <th style="width:22px">No</th>
        <th>Rincian Bukti Belanja / Kwitansi / Penerima</th>
        <th style="width:78px">Anggaran (Rp)</th>
        <th style="width:78px">Realisasi (Rp)</th>
        <th style="width:78px">Biaya Dikwitansi (Rp)</th>
        <th style="width:72px">Selisih (Rp)</th>
        <th style="width:90px">TPK / Penerima</th>
        <th style="width:85px">Uji Pajak</th>
        <th style="width:90px">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $no = 1;
        $totSubPagu = 0;
        $totSubReal = 0;
        $totSubKwi  = 0;
        foreach ($subItems as $it): 
          $itPagu = (float)($it['pagu_anggaran'] ?? 0);
          $itReal = (float)($it['realisasi'] ?? 0);
          $itKwi  = (float)($it['biaya_dikwitansi'] ?? 0);
          $itSel  = $itReal - $itKwi;
          $totSubPagu += $itPagu;
          $totSubReal += $itReal;
          $totSubKwi  += $itKwi;

          $buktiInfo = trim((!empty($it['no_bukti']) ? 'No. ' . $it['no_bukti'] : '') . (!empty($it['tgl_bukti']) ? ' (' . date('d/m/Y', strtotime($it['tgl_bukti'])) . ')' : ''));
          
          $pajakDesc = '-';
          if (!empty($it['status_pajak']) && $it['status_pajak'] === 'SUDAH_SETOR') {
              $nomP = (float)($it['nominal_ppn'] ?? 0) + (float)($it['nominal_pph'] ?? 0);
              $pajakDesc = 'Setor ' . ($it['potong_pph'] ?: 'PPN') . ($nomP > 0 ? '<br>Rp ' . number_format($nomP, 0, ',', '.') : '');
          } elseif (!empty($it['status_pajak']) && $it['status_pajak'] === 'BELUM_SETOR') {
              $pajakDesc = '<b style="color:red">Belum Setor</b>';
          }
      ?>
        <tr>
          <td style="text-align:center"><?= $no++ ?></td>
          <td>
            <?php if ($buktiInfo): ?>
              <div style="font-weight:bold;color:#1e3a8a;font-size:7.5pt;margin-bottom:2px">[<?= e($buktiInfo) ?>]</div>
            <?php endif; ?>
            <div><?= e($it['uraian']) ?></div>
          </td>
          <td class="num"><?= $itPagu > 0 ? number_format($itPagu,0,',','.') : '-' ?></td>
          <td class="num"><?= number_format($itReal,0,',','.') ?></td>
          <td class="num"><?= number_format($itKwi,0,',','.') ?></td>
          <td class="num"><?= number_format($itSel,0,',','.') ?></td>
          <td><?= (!empty($it['jenis_penerima']) && $it['jenis_penerima'] === 'TPK' ? '<b>[TPK]</b> ' : '') . e($it['penerima'] ?: '-') ?></td>
          <td style="text-align:center;font-size:8pt"><?= $pajakDesc ?></td>
          <td style="font-size:8pt"><?= e($it['keterangan'] ?: '-') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2" style="text-align:center">JUMLAH (<?= e($rek['uraian']) ?>)</td>
        <td class="num"><?= number_format($paguRekening,0,',','.') ?></td>
        <td class="num"><?= number_format($totSubReal,0,',','.') ?></td>
        <td class="num"><?= number_format($totSubKwi,0,',','.') ?></td>
        <td class="num"><?= number_format($totSubReal - $totSubKwi,0,',','.') ?></td>
        <td colspan="3"></td>
      </tr>
    </tfoot>
  </table>

  <div class="sec">
    <div class="ttl">SUMBER DATA:</div>
    <div class="isi"><?= nl2br(e($sesi['sumber_data'] ?: '1. Surat Pertanggungjawaban (SPJ) Kepenghuluan ' . $sesi['desa_nama'] . "\n2. Dokumen Realisasi APBDesa TA " . $sesi['tahun_anggaran'])) ?></div>
  </div>
  <div class="sec">
    <div class="ttl">KESIMPULAN AUDIT ATAS REKENING INI:</div>
    <div class="isi"><?= nl2br(e($sesi['kesimpulan'] ?: 'Pemeriksaan fisik dan verifikasi administrasi kelengkapan kwitansi SPJ telah dilakukan sesuai standar pengawasan Inspektorat Kabupaten Rokan Hilir.')) ?></div>
  </div>
  <?php if (!empty($sesi['kendala_lapangan'])): ?>
  <div class="sec">
    <div class="ttl" style="color:#b45309">HAMBATAN / KENDALA DI LAPANGAN:</div>
    <div class="isi" style="background:#fffbeb;border-left:3px solid #f59e0b;padding:4px 8px"><?= nl2br(e($sesi['kendala_lapangan'])) ?></div>
  </div>
  <?php endif; ?>



  <div class="page-footer">
    <div>Portal E-LHP · Inspektorat Kab. Rokan Hilir</div>
    <div>Lembar KKA <?= $rIndex + 1 ?> dari <?= $totalLembar ?> · Sub Kegiatan: <?= e($rek['uraian']) ?></div>
    <div>Dicetak: <?= date('d/m/Y H:i') ?></div>
  </div>
</div>
<?php 
    endforeach;
  endif;
?>
</body>
</html>
