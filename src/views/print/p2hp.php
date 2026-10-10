<?php
/**
 * Dokumen Resmi Pokok-Pokok Hasil Pemeriksaan (P2HP)
 * Standar Resmi Inspektorat Kabupaten Rokan Hilir - Sesuai Format Baku Lapangan
 */
$totalTemuan = 0;
foreach ($daftarTemuan as $t) {
    $totalTemuan += (float)$t['nominal'];
}

$stNhp = $spt['status_nhp'] ?? 'DRAFT';
$tglSekarang = tgl_id(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>P2HP - Kepenghuluan <?= e($desa['nama']) ?> TA <?= $tahun ?></title>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 20mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Bookman Old Style", Georgia, "Times New Roman", serif;
      font-size: 10.5pt;
      line-height: 1.45;
      color: #000;
      margin: 0;
      padding: 0;
      background: #fff;
    }

    /* KOP RESMI */
    .kop {
      text-align: center;
      border-bottom: 3px double #000;
      padding-bottom: 8px;
      margin-bottom: 16px;
      position: relative;
    }
    .kop h1 { margin: 0; font-size: 13.5pt; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
    .kop h2 { margin: 2px 0 0; font-size: 12pt; text-transform: uppercase; font-weight: 700; }
    .kop p { margin: 3px 0 0; font-size: 8.5pt; font-family: Arial, sans-serif; line-height: 1.3; }

    /* JUDUL DOKUMEN */
    .judul-doc {
      text-align: center;
      margin: 16px 0 20px;
    }
    .judul-doc h3 {
      margin: 0;
      font-size: 12.5pt;
      text-transform: uppercase;
      font-weight: 800;
      letter-spacing: 0.5px;
    }
    .judul-doc h4 {
      margin: 3px 0 0;
      font-size: 11pt;
      text-transform: uppercase;
      font-weight: 700;
    }
    .judul-doc .sub {
      margin-top: 4px;
      font-size: 10pt;
      font-weight: bold;
    }

    .section-title {
      font-size: 11pt;
      font-weight: 800;
      text-transform: uppercase;
      margin: 18px 0 8px;
      border-bottom: 1px solid #000;
      padding-bottom: 3px;
    }

    .paragraf {
      text-align: justify;
      text-indent: 32px;
      margin-bottom: 10px;
      line-height: 1.5;
    }

    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0 14px;
      font-size: 10pt;
    }
    .meta-table td {
      padding: 3px 4px;
      vertical-align: top;
    }

    /* ITEM TEMUAN 5 UNSUR */
    .finding-card {
      margin-bottom: 22px;
      page-break-inside: avoid;
    }
    .finding-header {
      font-size: 11pt;
      font-weight: 800;
      margin-bottom: 6px;
      color: #000;
    }
    .finding-nominal {
      font-size: 10.5pt;
      font-weight: 800;
      color: #b91c1c;
      margin-bottom: 6px;
    }
    .finding-block {
      margin-bottom: 8px;
      text-align: justify;
    }
    .finding-block strong {
      display: inline-block;
      min-width: 110px;
    }

    /* RUANG TULISAN TANGAN AUDITI */
    .tanggapan-box {
      margin: 10px 0 16px;
      border: 1px dashed #64748b;
      padding: 10px 14px;
      background: #fafafa;
      border-radius: 4px;
    }
    .tanggapan-title {
      font-weight: 700;
      font-size: 9.5pt;
      margin-bottom: 6px;
    }
    .line-blank {
      border-bottom: 1px dotted #555;
      height: 22px;
      margin-bottom: 4px;
    }

    /* PENGESAHAN INSPEKTUR BARCODE */
    .tte-box {
      border: 2px solid #047857;
      background: #f0fdf4;
      padding: 10px 14px;
      margin: 18px 0 14px;
      border-radius: 4px;
      page-break-inside: avoid;
    }

    /* TANDA TANGAN BERSAMA */
    .ttd-box {
      width: 100%;
      margin-top: 24px;
      page-break-inside: avoid;
    }
    .ttd-table {
      width: 100%;
      border-collapse: collapse;
    }
    .ttd-table td {
      width: 50%;
      vertical-align: top;
      font-size: 10pt;
      padding: 6px 14px;
    }

    /* FLOATING TOOLBAR */
    .no-print {
      position: fixed;
      top: 12px;
      right: 12px;
      background: #fff;
      border: 1px solid #cbd5e1;
      padding: 12px 16px;
      border-radius: 8px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.18);
      font-family: Arial, sans-serif;
      z-index: 9999;
      max-width: 320px;
    }
    .no-print input {
      width: 100%;
      padding: 4px 8px;
      font-size: 11px;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      margin-bottom: 6px;
    }
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
    }
  </style>
</head>
<body>

<!-- FLOATING TOOLBAR OPSIONAL UNTUK NAMA PIHAK KEPENGHULUAN -->
<div class="no-print">
  <div style="font-weight:bold;font-size:12px;margin-bottom:8px;color:#0f172a">
    ⚙️ Pengaturan Cetak P2HP &amp; BA
  </div>
  <form method="get" action="<?= url('print/p2hp') ?>">
    <input type="hidden" name="desa_id" value="<?= $desa['id'] ?>">
    <input type="hidden" name="tahun" value="<?= $tahun ?>">
    <label style="font-size:10px;font-weight:bold;color:#475569">Pj. Penghulu:</label>
    <input type="text" name="penghulu" value="<?= e($pjPenghulu ?? '') ?>" placeholder="Nama Pj. Penghulu">
    <label style="font-size:10px;font-weight:bold;color:#475569">Sekretaris Kepenghuluan:</label>
    <input type="text" name="sekdes" value="<?= e($sekdes ?? '') ?>" placeholder="Nama Sekretaris">
    <label style="font-size:10px;font-weight:bold;color:#475569">Kaur Keuangan:</label>
    <input type="text" name="kaur" value="<?= e($kaurKeuangan ?? '') ?>" placeholder="Nama Kaur Keuangan">
    <div style="display:flex;gap:6px;margin-top:6px">
      <button type="submit" style="background:#0284c7;color:#fff;border:none;padding:5px 10px;border-radius:4px;font-size:11px;cursor:pointer;font-weight:bold">Terapkan</button>
      <button type="button" onclick="window.print()" style="background:#16a34a;color:#fff;border:none;padding:5px 12px;border-radius:4px;font-size:11px;cursor:pointer;font-weight:bold">🖨️ Cetak</button>
      <button type="button" onclick="window.close()" style="background:#64748b;color:#fff;border:none;padding:5px 8px;border-radius:4px;font-size:11px;cursor:pointer">Tutup</button>
    </div>
  </form>
  <div style="margin-top:10px;border-top:1px solid #e2e8f0;padding-top:8px">
    <a href="<?= url('print/ba-kesepakatan?desa_id=' . $desa['id'] . '&tahun=' . $tahun . (!empty($pjPenghulu) ? '&penghulu=' . urlencode($pjPenghulu) : '') . (!empty($sekdes) ? '&sekdes=' . urlencode($sekdes) : '') . (!empty($kaurKeuangan) ? '&kaur=' . urlencode($kaurKeuangan) : '')) ?>" target="_blank" style="font-size:11px;color:#0284c7;text-decoration:none;font-weight:bold;display:block">
      📄 Buka Berita Acara Kesepakatan &raquo;
    </a>
  </div>
</div>

<!-- KOP SURAT RESMI PEMKAB ROHIL - INSPEKTORAT DAERAH -->
<div class="kop">
  <h1>PEMERINTAH KABUPATEN ROKAN HILIR</h1>
  <h2>INSPEKTORAT DAERAH</h2>
  <p>Komplek Perkantoran Batu Enam, Bagansiapiapi - Riau<br>Email: inspektorat@rohilkab.go.id | Website: www.rohilkab.go.id</p>
</div>

<!-- JUDUL UTAMA DOKUMEN -->
<div class="judul-doc">
  <h3>POKOK-POKOK HASIL PEMERIKSAAN (P2HP)</h3>
  <h4>INSPEKTORAT KABUPATEN ROKAN HILIR</h4>
  <h4>PADA KANTOR KEPENGHULUAN <?= strtoupper(e($desa['nama'])) ?></h4>
  <div class="sub">KECAMATAN <?= strtoupper(e($desa['kecamatan_nama'])) ?> &bull; TAHUN ANGGARAN <?= $tahun ?></div>
</div>

<?php if ($stNhp !== 'DISETUJUI_EKSPOSE'): ?>
  <div style="border: 1.5px dashed #dc2626; background: #fef2f2; padding: 8px 12px; margin-bottom: 16px; text-align: center; color: #b91c1c; font-size: 8.5pt; font-family: Arial, sans-serif;">
    <b>⚠️ LEMBAR KONSEP / DRAFT P2HP — BELUM DISAHKAN INSPEKTUR DAERAH</b><br>
    Naskah ini merupakan konsep telaah internal. Wajib mendapatkan telaah &amp; persetujuan pra-ekspose Inspektur Daerah sebelum dipaparkan pada forum ekspose resmi.
  </div>
<?php endif; ?>

<!-- I. PENDAHULUAN -->
<div class="section-title">I. PENDAHULUAN</div>
<p class="paragraf">
  Berdasarkan Surat Perintah Tugas Inspektur Daerah Kabupaten Rokan Hilir Nomor <b><?= e($spt['no_spt'] ?? '700.1.2.1/SPT/ITKAB-DESA/' . $tahun) ?></b> tanggal <b><?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : $tglSekarang ?></b>, telah dilakukan Pemeriksaan Ketaatan / Audit Dengan Tujuan Tertentu Terhadap Pertanggungjawaban Pelaksanaan APBKep pada Kepenghuluan <b><?= e($desa['nama']) ?></b> Tahun Anggaran <b><?= $tahun ?></b> dengan rincian sebagai berikut:
</p>

<table class="meta-table">
  <tr>
    <td style="width:160px"><b>Objek Audit</b></td>
    <td style="width:15px">:</td>
    <td>Kepenghuluan <?= e($desa['nama']) ?> Kecamatan <?= e($desa['kecamatan_nama']) ?>.</td>
  </tr>
  <tr>
    <td><b>Tujuan Audit</b></td>
    <td>:</td>
    <td>Untuk memastikan pertanggungjawaban belanja atas penggunaan Keuangan Kepenghuluan telah dilakukan secara akuntabel, tertib bukti, dan mengikuti ketentuan peraturan perundang-undangan yang berlaku.</td>
  </tr>
  <tr>
    <td><b>Periode Yang Diaudit</b></td>
    <td>:</td>
    <td>Tahun Anggaran <?= $tahun ?> (Januari s.d. Desember).</td>
  </tr>
  <tr>
    <td><b>Tim Audit APIP</b></td>
    <td>:</td>
    <td>
      <div style="margin-bottom:2px">1. <b><?= e($spt['wakil_pj_nama'] ?: ($irbanUser['nama'] ?? 'Inspektur Pembantu')) ?></b> / NIP. <?= e(format_nip($irbanUser['nip'] ?? '-')) ?> <i>(Wakil Penanggungjawab)</i></div>
      <div style="margin-bottom:2px">2. <b><?= e($spt['dalnis_nama'] ?: ($dalnisUser['nama'] ?? 'Pengendali Teknis')) ?></b> / NIP. <?= e(format_nip($dalnisUser['nip'] ?? '-')) ?> <i>(Pengendali Teknis)</i></div>
      <div style="margin-bottom:2px">3. <b><?= e($spt['ketua_tim_nama'] ?: ($ketuaUser['nama'] ?? 'Ketua Tim')) ?></b> / NIP. <?= e(format_nip($ketuaUser['nip'] ?? '-')) ?> <i>(Ketua Tim)</i></div>
      <?php if (!empty($anggotaList)): ?>
        <?php $noAg = 4; foreach ($anggotaList as $ag): ?>
          <div style="margin-bottom:2px"><?= $noAg++ ?>. <b><?= e($ag['nama']) ?></b> / NIP. <?= e(format_nip($ag['nip'] ?? '-')) ?> <i>(Anggota Tim)</i></div>
        <?php endforeach; ?>
      <?php else: ?>
        <div>4. Anggota Tim Pemeriksa</div>
      <?php endif; ?>
    </td>
  </tr>
</table>

<!-- II. HASIL AUDIT -->
<div class="section-title">II. HASIL AUDIT (POKOK-POKOK TEMUAN)</div>
<p style="margin-bottom:14px">
  Dari hasil audit yang telah dilaksanakan oleh Tim Audit, terdapat beberapa pokok permasalahan dan temuan pemeriksaan yang perlu mendapat perhatian serta tindak lanjut dari Pihak Auditi sebagai berikut:
</p>

<?php if (empty($daftarTemuan)): ?>
  <div style="border:1px solid #cbd5e1;padding:20px;text-align:center;font-style:italic;margin-bottom:20px">
    Tidak ditemukan adanya penyimpangan atau ketidakpatuhan material (Nihil Temuan Pemeriksaan).
  </div>
<?php else: ?>
  <?php $no=1; foreach ($daftarTemuan as $t): ?>
    <div class="finding-card">
      <div class="finding-header">
        <?= $no++ ?>. <?= e($t['judul']) ?>
      </div>
      <?php if ((float)$t['nominal'] > 0): ?>
        <div class="finding-nominal">
          Nilai Temuan / Selisih: Rp <?= number_format((float)$t['nominal'], 0, ',', '.') ?>
        </div>
      <?php endif; ?>

      <!-- 1. KONDISI -->
      <div class="finding-block">
        <b>Kondisi Fakta:</b><br>
        <?= nl2br(e($t['kondisi'])) ?>
      </div>

      <!-- 2. KRITERIA -->
      <?php if (!empty($t['kriteria'])): ?>
        <div class="finding-block">
          <b>Kriteria (Ketentuan):</b><br>
          <?= nl2br(e($t['kriteria'])) ?>
        </div>
      <?php endif; ?>

      <!-- 3. SEBAB -->
      <?php if (!empty($t['sebab'])): ?>
        <div class="finding-block">
          <b>Sebab:</b><br>
          <?= nl2br(e($t['sebab'])) ?>
        </div>
      <?php endif; ?>

      <!-- 4. AKIBAT -->
      <?php if (!empty($t['akibat'])): ?>
        <div class="finding-block">
          <b>Akibat:</b><br>
          <?= nl2br(e($t['akibat'])) ?>
        </div>
      <?php endif; ?>

      <!-- 5. REKOMENDASI -->
      <?php if (!empty($t['rekomendasi'])): ?>
        <div class="finding-block">
          <b>Rekomendasi Tim Audit:</b><br>
          <?= nl2br(e($t['rekomendasi'])) ?>
        </div>
      <?php endif; ?>

      <!-- TANGGAPAN PIHAK AUDITI -->
      <div class="tanggapan-box">
        <div class="tanggapan-title">
          Tanggapan Pj. Penghulu <?= e($desa['nama']) ?>:
        </div>
        <?php if (!empty($t['tanggapan_auditi'])): ?>
          <div style="font-size:9.5pt;font-style:italic;line-height:1.4">
            "<?= nl2br(e($t['tanggapan_auditi'])) ?>"
          </div>
        <?php else: ?>
          <!-- Ruang isian tulisan tangan resmi saat forum ekspose -->
          <div class="line-blank"></div>
          <div class="line-blank"></div>
          <div class="line-blank"></div>
          <div class="line-blank"></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- III. PENUTUP -->
<div class="section-title">III. PENUTUP</div>
<p class="paragraf">
  Demikian Pokok-pokok Hasil Pemeriksaan (P2HP) ini disampaikan untuk dapat menjadi perhatian, ditanggapi, serta ditindaklanjuti sebagaimana mestinya sesuai ketentuan perundang-undangan yang berlaku.
</p>

<?php if ($stNhp === 'DISETUJUI_EKSPOSE'): ?>
  <!-- KOTAK PENGESAHAN TELAAH PRA-EKSPOSE INSPEKTUR DAERAH DENGAN BARCODE TTE -->
  <div class="tte-box">
    <table style="width:100%;border-collapse:collapse">
      <tr>
        <td style="width:78px;vertical-align:middle;text-align:center">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=76x76&data=<?= urlencode('VERIFIKASI-P2HP-KKA:' . ($spt['tte_barcode_nhp'] ?? '') . ':' . $desa['nama'] . ':' . $tahun) ?>" alt="QR TTE" style="width:76px;height:76px;border:1px solid #047857;padding:2px;background:#fff;display:block">
        </td>
        <td style="padding-left:14px;vertical-align:top;font-size:8.5pt;color:#064e3b">
          <div style="font-weight:800;font-size:9.5pt;text-transform:uppercase;color:#047857;letter-spacing:0.3px">
            PENGESAHAN TELAAH PRA-EKSPOSE INSPEKTUR DAERAH
          </div>
          <div style="margin-top:3px;line-height:1.35">
            Naskah Pokok-Pokok Hasil Pemeriksaan (P2HP) ini telah ditelaah dan <b>DISAHKAN</b> oleh Inspektur Daerah Kabupaten Rokan Hilir untuk dipaparkan pada Forum Ekspose Hasil Pemeriksaan bersama Pihak Auditi Kepenghuluan <?= e($desa['nama']) ?>.
          </div>
          <div style="margin-top:4px;font-size:8.5pt">
            <b>Otorisasi Digital:</b> <?= e($spt['disetujui_oleh_nhp'] ?? 'Inspektur Daerah Kab. Rokan Hilir') ?> &bull; 
            <b>Tanggal:</b> <?= !empty($spt['tgl_disetujui_nhp']) ? tgl_id($spt['tgl_disetujui_nhp']) : '-' ?> &bull; 
            <b>Kode TTE:</b> <code style="font-family:monospace;font-weight:bold;background:#dcfce7;padding:1px 4px"><?= e($spt['tte_barcode_nhp'] ?? '-') ?></code>
          </div>
          <?php if (!empty($spt['catatan_inspektur_nhp'])): ?>
            <div style="margin-top:3px;font-style:italic;color:#166534">
              <b>Catatan/Arahan Inspektur:</b> "<?= e($spt['catatan_inspektur_nhp']) ?>"
            </div>
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>
<?php endif; ?>

<!-- LEMBAR TANDA TANGAN BERSAMA -->
<div class="ttd-box">
  <div style="text-align:right;margin-bottom:8px;font-size:10pt">
    Bagansiapiapi, <?= $tglSekarang ?>
  </div>
  <table class="ttd-table">
    <tr>
      <td style="text-align:center">
        <b>Menyetujui :</b><br>
        <b>Pemerintah Kepenghuluan <?= e($desa['nama']) ?></b><br>
        Pj. Penghulu
        <br><br><br><br>
        <b><u>( <?= e($pjPenghulu ?: '....................................................') ?> )</u></b>
        <br><br>
        Sekretaris Kepenghuluan
        <br><br><br><br>
        <b><u>( <?= e($sekdes ?: '....................................................') ?> )</u></b>
        <br><br>
        Kaur Keuangan
        <br><br><br><br>
        <b><u>( <?= e($kaurKeuangan ?: '....................................................') ?> )</u></b>
      </td>
      <td style="text-align:center">
        <b>Tim Audit APIP</b><br>
        <b>Inspektorat Kabupaten Rokan Hilir</b><br><br>
        
        <table style="width:100%;font-size:9pt;border-collapse:collapse;text-align:left">
          <tr>
            <td style="width:20px;vertical-align:top">1.</td>
            <td style="vertical-align:top">
              <b><?= e($spt['wakil_pj_nama'] ?: ($irbanUser['nama'] ?? 'Inspektur Pembantu')) ?></b><br>
              NIP. <?= e(format_nip($irbanUser['nip'] ?? '-')) ?><br>
              <i>Wakil Penanggungjawab</i>
            </td>
            <td style="width:80px;text-align:right;vertical-align:bottom">...................</td>
          </tr>
          <tr><td colspan="3" style="height:12px"></td></tr>
          <tr>
            <td style="vertical-align:top">2.</td>
            <td style="vertical-align:top">
              <b><?= e($spt['dalnis_nama'] ?: ($dalnisUser['nama'] ?? 'Pengendali Teknis')) ?></b><br>
              NIP. <?= e(format_nip($dalnisUser['nip'] ?? '-')) ?><br>
              <i>Pengendali Teknis</i>
            </td>
            <td style="text-align:right;vertical-align:bottom">...................</td>
          </tr>
          <tr><td colspan="3" style="height:12px"></td></tr>
          <tr>
            <td style="vertical-align:top">3.</td>
            <td style="vertical-align:top">
              <b><?= e($spt['ketua_tim_nama'] ?: ($ketuaUser['nama'] ?? 'Ketua Tim')) ?></b><br>
              NIP. <?= e(format_nip($ketuaUser['nip'] ?? '-')) ?><br>
              <i>Ketua Tim</i>
            </td>
            <td style="text-align:right;vertical-align:bottom">...................</td>
          </tr>
          <?php if (!empty($anggotaList)): ?>
            <?php $noAg = 4; foreach ($anggotaList as $ag): ?>
              <tr><td colspan="3" style="height:12px"></td></tr>
              <tr>
                <td style="vertical-align:top"><?= $noAg++ ?>.</td>
                <td style="vertical-align:top">
                  <b><?= e($ag['nama']) ?></b><br>
                  NIP. <?= e(format_nip($ag['nip'] ?? '-')) ?><br>
                  <i>Anggota Tim</i>
                </td>
                <td style="text-align:right;vertical-align:bottom">...................</td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </table>
      </td>
    </tr>
  </table>
</div>

</body>
</html>
