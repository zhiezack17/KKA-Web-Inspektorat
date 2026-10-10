<?php
/**
 * Dokumen Resmi Berita Acara Kesepakatan Temuan
 * Standar Resmi Inspektorat Kabupaten Rokan Hilir - Format Baku Lapangan
 */
$tglSekarang = tgl_id(date('Y-m-d'));
$hariIni     = nama_hari_id(date('Y-m-d'));
$tglHari     = (int)date('d');
$tahunAngka  = (int)date('Y');

$stNhp = $spt['status_nhp'] ?? 'DRAFT';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Berita Acara Kesepakatan Temuan - Kepenghuluan <?= e($desa['nama']) ?></title>
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
      margin: 14px 0 16px;
    }
    .judul-doc h3 {
      margin: 0;
      font-size: 12pt;
      text-transform: uppercase;
      font-weight: 800;
      letter-spacing: 0.5px;
      text-decoration: underline;
    }
    .judul-doc h4 {
      margin: 3px 0 0;
      font-size: 10.5pt;
      text-transform: uppercase;
      font-weight: 700;
    }

    .paragraf {
      text-align: justify;
      text-indent: 32px;
      margin-bottom: 10px;
      line-height: 1.5;
    }

    .pihak-table {
      width: 100%;
      border-collapse: collapse;
      margin: 4px 0 10px;
      font-size: 10pt;
    }
    .pihak-table td {
      padding: 1.5px 3px;
      vertical-align: top;
    }

    /* TANDA TANGAN BERSAMA */
    .ttd-box {
      width: 100%;
      margin-top: 20px;
      page-break-inside: avoid;
    }
    .ttd-table {
      width: 100%;
      border-collapse: collapse;
    }
    .ttd-table td {
      width: 50%;
      vertical-align: top;
      font-size: 9.5pt;
      padding: 6px 12px;
    }

    /* PENGESAHAN INSPEKTUR BARCODE */
    .tte-box {
      border: 1.5px solid #047857;
      background: #f0fdf4;
      padding: 8px 12px;
      margin: 14px 0 10px;
      border-radius: 4px;
      page-break-inside: avoid;
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
    ⚙️ Cetak Berita Acara Kesepakatan
  </div>
  <form method="get" action="<?= url('print/ba-kesepakatan') ?>">
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
    <a href="<?= url('print/p2hp?desa_id=' . $desa['id'] . '&tahun=' . $tahun . (!empty($pjPenghulu) ? '&penghulu=' . urlencode($pjPenghulu) : '') . (!empty($sekdes) ? '&sekdes=' . urlencode($sekdes) : '') . (!empty($kaurKeuangan) ? '&kaur=' . urlencode($kaurKeuangan) : '')) ?>" target="_blank" style="font-size:11px;color:#0284c7;text-decoration:none;font-weight:bold;display:block">
      📑 Buka Pokok-Pokok Temuan (P2HP) &raquo;
    </a>
  </div>
</div>

<!-- KOP RESMI PEMKAB ROHIL - INSPEKTORAT DAERAH -->
<div class="kop">
  <h1>PEMERINTAH KABUPATEN ROKAN HILIR</h1>
  <h2>INSPEKTORAT DAERAH</h2>
  <p>Komplek Perkantoran Batu Enam, Bagansiapiapi - Riau<br>Email: inspektorat@rohilkab.go.id | Website: www.rohilkab.go.id</p>
</div>

<!-- JUDUL BERITA ACARA -->
<div class="judul-doc">
  <h3>BERITA ACARA KESEPAKATAN TEMUAN</h3>
  <h4>ANTARA INSPEKTORAT DAN KEPENGHULUAN <?= strtoupper(e($desa['nama'])) ?></h4>
  <h4>KECAMATAN <?= strtoupper(e($desa['kecamatan_nama'])) ?> KABUPATEN ROKAN HILIR</h4>
</div>

<p class="paragraf">
  Pada hari ini <b><?= $hariIni ?></b> Tanggal <b><?= $tglHari ?> (<?= terbilang_angka($tglHari) ?>)</b> bulan <b><?= date('F') === 'October' ? 'Oktober' : tgl_id(date('Y-m-d')) ?></b> bertempat di Kantor Inspektorat Kabupaten Rokan Hilir, kami atas nama Tim Audit sebagai berikut:
</p>

<!-- DAFTAR PIHAK PERTAMA (TIM AUDIT) -->
<table class="pihak-table">
  <tr>
    <td style="width:20px">1.</td>
    <td style="width:110px">Nama</td>
    <td style="width:12px">:</td>
    <td><b><?= e($spt['wakil_pj_nama'] ?: ($irbanUser['nama'] ?? 'Inspektur Pembantu')) ?></b></td>
  </tr>
  <tr>
    <td></td>
    <td>NIP</td>
    <td>:</td>
    <td><?= e(format_nip($irbanUser['nip'] ?? '-')) ?></td>
  </tr>
  <tr>
    <td></td>
    <td>Jabatan</td>
    <td>:</td>
    <td>Wakil Penanggungjawab</td>
  </tr>

  <tr><td colspan="4" style="height:4px"></td></tr>
  <tr>
    <td>2.</td>
    <td>Nama</td>
    <td>:</td>
    <td><b><?= e($spt['dalnis_nama'] ?: ($dalnisUser['nama'] ?? 'Pengendali Teknis')) ?></b></td>
  </tr>
  <tr>
    <td></td>
    <td>NIP</td>
    <td>:</td>
    <td><?= e(format_nip($dalnisUser['nip'] ?? '-')) ?></td>
  </tr>
  <tr>
    <td></td>
    <td>Jabatan</td>
    <td>:</td>
    <td>Pengendali Teknis</td>
  </tr>

  <tr><td colspan="4" style="height:4px"></td></tr>
  <tr>
    <td>3.</td>
    <td>Nama</td>
    <td>:</td>
    <td><b><?= e($spt['ketua_tim_nama'] ?: ($ketuaUser['nama'] ?? 'Ketua Tim')) ?></b></td>
  </tr>
  <tr>
    <td></td>
    <td>Pangkat / Gol</td>
    <td>:</td>
    <td><?= e($ketuaUser['pangkat'] ?? 'Penata Tk. I / III.d') ?></td>
  </tr>
  <tr>
    <td></td>
    <td>NIP</td>
    <td>:</td>
    <td><?= e(format_nip($ketuaUser['nip'] ?? '-')) ?></td>
  </tr>
  <tr>
    <td></td>
    <td>Jabatan</td>
    <td>:</td>
    <td>Ketua Tim</td>
  </tr>

  <?php if (!empty($anggotaList)): ?>
    <?php $noAg = 4; foreach ($anggotaList as $ag): ?>
      <tr><td colspan="4" style="height:4px"></td></tr>
      <tr>
        <td><?= $noAg++ ?>.</td>
        <td>Nama</td>
        <td>:</td>
        <td><b><?= e($ag['nama']) ?></b></td>
      </tr>
      <tr>
        <td></td>
        <td>NIP</td>
        <td>:</td>
        <td><?= e(format_nip($ag['nip'] ?? '-')) ?></td>
      </tr>
      <tr>
        <td></td>
        <td>Jabatan</td>
        <td>:</td>
        <td>Anggota Tim</td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="4" style="height:4px"></td></tr>
    <tr>
      <td>4.</td>
      <td>Nama / Jabatan</td>
      <td>:</td>
      <td>Anggota Tim Pemeriksa</td>
    </tr>
  <?php endif; ?>
</table>

<p style="margin:2px 0 10px">
  Sebagai <b>&ldquo;Pihak Kesatu&rdquo;</b>.
</p>

<p style="margin:6px 0 4px">
  Kemudian selanjutnya:
</p>

<!-- DAFTAR PIHAK KEDUA (PEMERINTAH KEPENGHULUAN) -->
<table class="pihak-table">
  <tr>
    <td style="width:20px">1.</td>
    <td style="width:110px">Nama</td>
    <td style="width:12px">:</td>
    <td><b><?= e($pjPenghulu ?: '..................................................................') ?></b></td>
  </tr>
  <tr>
    <td></td>
    <td>Jabatan</td>
    <td>:</td>
    <td>Pj. Penghulu <?= e($desa['nama']) ?></td>
  </tr>

  <tr><td colspan="4" style="height:4px"></td></tr>
  <tr>
    <td>2.</td>
    <td>Nama</td>
    <td>:</td>
    <td><b><?= e($sekdes ?: '..................................................................') ?></b></td>
  </tr>
  <tr>
    <td></td>
    <td>Jabatan</td>
    <td>:</td>
    <td>Sekretaris Kepenghuluan</td>
  </tr>

  <tr><td colspan="4" style="height:4px"></td></tr>
  <tr>
    <td>3.</td>
    <td>Nama</td>
    <td>:</td>
    <td><b><?= e($kaurKeuangan ?: '..................................................................') ?></b></td>
  </tr>
  <tr>
    <td></td>
    <td>Jabatan</td>
    <td>:</td>
    <td>Kaur Keuangan</td>
  </tr>
</table>

<p class="paragraf" style="margin-top:6px">
  Mewakili Pemerintah Kepenghuluan <b><?= e($desa['nama']) ?></b> Kecamatan <b><?= e($desa['kecamatan_nama']) ?></b> Kabupaten Rokan Hilir selanjutnya disebut <b>&ldquo;Pihak Kedua&rdquo;</b>.
</p>

<!-- KESEPAKATAN INTI -->
<p class="paragraf">
  Kedua belah pihak telah mengadakan pembahasan bersama mengenai hasil audit, dengan pokok permasalahan sebagaimana terlampir dalam <b>Pokok-Pokok Hasil Pemeriksaan (P2HP)</b>.
</p>

<p class="paragraf">
  Kemudian pihak kedua sepakat untuk menindaklanjuti temuan hasil audit selambat-lambatnya <b>60 (enam puluh) hari</b> terhitung sejak diterima Laporan Hasil Audit (LHA) / Laporan Hasil Pengawasan (LHP).
</p>

<?php if ($stNhp === 'DISETUJUI_EKSPOSE'): ?>
  <!-- KOTAK TTE DIGITAL VERIFIKASI INSPEKTUR -->
  <div class="tte-box">
    <table style="width:100%;border-collapse:collapse">
      <tr>
        <td style="width:65px;vertical-align:middle;text-align:center">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=65x65&data=<?= urlencode('VERIFIKASI-BA-KESEPAKATAN:' . ($spt['tte_barcode_nhp'] ?? '') . ':' . $desa['nama'] . ':' . $tahun) ?>" alt="QR TTE" style="width:65px;height:65px;border:1px solid #047857;padding:2px;background:#fff;display:block">
        </td>
        <td style="padding-left:12px;vertical-align:middle;font-size:8.5pt;color:#064e3b">
          <div style="font-weight:800;font-size:9pt;text-transform:uppercase;color:#047857">
            TELAAH PRA-EKSPOSE INSPEKTUR DAERAH DISAHKAN
          </div>
          <div>Dokumen Berita Acara Kesepakatan Temuan ini sah diterbitkan berdasarkan Surat Tugas No. <?= e($spt['no_spt'] ?: 'DRAFT') ?>.</div>
          <div style="font-size:8pt;margin-top:2px">
            <b>Kode Otorisasi:</b> <code style="font-family:monospace;font-weight:bold"><?= e($spt['tte_barcode_nhp'] ?? '-') ?></code> &bull; Tanggal: <?= !empty($spt['tgl_disetujui_nhp']) ? tgl_id($spt['tgl_disetujui_nhp']) : '-' ?>
          </div>
        </td>
      </tr>
    </table>
  </div>
<?php endif; ?>

<!-- TANDA TANGAN BERSAMA -->
<div class="ttd-box">
  <div style="text-align:right;margin-bottom:6px;font-size:10pt">
    Bagansiapiapi, <?= $tglSekarang ?>
  </div>
  <table class="ttd-table">
    <tr>
      <td style="text-align:center">
        <b>Pihak Kedua</b><br>
        Pemerintah Kepenghuluan <?= e($desa['nama']) ?><br><br>
        
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
        <b>Pihak Pertama</b><br>
        Tim Audit Inspektorat Daerah<br><br>

        <table style="width:100%;font-size:9pt;border-collapse:collapse;text-align:left">
          <tr>
            <td style="width:16px;vertical-align:top">1.</td>
            <td style="vertical-align:top">
              <b><?= e($spt['wakil_pj_nama'] ?: ($irbanUser['nama'] ?? 'Inspektur Pembantu')) ?></b><br>
              NIP. <?= e(format_nip($irbanUser['nip'] ?? '-')) ?>
            </td>
            <td style="width:70px;text-align:right;vertical-align:bottom">.................</td>
          </tr>
          <tr><td colspan="3" style="height:14px"></td></tr>
          <tr>
            <td style="vertical-align:top">2.</td>
            <td style="vertical-align:top">
              <b><?= e($spt['dalnis_nama'] ?: ($dalnisUser['nama'] ?? 'Pengendali Teknis')) ?></b><br>
              NIP. <?= e(format_nip($dalnisUser['nip'] ?? '-')) ?>
            </td>
            <td style="text-align:right;vertical-align:bottom">.................</td>
          </tr>
          <tr><td colspan="3" style="height:14px"></td></tr>
          <tr>
            <td style="vertical-align:top">3.</td>
            <td style="vertical-align:top">
              <b><?= e($spt['ketua_tim_nama'] ?: ($ketuaUser['nama'] ?? 'Ketua Tim')) ?></b><br>
              NIP. <?= e(format_nip($ketuaUser['nip'] ?? '-')) ?>
            </td>
            <td style="text-align:right;vertical-align:bottom">.................</td>
          </tr>
          <?php if (!empty($anggotaList)): ?>
            <?php $noAg = 4; foreach ($anggotaList as $ag): ?>
              <tr><td colspan="3" style="height:14px"></td></tr>
              <tr>
                <td style="vertical-align:top"><?= $noAg++ ?>.</td>
                <td style="vertical-align:top">
                  <b><?= e($ag['nama']) ?></b><br>
                  NIP. <?= e(format_nip($ag['nip'] ?? '-')) ?>
                </td>
                <td style="text-align:right;vertical-align:bottom">.................</td>
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
