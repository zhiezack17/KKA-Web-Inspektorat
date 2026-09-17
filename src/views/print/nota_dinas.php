<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nota Dinas Penugasan — <?= e($nd['no_nd']) ?></title>
  <style>
    @page {
      size: 215mm 330mm; /* Standar Folio / F4 */
      margin: 15mm 20mm 15mm 20mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Times New Roman", Times, serif;
      font-size: 11.5pt;
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
      background: #0284c7;
      color: #fff;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-family: sans-serif;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: none;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 15px 25px;
    }
    .kop {
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 3px double #000;
      padding-bottom: 8px;
      margin-bottom: 16px;
    }
    .kop-logo { width: 75px; text-align: center; }
    .kop-logo img { width: 70px; height: auto; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h2 { margin: 0; font-size: 14pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
    .kop-text h1 { margin: 2px 0; font-size: 17pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .kop-text p { margin: 0; font-size: 8.5pt; line-height: 1.25; }

    .doc-title {
      text-align: center;
      font-size: 14pt;
      font-weight: bold;
      text-decoration: underline;
      text-transform: uppercase;
      margin: 12px 0 4px;
      letter-spacing: 1px;
    }
    .doc-sub {
      text-align: center;
      font-size: 11pt;
      margin-bottom: 16px;
    }

    table.meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 16px;
      font-size: 11pt;
    }
    table.meta-table td {
      padding: 3px 4px;
      vertical-align: top;
    }

    .divider {
      border-bottom: 1px solid #000;
      margin: 12px 0 16px;
    }

    .content-body {
      text-align: justify;
      line-height: 1.45;
    }
    .content-body p { margin: 0 0 10px; text-indent: 32px; }

    table.grid-table {
      width: 100%;
      border-collapse: collapse;
      margin: 12px 0;
      font-size: 10.5pt;
    }
    table.grid-table th, table.grid-table td {
      border: 1px solid #000;
      padding: 5px 8px;
      vertical-align: middle;
    }
    table.grid-table th {
      background-color: #f1f5f9;
      text-align: center;
      font-weight: bold;
    }

    .box-disposisi {
      border: 2px solid #000;
      padding: 12px 16px;
      margin-top: 24px;
      page-break-inside: avoid;
    }

    @media print {
      .no-print { display: none !important; }
      body { margin: 0; background: #fff; }
      .container { padding: 0; width: 100%; }
    }
  </style>
</head>
<body>

<?php
$showDisposisi = in_array($nd['status'], ['DISETUJUI', 'DITOLAK']) || !empty($nd['tgl_disposisi']) || (isset($_GET['with_disposisi']) && $_GET['with_disposisi'] == '1');
?>
<div class="no-print">
  <div style="font-family:sans-serif;font-size:13px;display:flex;align-items:center;gap:12px">
    <b>Preview Cetak:</b>
    <?php if ($nd['status'] === 'DISETUJUI'): ?>
      <span style="background:#dcfce7;color:#15803d;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600">
        &#10004; Telah Didisposisi Inspektur Daerah
      </span>
    <?php elseif ($nd['status'] === 'DITOLAK'): ?>
      <span style="background:#fee2e2;color:#b91c1c;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600">
        &#10008; Dikembalikan ke Irban (Revisi)
      </span>
    <?php else: ?>
      <span style="background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600">
        Nota Dinas Usulan Irban (Menunggu Disposisi)
      </span>
    <?php endif; ?>
  </div>
  <div style="display:flex;gap:12px;align-items:center">
    <?php if (!$showDisposisi): ?>
      <a href="?id=<?= $nd['id'] ?>&with_disposisi=1" style="font-family:sans-serif;font-size:12px;color:#475569;text-decoration:underline">
        + Tampilkan Blangko Disposisi Manual
      </a>
    <?php elseif ($nd['status'] !== 'DISETUJUI' && $nd['status'] !== 'DITOLAK'): ?>
      <a href="?id=<?= $nd['id'] ?>" style="font-family:sans-serif;font-size:12px;color:#475569;text-decoration:underline">
        - Sembunyikan Blangko Disposisi
      </a>
    <?php endif; ?>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
  </div>
</div>

<div class="container">
  <!-- KOP SURAT -->
  <div class="kop">
    <div class="kop-logo">
      <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rokan Hilir">
    </div>
    <div class="kop-text">
      <h2>PEMERINTAH KABUPATEN ROKAN HILIR</h2>
      <h1>INSPEKTORAT</h1>
      <p>Komplek Perkantoran Batu 6 Jl. Lintas Pesisir Sungai Rokan, Kec. Bangko - Bagansiapiapi</p>
      <p>Telp. (0767) 2700270 &middot; Email: inspektorat@rohilkab.go.id &middot; Website: inspektorat.rohilkab.go.id</p>
    </div>
  </div>

  <!-- JUDUL -->
  <div class="doc-title">NOTA DINAS</div>
  <div class="doc-sub">Nomor : <?= e($nd['no_nd']) ?></div>

<?php
$jabatanIrban = !empty($irbanUser['jabatan']) ? $irbanUser['jabatan'] : 'Inspektur Pembantu IV';
$dariKantor = str_contains($jabatanIrban, 'Inspektorat') ? $jabatanIrban : ($jabatanIrban . ' Inspektorat Kabupaten Rokan Hilir');
?>
  <!-- META NOTA DINAS -->
  <table class="meta-table">
    <tr>
      <td style="width:110px"><b>Kepada Yth.</b></td>
      <td style="width:10px">:</td>
      <td><b>Inspektur Daerah Kabupaten Rokan Hilir</b></td>
    </tr>
    <tr>
      <td><b>Dari</b></td>
      <td>:</td>
      <td><b><?= e($dariKantor) ?></b></td>
    </tr>
    <tr>
      <td><b>Tanggal</b></td>
      <td>:</td>
      <td><?= tgl_id($nd['tgl_nd']) ?></td>
    </tr>
    <tr>
      <td><b>Sifat</b></td>
      <td>:</td>
      <td>Penting</td>
    </tr>
    <tr>
      <td><b>Lampiran</b></td>
      <td>:</td>
      <td>1 (satu) Berkas</td>
    </tr>
    <tr>
      <td><b>Perihal</b></td>
      <td>:</td>
      <td><b>Permohonan Penerbitan Surat Perintah Tugas (SPT) Pengawasan Keuangan Kepenghuluan <?= e($nd['desa_nama']) ?> TA <?= (int)$nd['tahun_anggaran'] ?></b></td>
    </tr>
  </table>

  <div class="divider"></div>

  <!-- ISI NOTA DINAS -->
  <div class="content-body">
    <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:11.5pt">
      <tr>
        <td style="width:65px;vertical-align:top"><b>Dasar</b></td>
        <td style="width:12px;vertical-align:top">:</td>
        <td style="vertical-align:top">Program Kerja Pengawasan Tahunan (PKPT) Tahun <?= (int)$nd['tahun_anggaran'] ?> Inspektorat Kabupaten Rokan Hilir;</td>
      </tr>
    </table>

    <p style="margin:0 0 10px;text-indent:0">
      Dengan ini mengusulkan penugasan <b><?= e($nd['jenis_audit']) ?></b> atas <b><?= e($nd['tujuan']) ?></b> pada Kepenghuluan <b><?= e($nd['desa_nama']) ?></b> Kecamatan <b><?= e($nd['kecamatan_nama']) ?></b> Tahun Anggaran <?= (int)$nd['tahun_anggaran'] ?>.
    </p>

    <p style="margin:0 0 12px;text-indent:0">
      Lama penugasan selama <b><?= (int)$nd['lama_hari'] ?> (<?= terbilang_angka((int)$nd['lama_hari']) ?>) hari kerja</b> mulai tanggal <b><?= tgl_id($nd['tgl_mulai']) ?></b> s.d <b><?= tgl_id($nd['tgl_selesai']) ?></b>.
    </p>

    <p style="margin:0 0 12px;text-indent:0">
      Adapun susunan nama yang diusulkan sebagai berikut :
    </p>

    <!-- SUSUNAN TIM BERPOIN RESMI TANPA TABEL KOTAK -->
    <div style="margin-left:8px;margin-bottom:16px;line-height:1.4">
      <!-- 1. Wakil Penanggung Jawab (Irban) -->
      <table style="width:100%;border-collapse:collapse;margin-bottom:8px;font-size:11pt">
        <tr>
          <td style="width:26px;vertical-align:top">1.</td>
          <td style="width:75px;vertical-align:top">Nama</td>
          <td style="width:12px;vertical-align:top">:</td>
          <td style="vertical-align:top"><b><?= e($nd['irban_nama'] ?: ($irbanUser['nama'] ?? '')) ?></b></td>
        </tr>
        <tr>
          <td></td>
          <td style="vertical-align:top">NIP.</td>
          <td style="vertical-align:top">:</td>
          <td style="vertical-align:top"><?= e(format_nip($irbanUser['nip'] ?? '')) ?></td>
        </tr>
        <tr>
          <td></td>
          <td style="vertical-align:top">Jabatan</td>
          <td style="vertical-align:top">:</td>
          <td style="vertical-align:top">Wakil Penanggungjawab</td>
        </tr>
      </table>

      <!-- 2. Pengendali Teknis (Dalnis) -->
      <table style="width:100%;border-collapse:collapse;margin-bottom:8px;font-size:11pt">
        <tr>
          <td style="width:26px;vertical-align:top">2.</td>
          <td style="width:75px;vertical-align:top">Nama</td>
          <td style="width:12px;vertical-align:top">:</td>
          <td style="vertical-align:top"><b><?= e($nd['dalnis_nama']) ?></b></td>
        </tr>
        <tr>
          <td></td>
          <td style="vertical-align:top">NIP.</td>
          <td style="vertical-align:top">:</td>
          <td style="vertical-align:top"><?= e(format_nip($dalnisUser['nip'] ?? '')) ?></td>
        </tr>
        <tr>
          <td></td>
          <td style="vertical-align:top">Jabatan</td>
          <td style="vertical-align:top">:</td>
          <td style="vertical-align:top">Pengendali Teknis</td>
        </tr>
      </table>

      <!-- 3. Ketua Tim -->
      <table style="width:100%;border-collapse:collapse;margin-bottom:8px;font-size:11pt">
        <tr>
          <td style="width:26px;vertical-align:top">3.</td>
          <td style="width:75px;vertical-align:top">Nama</td>
          <td style="width:12px;vertical-align:top">:</td>
          <td style="vertical-align:top"><b><?= e($nd['ketua_tim_nama']) ?></b></td>
        </tr>
        <tr>
          <td></td>
          <td style="vertical-align:top">NIP.</td>
          <td style="vertical-align:top">:</td>
          <td style="vertical-align:top"><?= e(format_nip($ketuaUser['nip'] ?? '')) ?></td>
        </tr>
        <tr>
          <td></td>
          <td style="vertical-align:top">Jabatan</td>
          <td style="vertical-align:top">:</td>
          <td style="vertical-align:top">Ketua Tim</td>
        </tr>
      </table>

      <!-- 4 dst. Anggota Tim -->
      <?php $noAg = 4; foreach ($anggotaList as $ag): ?>
        <table style="width:100%;border-collapse:collapse;margin-bottom:8px;font-size:11pt">
          <tr>
            <td style="width:26px;vertical-align:top"><?= $noAg++ ?>.</td>
            <td style="width:75px;vertical-align:top">Nama</td>
            <td style="width:12px;vertical-align:top">:</td>
            <td style="vertical-align:top"><b><?= e($ag['nama']) ?></b></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">NIP.</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e(format_nip($ag['nip'] ?? '')) ?></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">Jabatan</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top">Anggota Tim</td>
          </tr>
        </table>
      <?php endforeach; ?>
    </div>

    <p style="margin:0 0 14px;text-indent:0">
      Demikian yang dapat disampaikan dan menunggu arahan lebih lanjut dari Bapak, atas perhatian diucapkan terima kasih.
    </p>
  </div>

  <!-- TANDA TANGAN IRBAN -->
  <table style="width:100%;margin-top:16px;page-break-inside:avoid">
    <tr>
      <td style="width:50%"></td>
      <td style="text-align:center;font-size:11pt">
        <b style="text-transform:uppercase"><?= e($jabatanIrban) ?>,</b>
        <br><br><br><br>
        <u><b><?= e($nd['irban_nama'] ?: ($irbanUser['nama'] ?? '')) ?></b></u><br>
        NIP. <?= e(format_nip($irbanUser['nip'] ?? '')) ?>
      </td>
    </tr>
  </table>

  <?php if ($showDisposisi): ?>
  <!-- LEMBAR DISPOSISI INSPEKTUR DAERAH (OTOMATIS MUNCUL SETELAH DIDISPOSISI ATAU MODE MANUAL) -->
  <div class="box-disposisi">
    <div style="display:flex;justify-content:space-between;border-bottom:1.5px solid #000;padding-bottom:4px;margin-bottom:8px">
      <b style="font-size:11pt;text-transform:uppercase">DISPOSISI / PETUNJUK INSPEKTUR DAERAH:</b>
      <span style="font-size:10pt">Tanggal Disposisi: <?= !empty($nd['tgl_disposisi']) ? tgl_id($nd['tgl_disposisi']) : '....................................' ?></span>
    </div>

    <div style="display:flex;gap:24px;margin-bottom:8px;font-size:10.5pt">
      <label>
        [ <?= ($nd['status'] === 'DISETUJUI') ? '&#10004;' : '&nbsp;&nbsp;' ?> ] <b>DISETUJUI</b> (Proses Surat Perintah Tugas)
      </label>
      <label>
        [ <?= ($nd['status'] === 'DITOLAK') ? '&#10004;' : '&nbsp;&nbsp;' ?> ] <b>KEMBALIKAN KE IRBAN</b> (Perlu Perbaikan)
      </label>
    </div>

    <div style="font-size:10.5pt;line-height:1.4;margin-bottom:10px">
      <b>Catatan / Instruksi Disposisi:</b><br>
      <div style="font-style:italic;padding-left:8px;min-height:30px">
        <?php if (!empty($nd['catatan_inspektur'])): ?>
          <?= nl2br(e($nd['catatan_inspektur'])) ?>
        <?php elseif ($nd['status'] === 'DISETUJUI'): ?>
          Disetujui. Teruskan ke Bagian SPT untuk penomoran dan penerbitan Surat Perintah Tugas resmi.
        <?php else: ?>
          <span style="color:#64748b;font-style:italic">(Tuliskan instruksi/catatan arahan di sini...)</span>
          <br><br>
        <?php endif; ?>
      </div>
    </div>

    <table style="width:100%">
      <tr>
        <td style="width:50%"></td>
        <td style="text-align:center;font-size:10.5pt">
          <b>Inspektur Daerah Kabupaten Rokan Hilir</b>
          <br><br><br><br>
          <u><b>H. SARMAN SYAHRONI, ST., M.IP</b></u><br>
          NIP. 19760810 200312 1 004
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
</div>

</body>
</html>

