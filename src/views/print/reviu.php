<?php 
$title = 'Lembar Reviu KKA - ' . ($sesi['desa_nama'] ?? 'Audit'); 

// Resolusi Pejabat & Format Nama / NIP Bersih
$rawPenyusunNama = $creatorUser['nama'] ?? ($sesi['dibuat_oleh'] ?? '');
$rawPenyusunNip  = $creatorUser['nip'] ?? '';
[$namaPenyusun, $nipPenyusun] = split_nama_nip($rawPenyusunNama, $rawPenyusunNip);

$rawKetuaNama = $ketuaUser['nama'] ?? ($sesi['direview_oleh'] ?? '');
$rawKetuaNip  = $ketuaUser['nip'] ?? '';
[$namaKetua, $nipKetua] = split_nama_nip($rawKetuaNama, $rawKetuaNip);

$rawDalnisNama = $dalnisUser['nama'] ?? ($sesi['dievaluasi_oleh'] ?? '');
$rawDalnisNip  = $dalnisUser['nip'] ?? '';
[$namaDalnis, $nipDalnis] = split_nama_nip($rawDalnisNama, $rawDalnisNip);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= e($title) ?></title>
<style>
  @page { size: A4 portrait; margin: 12mm 12mm 14mm 12mm; }
  *{box-sizing:border-box}
  body{font-family:'Times New Roman',serif;color:#000;font-size:10pt;margin:0;background:#f1f5f9;line-height:1.3}
  .page{
    background:#fff;
    width:210mm; min-height:297mm;
    margin:14px auto;
    padding:12mm 14mm 20mm 14mm;
    box-shadow:0 8px 24px rgba(0,0,0,.12);
    position:relative;
  }
  .kop{
    display:flex;gap:10px;align-items:flex-start;
    border-bottom:3px double #000;
    padding-bottom:8px;margin-bottom:12px;
    min-height:72px;
  }
  .kop .logo{width:64px;height:64px;display:grid;place-items:center;flex-shrink:0;margin-top:-2px}
  .kop .logo img{max-width:100%;max-height:100%;object-fit:contain;display:block}
  .kop .center{flex:1;text-align:center;line-height:1.3;padding-top:2px}
  .kop .center .l1{font-size:11.5pt;font-weight:bold;letter-spacing:.3px}
  .kop .center .l2{font-size:16pt;font-weight:bold;letter-spacing:1px;margin:1px 0}
  .kop .center .l3{font-size:9pt}
  h1{text-align:center;font-size:13pt;margin:6px 0 2px;text-decoration:underline;font-weight:bold}
  h2{text-align:center;font-size:10.5pt;margin:0 0 12px;font-weight:normal}

  .id-table{width:100%;border-collapse:collapse;margin-bottom:12px;font-size:9pt}
  .id-table td{padding:2px 4px;vertical-align:top}
  .id-table td.lbl{width:32mm;font-weight:bold;white-space:nowrap}
  .id-table td.sep{width:6px;text-align:center}
  .id-table td.val-left{width:68mm}
  .id-table td.lbl-r{width:30mm;font-weight:bold;white-space:nowrap;padding-left:6px}
  .id-table td.val-right{width:auto}

  .box-status{
    border:1px solid #cbd5e1;
    background:#f8fafc;
    border-radius:4px;
    padding:8px 12px;
    margin-bottom:12px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:9.5pt;
  }
  .badge-status{
    display:inline-block;
    padding:3px 10px;
    border-radius:999px;
    font-size:8.5pt;
    font-weight:bold;
    letter-spacing:0.3px;
  }
  .status-DRAFT{background:#f1f5f9;color:#475569;border:1px solid #cbd5e1}
  .status-REVIEW_KETUA{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
  .status-REVIEW_DALNIS{background:#e0f2fe;color:#075985;border:1px solid #bae6fd}
  .status-PERLU_REVISI{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
  .status-SELESAI_FINAL{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}

  .table-reviu{
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
    margin-bottom:14px;
    font-size:9pt;
  }
  .table-reviu th, .table-reviu td{
    border:1px solid #000;
    padding:6px 8px;
    vertical-align:top;
  }
  .table-reviu thead th{
    background:#e2e8f0;
    text-align:center;
    font-weight:bold;
    font-size:9pt;
  }
  .table-reviu td.level{
    font-weight:bold;
    width:32%;
    background:#f8fafc;
  }
  .table-reviu .note-text{
    min-height:36px;
    white-space:pre-wrap;
    line-height:1.35;
  }

  .sec-title{
    font-weight:bold;
    font-size:9.5pt;
    text-decoration:underline;
    margin-top:10px;
    margin-bottom:4px;
  }

  .ttd-container{
    margin-top:24px;
    display:flex;
    justify-content:space-between;
    font-size:9pt;
    page-break-inside:avoid;
  }
  .ttd-col{
    width:31%;
    text-align:center;
  }
  .ttd-space{height:54px}
  .ttd-name{font-weight:bold;text-decoration:underline}
  .ttd-nip{font-size:8pt;color:#333;margin-top:2px}
  .ttd-date{font-size:8pt;color:#555;margin-top:2px}

  .page-footer{
    position:absolute;
    left:14mm;right:14mm;bottom:8mm;
    padding-top:5px;
    border-top:1px dashed #999;
    display:flex;justify-content:space-between;
    color:#666;font-size:8pt;font-style:italic;
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
    .page{box-shadow:none;margin:0;width:auto;min-height:0;padding:0}
    .page-footer{position:fixed;bottom:6mm;left:14mm;right:14mm}
  }
</style>
</head>
<body>
<div class="toolbar no-print">
  <div class="info">Dokumen Resmi: <b>Lembar Reviu Berjenjang KKA</b> · Format <b>A4 Portrait</b>.</div>
  <div>
    <a class="alt" href="<?= url('sesi/show?id='.$sesi['id']) ?>">← Kembali ke Sesi</a>
    <button onclick="window.print()">🖨 Cetak Lembar Reviu</button>
  </div>
</div>

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

  <h1>LEMBAR REVIU KERTAS KERJA AUDIT (KKA)</h1>
  <h2>KENDALI MUTU PENGAWASAN (QUALITY ASSURANCE APIP) — TAHUN <?= (int)$sesi['tahun_anggaran'] ?></h2>

  <div class="box-status">
    <div>
      <b>No. Lembar Reviu:</b> LR-KKA/<?= str_pad((string)$sesi['id'], 4, '0', STR_PAD_LEFT) ?>/<?= (int)$sesi['tahun_anggaran'] ?>
    </div>
    <div>
      <b>Status KKA:</b>
      <?php
        $st = $sesi['status'] ?? 'DRAFT';
        $stLabel = [
          'DRAFT'          => 'DRAFT (PENYUSUNAN)',
          'REVIEW_KETUA'   => 'DIREVIU KETUA TIM',
          'REVIEW_DALNIS'  => 'DIREVIU PENGENDALI TEKNIS (DALNIS)',
          'PERLU_REVISI'   => 'PERLU REVISI AUDITOR',
          'SELESAI_FINAL'  => 'DISETUJUI / FINAL (SAH)',
        ];
      ?>
      <span class="badge-status status-<?= e($st) ?>"><?= $stLabel[$st] ?? $st ?></span>
    </div>
  </div>

  <table class="id-table">
    <tr>
      <td class="lbl">Kepenghuluan / Desa</td><td class="sep">:</td><td class="val-left"><?= e($sesi['desa_nama']) ?> (Kec. <?= e($sesi['kecamatan_nama']) ?>)</td>
      <td class="lbl-r">No. KKA</td><td class="sep">:</td><td class="val-right"><?= e($sesi['no_kka'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Objek Audit</td><td class="sep">:</td><td class="val-left"><?= e($sesi['objek_audit']) ?></td>
      <td class="lbl-r">Ref. PKA</td><td class="sep">:</td><td class="val-right"><?= e($sesi['ref_kka'] ?: '-') ?></td>
    </tr>
    <tr>
      <td class="lbl">Masa Audit</td><td class="sep">:</td><td class="val-left">Semester <?= (int)$sesi['semester'] ?> Tahun <?= (int)$sesi['tahun_anggaran'] ?></td>
      <td class="lbl-r">Pagu Anggaran</td><td class="sep">:</td><td class="val-right" style="font-weight:bold">Rp <?= number_format((float)$sesi['pagu_anggaran'],0,',','.') ?></td>
    </tr>
    <tr>
      <td class="lbl">Bidang</td><td class="sep">:</td><td class="val-left"><?= e($sesi['bidang_nama']) ?></td>
      <td class="lbl-r">Realisasi</td><td class="sep">:</td><td class="val-right">Rp <?= number_format((float)($totals['realisasi'] ?? 0),0,',','.') ?></td>
    </tr>
    <tr>
      <td class="lbl">Kegiatan</td><td class="sep">:</td><td class="val-left"><?= e($sesi['kegiatan'] ?: '-') ?></td>
      <td class="lbl-r">Selisih Belanja</td><td class="sep">:</td><td class="val-right" style="font-weight:bold">Rp <?= number_format((float)($totals['realisasi'] ?? 0) - (float)($totals['dikwitansi'] ?? 0),0,',','.') ?></td>
    </tr>
  </table>

  <div class="sec-title">MATRIKS HASIL REVIU BERJENJANG:</div>
  <table class="table-reviu">
    <thead>
      <tr>
        <th style="width:28%">Jenjang Pengawasan / Pejabat Reviu</th>
        <th style="width:48%">Catatan Reviu / Arahan Perbaikan</th>
        <th style="width:24%">Paraf & Tanggal Reviu</th>
      </tr>
    </thead>
    <tbody>
      <!-- Jenjang 1: Ketua Tim -->
      <tr>
        <td class="level">
          <b>JENJANG I: KETUA TIM</b><br>
          <small style="color:#555">Pemeriksaan kelengkapan bukti kuitansi, pengujian rincian belanja, dan akurasi selisih.</small><br><br>
          <b>Pejabat:</b> <?= !empty($namaKetua) ? e($namaKetua) : '-' ?><br>
          <?php if (!empty($nipKetua) && $nipKetua !== '-'): ?><small>NIP. <?= e(format_nip($nipKetua)) ?></small><?php endif; ?>
        </td>
        <td>
          <div class="note-text"><?= nl2br(e($sesi['catatan_reviu_ketua'] ?: 'Telah dilakukan pemeriksaan rincian belanja dan bukti transaksi. Data pengujian dinyatakan sesuai dan lengkap.')) ?></div>
        </td>
        <td style="text-align:center">
          <?php if (!empty($sesi['tgl_reviu_ketua']) || in_array($st, ['REVIEW_DALNIS','SELESAI_FINAL'])): ?>
            <div style="color:#166534;font-weight:bold;margin-bottom:8px">✓ TELAH DIREVIU</div>
            <div style="font-size:8pt;color:#555"><?= !empty($sesi['tgl_reviu_ketua']) ? date('d/m/Y H:i', strtotime($sesi['tgl_reviu_ketua'])) : tgl_id($sesi['tanggal_review']) ?></div>
          <?php elseif ($st === 'PERLU_REVISI'): ?>
            <div style="color:#991b1b;font-weight:bold">↩ CATATAN REVISI</div>
          <?php else: ?>
            <div style="color:#999;font-style:italic">Menunggu Reviu</div>
          <?php endif; ?>
        </td>
      </tr>

      <!-- Jenjang 2: Dalnis -->
      <tr>
        <td class="level">
          <b>JENJANG II: PENGENDALI TEKNIS (DALNIS)</b><br>
          <small style="color:#555">Validasi substansi tujuan audit, kesesuaian kriteria peraturan, dan simpulan audit.</small><br><br>
          <b>Pejabat:</b> <?= !empty($namaDalnis) ? e($namaDalnis) : '-' ?><br>
          <?php if (!empty($nipDalnis) && $nipDalnis !== '-'): ?><small>NIP. <?= e(format_nip($nipDalnis)) ?></small><?php endif; ?>
        </td>
        <td>
          <div class="note-text"><?= nl2br(e($sesi['catatan_reviu_dalnis'] ?: 'Kertas Kerja Audit telah memenuhi standar pengawasan intern pemerintah dan disetujui sebagai bahan LHP.')) ?></div>
        </td>
        <td style="text-align:center">
          <?php if ($st === 'SELESAI_FINAL' || !empty($sesi['tgl_reviu_dalnis'])): ?>
            <div style="color:#166534;font-weight:bold;margin-bottom:8px">✓ TELAH DISETUJUI / SAH</div>
            <div style="font-size:8pt;color:#555"><?= !empty($sesi['tgl_reviu_dalnis']) ? date('d/m/Y H:i', strtotime($sesi['tgl_reviu_dalnis'])) : tgl_id($sesi['tanggal_evaluasi']) ?></div>
          <?php elseif ($st === 'REVIEW_DALNIS'): ?>
            <div style="color:#0369a1;font-weight:bold">⏳ SEDANG DIREVIU</div>
          <?php else: ?>
            <div style="color:#999;font-style:italic">Menunggu Tahapan</div>
          <?php endif; ?>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="sec-title">SIMPULAN AUDIT:</div>
  <div style="border:1px solid #cbd5e1;padding:8px 10px;border-radius:4px;background:#fbfcfe;margin-bottom:8px;font-size:9.5pt">
    <?= nl2br(e($sesi['kesimpulan'] ?: '- Belum ada kesimpulan audit -')) ?>
  </div>

  <div class="ttd-container">
    <div class="ttd-col">
      <div>Disusun Oleh:</div>
      <div style="font-weight:bold;margin-top:2px">Auditor (Penyusun)</div>
      <div class="ttd-space"></div>
      <div class="ttd-name"><?= !empty($namaPenyusun) ? e($namaPenyusun) : '...........................' ?></div>
      <?php if (!empty($nipPenyusun) && $nipPenyusun !== '-'): ?>
        <div class="ttd-nip">NIP. <?= e(format_nip($nipPenyusun)) ?></div>
      <?php endif; ?>
      <div class="ttd-date">Tgl: <?= tgl_id($sesi['tanggal_dibuat']) ?></div>
    </div>
    <div class="ttd-col">
      <div>Direview Oleh:</div>
      <div style="font-weight:bold;margin-top:2px">Ketua Tim Audit</div>
      <div class="ttd-space"></div>
      <div class="ttd-name"><?= !empty($namaKetua) ? e($namaKetua) : '...........................' ?></div>
      <?php if (!empty($nipKetua) && $nipKetua !== '-'): ?>
        <div class="ttd-nip">NIP. <?= e(format_nip($nipKetua)) ?></div>
      <?php endif; ?>
      <div class="ttd-date">Tgl: <?= !empty($sesi['tgl_reviu_ketua']) ? date('d/m/Y', strtotime($sesi['tgl_reviu_ketua'])) : tgl_id($sesi['tanggal_review']) ?></div>
    </div>
    <div class="ttd-col">
      <div>Disetujui / Disahkan Oleh:</div>
      <div style="font-weight:bold;margin-top:2px">Pengendali Teknis (Dalnis)</div>
      <div class="ttd-space"></div>
      <div class="ttd-name"><?= !empty($namaDalnis) ? e($namaDalnis) : '...........................' ?></div>
      <?php if (!empty($nipDalnis) && $nipDalnis !== '-'): ?>
        <div class="ttd-nip">NIP. <?= e(format_nip($nipDalnis)) ?></div>
      <?php endif; ?>
      <div class="ttd-date">Tgl: <?= !empty($sesi['tgl_reviu_dalnis']) ? date('d/m/Y', strtotime($sesi['tgl_reviu_dalnis'])) : tgl_id($sesi['tanggal_evaluasi']) ?></div>
    </div>
  </div>

  <div class="page-footer">
    <div>Portal Arsip Digital · Inspektorat Kab. Rokan Hilir</div>
    <div>Dicetak: <?= date('d/m/Y H:i') ?></div>
    <div>Lembar Reviu KKA #<?= (int)$sesi['id'] ?></div>
  </div>
</div>
</body>
</html>
