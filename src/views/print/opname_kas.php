<?php
/**
 * Berita Acara Pemeriksaan Kas (Cash Opname) Resmi
 * Inspektorat Daerah Kabupaten Rokan Hilir
 * Standar Naskah Dinas Pengawasan APIP & Kendali Mutu
 * Format: A4 Portrait
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Berita Acara Opname Kas — <?= e($row['desa_nama']) ?></title>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 20mm 15mm 20mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Bookman Old Style", Georgia, "Times New Roman", serif;
      font-size: 10.5pt;
      line-height: 1.4;
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
      padding: 8px 18px;
      border-radius: 6px;
      text-decoration: none;
      font-family: sans-serif;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      border: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 10px 20px;
    }

    /* KOP RESMI */
    .kop {
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 3px double #000;
      padding-bottom: 8px;
      margin-bottom: 14px;
    }
    .kop-logo { width: 70px; text-align: center; }
    .kop-logo img { width: 68px; height: auto; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h3 { margin: 0; font-size: 11pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
    .kop-text h2 { margin: 2px 0; font-size: 14pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .kop-text p { margin: 0; font-size: 8.5pt; line-height: 1.25; font-family: Arial, sans-serif; }

    .doc-title {
      text-align: center;
      font-size: 12pt;
      font-weight: bold;
      text-decoration: underline;
      text-transform: uppercase;
      margin-top: 14px;
      margin-bottom: 2px;
    }
    .doc-number {
      text-align: center;
      font-size: 10.5pt;
      margin-bottom: 16px;
    }

    .narrative {
      text-align: justify;
      margin-bottom: 10px;
      font-size: 10.5pt;
    }

    /* TABEL PECAHAN */
    table.table-bap {
      width: 100%;
      border-collapse: collapse;
      margin: 10px 0 14px;
      font-size: 9.5pt;
    }
    table.table-bap th, table.table-bap td {
      border: 1px solid #000;
      padding: 4px 6px;
      vertical-align: middle;
    }
    table.table-bap th {
      background: #f1f1f1;
      font-weight: bold;
      text-align: center;
    }
    .num { text-align: right; white-space: nowrap; }
    .center { text-align: center; }
    .bold { font-weight: bold; }

    @media print {
      .no-print { display: none !important; }
      body { font-size: 10pt; line-height: 1.35; }
      .container { padding: 0; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <div>
      <b style="font-family:sans-serif;color:#0f172a">Berita Acara Pemeriksaan Kas (Opname Kas Desa)</b>
      <div style="font-size:12px;color:#64748b;font-family:sans-serif">Format Standar Kendali Mutu APIP &bull; Inspektorat Kab. Rokan Hilir</div>
    </div>
    <div style="display:flex;gap:8px">
      <button class="btn-print" onclick="window.print()">
        🖨️ Cetak / Ekspor PDF (A4)
      </button>
      <button class="btn-print" style="background:#475569" onclick="window.close()">
        Tutup
      </button>
    </div>
  </div>

  <div class="container">
    <!-- KOP SURAT -->
    <div class="kop">
      <div class="kop-logo">
        <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rohil">
      </div>
      <div class="kop-text">
        <h3>PEMERINTAH KABUPATEN ROKAN HILIR</h3>
        <h2>INSPEKTORAT DAERAH</h2>
        <p>Jalan Komplek Perkantoran Bagansiapiapi, Kabupaten Rokan Hilir, Riau 28912</p>
        <p>Website: inspektorat.rohilkab.go.id &bull; Email: inspektorat@rohilkab.go.id</p>
      </div>
    </div>

    <!-- JUDUL -->
    <div class="doc-title">BERITA ACARA PEMERIKSAAN KAS (CASH OPNAME)</div>
    <div class="doc-number">Nomor : <?= e($row['no_bap']) ?></div>

    <!-- PEMBUKA NARRATIVE -->
    <div class="narrative">
      Pada hari ini, tanggal <b><?= tgl_id($row['tgl_pemeriksaan']) ?></b> sekitar pukul <b><?= e($row['waktu_pemeriksaan']) ?></b> bertempat di <b><?= e($row['tempat_pemeriksaan']) ?></b>, kami yang bertanda tangan di bawah ini Tim Pemeriksa Inspektorat Daerah Kabupaten Rokan Hilir berdasarkan Surat Perintah Tugas Inspektur Nomor: <b><?= e($row['no_spt'] ?? '700/SPT-INSP/' . $row['tahun_anggaran'] . '/...') ?></b> tanggal <b><?= !empty($row['tgl_spt']) ? tgl_id($row['tgl_spt']) : '..............' ?></b>:
    </div>

    <table style="width:100%;font-size:10.5pt;margin-bottom:10px;border-collapse:collapse">
      <tr>
        <td style="width:28px;vertical-align:top">1.</td>
        <td style="width:180px;vertical-align:top">Nama / NIP Ketua Tim</td>
        <td style="width:10px;vertical-align:top">:</td>
        <td style="vertical-align:top"><b><?= e($row['nama_ketua_tim']) ?></b> <?= !empty($row['nip_ketua_tim']) ? ' / NIP. ' . e($row['nip_ketua_tim']) : '' ?></td>
      </tr>
      <tr>
        <td style="vertical-align:top">2.</td>
        <td style="vertical-align:top">Jabatan</td>
        <td style="vertical-align:top">:</td>
        <td style="vertical-align:top">Ketua Tim Pemeriksa Inspektorat Kab. Rokan Hilir</td>
      </tr>
    </table>

    <div class="narrative">
      telah melakukan pemeriksaan setempat atas keadaan kas (uang tunai dan saldo bank) yang dikelola oleh:
    </div>

    <table style="width:100%;font-size:10.5pt;margin-bottom:12px;border-collapse:collapse">
      <tr>
        <td style="width:28px;vertical-align:top">1.</td>
        <td style="width:180px;vertical-align:top">Nama Bendahara</td>
        <td style="width:10px;vertical-align:top">:</td>
        <td style="vertical-align:top"><b><?= e($row['nama_bendahara']) ?></b> <?= !empty($row['nip_bendahara']) ? ' / NIP. ' . e($row['nip_bendahara']) : '' ?></td>
      </tr>
      <tr>
        <td style="vertical-align:top">2.</td>
        <td style="vertical-align:top">Kepenghuluan / Desa</td>
        <td style="vertical-align:top">:</td>
        <td style="vertical-align:top"><b><?= e($row['desa_nama']) ?></b>, Kecamatan <?= e($row['kecamatan_nama']) ?></td>
      </tr>
      <tr>
        <td style="vertical-align:top">3.</td>
        <td style="vertical-align:top">Pj. Penghulu / Penghulu</td>
        <td style="vertical-align:top">:</td>
        <td style="vertical-align:top"><b><?= e($row['nama_kepala_desa']) ?></b></td>
      </tr>
    </table>

    <div class="narrative">
      Hasil pengujian fisik uang tunai dalam brankas/tempat penyimpanan dan pencocokan saldo rekening kas desa adalah sebagai berikut:
    </div>

    <!-- TABEL DUA KOLOM: UANG KERTAS & UANG LOGAM -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:8px">
      <tr>
        <!-- Sisi Kiri: Uang Kertas -->
        <td style="width:52%;vertical-align:top;padding-right:6px">
          <table class="table-bap">
            <thead>
              <tr>
                <th colspan="3">A.1. UANG KERTAS</th>
              </tr>
              <tr>
                <th>Pecahan</th>
                <th style="width:60px">Lembar</th>
                <th>Subtotal (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $kertasPecahan = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
              foreach ($kertasPecahan as $p): 
                $qty = (int)($rincianKertas[$p]['lembar'] ?? 0);
                $sub = (float)($rincianKertas[$p]['subtotal'] ?? ($qty * $p));
              ?>
                <tr>
                  <td>Rp <?= number_format($p, 0, ',', '.') ?></td>
                  <td class="center"><?= number_format($qty, 0, ',', '.') ?></td>
                  <td class="num"><?= number_format($sub, 0, ',', '.') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background:#f1f1f1;font-weight:bold">
                <td colspan="2">Jumlah Uang Kertas</td>
                <td class="num"><?= number_format((float)$row['total_kertas'], 0, ',', '.') ?></td>
              </tr>
            </tfoot>
          </table>
        </td>

        <!-- Sisi Kanan: Uang Logam & Ringkasan Fisik -->
        <td style="width:48%;vertical-align:top;padding-left:6px">
          <table class="table-bap">
            <thead>
              <tr>
                <th colspan="3">A.2. UANG LOGAM (KOIN)</th>
              </tr>
              <tr>
                <th>Pecahan</th>
                <th style="width:60px">Keping</th>
                <th>Subtotal (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $logamPecahan = [1000, 500, 200, 100];
              foreach ($logamPecahan as $p): 
                $qty = (int)($rincianLogam[$p]['keping'] ?? 0);
                $sub = (float)($rincianLogam[$p]['subtotal'] ?? ($qty * $p));
              ?>
                <tr>
                  <td>Rp <?= number_format($p, 0, ',', '.') ?></td>
                  <td class="center"><?= number_format($qty, 0, ',', '.') ?></td>
                  <td class="num"><?= number_format($sub, 0, ',', '.') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background:#f1f1f1;font-weight:bold">
                <td colspan="2">Jumlah Uang Logam</td>
                <td class="num"><?= number_format((float)$row['total_logam'], 0, ',', '.') ?></td>
              </tr>
              <tr style="background:#e5e7eb;font-weight:bold">
                <td colspan="2">TOTAL KAS FISIK (A.1 + A.2)</td>
                <td class="num"><?= number_format((float)$row['total_kas_fisik'], 0, ',', '.') ?></td>
              </tr>
            </tfoot>
          </table>

          <div style="font-size:8.5pt;font-style:italic;margin-top:-6px;line-height:1.2">
            <i>Terbilang Kas Fisik: <?= e($terbilangKasFisik) ?></i>
          </div>
        </td>
      </tr>
    </table>

    <!-- REKONSILIASI KAS RIIL DENGAN BUKU KAS UMUM (BKU) -->
    <table class="table-bap" style="margin-top:4px">
      <thead>
        <tr>
          <th colspan="3" style="text-align:left;padding-left:10px">B. REKONSILIASI KAS RIIL DENGAN BUKU KAS UMUM (BKU)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="width:30px;text-align:center">1.</td>
          <td>Jumlah Uang Tunai di Brankas (Kas Fisik)</td>
          <td class="num" style="width:160px"><?= rupiah($row['total_kas_fisik']) ?></td>
        </tr>
        <tr>
          <td style="text-align:center">2.</td>
          <td>
            Saldo Rekening Kas Desa pada <b><?= e($row['nama_bank'] ?? 'Bank Riau Kepri Syariah') ?></b>
            <?php if (!empty($row['no_rekening_bank'])): ?>
              <br><span style="font-size:8.5pt;color:#333">(No. Rekening: <?= e($row['no_rekening_bank']) ?>)</span>
            <?php endif; ?>
          </td>
          <td class="num"><?= rupiah($row['saldo_bank']) ?></td>
        </tr>
        <tr style="font-weight:bold;background:#f8fafc">
          <td style="text-align:center">3.</td>
          <td>JUMLAH KAS RIIL DESA (1 + 2)</td>
          <td class="num"><?= rupiah($row['total_kas_riil']) ?></td>
        </tr>
        <tr>
          <td style="text-align:center">4.</td>
          <td>Saldo menurut Buku Kas Umum (BKU) pada tanggal pemeriksaan</td>
          <td class="num"><?= rupiah($row['saldo_bku']) ?></td>
        </tr>
        <tr style="font-weight:bold;background:#f1f5f9">
          <td style="text-align:center">5.</td>
          <td>
            SELISIH KAS (3 - 4) &bull; Status: 
            <u><?= e($row['status_selisih']) ?></u>
          </td>
          <td class="num" style="color:<?= (float)$row['selisih_kas'] < 0 ? '#b91c1c' : '#000' ?>">
            <?= rupiah($row['selisih_kas']) ?>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- PENJELASAN SELISIH ATAU CATATAN -->
    <?php if (!empty($row['penjelasan_selisih'])): ?>
      <div style="font-size:9.5pt;margin-bottom:8px;border:1px dashed #000;padding:6px 10px">
        <b>Penjelasan Selisih Kas:</b><br>
        <?= nl2br(e($row['penjelasan_selisih'])) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($row['catatan_pemeriksaan'])): ?>
      <div style="font-size:9.5pt;margin-bottom:8px;border:1px dashed #000;padding:6px 10px">
        <b>Catatan Tim Pemeriksa APIP:</b><br>
        <?= nl2br(e($row['catatan_pemeriksaan'])) ?>
      </div>
    <?php endif; ?>

    <!-- PERNYATAAN PENUTUPAN -->
    <div class="narrative" style="margin-top:10px">
      Demikian Berita Acara Pemeriksaan Kas ini dibuat dengan sebenarnya dalam rangkap secukupnya untuk dipergunakan sebagaimana mestinya. Seluruh uang dan bukti pembukuan tersebut di atas telah dihitung di hadapan Bendahara Kepenghuluan dan diserahkan kembali secara utuh tanpa ada yang dikurangkan maupun disembunyikan.
    </div>

    <!-- LEMBAR TANDA TANGAN 3 PIHAK (BENDAHARA, KETUA TIM, PENGHULU) -->
    <table style="width:100%;margin-top:20px;font-size:10pt;border-collapse:collapse">
      <tr>
        <td style="width:50%;text-align:center;vertical-align:top;padding-bottom:55px">
          Yang Diperiksa,<br>
          <b>Bendahara Kepenghuluan</b><br><br><br><br>
          <b><u><?= e($row['nama_bendahara']) ?></u></b><br>
          <?= !empty($row['nip_bendahara']) ? 'NIP. ' . e($row['nip_bendahara']) : 'Bendahara Desa' ?>
        </td>
        <td style="width:50%;text-align:center;vertical-align:top;padding-bottom:55px">
          <?= e($row['desa_nama']) ?>, <?= tgl_id($row['tgl_pemeriksaan']) ?><br>
          Yang Memeriksa,<br>
          <b>Ketua Tim Pemeriksa APIP</b><br><br><br><br>
          <b><u><?= e($row['nama_ketua_tim']) ?></u></b><br>
          <?= !empty($row['nip_ketua_tim']) ? 'NIP. ' . e($row['nip_ketua_tim']) : 'Auditor Inspektorat' ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="text-align:center;vertical-align:top">
          Mengetahui / Menyetujui,<br>
          <b>Pj. Penghulu / Penghulu <?= e($row['desa_nama']) ?></b><br><br><br><br>
          <b style="font-size:10.5pt"><u><?= e($row['nama_kepala_desa']) ?></u></b>
        </td>
      </tr>
    </table>

  </div>

</body>
</html>
