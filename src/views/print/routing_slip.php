<?php
/**
 * Lembar Cetak Resmi: KONSEP LAPORAN HASIL AUDIT (ROUTING SLIP LHA)
 * Inspektorat Kabupaten Rokan Hilir
 * Disesuaikan dengan Formulir Kendali Mutu LHP.
 */
$namaInspektur = 'H. SARMAN SYAHRONI, ST., M.IP';
$nipInspektur  = '19760810 200312 1 004';
$jabatanInspektur = 'Inspektur Daerah Kabupaten Rokan Hilir';

// Resolusi Irban (Wakil Penanggung Jawab)
$rawIrbanNama = $irbanUser['nama'] ?? ($sesi['irban_nama'] ?? '');
$rawIrbanNip  = $irbanUser['nip'] ?? '';
[$namaIrban, $nipIrban] = split_nama_nip($rawIrbanNama, $rawIrbanNip);
if ($namaIrban === 'Inspektur Pembantu (Irban)' || strtolower(trim($namaIrban)) === 'irban') {
    $namaIrban = '';
}

// Resolusi Dalnis (Pengendali Teknis)
$rawDalnisNama = $dalnisUser['nama'] ?? ($sesi['dievaluasi_oleh'] ?? '');
$rawDalnisNip  = $dalnisUser['nip'] ?? '';
[$namaDalnis, $nipDalnis] = split_nama_nip($rawDalnisNama, $rawDalnisNip);

// Resolusi Ketua Tim
$rawKetuaNama = $ketuaUser['nama'] ?? ($sesi['direview_oleh'] ?? '');
$rawKetuaNip  = $ketuaUser['nip'] ?? '';
[$namaKetua, $nipKetua] = split_nama_nip($rawKetuaNama, $rawKetuaNip);

// Resolusi Pembuat / Penyusun KKA
$rawPenyusunNama = $creatorUser['nama'] ?? ($sesi['dibuat_oleh'] ?? '');
$rawPenyusunNip  = $creatorUser['nip'] ?? '';
[$namaPenyusun, $nipPenyusun] = split_nama_nip($rawPenyusunNama, $rawPenyusunNip);

// Susun Anggota Tim TANPA DUPLIKAT (Jika pembuat adalah Ketua Tim, jangan masukkan ganda di Anggota)
$isSamePerson = function(string $n1, string $n2): bool {
    $n1 = strtolower(trim(preg_replace('/\s*\(NIP\.?[^\)]*\)/i', '', $n1)));
    $n2 = strtolower(trim(preg_replace('/\s*\(NIP\.?[^\)]*\)/i', '', $n2)));
    return ($n1 !== '' && $n2 !== '' && ($n1 === $n2 || str_starts_with($n1, $n2) || str_starts_with($n2, $n1)));
};

$anggotaTim = [];
// Jika pembuat/penyusun BUKAN Ketua Tim dan BUKAN Dalnis, masukkan sebagai Anggota #1
if (!empty($namaPenyusun) && !$isSamePerson($namaPenyusun, $namaKetua) && !$isSamePerson($namaPenyusun, $namaDalnis)) {
    $anggotaTim[] = ['nama' => $namaPenyusun, 'nip' => $nipPenyusun];
}

// Masukkan auditor dari kka_sesi_share (jika bukan Ketua Tim, bukan Dalnis, dan belum ada di list)
if (!empty($sharedWith) && is_array($sharedWith)) {
    foreach ($sharedWith as $sw) {
        [$swNama, $swNip] = split_nama_nip($sw['nama'] ?? '', $sw['nip'] ?? '');
        if ($isSamePerson($swNama, $namaKetua) || $isSamePerson($swNama, $namaDalnis)) {
            continue;
        }
        $already = false;
        foreach ($anggotaTim as $at) {
            if ($isSamePerson($swNama, $at['nama'])) { $already = true; break; }
        }
        if (!$already && $swNama !== '') {
            $anggotaTim[] = ['nama' => $swNama, 'nip' => $swNip];
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Routing Slip LHA - <?= e($sesi['objek_audit']) ?></title>
<style>
  @page {
    size: 215mm 330mm; /* Standar Folio / F4 */
    margin: 12mm 15mm 12mm 15mm;
  }
  * { box-sizing: border-box; }
  body {
    font-family: "Times New Roman", Times, serif;
    font-size: 11pt;
    line-height: 1.25;
    color: #000;
    background: #fff;
    margin: 0;
    padding: 0;
  }
  .no-print {
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
  }
  .kop {
    display: flex;
    align-items: center;
    gap: 16px;
    border-bottom: 3px double #000;
    padding-bottom: 8px;
    margin-bottom: 12px;
  }
  .kop-logo { width: 75px; height: auto; text-align: center; }
  .kop-logo img { width: 70px; height: auto; }
  .kop-text { flex: 1; text-align: center; }
  .kop-text h2 { margin: 0; font-size: 14pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
  .kop-text h1 { margin: 2px 0; font-size: 17pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
  .kop-text p { margin: 0; font-size: 8.5pt; line-height: 1.25; }

  .doc-title {
    text-align: center;
    font-size: 13pt;
    font-weight: bold;
    text-decoration: underline;
    text-transform: uppercase;
    margin: 10px 0 14px;
    letter-spacing: 0.5px;
  }

  table.form-meta {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    font-size: 10pt;
  }
  table.form-meta td {
    padding: 2.5px 4px;
    vertical-align: top;
  }

  table.grid-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    font-size: 9.5pt;
  }
  table.grid-table th, table.grid-table td {
    border: 1px solid #000;
    padding: 4px 6px;
    vertical-align: middle;
  }
  table.grid-table th {
    background: #f1f5f9;
    font-weight: bold;
    text-align: center;
    text-transform: uppercase;
  }

  .text-center { text-align: center; }
  .text-bold { font-weight: bold; }
  .page-break { page-break-before: always; }

  @media print {
    .no-print { display: none !important; }
    body { font-size: 10.5pt; }
  }
</style>
</head>
<body>

<div class="no-print">
  <div>
    <strong style="font-family:sans-serif;color:#1e293b;font-size:14px">
      <i class="fa-solid fa-folder-open" style="color:#d97706"></i> Lembar Kendali Mutu LHA (Routing Slip)
    </strong>
    <span style="font-family:sans-serif;color:#64748b;font-size:12px;margin-left:10px">
      Format Standar Operasional Prosedur Pengawasan Inspektorat Kabupaten Rokan Hilir
    </span>
  </div>
  <div style="display:flex;gap:8px">
    <button onclick="window.print()" style="background:#d97706;color:#fff;border:0;padding:7px 16px;border-radius:6px;cursor:pointer;font-weight:bold;font-family:sans-serif">
      🖨️ Cetak / Simpan PDF
    </button>
    <button onclick="window.close()" style="background:#e2e8f0;color:#334155;border:0;padding:7px 14px;border-radius:6px;cursor:pointer;font-family:sans-serif">
      Tutup
    </button>
  </div>
</div>

<div style="padding: 10px 15px;">
  <!-- KOP SURAT RESMI -->
  <div class="kop">
    <div class="kop-logo">
      <img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil">
    </div>
    <div class="kop-text">
      <h2>Pemerintah Kabupaten Rokan Hilir</h2>
      <h1>INSPEKTORAT</h1>
      <p>Komplek Perkantoran Batu 6 Jalan Lintas Pesisir Sungai Rokan Telp. (0767) 2700270 Fax. (0767) 2700271<br><b>KECAMATAN BANGKO - BAGANSIAPIAPI</b></p>
    </div>
  </div>

  <div class="doc-title">KONSEP LAPORAN HASIL AUDIT</div>

  <!-- IDENTITAS OBJEK & PENUGASAN -->
  <table class="form-meta">
    <tr>
      <td style="width:28px">1.</td>
      <td style="width:200px" class="text-bold">NAMA OBYEK YANG DIAUDIT</td>
      <td style="width:10px">:</td>
      <td class="text-bold" colspan="3"><?= e($sesi['objek_audit']) ?></td>
    </tr>
    <tr>
      <td>2.</td>
      <td class="text-bold">ALAMAT DAN TELEPON</td>
      <td>:</td>
      <td colspan="3"><?= e($sesi['alamat_objek'] ?: ('Kepenghuluan ' . $sesi['desa_nama'] . ', Kec. ' . $sesi['kecamatan_nama'])) ?></td>
    </tr>
    <tr>
      <td>3.</td>
      <td class="text-bold">PERIODE AUDIT</td>
      <td>:</td>
      <td style="width:220px">Semester <?= (int)$sesi['semester'] ?> / Tahun <?= (int)$sesi['tahun_anggaran'] ?></td>
      <td style="width:110px" class="text-bold">JENIS AUDIT :</td>
      <td><?= e($sesi['jenis_audit'] ?: 'Audit Dengan Tujuan Tertentu (ADTT)') ?></td>
    </tr>
    <tr>
      <td>4.</td>
      <td class="text-bold">NO/TGL. SPT</td>
      <td>:</td>
      <td><?= e($sesi['no_spt'] ?: 'SPT/......../INSP/2026') ?> &nbsp; (<?= tgl_id($sesi['tgl_spt'] ?: $sesi['tanggal_dibuat']) ?>)</td>
      <td class="text-bold">SPT S.D. TGL. :</td>
      <td><?= tgl_id($sesi['tgl_spt_selesai'] ?: date('Y-m-d', strtotime(($sesi['tgl_spt'] ?: $sesi['tanggal_dibuat']) . ' +14 days'))) ?></td>
    </tr>
  </table>

  <!-- TABEL PROSES PEMBAHASAN -->
  <table class="grid-table">
    <thead>
      <tr>
        <th style="width:40%">PROSES PEMBAHASAN</th>
        <th style="width:8%"></th>
        <th style="width:28%">Nama</th>
        <th style="width:4.8%">Paraf/Tanggal<br>(I)</th>
        <th style="width:4.8%">(II)</th>
        <th style="width:4.8%">(III)</th>
        <th style="width:4.8%">(IV)</th>
        <th style="width:4.8%">(V)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1. Konsep LHA <u>diserahkan</u> oleh Ketua Tim (KT) kepada Pengendali Teknis (PT)</td>
        <td class="text-center text-bold">KT</td>
        <td><?= e($namaKetua) ?></td>
        <td class="text-center"><?= !empty($sesi['tgl_reviu_ketua']) ? '✓<br><small style="font-size:7pt">'.date('d/m', strtotime($sesi['tgl_reviu_ketua'])).'</small>' : '' ?></td>
        <td></td><td></td><td></td><td></td>
      </tr>
      <tr>
        <td>2. Setelah <u>direviu</u> oleh PT, diserahkan kepada Inspektur (IR) melalui TU Inspektur (TU IR)</td>
        <td class="text-center text-bold">PT</td>
        <td><?= e($namaDalnis) ?></td>
        <td class="text-center"><?= !empty($sesi['tgl_reviu_dalnis']) ? '✓<br><small style="font-size:7pt">'.date('d/m', strtotime($sesi['tgl_reviu_dalnis'])).'</small>' : '' ?></td>
        <td></td><td></td><td></td><td></td>
      </tr>
      <tr>
        <td rowspan="2">3. Konsep LHA <u>direviu</u> oleh Inspektur</td>
        <td class="text-center text-bold">IR</td>
        <td style="font-style:italic">Diterima Inspektur</td>
        <td></td><td></td><td></td><td></td><td></td>
      </tr>
      <tr>
        <td class="text-center text-bold">IR</td>
        <td style="font-style:italic">Direviu Inspektur</td>
        <td class="text-center"><?= ($sesi['status'] === 'SELESAI_FINAL') ? '✓' : '' ?></td>
        <td></td><td></td><td></td><td></td>
      </tr>
    </tbody>
  </table>

  <!-- TABEL PROSES ADMINISTRASI -->
  <table class="grid-table">
    <thead>
      <tr>
        <th style="width:48%">PROSES ADMINISTRASI</th>
        <th style="width:22%">Nama yang menyerahkan</th>
        <th style="width:20%">Nama yang menerima</th>
        <th style="width:5%">Paraf</th>
        <th style="width:5%">Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1) Konsep LHA beserta KKA, <u>diserahkan</u> Tim Pemeriksa (TP) kepada TU IR</td>
        <td><?= e($namaKetua) ?></td>
        <td>Petugas TU IR</td>
        <td class="text-center"><?= !empty($sesi['tgl_reviu_ketua']) ? '✓' : '' ?></td>
        <td class="text-center" style="font-size:8pt"><?= !empty($sesi['tgl_reviu_ketua']) ? date('d/m/y', strtotime($sesi['tgl_reviu_ketua'])) : '' ?></td>
      </tr>
      <tr>
        <td>2) Setelah direviu oleh Inspektur, <u>diserahkan</u> TU IR kepada KT/PT untuk perbaikan</td>
        <td>Petugas TU IR</td>
        <td><?= e($namaKetua) ?></td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <!-- TABEL SELESAI DIREVIU INSPEKTUR -->
  <table class="grid-table">
    <thead>
      <tr>
        <th style="width:65%">Selesai direviu Inspektur :</th>
        <th style="width:21%">Nama</th>
        <th style="width:7%">Paraf</th>
        <th style="width:7%">Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>3) Konsep LHA dan KKA <u>diperbaiki/dilengkapi</u> oleh KT/PT</td>
        <td><?= e($namaKetua) ?> / <?= e($namaDalnis) ?></td>
        <td class="text-center"><?= in_array($sesi['status'], ['REVIEW_DALNIS','SELESAI_FINAL']) ? '✓' : '' ?></td>
        <td class="text-center" style="font-size:8pt"><?= !empty($sesi['tgl_reviu_dalnis']) ? date('d/m/y', strtotime($sesi['tgl_reviu_dalnis'])) : '' ?></td>
      </tr>
      <tr>
        <td>4) Konsep LHA dan KKA <u>diteliti/diperiksa</u> oleh Inspektur Pembantu</td>
        <td><?= e($namaIrban) ?></td>
        <td class="text-center"><?= !empty($sesi['tgl_reviu_irban']) || $sesi['status'] === 'SELESAI_FINAL' ? '✓' : '' ?></td>
        <td class="text-center" style="font-size:8pt"><?= !empty($sesi['tgl_reviu_irban']) ? date('d/m/y', strtotime($sesi['tgl_reviu_irban'])) : '' ?></td>
      </tr>
      <tr>
        <td>5) Konsep LHA <u>ditandatangani</u> oleh Inspektur</td>
        <td><?= e($namaInspektur) ?></td>
        <td class="text-center"><?= ($sesi['status'] === 'SELESAI_FINAL') ? '✓' : '' ?></td>
        <td class="text-center" style="font-size:8pt"><?= ($sesi['status'] === 'SELESAI_FINAL' && !empty($sesi['updated_at'])) ? date('d/m/y', strtotime($sesi['updated_at'])) : '' ?></td>
      </tr>
      <tr>
        <td>6) LHA <u>dicatat/diberi nomor/dijilid</u> dan diarsipkan oleh Tenaga Administrasi Bagian Perencanaan</td>
        <td>Staf Bag. Perencanaan</td>
        <td></td><td></td>
      </tr>
      <tr>
        <td>7) LHA <u>dikirim</u> kepada Obyek yang diperiksa oleh Bagian Perencanaan</td>
        <td>Staf Bag. Perencanaan</td>
        <td></td><td></td>
      </tr>
      <tr>
        <td>8) LHA <u>dicatat/diteliti/diinput</u> oleh Bagian Analisis &amp; Evaluasi Ke Sim HP</td>
        <td>Staf Bag. Anev (SIM HP)</td>
        <td></td><td></td>
      </tr>
    </tbody>
  </table>

  <!-- NOMOR LHA & TANGGAL -->
  <div style="display:flex;justify-content:space-between;align-items:center;margin:5px 0;font-size:9.5pt">
    <div><b>Nomor LHA :</b> &nbsp; <?= e($sesi['no_lha'] ?: '..............................................................') ?></div>
    <div><b>Tanggal :</b> &nbsp; <?= !empty($sesi['tgl_lha']) ? tgl_id($sesi['tgl_lha']) : '........................................' ?></div>
  </div>

  <!-- TABEL LAMPIRAN LHA -->
  <table class="grid-table" style="margin-bottom:6px">
    <thead>
      <tr>
        <th colspan="2" style="text-align:left;padding:3px 8px;font-size:9.5pt">Lampiran LHA :</th>
      </tr>
    </thead>
    <tbody>
      <tr><td style="width:25px;text-align:center;padding:1.5px 4px">1.</td><td style="padding:1.5px 8px">Lembar Kertas Kerja Audit (KKA) Belanja Desa</td></tr>
      <tr><td style="text-align:center;padding:1.5px 4px">2.</td><td style="padding:1.5px 8px">Rekapitulasi Realisasi Belanja Kegiatan</td></tr>
      <tr><td style="text-align:center;padding:1.5px 4px">3.</td><td style="padding:1.5px 8px">Bukti SPJ, Kwitansi, Nota, Bukti Transfer &amp; Setor Pajak</td></tr>
      <tr><td style="text-align:center;padding:1.5px 4px">4.</td><td style="padding:1.5px 8px">Matriks Temuan Pemeriksaan (KTP 5 Unsur)</td></tr>
      <tr><td style="text-align:center;padding:1.5px 4px">5.</td><td style="padding:1.5px 8px">Dokumentasi Foto Fisik Lapangan / Foto Kegiatan</td></tr>
      <tr><td style="text-align:center;padding:1.5px 4px">6.</td><td style="padding:1.5px 8px">Lembar Pengukuran Fisik Lapangan (STA / Back Up Data Fisik)</td></tr>
      <tr><td style="text-align:center;padding:1.5px 4px">7.</td><td style="padding:1.5px 8px">Surat Perintah Tugas (SPT) Pengawasan Inspektorat</td></tr>
      <tr><td style="text-align:center;padding:1.5px 4px">8.</td><td style="padding:1.5px 8px">Notulen Pembahasan &amp; Lembar Quality Assurance (QA)</td></tr>
    </tbody>
  </table>

  <!-- TABEL SUSUNAN TIM PEMERIKSA -->
  <table class="grid-table" style="margin-top:4px">
    <thead>
      <tr>
        <th style="width:34%">Susunan Tim Pemeriksa</th>
        <th style="width:40%">Nama</th>
        <th style="width:26%">NIP</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-bold">- Wakil Penanggung Jawab</td>
        <td><?= !empty($namaIrban) ? e($namaIrban) : '..................................................' ?></td>
        <td><?= (!empty($nipIrban) && $nipIrban !== '-') ? e(format_nip($nipIrban)) : '..................................................' ?></td>
      </tr>
      <tr>
        <td class="text-bold">- Data Dalnis Penanggung Jawab</td>
        <td><?= !empty($namaDalnis) ? e($namaDalnis) : '..................................................' ?></td>
        <td><?= (!empty($nipDalnis) && $nipDalnis !== '-') ? e(format_nip($nipDalnis)) : '..................................................' ?></td>
      </tr>
      <tr>
        <td class="text-bold">- Ketua Tim</td>
        <td><?= !empty($namaKetua) ? e($namaKetua) : '..................................................' ?></td>
        <td><?= (!empty($nipKetua) && $nipKetua !== '-') ? e(format_nip($nipKetua)) : '..................................................' ?></td>
      </tr>
      <?php 
      $totalSlots = max(10, count($anggotaTim));
      for ($i = 0; $i < $totalSlots; $i++): 
          $at = $anggotaTim[$i] ?? null;
          $namaSlot = $at ? e($at['nama']) : '';
          $nipSlot  = ($at && !empty($at['nip']) && $at['nip'] !== '-') ? e(format_nip($at['nip'])) : '';
      ?>
        <tr>
          <?php if ($i === 0): ?>
            <td class="text-bold" rowspan="<?= $totalSlots ?>" style="vertical-align:top">- Anggota Tim</td>
          <?php endif; ?>
          <td><?= ($i + 1) ?>. <?= $namaSlot ?></td>
          <td><?= $nipSlot ?></td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>
</div>

<!-- HALAMAN 2: LEMBAR CATATAN HASIL REVIU PEMERIKSAAN (REVIEW SHEET) -->
<div class="page-break" style="padding: 15px;">
  <div style="text-align:center;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:14px">
    <h3 style="margin:0;font-size:12.5pt;font-weight:bold;text-transform:uppercase;letter-spacing:0.5px">LEMBAR CATATAN HASIL REVIU PEMERIKSAAN (REVIEW SHEET)</h3>
    <h4 style="margin:3px 0 0;font-size:10pt;font-weight:normal;color:#333">ROUTING SLIP KENDALI MUTU KONSEP LAPORAN HASIL AUDIT (LHA)</h4>
  </div>

  <table class="form-meta" style="margin-bottom:14px">
    <tr>
      <td style="width:160px" class="text-bold">Obyek yang Diperiksa</td>
      <td style="width:10px">:</td>
      <td class="text-bold"><?= e($sesi['objek_audit']) ?> (Kec. <?= e($sesi['kecamatan_nama']) ?>)</td>
    </tr>
    <tr>
      <td class="text-bold">Tahun / Semester</td>
      <td>:</td>
      <td>Tahun Anggaran <?= (int)$sesi['tahun_anggaran'] ?> &nbsp; (Semester <?= (int)$sesi['semester'] ?>)</td>
    </tr>
  </table>

  <!-- KOTAK CATATAN DALNIS -->
  <div style="border:1px solid #000;padding:12px;margin-bottom:14px;min-height:160px">
    <div style="display:flex;justify-content:space-between;border-bottom:1px dashed #666;padding-bottom:6px;margin-bottom:8px">
      <b style="font-size:10.5pt">A. Catatan / Arahan Koreksi Pengendali Teknis (PT / Dalnis)</b>
      <span style="font-size:9.5pt">Tanggal: <?= !empty($sesi['tgl_reviu_dalnis']) ? tgl_id($sesi['tgl_reviu_dalnis']) : '..........................' ?></span>
    </div>
    <div style="font-size:10pt;line-height:1.6;white-space:pre-wrap"><?php if (!empty($sesi['catatan_reviu_dalnis'])): ?><?= e($sesi['catatan_reviu_dalnis']) ?><?php else: ?><div style="min-height:70px;color:#94a3b8;font-style:italic;padding-top:4px">(Catatan / arahan perbaikan diinput melalui sistem atau ditulis langsung pada lembar ini)</div><?php endif; ?></div>
    <div style="text-align:right;margin-top:20px">
      <b>Pengendali Teknis,</b><br><br><br>
      <u><b><?= !empty($namaDalnis) ? e($namaDalnis) : '..................................................' ?></b></u><br>
      NIP. <?= (!empty($nipDalnis) && $nipDalnis !== '-') ? e(format_nip($nipDalnis)) : '..................................................' ?>
    </div>
  </div>

  <!-- KOTAK CATATAN IRBAN -->
  <div style="border:1px solid #000;padding:12px;margin-bottom:14px;min-height:160px">
    <div style="display:flex;justify-content:space-between;border-bottom:1px dashed #666;padding-bottom:6px;margin-bottom:8px">
      <b style="font-size:10.5pt">B. Catatan / Arahan Inspektur Pembantu (Wakil Penanggung Jawab)</b>
      <span style="font-size:9.5pt">Tanggal: <?= !empty($sesi['tgl_reviu_irban']) ? tgl_id($sesi['tgl_reviu_irban']) : '..........................' ?></span>
    </div>
    <div style="font-size:10pt;line-height:1.6;white-space:pre-wrap"><?php if (!empty($sesi['catatan_reviu_irban'])): ?><?= e($sesi['catatan_reviu_irban']) ?><?php else: ?><div style="min-height:70px;color:#94a3b8;font-style:italic;padding-top:4px">(Catatan / arahan pemeriksaan Irban diinput melalui sistem atau ditulis langsung pada lembar ini)</div><?php endif; ?></div>
    <div style="text-align:right;margin-top:20px">
      <b>Wakil Penanggung Jawab,</b><br><br><br>
      <u><b><?= !empty($namaIrban) ? e($namaIrban) : '..................................................' ?></b></u><br>
      NIP. <?= (!empty($nipIrban) && $nipIrban !== '-') ? e(format_nip($nipIrban)) : '..................................................' ?>
    </div>
  </div>

  <!-- KOTAK CATATAN INSPEKTUR -->
  <div style="border:1px solid #000;padding:12px;min-height:160px">
    <div style="display:flex;justify-content:space-between;border-bottom:1px dashed #666;padding-bottom:6px;margin-bottom:8px">
      <b style="font-size:10.5pt">C. Arahan / Disposisi Inspektur Daerah (Penanggung Jawab)</b>
      <span style="font-size:9.5pt">Tanggal: <?= ($sesi['status'] === 'SELESAI_FINAL') ? tgl_id($sesi['updated_at']) : '..........................' ?></span>
    </div>
    <div style="font-size:10pt;line-height:1.5;white-space:pre-wrap">Disetujui untuk diterbitkan LHA resmi dan diteruskan ke Bagian Perencanaan untuk penomoran, penggandaan, dan penginputan ke SIM HP.</div>
    <div style="text-align:right;margin-top:20px">
      <b>Inspektur Daerah Kabupaten Rokan Hilir,</b><br><br><br>
      <u><b><?= e($namaInspektur) ?></b></u><br>
      NIP. <?= e(format_nip($nipInspektur)) ?>
    </div>
  </div>
</div>

</body>
</html>

