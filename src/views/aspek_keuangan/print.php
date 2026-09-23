<?php 
$desa = $analisis['desa'];
$title = 'Lembar Uji Aspek Keuangan - ' . $desa['desa_nama'] . ' TA ' . $tahun; 
?>
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
    padding:12mm 14mm 18mm 14mm;
    box-shadow:0 8px 24px rgba(0,0,0,.12);
    position:relative;
  }
  .kop{
    display:flex;gap:12px;align-items:flex-start;
    border-bottom:3px double #000;
    padding-bottom:8px;margin-bottom:12px;
    min-height:70px;
  }
  .kop .logo{width:64px;height:64px;display:grid;place-items:center;flex-shrink:0}
  .kop .logo img{max-width:100%;max-height:100%;object-fit:contain;display:block}
  .kop .center{flex:1;text-align:center;line-height:1.25}
  .kop .center .l1{font-size:11.5pt;font-weight:bold;letter-spacing:.3px}
  .kop .center .l2{font-size:15pt;font-weight:bold;letter-spacing:1px;margin:1px 0}
  .kop .center .l3{font-size:8.5pt}
  
  h1{text-align:center;font-size:12.5pt;margin:6px 0 2px;text-decoration:underline;text-transform:uppercase}
  h2{text-align:center;font-size:10pt;margin:0 0 10px;font-weight:normal}
  
  .id-table{width:100%;border-collapse:collapse;margin-bottom:10px;font-size:9.5pt}
  .id-table td{padding:2px 4px;vertical-align:top}
  .id-table td.lbl{width:36mm;font-weight:bold}
  .id-table td.sep{width:4px}
  
  .sec-title{font-size:10pt;font-weight:bold;margin:12px 0 4px;background:#f3f4f6;padding:3px 6px;border-left:4px solid #000}
  
  table.data{width:100%;border-collapse:collapse;margin-top:4px;font-size:9pt}
  table.data th, table.data td{border:1px solid #000;padding:4px 6px;vertical-align:top}
  table.data thead th{background:#e5e7eb;text-align:center;font-weight:bold}
  table.data .num{text-align:right;white-space:nowrap}
  table.data .center{text-align:center}
  
  .box-status{border:1.5px solid #000;padding:8px 10px;margin-top:6px;background:#fafafa}
  
  .ttd-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:24px;page-break-inside:avoid;font-size:9.5pt}
  .ttd-box{text-align:center;line-height:1.3}
  .ttd-space{height:55px}
  
  @media print {
    body{background:#fff}
    .page{margin:0;box-shadow:none;width:100%;min-height:auto;padding:0}
    .no-print{display:none !important}
  }
</style>
</head>
<body>

<div class="no-print" style="position:fixed;top:10px;right:10px;z-index:99999;display:flex;gap:8px">
  <button onclick="window.print()" style="background:#059669;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-weight:bold;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,0.2)">
    🖨️ Cetak / Simpan PDF
  </button>
  <button onclick="window.close()" style="background:#64748b;color:#fff;border:none;padding:8px 14px;border-radius:6px;cursor:pointer">
    Tutup
  </button>
</div>

<div class="page">
  <!-- KOP SURAT RESMI -->
  <div class="kop">
    <div class="logo"><img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil"></div>
    <div class="center">
      <div class="l1">PEMERINTAH KABUPATEN ROKAN HILIR</div>
      <div class="l2">INSPEKTORAT DAERAH</div>
      <div class="l3">Komplek Perkantoran Batu 6 Jl. Lintas Pesisir Sungai Rokan, Kec. Bangko - Bagansiapiapi</div>
      <div class="l3">Telp. (0767) 2700270 · Email: inspektorat@rohilkab.go.id · Website: arsipdigital-inspektorat.com</div>
    </div>
    <div class="logo"><img src="<?= asset('img/logo-inspektorat.png') ?>" alt="Inspektorat"></div>
  </div>

  <h1>KERTAS KERJA PENGUJIAN ASPEK KEUANGAN DESA</h1>
  <h2>UJI KESEIMBANGAN KAS, KEPATUHAN PERPAJAKAN &amp; PROPORSI APBDES (METODE SISWASKEUDES)</h2>

  <table class="id-table">
    <tr>
      <td class="lbl">Kepenghuluan (Desa)</td><td class="sep">:</td><td><strong><?= e($desa['desa_nama']) ?></strong></td>
      <td class="lbl" style="width:30mm">Tahun Anggaran</td><td class="sep">:</td><td><strong><?= $tahun ?></strong></td>
    </tr>
    <tr>
      <td class="lbl">Kecamatan</td><td class="sep">:</td><td>Kecamatan <?= e($desa['kecamatan_nama']) ?></td>
      <td class="lbl">Surat Tugas (SPT)</td><td class="sep">:</td><td><?= e($spt['no_spt'] ?? 'Terlampir dalam Penugasan') ?></td>
    </tr>
    <tr>
      <td class="lbl">Ref. BAP Opname Kas</td><td class="sep">:</td><td><?= e($analisis['opname']['no_bap'] ?? 'Belum Ada BAP Kas') ?></td>
      <td class="lbl">Tanggal Uji</td><td class="sep">:</td><td><?= !empty($analisis['opname']['tgl_pemeriksaan']) ? date('d F Y', strtotime($analisis['opname']['tgl_pemeriksaan'])) : date('d F Y') ?></td>
    </tr>
  </table>

  <!-- BAGIAN A: UJI KESEIMBANGAN KAS DESA -->
  <div class="sec-title">A. PENGUJIAN POSISI KESEIMBANGAN KAS (BUKU vs RIIL)</div>
  <table class="data">
    <thead>
      <tr>
        <th style="width:35px">No</th>
        <th>Uraian Komponen Saldo Kas</th>
        <th style="width:130px">Nilai Menurut Buku (Rp)</th>
        <th style="width:130px">Nilai Fisik / Rekening Riil (Rp)</th>
        <th style="width:120px">Selisih Kas (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="center">1</td>
        <td>Saldo Kas di Rekening Bank (Bank Riau Kepri Syariah)</td>
        <td class="num">-</td>
        <td class="num"><?= rupiah($analisis['kas_bank']) ?></td>
        <td class="center" rowspan="2" style="vertical-align:middle;font-weight:bold;color:<?= $analisis['selisih_kas'] < 0 ? '#991b1b' : '#000' ?>">
          <?= $analisis['selisih_kas'] < 0 ? '-' . rupiah(abs($analisis['selisih_kas'])) : rupiah($analisis['selisih_kas']) ?>
        </td>
      </tr>
      <tr>
        <td class="center">2</td>
        <td>Saldo Kas Tunai di Brankas Bendahara Desa</td>
        <td class="num">-</td>
        <td class="num"><?= rupiah($analisis['kas_fisik']) ?></td>
      </tr>
      <tr style="font-weight:bold;background:#f9fafb">
        <td colspan="2" style="text-align:right">JUMLAH KAS RIIL (1 + 2):</td>
        <td class="num">-</td>
        <td class="num"><?= rupiah($analisis['kas_riil']) ?></td>
        <td class="center"></td>
      </tr>
      <tr style="font-weight:bold;background:#f3f4f6">
        <td colspan="2" style="text-align:right">SALDO KAS MENURUT BUKU KAS UMUM (BKU):</td>
        <td class="num"><?= rupiah($analisis['kas_bku']) ?></td>
        <td class="num">-</td>
        <td class="center"></td>
      </tr>
    </tbody>
  </table>

  <div class="box-status">
    <strong>Kesimpulan Uji Kas: </strong>
    <?php if ($analisis['status_kas'] === 'TEKOR_KAS'): ?>
      <span style="color:#b91c1c;font-weight:bold">TERDAPAT KETEKORAN KAS (SELISIH KURANG) SEBESAR <?= rupiah($analisis['tekor_kas_nominal']) ?>.</span>
      <div style="font-size:8.5pt;margin-top:3px">
        Uang fisik kas riil di brankas dan bank lebih kecil daripada yang tercatat pada Buku Kas Umum. Bendahara/Penghulu wajib mengembalikan selisih kas tersebut ke Rekening Kas Desa via Surat Tanda Setor (STS).
      </div>
    <?php elseif ($analisis['status_kas'] === 'COCOK'): ?>
      <span style="color:#047857;font-weight:bold">KAS TERTIB (COCOK / NIHIL SELISIH).</span>
      <div style="font-size:8.5pt;margin-top:3px">Saldo menurut buku kas klop 100% dengan fisik uang tunai di brankas dan rekening bank.</div>
    <?php else: ?>
      <span>Pemeriksaan fisik kas (Opname Kas) belum dilaksanakan.</span>
    <?php endif; ?>
  </div>

  <!-- BAGIAN B: UJI KEPATUHAN PERPAJAKAN BELANJA -->
  <div class="sec-title" style="margin-top:14px">B. PENGUJIAN KEPATUHAN PENYETORAN PAJAK BELANJA (PPN &amp; PPh)</div>
  <table class="data">
    <thead>
      <tr>
        <th style="width:35px">No</th>
        <th>Uraian Perpajakan APBDes</th>
        <th style="width:130px">Pajak Dipungut (Rp)</th>
        <th style="width:130px">Pajak Disetor Ber-NTPN (Rp)</th>
        <th style="width:120px">Tunggakan Belum Setor (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="center">1</td>
        <td>Kewajiban Pajak Belanja Barang, Jasa &amp; Fisik</td>
        <td class="num"><?= rupiah($analisis['pajak_dipotong']) ?></td>
        <td class="num"><?= rupiah($analisis['pajak_disetor']) ?></td>
        <td class="num" style="font-weight:bold;color:<?= $analisis['pajak_belum_setor'] > 0 ? '#b91c1c' : '#000' ?>">
          <?= rupiah($analisis['pajak_belum_setor']) ?>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="box-status">
    <strong>Kesimpulan Uji Perpajakan: </strong>
    <?php if ($analisis['pajak_belum_setor'] > 0): ?>
      <span style="color:#b91c1c;font-weight:bold">TERDAPAT KETEKORAN PAJAK SEBESAR <?= rupiah($analisis['pajak_belum_setor']) ?> (Tingkat Kepatuhan: <?= $analisis['persen_setor_pajak'] ?>%).</span>
      <div style="font-size:8.5pt;margin-top:3px">
        Terdapat <?= count($analisis['daftar_pajak_terutang']) ?> bukti transaksi belanja yang telah dipotong pajak tetapi belum disetorkan ke Kas Negara atau belum divalidasi kode NTPN. Wajib segera disetorkan ke kas negara.
      </div>
    <?php else: ?>
      <span style="color:#047857;font-weight:bold">PAJAK TERTIB (100% TELAH DISETOR LUNAS KE KAS NEGARA DENGAN KODE NTPN).</span>
    <?php endif; ?>
  </div>

  <!-- BAGIAN C: EVALUASI PROPORSI APBDES (MAKS 30%) -->
  <div class="sec-title" style="margin-top:14px">C. EVALUASI PROPORSI BELANJA OPERASIONAL APBDES (BATAS MAKSIMAL 30%)</div>
  <table class="data">
    <thead>
      <tr>
        <th style="width:35px">Kode</th>
        <th>Bidang Pengeluaran APBDes</th>
        <th style="width:140px">Realisasi Belanja (Rp)</th>
        <th style="width:90px">Proporsi (%)</th>
        <th style="width:110px">Kriteria Siswaskeudes</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($analisis['bidang_breakdown'] as $bb): 
        $pct = $analisis['total_belanja_bidang'] > 0 ? round(((float)$bb['total_belanja'] / $analisis['total_belanja_bidang']) * 100, 1) : 0;
      ?>
        <tr>
          <td class="center"><?= e($bb['kode']) ?></td>
          <td><?= e($bb['nama']) ?></td>
          <td class="num"><?= rupiah((float)$bb['total_belanja']) ?></td>
          <td class="center"><?= $pct ?>%</td>
          <td class="center"><?= str_starts_with((string)$bb['kode'], '1') ? 'Maksimal 30%' : 'Minimal 70%' ?></td>
        </tr>
      <?php endforeach; ?>
      <tr style="font-weight:bold;background:#f3f4f6">
        <td colspan="2" style="text-align:right">TOTAL REALISASI BELANJA APBDES:</td>
        <td class="num"><?= rupiah($analisis['total_belanja_bidang']) ?></td>
        <td class="center">100.0%</td>
        <td class="center">
          <?= $analisis['status_proporsi'] === 'WAJAR' ? 'SESUAI ATURAN' : 'MELEBIHI BATAS' ?>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- TANDA TANGAN 3 PIHAK -->
  <div class="ttd-grid">
    <div class="ttd-box">
      Mengetahui,<br>
      <strong>Bendahara Kepenghuluan</strong>
      <div class="ttd-space"></div>
      <strong><u><?= e($analisis['opname']['nama_bendahara'] ?? '.....................................') ?></u></strong><br>
      <?= !empty($analisis['opname']['nip_bendahara']) ? 'NIP. ' . e($analisis['opname']['nip_bendahara']) : 'Bendahara Desa' ?>
    </div>

    <div class="ttd-box">
      Diperiksa Oleh,<br>
      <strong>Ketua Tim Pemeriksa</strong>
      <div class="ttd-space"></div>
      <strong><u><?= e($analisis['opname']['nama_ketua_tim'] ?? ($spt['ketua_tim_nama'] ?? '.....................................')) ?></u></strong><br>
      <?= !empty($analisis['opname']['nip_ketua_tim']) ? 'NIP. ' . e($analisis['opname']['nip_ketua_tim']) : 'Ketua Tim APIP' ?>
    </div>

    <div class="ttd-box">
      Disetujui Oleh,<br>
      <strong>Pengendali Teknis (Dalnis)</strong>
      <div class="ttd-space"></div>
      <strong><u><?= e($spt['dalnis_nama'] ?? '.....................................') ?></u></strong><br>
      <?= !empty($spt['dalnis_nip']) ? 'NIP. ' . e($spt['dalnis_nip']) : 'Pengendali Teknis APIP' ?>
    </div>
  </div>
</div>

</body>
</html>
