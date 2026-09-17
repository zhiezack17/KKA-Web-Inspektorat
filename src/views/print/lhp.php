<?php
/**
 * Format Cetak Resmi Laporan Hasil Pengawasan (LHP) Desa
 * Standar Naskah Dinas Pengawasan Inspektorat Kabupaten Rokan Hilir
 * A4 Portrait
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>LHP - Kepenghuluan <?= e($desa['nama']) ?> TA <?= $tahun ?></title>
  <style>
    @page {
      size: A4 portrait;
      margin: 20mm 20mm 20mm 25mm; /* Standar naskah dinas: kiri 25mm */
      @bottom-right {
        content: counter(page);
        font-family: "Bookman Old Style", Georgia, serif;
        font-size: 9pt;
      }
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Bookman Old Style", Georgia, "Times New Roman", serif;
      font-size: 11pt;
      line-height: 1.45;
      color: #000;
      margin: 0;
      padding: 0;
    }
    .text-center { text-align: center; }
    .text-justify { text-align: justify; }
    .num { text-align: right; white-space: nowrap; }
    .bold { font-weight: bold; }
    .uppercase { text-transform: uppercase; }

    /* KOP SURAT */
    .kop {
      border-bottom: 3px double #000;
      padding-bottom: 8px;
      margin-bottom: 18px;
      position: relative;
      text-align: center;
    }
    .kop img {
      position: absolute;
      left: 0;
      top: 2px;
      width: 65px;
      height: auto;
    }
    .kop h3 { margin: 0; font-size: 12pt; font-weight: bold; letter-spacing: 0.5px; }
    .kop h2 { margin: 2px 0 0; font-size: 15pt; font-weight: 800; letter-spacing: 1px; }
    .kop p { margin: 2px 0 0; font-size: 9pt; font-style: italic; }

    /* JUDUL */
    .judul-box { text-align: center; margin: 18px 0 22px; }
    .judul-lhp { font-size: 13pt; font-weight: bold; text-decoration: underline; letter-spacing: 0.5px; }
    .nomor-lhp { font-size: 10pt; margin-top: 4px; }
    .tentang-lhp { font-size: 11pt; font-weight: bold; margin-top: 6px; }

    /* BAB & SUBBAB */
    .bab-title {
      font-size: 11.5pt;
      font-weight: bold;
      text-transform: uppercase;
      border-bottom: 1.5px solid #000;
      padding-bottom: 3px;
      margin: 22px 0 10px;
    }
    .subbab { font-weight: bold; margin: 10px 0 4px; }

    /* TABEL */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin: 10px 0;
      font-size: 9.5pt;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #000;
      padding: 5px 6px;
      vertical-align: top;
    }
    table.data-table th {
      background: #f2f2f2;
      font-weight: bold;
      text-align: center;
    }

    /* KOTAK TEMUAN */
    .temuan-card {
      border: 1px solid #000;
      padding: 10px 12px;
      margin-bottom: 14px;
      page-break-inside: avoid;
    }
    .temuan-head {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid #000;
      padding-bottom: 4px;
      margin-bottom: 8px;
      font-weight: bold;
    }
    .unsur-item { margin-bottom: 6px; text-align: justify; }
    .unsur-label { font-weight: bold; }

    /* TANDA TANGAN */
    .ttd-section { margin-top: 30px; page-break-inside: avoid; }
    .ttd-table { width: 100%; border-collapse: collapse; font-size: 10.5pt; }
    .ttd-table td { width: 50%; text-align: center; vertical-align: top; padding-bottom: 55px; }

    .page-break { page-break-before: always; }

    .no-print {
      position: fixed;
      top: 15px;
      right: 15px;
      background: #fff;
      border: 1px solid #999;
      padding: 8px 16px;
      border-radius: 6px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      font-family: sans-serif;
      z-index: 1000;
    }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>

<div class="no-print">
  <button onclick="window.print()" style="background:#059669;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;font-weight:bold">🖨️ Cetak / Simpan PDF</button>
  <button onclick="window.close()" style="background:#64748b;color:#fff;border:none;padding:8px 12px;border-radius:4px;cursor:pointer;margin-left:6px">Tutup</button>
</div>

<!-- KOP RESMI -->
<div class="kop">
  <img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil">
  <h3>PEMERINTAH KABUPATEN ROKAN HILIR</h3>
  <h2>INSPEKTORAT DAERAH</h2>
  <p>Jl. Perkantoran Bagansiapiapi &bull; Email: inspektorat@rohilkab.go.id &bull; Kode Pos: 28912</p>
</div>

<div class="judul-box">
  <div class="judul-lhp uppercase">LAPORAN HASIL PENGAWASAN (LHP)</div>
  <div class="nomor-lhp">NOMOR : <?= e($spt['no_lha'] ?? ('700/LHA-INSP/' . $tahun . '/' . sprintf('%03d', $desa['id']))) ?></div>
  <div class="tentang-lhp uppercase">
    AUDIT DENGAN TUJUAN TERTENTU (ADTT) ATAS PENGELOLAAN KEUANGAN KEPENGHULUAN <?= e($desa['nama']) ?><br>
    KECAMATAN <?= e($desa['kecamatan_nama']) ?> KABUPATEN ROKAN HILIR<br>
    TAHUN ANGGARAN <?= $tahun ?>
  </div>
</div>

<!-- RINGKASAN EKSEKUTIF -->
<div style="border:1px solid #000;padding:12px 14px;margin-bottom:20px;background:#fcfcfc">
  <div style="font-weight:bold;text-transform:uppercase;border-bottom:1px solid #000;padding-bottom:4px;margin-bottom:8px;font-size:10.5pt">
    RINGKASAN EKSEKUTIF
  </div>
  <p class="text-justify" style="margin:0 0 8px;font-size:10pt">
    Berdasarkan Surat Perintah Tugas Inspektur Daerah Kabupaten Rokan Hilir Nomor: <b><?= e($spt['no_spt'] ?? '...........................') ?></b> tanggal <b><?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '..............' ?></b>, Tim Pemeriksa telah melaksanakan Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan Kepenghuluan <?= e($desa['nama']) ?> Kecamatan <?= e($desa['kecamatan_nama']) ?> Tahun Anggaran <?= $tahun ?>.
  </p>
  <p class="text-justify" style="margin:0;font-size:10pt">
    Dari hasil pemeriksaan dokumen belanja dan verifikasi fisik pekerjaan di lapangan atas realisasi belanja desa sebesar <b><?= rupiah($totalRealisasi) ?></b>, ditemukan <b><?= count($daftarTemuan) ?> butir temuan</b> dengan total nilai ketidaksesuaian/indikasi kerugian kas kepenghuluan sebesar <b><?= rupiah($totalNominalTemuan) ?></b>.
  </p>
</div>

<!-- BAB I -->
<div class="bab-title">BAB I : INFORMASI UMUM PENUGASAN</div>
<table style="width:100%;font-size:10.5pt;border-collapse:collapse">
  <tr><td style="width:25%;padding:2px 0;vertical-align:top"><b>1. Dasar Audit</b></td><td style="width:2%">:</td><td style="padding:2px 0">Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Kabupaten Rokan Hilir Tahun Anggaran <?= $tahun ?> &bull; Surat Perintah Tugas Nomor: <?= e($spt['no_spt'] ?? '-') ?> tanggal <?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '-' ?>.</td></tr>
  <tr><td style="padding:2px 0;vertical-align:top"><b>2. Tujuan Audit</b></td><td>:</td><td style="padding:2px 0"><?= e($spt['tujuan'] ?? 'Memberikan keyakinan memadai atas ketaatan dan akuntabilitas pengelolaan keuangan kepenghuluan.') ?></td></tr>
  <tr><td style="padding:2px 0;vertical-align:top"><b>3. Ruang Lingkup</b></td><td>:</td><td style="padding:2px 0">Pemeriksaan bukti pertanggungjawaban (SPJ) pengeluaran kas belanja desa, pengujian kepatuhan perpajakan, dan opname fisik pembangunan desa TA <?= $tahun ?>.</td></tr>
  <tr>
    <td style="padding:2px 0;vertical-align:top"><b>4. Susunan Tim</b></td><td>:</td>
    <td style="padding:2px 0">
      <div>a. Penanggung Jawab : <?= e($inspektur['nama']) ?> (Inspektur Daerah)</div>
      <div>b. Wakil Penanggung Jawab : <?= e($spt['wakil_pj_nama'] ?? '-') ?> (Inspektur Pembantu)</div>
      <div>c. Pengendali Teknis : <?= e($spt['dalnis_nama'] ?? '-') ?></div>
      <div>d. Ketua Tim : <?= e($spt['ketua_tim_nama'] ?? '-') ?></div>
      <?php if (!empty($anggotaList)): ?>
        <div>e. Anggota Tim : 
          <?= implode(', ', array_map(fn($a) => e($a['nama'] ?? ''), $anggotaList)) ?>
        </div>
      <?php endif; ?>
    </td>
  </tr>
</table>

<!-- BAB II -->
<div class="bab-title">BAB II : GAMBARAN PENGELOLAAN KEUANGAN KEPENGHULUAN</div>
<p class="text-justify" style="margin:0 0 6px">
  Realisasi belanja APBDesa Kepenghuluan <?= e($desa['nama']) ?> Tahun Anggaran <?= $tahun ?> yang dilakukan pengujian oleh Tim Pengawasan dirangkum pada tabel berikut:
</p>

<table class="data-table">
  <thead>
    <tr>
      <th style="width:25px">NO</th>
      <th>BIDANG BELANJA APBDESA</th>
      <th class="num" style="width:110px">PAGU (RP)</th>
      <th class="num" style="width:110px">REALISASI (RP)</th>
      <th class="num" style="width:110px">KUITANSI (RP)</th>
      <th class="num" style="width:100px">SELISIH (RP)</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($rekapBidang)): ?>
      <tr><td colspan="6" class="text-center">Belum ada rincian belanja tercatat.</td></tr>
    <?php else: $no=1; foreach ($rekapBidang as $rb): 
      $sel = (float)$rb['realisasi'] - (float)$rb['kuitansi'];
    ?>
      <tr>
        <td class="text-center"><?= $no++ ?></td>
        <td><b><?= e($rb['nama']) ?></b></td>
        <td class="num"><?= number_format((float)$rb['pagu'],0,',','.') ?></td>
        <td class="num"><?= number_format((float)$rb['realisasi'],0,',','.') ?></td>
        <td class="num"><?= number_format((float)$rb['kuitansi'],0,',','.') ?></td>
        <td class="num" style="font-weight:bold"><?= number_format($sel,0,',','.') ?></td>
      </tr>
    <?php endforeach; endif; ?>
    <tr style="font-weight:bold;background:#f2f2f2">
      <td colspan="2" class="text-center">TOTAL BELANJA DIUJI</td>
      <td class="num"><?= number_format((float)$totalPagu,0,',','.') ?></td>
      <td class="num"><?= number_format((float)$totalRealisasi,0,',','.') ?></td>
      <td class="num"><?= number_format((float)$totalKuitansi,0,',','.') ?></td>
      <td class="num"><?= number_format((float)$totalSelisih,0,',','.') ?></td>
    </tr>
  </tbody>
</table>

<p class="text-justify" style="margin:8px 0 0;font-size:10pt">
  <b>Kepatuhan Perpajakan:</b> Dari pengujian transaksi belanja, total pajak terutang PPN sebesar <b><?= rupiah($rekapPajak['total_ppn'] ?? 0) ?></b> dan PPh sebesar <b><?= rupiah($rekapPajak['total_pph'] ?? 0) ?></b>. Pajak yang telah disetor ke kas negara sebesar <b><?= rupiah($rekapPajak['pajak_disetor'] ?? 0) ?></b> dan tunggakan pajak yang belum disetor sebesar <b><?= rupiah($rekapPajak['pajak_belum_setor'] ?? 0) ?></b>.
</p>

<!-- BAB III -->
<div class="bab-title page-break">BAB III : POKOK TEMUAN PEMERIKSAAN &amp; REKOMENDASI</div>
<?php if (empty($daftarTemuan)): ?>
  <p class="text-justify" style="font-style:italic">
    Berdasarkan hasil pengujian atas sampel bukti pertanggungjawaban yang disampaikan pihak Kepenghuluan, tidak ditemukan adanya penyimpangan signifikan yang berindikasi merugikan kas desa.
  </p>
<?php else: $nt=1; foreach ($daftarTemuan as $t): ?>
  <div class="temuan-card">
    <div class="temuan-head">
      <div><?= $nt++ ?>. <?= e($t['judul']) ?></div>
      <div>[ <?= e($t['nomor_temuan']) ?> ]</div>
    </div>
    <?php if ((float)$t['nominal'] > 0): ?>
      <div style="font-size:10pt;font-weight:bold;margin-bottom:6px">
        Nilai Temuan / Indikasi Kerugian: <?= rupiah($t['nominal']) ?>
      </div>
    <?php endif; ?>
    <div class="unsur-item"><span class="unsur-label">a. Kondisi:</span> <?= nl2br(e($t['kondisi'])) ?></div>
    <div class="unsur-item"><span class="unsur-label">b. Kriteria:</span> <?= nl2br(e($t['kriteria'])) ?></div>
    <div class="unsur-item"><span class="unsur-label">c. Sebab:</span> <?= nl2br(e($t['sebab'])) ?></div>
    <div class="unsur-item"><span class="unsur-label">d. Akibat:</span> <?= nl2br(e($t['akibat'])) ?></div>
    <div class="unsur-item"><span class="unsur-label">e. Rekomendasi:</span> <?= nl2br(e($t['rekomendasi'])) ?></div>
    <?php if (!empty($t['tanggapan_auditi'])): ?>
      <div class="unsur-item" style="font-style:italic"><span class="unsur-label">f. Tanggapan Auditi:</span> <?= nl2br(e($t['tanggapan_auditi'])) ?></div>
    <?php endif; ?>
  </div>
<?php endforeach; endif; ?>

<!-- BAB IV -->
<div class="bab-title">BAB IV : KESIMPULAN &amp; PENUTUP</div>
<p class="text-justify">
  Demikian Laporan Hasil Pengawasan (LHP) atas Audit Dengan Tujuan Tertentu (ADTT) Pengelolaan Keuangan Kepenghuluan <?= e($desa['nama']) ?> Kecamatan <?= e($desa['kecamatan_nama']) ?> ini disusun untuk dipergunakan sebagaimana mestinya. Sesuai ketentuan peraturan perundang-undangan, Pj. Penghulu wajib menyampaikan laporan tindak lanjut atas rekomendasi dalam laporan ini selambat-lambatnya 60 (enam puluh) hari kalender setelah LHP ini diterima.
</p>

<!-- TANDA TANGAN & PENGESAHAN -->
<div class="ttd-section">
  <table class="ttd-table">
    <tr>
      <td>
        Mengetahui,<br>
        <b>Inspektur Pembantu (Irban)</b><br><br><br><br><br>
        <b><u><?= e($spt['wakil_pj_nama'] ?? 'MARWAN, M.T') ?></u></b><br>
        NIP. <?= !empty($spt['wakil_pj_nip']) ? e($spt['wakil_pj_nip']) : '19770727 200212 1 005' ?>
      </td>
      <td>
        Bagansiapiapi, <?= tgl_id(date('Y-m-d')) ?><br>
        <b>Ketua Tim Pemeriksa</b><br><br><br><br><br>
        <b><u><?= e($spt['ketua_tim_nama'] ?? '............................................') ?></u></b><br>
        NIP. <?= !empty($spt['ketua_tim_nip']) ? e($spt['ketua_tim_nip']) : '............................................' ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="padding-bottom:0">
        Mengesahkan,<br>
        <b>INSPEKTUR DAERAH KABUPATEN ROKAN HILIR</b><br><br><br><br><br>
        <b><u><?= e($inspektur['nama']) ?></u></b><br>
        NIP. <?= e($inspektur['nip']) ?>
      </td>
    </tr>
  </table>
</div>

</body>
</html>
