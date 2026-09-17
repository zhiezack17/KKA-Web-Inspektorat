<?php
$title = 'SOP & Alur Kerja (Workflow) KKA Digital - Inspektorat Rokan Hilir';
$auth  = $GLOBALS['auth'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <link rel="icon" type="image/png" href="<?= asset('img/logo-rohil.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

    :root {
      --primary: #0f3460;
      --primary-dark: #16213e;
      --accent: #d97706;
      --gold: #f59e0b;
      --slate-50: #f8fafc;
      --slate-100: #f1f5f9;
      --slate-200: #e2e8f0;
      --slate-300: #cbd5e1;
      --slate-600: #475569;
      --slate-700: #334155;
      --slate-800: #1e293b;
      --slate-900: #0f172a;
      --green-600: #16a34a;
      --green-50: #f0fdf4;
      --green-border: #bbf7d0;
      --indigo-600: #4f46e5;
      --indigo-50: #eef2ff;
      --amber-600: #d97706;
      --amber-50: #fffbeb;
      --red-600: #dc2626;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background: #f1f5f9;
      color: var(--slate-800);
      line-height: 1.6;
      font-size: 13.5px;
      padding-bottom: 50px;
    }

    /* Screen Action Bar */
    .action-bar {
      position: sticky;
      top: 0;
      z-index: 100;
      background: #ffffff;
      border-bottom: 1px solid var(--slate-200);
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .action-bar .left {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .action-bar .brand-title {
      font-weight: 800;
      color: var(--primary);
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .action-bar .actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      border: 1px solid transparent;
    }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-print { background: #059669; color: #fff; border-color: #059669; }
    .btn-print:hover { background: #047857; }
    .btn-outline { background: #fff; color: var(--slate-700); border-color: var(--slate-300); }
    .btn-outline:hover { background: var(--slate-100); }

    /* Main Container (A4 layout) */
    .document-page {
      max-width: 960px;
      margin: 28px auto;
      background: #ffffff;
      padding: 44px 52px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.07);
      border-radius: 12px;
      border: 1px solid var(--slate-200);
    }

    /* Kop Surat */
    .kop-surat {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      padding-bottom: 16px;
      border-bottom: 3px double #000;
      margin-bottom: 24px;
      text-align: center;
    }
    .kop-logo img {
      width: 76px;
      height: auto;
      object-fit: contain;
    }
    .kop-text h3 {
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #000;
      margin-bottom: 2px;
    }
    .kop-text h2 {
      font-size: 20px;
      font-weight: 900;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #000;
      margin-bottom: 4px;
    }
    .kop-text p {
      font-size: 11px;
      color: #333;
      margin-top: 2px;
      line-height: 1.35;
    }

    /* Judul Dokumen */
    .doc-title-block {
      text-align: center;
      margin-bottom: 30px;
      padding: 16px 20px;
      background: linear-gradient(180deg, #f8fafc, #ffffff);
      border: 1px solid var(--slate-200);
      border-radius: 10px;
    }
    .doc-title-block h1 {
      font-size: 17px;
      font-weight: 900;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .doc-title-block .doc-subtitle {
      font-size: 13px;
      font-weight: 700;
      color: var(--accent);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .doc-title-block .doc-meta {
      margin-top: 8px;
      font-size: 11.5px;
      color: var(--slate-600);
      font-family: 'JetBrains Mono', monospace;
    }

    /* Section Styles */
    .section {
      margin-bottom: 32px;
      page-break-inside: avoid;
    }
    .section-head {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14.5px;
      font-weight: 800;
      color: var(--primary);
      border-bottom: 2px solid var(--slate-200);
      padding-bottom: 6px;
      margin-bottom: 14px;
      text-transform: uppercase;
    }
    .section-head i {
      color: var(--accent);
      font-size: 15px;
    }

    p { margin-bottom: 10px; text-align: justify; }

    /* Tables */
    .sop-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 16px;
      font-size: 12.5px;
    }
    .sop-table th {
      background: #f1f5f9;
      color: var(--slate-800);
      border: 1px solid #cbd5e1;
      padding: 9px 12px;
      text-align: left;
      font-weight: 700;
    }
    .sop-table td {
      border: 1px solid #cbd5e1;
      padding: 8px 12px;
      vertical-align: top;
      color: var(--slate-700);
    }
    .sop-table tr:nth-child(even) td {
      background: #f8fafc;
    }

    /* Step Cards / Workflow Graphic */
    .flow-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin: 18px 0;
    }
    @media (max-width: 768px) {
      .flow-container { grid-template-columns: 1fr; }
    }
    .flow-box {
      border: 1px solid var(--slate-300);
      border-radius: 10px;
      background: #fff;
      padding: 14px;
      position: relative;
      transition: transform 0.2s ease;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .flow-box.active {
      border-color: #6366f1;
      background: #faf5ff;
    }
    .flow-box .step-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      font-weight: 800;
      font-size: 12px;
      margin-bottom: 8px;
    }
    .flow-box h4 {
      font-size: 13.5px;
      font-weight: 800;
      color: var(--slate-800);
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .flow-box p {
      font-size: 11.5px;
      color: var(--slate-600);
      line-height: 1.45;
      margin: 0;
    }
    .flow-box .actor {
      margin-top: 8px;
      display: inline-block;
      font-size: 10.5px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 999px;
      background: var(--slate-100);
      color: var(--slate-700);
      border: 1px solid var(--slate-200);
    }

    /* Badges */
    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      font-family: 'JetBrains Mono', monospace;
    }
    .badge-draft { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .badge-ketua { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-dalnis { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .badge-revisi { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-final { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

    /* Callout Box */
    .callout {
      padding: 14px 16px;
      border-radius: 8px;
      border-left: 4px solid;
      margin: 16px 0;
      font-size: 12.5px;
      line-height: 1.5;
    }
    .callout-info {
      background: #eff6ff;
      border-color: #3b82f6;
      color: #1e40af;
    }
    .callout-warning {
      background: #fffbeb;
      border-color: #f59e0b;
      color: #92400e;
    }

    /* Signature Box */
    .signature-row {
      display: flex;
      justify-content: flex-end;
      margin-top: 40px;
      page-break-inside: avoid;
    }
    .sig-box {
      text-align: center;
      min-width: 260px;
    }
    .sig-space {
      height: 70px;
    }

    /* Print Stylesheet */
    @media print {
      body {
        background: #fff !important;
        color: #000 !important;
        font-size: 11pt !important;
        padding: 0 !important;
      }
      .action-bar {
        display: none !important;
      }
      .document-page {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
      }
      .sop-table th, .sop-table td {
        border-color: #000 !important;
      }
      .flow-box {
        border-color: #000 !important;
        box-shadow: none !important;
      }
      .section {
        page-break-inside: avoid;
      }
      a { text-decoration: none; color: #000 !important; }
    }
  </style>
</head>
<body>

  <!-- Top Action Bar (Screen Only) -->
  <div class="action-bar">
    <div class="left">
      <div class="brand-title">
        <i class="fa-solid fa-file-shield" style="color:var(--accent);font-size:18px"></i>
        <span>Dokumen SOP &amp; Panduan Alur Kerja KKA Digital</span>
      </div>
    </div>
    <div class="actions">
      <a href="<?= url('dashboard') ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
      <a href="<?= url('sesi') ?>" class="btn btn-outline"><i class="fa-solid fa-clipboard-list"></i> Sesi Audit</a>
      <button onclick="window.print()" class="btn btn-print"><i class="fa-solid fa-print"></i> Cetak / Simpan PDF</button>
    </div>
  </div>

  <!-- Document Body (Print Ready) -->
  <div class="document-page">

    <!-- Kop Surat Resmi Inspektorat Rohil -->
    <div class="kop-surat">
      <div class="kop-logo">
        <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rohil">
      </div>
      <div class="kop-text">
        <h3>Pemerintah Kabupaten Rokan Hilir</h3>
        <h2>Inspektorat Daerah</h2>
        <p>
          Komplek Perkantoran Terpadu Bagansiapiapi, Batu Enam, Kabupaten Rokan Hilir, Riau<br>
          Laman Resmi: <b>arsipdigital-inspektorat.com</b> · Email: inspektorat@rohilkab.go.id
        </p>
      </div>
      <div class="kop-logo">
        <img src="<?= asset('img/logo-inspektorat.png') ?>" alt="Logo Inspektorat">
      </div>
    </div>

    <!-- Judul Dokumen SOP -->
    <div class="doc-title-block">
      <h1>Standar Operasional Prosedur (SOP) &amp; Workflow</h1>
      <div class="doc-subtitle">Penyusunan dan Reviu Berjenjang Kertas Kerja Audit (KKA) Keuangan Kepenghuluan</div>
      <div class="doc-meta">Nomor Registrasi SOP: SOP/KKA-QA/INSP-ROHIL/2026 · Efektif Berlaku: Tahun Anggaran 2026</div>
    </div>

    <!-- 1. LATAR BELAKANG & TUJUAN -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-circle-info"></i>
        <span>1. Latar Belakang &amp; Tujuan</span>
      </div>
      <p>
        Aplikasi <b>Kertas Kerja Audit (KKA) Digital Inspektorat Kabupaten Rokan Hilir</b> dibangun untuk mendukung penugasan pengawasan, pemeriksaan, dan evaluasi pengelolaan keuangan kepenghuluan/desa. Sistem ini menjamin terlaksananya <i>Quality Control</i> dan <i>Quality Assurance</i> secara berjenjang, objektif, dan akuntabel sesuai dengan Standar Audit Intern Pemerintah Indonesia (SAIPI).
      </p>
      <p>
        Tujuan utama diterapkannya alur digital berjenjang ini meliputi:
      </p>
      <ul style="margin-left: 24px; line-height: 1.7">
        <li><b>Kepatuhan Standar Audit:</b> Setiap temuan belanja desa didukung bukti sah (SPP, kwitansi, bukti bayar fisik) yang terverifikasi.</li>
        <li><b>Integritas Perhitungan:</b> Otomatisasi kalkulasi selisih anggaran (Pagu vs Realisasi SPJ vs Kwitansi yang Diakui).</li>
        <li><b>Pengendalian Mutu Bertingkat:</b> Pengesahan dilakukan teratur mulai dari Anggota Tim, ditelaah oleh Ketua Tim, hingga disahkan oleh Pengendali Teknis (Dalnis).</li>
        <li><b>Keamanan &amp; Perlindungan Data:</b> Penerapan fitur <i>Data Freeze (Kunci Data)</i> saat proses reviu agar angka belanja tidak berubah di tengah pemeriksaan.</li>
      </ul>
    </div>

    <!-- 2. PANDUAN LOGIN & KREDENSIAL AUDITOR -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-id-card"></i>
        <span>2. Panduan Akun &amp; Kredensial Masuk (Login)</span>
      </div>
      <p>
        Untuk kemudahan seluruh Pejabat Fungsional Auditor Inspektorat Rohil, sistem mengadopsi integrasi basis akun <b>E-Reviu</b>. Setiap auditor dapat masuk ke aplikasi tanpa registrasi ulang:
      </p>
      <table class="sop-table">
        <thead>
          <tr>
            <th style="width: 28%">Metode Login</th>
            <th style="width: 32%">Format Input</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><b>1. Nomor Induk Pegawai (NIP)</b></td>
            <td><code>18 Digit Angka</code> (mis. <code>197908052000121001</code>)</td>
            <td>Metode utama tercepat. Sistem otomatis mengenali NIP baik dengan spasi maupun tanpa spasi.</td>
          </tr>
          <tr>
            <td><b>2. Username E-Reviu</b></td>
            <td><code>Username akun E-Reviu</code></td>
            <td>Username persis sesuai master database pengguna aplikasi E-Reviu Inspektorat.</td>
          </tr>
          <tr>
            <td><b>3. Email Terdaftar</b></td>
            <td><code>nama@inspektorat.rohilkab.go.id</code></td>
            <td>Alamat surel resmi yang terdaftar pada profil pengguna.</td>
          </tr>
          <tr>
            <td><b>Password Standar</b></td>
            <td><code>12345678</code></td>
            <td>Kata sandi default untuk seluruh akun baru. Wajib diubah mandiri via menu <b>Profil</b>.</td>
          </tr>
        </tbody>
      </table>

      <div class="callout callout-info">
        <i class="fa-solid fa-lightbulb"></i> <b>Catatan Penting Keamanan:</b> Segera perbarui password default Anda setelah login pertama melalui klik foto avatar di pojok kiri bawah &rarr; menu <b>Profil Pengguna</b>.
      </div>
    </div>

    <!-- 3. MATRIKS PERAN & WEWENANG -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-user-shield"></i>
        <span>3. Matriks Peran &amp; Wewenang (Role Matrix)</span>
      </div>
      <p>
        Berdasarkan jenjang jabatan fungsional auditor dan hierarki penugasan tim audit, wewenang dalam aplikasi KKA dibagi menjadi:
      </p>
      <table class="sop-table">
        <thead>
          <tr>
            <th style="width: 22%">Peran / Role</th>
            <th style="width: 25%">Jenjang Jabatan</th>
            <th>Tugas &amp; Wewenang dalam Sistem KKA</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><b>Auditor / Anggota Tim</b></td>
            <td>Auditor Terampil / Mahir / Pertama</td>
            <td>
              • Membuat sesi audit baru berdasarkan Surat Tugas.<br>
              • Memilih penugasan Ketua Tim dan Pengendali Teknis.<br>
              • Mengentri rincian belanja (Uraian, Pagu, Realisasi, Nilai Kwitansi).<br>
              • Mengunggah bukti lampiran dokumen (PDF/Excel/Foto).<br>
              • Mengajukan KKA ke Ketua Tim bila data telah lengkap.
            </td>
          </tr>
          <tr>
            <td><b>Ketua Tim (Penelaah)</b></td>
            <td>Auditor Muda</td>
            <td>
              • Meneliti keabsahan bukti dukung dan uji petik fisik belanja.<br>
              • Meneliti konsistensi rumus perhitungan selisih/deviasi.<br>
              • Memberikan catatan revisi bila terdapat kekurangan bukti.<br>
              • Menyetujui KKA dan meneruskan ke Pengendali Teknis (Dalnis).
            </td>
          </tr>
          <tr>
            <td><b>Pengendali Teknis / Dalnis</b></td>
            <td>Auditor Madya</td>
            <td>
              • Melakukan kendali mutu tingkat akhir (Quality Control).<br>
              • Menguji apakah temuan dan simpulan telah memenuhi kriteria SAIPI.<br>
              • Mengembalikan dengan arahan bila masih perlu penyempurnaan.<br>
              • <b>Mengesahkan KKA secara resmi (Sah/Final)</b> untuk lampiran LHP.
            </td>
          </tr>
          <tr>
            <td><b>Administrator</b></td>
            <td>Admin Inspektorat</td>
            <td>
              • Memelihara master desa, kecamatan, dan struktur APBDes.<br>
              • Manajemen pengguna, hak akses, dan monitoring sistem.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 4. WORKFLOW 6 TAHAP -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-diagram-project"></i>
        <span>4. Alur Kerja (Workflow) 6 Tahap Reviu Berjenjang</span>
      </div>
      <p>
        Alur kerja penyusunan KKA dari mulai penyusunan awal hingga terbit dokumen sah yang siap dilampirkan pada LHP:
      </p>

      <div class="flow-container">
        <!-- Step 1 -->
        <div class="flow-box">
          <div class="step-badge">1</div>
          <h4><i class="fa-solid fa-right-to-bracket" style="color:var(--primary)"></i> Akses &amp; Buat Sesi</h4>
          <p>Auditor masuk menggunakan NIP, memilih Kepenghuluan, Bidang, menunjuk Ketua Tim &amp; Dalnis.</p>
          <span class="actor">Status: <span class="badge-status badge-draft">DRAFT</span></span>
        </div>

        <!-- Step 2 -->
        <div class="flow-box">
          <div class="step-badge">2</div>
          <h4><i class="fa-solid fa-receipt" style="color:var(--accent)"></i> Input Belanja &amp; Bukti</h4>
          <p>Auditor mengisi rincian belanja, pagu, realisasi, kwitansi, serta mengunggah file bukti dokumen.</p>
          <span class="actor">Peran: Auditor / Anggota</span>
        </div>

        <!-- Step 3 -->
        <div class="flow-box">
          <div class="step-badge">3</div>
          <h4><i class="fa-solid fa-paper-plane" style="color:#0284c7"></i> Ajukan ke Ketua Tim</h4>
          <p>Auditor mengklik "Ajukan ke Ketua Tim". Sistem langsung membekukan data belanja (Data Freeze).</p>
          <span class="actor">Status: <span class="badge-status badge-ketua">REVIEW_KETUA</span></span>
        </div>

        <!-- Step 4 -->
        <div class="flow-box">
          <div class="step-badge">4</div>
          <h4><i class="fa-solid fa-user-check" style="color:var(--amber-600)"></i> Telaah Ketua Tim</h4>
          <p>Ketua Tim meneliti bukti. Opsi: <b>Setuju &rarr; Teruskan ke Dalnis</b> atau <b>Kembalikan (Perlu Revisi)</b>.</p>
          <span class="actor">Peran: Auditor Muda</span>
        </div>

        <!-- Step 5 -->
        <div class="flow-box">
          <div class="step-badge">5</div>
          <h4><i class="fa-solid fa-stamp" style="color:var(--indigo-600)"></i> Pengesahan Dalnis</h4>
          <p>Dalnis melakukan reviu akhir. Opsi: <b>Sahkan KKA (Final)</b> atau <b>Kembalikan (Perlu Revisi)</b>.</p>
          <span class="actor">Status: <span class="badge-status badge-dalnis">REVIEW_DALNIS</span></span>
        </div>

        <!-- Step 6 -->
        <div class="flow-box">
          <div class="step-badge">6</div>
          <h4><i class="fa-solid fa-file-circle-check" style="color:var(--green-600)"></i> KKA Sah &amp; Siap LHP</h4>
          <p>Dokumen KKA dinyatakan sah secara digital, siap dicetak ber-QR/TTE dan diekspor ke Excel.</p>
          <span class="actor">Status: <span class="badge-status badge-final">SELESAI_FINAL</span></span>
        </div>
      </div>
    </div>

    <!-- 5. ATURAN PENGUNCIAN DATA (DATA FREEZE) -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-lock"></i>
        <span>5. Ketentuan Penguncian Data (Audit Data Freeze)</span>
      </div>
      <p>
        Untuk mencegah terjadinya manipulasi angka belanja atau pergantian dokumen lampiran saat proses pemeriksaan berlangsung, sistem memberlakukan protokol <b>Data Freeze</b> dengan rincian sebagai berikut:
      </p>

      <table class="sop-table">
        <thead>
          <tr>
            <th style="width: 25%">Status Sesi KKA</th>
            <th style="width: 25%">Kondisi Rincian &amp; Bukti</th>
            <th>Aksi yang Diperbolehkan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="badge-status badge-draft">DRAFT</span></td>
            <td><b style="color:var(--green-600)"><i class="fa-solid fa-lock-open"></i> Terbuka (Editable)</b></td>
            <td>Auditor bebas menambah, mengubah, atau menghapus baris belanja dan file lampiran.</td>
          </tr>
          <tr>
            <td><span class="badge-status badge-ketua">REVIEW_KETUA</span></td>
            <td><b style="color:var(--amber-600)"><i class="fa-solid fa-lock"></i> Terkunci (Locked)</b></td>
            <td>Seluruh input dibekukan. Ketua Tim dapat meneliti dan memutuskan Setuju / Revisi.</td>
          </tr>
          <tr>
            <td><span class="badge-status badge-dalnis">REVIEW_DALNIS</span></td>
            <td><b style="color:var(--indigo-600)"><i class="fa-solid fa-lock"></i> Terkunci (Locked)</b></td>
            <td>Seluruh input dibekukan. Dalnis dapat mengesahkan atau mengembalikan untuk perbaikan.</td>
          </tr>
          <tr>
            <td><span class="badge-status badge-revisi">PERLU_REVISI</span></td>
            <td><b style="color:var(--green-600)"><i class="fa-solid fa-lock-open"></i> Terbuka Kembali</b></td>
            <td>Auditor dapat memperbaiki rincian belanja sesuai instruksi tertulis dari Ketua/Dalnis.</td>
          </tr>
          <tr>
            <td><span class="badge-status badge-final">SELESAI_FINAL</span></td>
            <td><b style="color:var(--green-600)"><i class="fa-solid fa-shield-halved"></i> Sah &amp; Terkunci Tetap</b></td>
            <td>KKA sah, tidak dapat diubah kembali demi mempertahankan integritas data hasil audit.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 6. LEMBAR PENGESAHAN DOKUMEN -->
    <div class="signature-row">
      <div class="sig-box">
        <div>Ditetapkan di: Bagansiapiapi</div>
        <div>Pada tanggal: <?= tgl_id(date('Y-m-d')) ?></div>
        <div style="font-weight:700;margin-top:6px">INSPEKTUR DAERAH<br>KABUPATEN ROKAN HILIR</div>
        <div class="sig-space"></div>
        <div style="font-weight:800;text-decoration:underline">H. SARMAN SYAHRONI, ST., M.IP., CGCAE</div>
        <div style="font-size:12px;color:var(--slate-600)">Pembina Utama Muda / IV.c</div>
        <div style="font-size:12px;color:var(--slate-600)">NIP. 19790615 200212 1 007</div>
      </div>
    </div>

  </div>

</body>
</html>

