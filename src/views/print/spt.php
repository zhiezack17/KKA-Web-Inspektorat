<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Surat Tugas — <?= e($spt['no_spt']) ?></title>
  <style>
    @page {
      size: 215mm 330mm; /* Standar Folio / F4 */
      margin: 15mm 20mm 15mm 20mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: "Times New Roman", Times, serif;
      font-size: 11pt;
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
      margin-bottom: 14px;
    }
    .kop-logo { width: 75px; text-align: center; }
    .kop-logo img { width: 70px; height: auto; }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h2 { margin: 0; font-size: 13.5pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
    .kop-text h1 { margin: 2px 0; font-size: 17pt; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .kop-text p { margin: 0; font-size: 8.5pt; line-height: 1.25; }

    .doc-title {
      text-align: center;
      font-size: 13.5pt;
      font-weight: bold;
      text-decoration: underline;
      text-transform: uppercase;
      margin: 10px 0 2px;
      letter-spacing: 1px;
    }
    .doc-sub {
      text-align: center;
      font-size: 11pt;
      font-weight: bold;
      margin-bottom: 14px;
    }

    .section-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      font-size: 11pt;
    }
    .section-table td {
      padding: 2px 4px;
      vertical-align: top;
    }

    .item-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      font-size: 11pt;
    }
    .item-table td {
      padding: 1.5px 2px;
      vertical-align: top;
    }

    @media print {
      .no-print { display: none !important; }
      body { margin: 0; background: #fff; }
      .container { padding: 0; width: 100%; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <div style="font-family:sans-serif;font-size:13px;color:#475569">
    <b>Preview Cetak:</b> Surat Tugas (SPT) Resmi Inspektorat Kabupaten Rokan Hilir
  </div>
  <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="container">
  <!-- KOP SURAT RESMI -->
  <div class="kop">
    <div class="kop-logo">
      <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rokan Hilir">
    </div>
    <div class="kop-text">
      <h2>PEMERINTAH KABUPATEN ROKAN HILIR</h2>
      <h1>INSPEKTORAT</h1>
      <p>Komplek Perkantoran Batu 6 Jalan Lintas Pesisir Sungai Rokan Telp. (0767) 2700270 Fax. (0767) 2700271</p>
      <p>Email : inspektorat@rohilkab.go.id</p>
      <p><b>KECAMATAN BANGKO - BAGANSIAPIAPI</b></p>
    </div>
  </div>

  <!-- JUDUL -->
  <div class="doc-title">SURAT TUGAS</div>
  <div class="doc-sub">NOMOR : <?= e($spt['no_spt']) ?></div>

  <!-- DASAR -->
  <table class="section-table">
    <tr>
      <td style="width:105px;vertical-align:top"><b>DASAR</b></td>
      <td style="width:15px;vertical-align:top">:</td>
      <td style="vertical-align:top">
        <table class="item-table">
          <tr>
            <td style="width:20px;vertical-align:top">1.</td>
            <td style="vertical-align:top;text-align:justify">
              Program Kerja Pengawasan Tahunan (PKPT) Tahun <?= (int)$spt['tahun_anggaran'] ?> Inspektorat Kabupaten Rokan Hilir.
            </td>
          </tr>
          <?php 
          // Parse dasar tambahan / surat permintaan audit (jika ada surat masuk dari OPD/Kepenghuluan/APH)
          $dasarTambahan = [];
          if (!empty($spt['dasar_hukum'])) {
              $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $spt['dasar_hukum']));
              foreach ($lines as $l) {
                  $t = trim($l);
                  if ($t === '') continue;
                  $tClean = preg_replace('/^\d+[\.\)]\s*/', '', $t);
                  // Abaikan jika isinya menyebut Nota Dinas atau mengulang PKPT
                  if (stripos($tClean, 'Nota Dinas') !== false) continue;
                  if (stripos($tClean, 'PKPT') !== false || stripos($tClean, 'Program Kerja Pengawasan') !== false) continue;
                  if (!empty($tClean)) {
                      $dasarTambahan[] = $tClean;
                  }
              }
          }
          $noDasar = 2;
          foreach ($dasarTambahan as $dt):
          ?>
            <tr>
              <td style="width:20px;vertical-align:top"><?= $noDasar++ ?>.</td>
              <td style="vertical-align:top;text-align:justify">
                <?= e($dt) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </td>
    </tr>
  </table>

  <!-- KEPADA -->
  <table class="section-table" style="margin-top:8px">
    <tr>
      <td style="width:105px;vertical-align:top"><b>KEPADA</b></td>
      <td style="width:15px;vertical-align:top">:</td>
      <td style="vertical-align:top">
        <!-- 1. Wakil Penanggung Jawab (Irban) -->
        <table class="item-table">
          <tr>
            <td style="width:20px;vertical-align:top">1.</td>
            <td style="width:90px;vertical-align:top">Nama</td>
            <td style="width:12px;vertical-align:top">:</td>
            <td style="vertical-align:top"><b><?= e($spt['wakil_pj_nama'] ?: ($irbanUser['nama'] ?? 'MARWAN, M.T')) ?></b></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">NIP</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e(format_nip($irbanUser['nip'] ?? '197707272002121005')) ?></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">Pangkat/Gol</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e($irbanUser['pangkat'] ?? 'Pembina Tk. I / IV.b') ?></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">Jabatan</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e($irbanUser['jabatan'] ?? 'Inspektur Pembantu IV') ?> (Wakil Penanggungjawab)</td>
          </tr>
        </table>

        <!-- 2. Pengendali Teknis (Dalnis) -->
        <table class="item-table">
          <tr>
            <td style="width:20px;vertical-align:top">2.</td>
            <td style="width:90px;vertical-align:top">Nama</td>
            <td style="width:12px;vertical-align:top">:</td>
            <td style="vertical-align:top"><b><?= e($spt['dalnis_nama']) ?></b></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">NIP</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e(format_nip($dalnisUser['nip'] ?? '')) ?></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">Pangkat/Gol</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e($dalnisUser['pangkat'] ?? 'Pembina Utama Muda / IV.c') ?></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">Jabatan</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e($dalnisUser['jabatan'] ?? 'Auditor Ahli Madya') ?> (Pengendali Teknis)</td>
          </tr>
        </table>

        <!-- 3. Ketua Tim -->
        <table class="item-table">
          <tr>
            <td style="width:20px;vertical-align:top">3.</td>
            <td style="width:90px;vertical-align:top">Nama</td>
            <td style="width:12px;vertical-align:top">:</td>
            <td style="vertical-align:top"><b><?= e($spt['ketua_tim_nama']) ?></b></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">NIP</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e(format_nip($ketuaUser['nip'] ?? '')) ?></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">Pangkat/Gol</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e($ketuaUser['pangkat'] ?? 'Penata Tk. I / III.d') ?></td>
          </tr>
          <tr>
            <td></td>
            <td style="vertical-align:top">Jabatan</td>
            <td style="vertical-align:top">:</td>
            <td style="vertical-align:top"><?= e($ketuaUser['jabatan'] ?? 'Auditor Ahli Muda') ?> (Ketua Tim)</td>
          </tr>
        </table>

        <!-- 4 dst. Anggota Tim -->
        <?php $noAg = 4; foreach ($anggotaList as $ag): ?>
          <table class="item-table">
            <tr>
              <td style="width:20px;vertical-align:top"><?= $noAg++ ?>.</td>
              <td style="width:90px;vertical-align:top">Nama</td>
              <td style="width:12px;vertical-align:top">:</td>
              <td style="vertical-align:top"><b><?= e($ag['nama']) ?></b></td>
            </tr>
            <tr>
              <td></td>
              <td style="vertical-align:top">NIP</td>
              <td style="vertical-align:top">:</td>
              <td style="vertical-align:top"><?= e(format_nip($ag['nip'] ?? '')) ?></td>
            </tr>
            <tr>
              <td></td>
              <td style="vertical-align:top">Pangkat/Gol</td>
              <td style="vertical-align:top">:</td>
              <td style="vertical-align:top"><?= e($ag['pangkat'] ?? 'Penata / III.c') ?></td>
            </tr>
            <tr>
              <td></td>
              <td style="vertical-align:top">Jabatan</td>
              <td style="vertical-align:top">:</td>
              <td style="vertical-align:top"><?= e($ag['jabatan'] ?? 'Auditor Ahli Pertama') ?> (Anggota Tim)</td>
            </tr>
          </table>
        <?php endforeach; ?>
      </td>
    </tr>
  </table>

  <!-- UNTUK -->
  <table class="section-table" style="margin-top:8px">
    <tr>
      <td style="width:105px;vertical-align:top"><b>UNTUK</b></td>
      <td style="width:15px;vertical-align:top">:</td>
      <td style="vertical-align:top">
        <table class="item-table">
          <tr>
            <td style="width:20px;vertical-align:top">1.</td>
            <td style="vertical-align:top;text-align:justify">
              Melakukan <?= e(!empty($spt['jenis_audit']) ? $spt['jenis_audit'] : 'Audit Dengan Tujuan Tertentu (ADTT)') ?> atas Pengelolaan Keuangan Desa pada Kepenghuluan <b><?= e($spt['desa_nama']) ?></b> Kecamatan <b><?= e($spt['kecamatan_nama']) ?></b> Tahun Anggaran <?= (int)$spt['tahun_anggaran'] ?>.
            </td>
          </tr>
          <tr>
            <td style="vertical-align:top">2.</td>
            <td style="vertical-align:top;text-align:justify">
              Lama Pelaksanaan Tugas tersebut Selama <b><?= (int)$spt['lama_hari'] ?> (<?= terbilang_angka((int)$spt['lama_hari']) ?>) hari kerja</b>, terhitung mulai tanggal <b><?= tgl_id($spt['tgl_mulai']) ?></b> s/d <b><?= tgl_id($spt['tgl_selesai']) ?></b>.
            </td>
          </tr>
          <tr>
            <td style="vertical-align:top">3.</td>
            <td style="vertical-align:top;text-align:justify">
              Kegiatan ini dibebankan pada Anggaran Pengawasan Inspektorat Kabupaten Rokan Hilir Tahun Anggaran <?= date('Y', strtotime($spt['tgl_spt'])) ?>.
            </td>
          </tr>
          <tr>
            <td style="vertical-align:top">4.</td>
            <td style="vertical-align:top;text-align:justify">
              Membuat Laporan Penugasan setelah selesainya pelaksanaan tugas.
            </td>
          </tr>
        </table>

        <!-- KALIMAT PENUTUP (SEJAJAR RAPI DENGAN ISI POIN DI ATASNYA) -->
        <div style="margin-top:14px;font-size:11pt;line-height:1.4">
          Demikian untuk dilaksanakan sebaik-baiknya dengan penuh rasa tanggungjawab.
        </div>
      </td>
    </tr>
  </table>

  <!-- TANDA TANGAN INSPEKTUR DAERAH RESMI SESUAI STANDAR BOGOR -->
  <table style="width:100%;margin-top:16px;page-break-inside:avoid">
    <tr>
      <td style="width:50%"></td>
      <td style="font-size:11pt;line-height:1.35">
        <table style="border-collapse:collapse;font-size:11pt;margin-bottom:4px">
          <tr>
            <td style="width:105px">Dikeluarkan di</td>
            <td style="width:10px">:</td>
            <td>Bagansiapiapi</td>
          </tr>
          <tr>
            <td>Pada Tanggal</td>
            <td>:</td>
            <td><?= tgl_id($spt['tgl_spt']) ?></td>
          </tr>
        </table>
        <div style="font-weight:bold;margin-top:4px;letter-spacing:0.5px">INSPEKTUR DAERAH</div>
        <br><br><br><br>
        <div style="font-weight:bold;text-decoration:underline">H. SARMAN SYAHRONI, ST., M.IP., CGCAE</div>
        <div>Pembina Utama Muda / IV.c</div>
        <div>NIP. 19760810 200312 1 004</div>
      </td>
    </tr>
  </table>
</div>

</body>
</html>
