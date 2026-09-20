<?php
$title = 'Master Blueprint & Workflow KKA Digital 2026 - Inspektorat Rokan Hilir';
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
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');

    :root {
      --primary: #022c22;
      --primary-mid: #064e3b;
      --emerald: #047857;
      --emerald-light: #10b981;
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
      --red-50: #fef2f2;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background: #f1f5f9;
      color: var(--slate-800);
      line-height: 1.65;
      font-size: 13px;
      padding-bottom: 60px;
    }

    /* Screen Top Action Bar */
    .action-bar {
      position: sticky;
      top: 0;
      z-index: 100;
      background: #ffffff;
      border-bottom: 1px solid var(--slate-200);
      padding: 12px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
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
      flex-wrap: wrap;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 12.5px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      border: 1px solid transparent;
      white-space: nowrap;
    }
    .btn-primary { background: var(--emerald); color: #fff; box-shadow: 0 2px 6px rgba(4,120,87,0.25); }
    .btn-primary:hover { background: #065f46; transform: translateY(-1px); }
    .btn-download { background: #b45309; color: #fff; border-color: #b45309; box-shadow: 0 2px 6px rgba(180,83,9,0.25); }
    .btn-download:hover { background: #92400e; transform: translateY(-1px); }
    .btn-outline { background: #fff; color: var(--slate-700); border-color: var(--slate-300); }
    .btn-outline:hover { background: var(--slate-100); }

    /* Main Container (A4 Printable Layout) */
    .document-page {
      max-width: 980px;
      margin: 28px auto;
      background: #ffffff;
      padding: 50px 56px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      border-radius: 12px;
      border: 1px solid var(--slate-200);
    }

    /* Kop Surat Resmi */
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
      width: 74px;
      height: auto;
      object-fit: contain;
    }
    .kop-text h3 {
      font-size: 14.5px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #000;
      margin-bottom: 2px;
    }
    .kop-text h2 {
      font-size: 19px;
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
      margin-bottom: 28px;
      padding: 18px 24px;
      background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
      border: 1.5px solid #a7f3d0;
      border-radius: 10px;
    }
    .doc-title-block h1 {
      font-size: 18px;
      font-weight: 900;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .doc-title-block .doc-subtitle {
      font-size: 13px;
      font-weight: 800;
      color: var(--emerald);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .doc-title-block .doc-meta {
      margin-top: 8px;
      font-size: 11px;
      color: var(--slate-600);
      font-family: 'JetBrains Mono', monospace;
    }

    /* Sections */
    .section {
      margin-bottom: 30px;
      page-break-inside: avoid;
    }
    .section-head {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      font-weight: 800;
      color: var(--primary);
      border-bottom: 2px solid var(--slate-200);
      padding-bottom: 6px;
      margin-bottom: 12px;
      text-transform: uppercase;
    }
    .section-head i {
      color: var(--emerald);
      font-size: 15px;
    }

    /* Comparison Card (Blueprint vs Workflow) */
    .comparison-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin: 16px 0;
    }
    .comp-card {
      border: 1px solid var(--slate-200);
      border-radius: 10px;
      padding: 16px;
      background: #fafafa;
    }
    .comp-card.blueprint {
      border-top: 4px solid #0284c7;
      background: #f0f9ff;
    }
    .comp-card.workflow {
      border-top: 4px solid var(--emerald);
      background: #f0fdf4;
    }
    .comp-card h4 {
      font-size: 14px;
      font-weight: 800;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .comp-card.blueprint h4 { color: #0369a1; }
    .comp-card.workflow h4 { color: var(--emerald); }
    .comp-card ul {
      margin-left: 18px;
      font-size: 12px;
      line-height: 1.6;
    }

    /* SOP Tables */
    .sop-table {
      width: 100%;
      border-collapse: collapse;
      margin: 14px 0;
      font-size: 12px;
    }
    .sop-table th {
      background: #f1f5f9;
      color: var(--slate-800);
      font-weight: 800;
      padding: 9px 12px;
      text-align: left;
      border: 1px solid var(--slate-300);
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.5px;
    }
    .sop-table td {
      padding: 9px 12px;
      border: 1px solid var(--slate-200);
      vertical-align: top;
      line-height: 1.5;
    }
    .sop-table tr:nth-child(even) td {
      background: #f8fafc;
    }

    /* Process Flow Container */
    .flow-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 14px;
      margin: 16px 0;
    }
    .flow-step-card {
      border: 1px solid var(--slate-200);
      border-radius: 10px;
      padding: 14px 16px;
      background: #ffffff;
      position: relative;
      box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }
    .flow-step-card .num-badge {
      position: absolute;
      top: -10px;
      left: 14px;
      background: var(--emerald);
      color: #ffffff;
      font-size: 11px;
      font-weight: 800;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .flow-step-card h5 {
      font-size: 13px;
      font-weight: 800;
      color: var(--slate-900);
      margin: 4px 0 6px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .flow-step-card p {
      font-size: 11.5px;
      color: var(--slate-600);
      line-height: 1.5;
      margin: 0;
    }
    .flow-step-card .meta {
      margin-top: 8px;
      font-size: 10.5px;
      font-weight: 700;
      color: var(--accent);
    }

    /* Badges */
    .badge-status {
      display: inline-block;
      padding: 2px 7px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 800;
      font-family: 'JetBrains Mono', monospace;
    }
    .badge-draft { background: #e2e8f0; color: #334155; }
    .badge-review { background: #fef3c7; color: #92400e; }
    .badge-final { background: #dcfce7; color: #15803d; }
    .badge-revisi { background: #fee2e2; color: #991b1b; }

    /* Callouts */
    .callout {
      padding: 12px 16px;
      border-radius: 8px;
      margin: 14px 0;
      font-size: 12px;
      border-left: 4px solid;
    }
    .callout-emerald { background: #ecfdf5; border-color: var(--emerald); color: #065f46; }
    .callout-amber { background: #fffbeb; border-color: var(--amber-600); color: #92400e; }
    .callout-blue { background: #f0f9ff; border-color: #0284c7; color: #0369a1; }

    /* Signature Section */
    .signature-row {
      display: flex;
      justify-content: flex-end;
      margin-top: 40px;
      page-break-inside: avoid;
    }
    .sig-box {
      text-align: center;
      width: 320px;
      font-size: 12px;
      line-height: 1.4;
    }
    .sig-space {
      height: 70px;
    }

    /* Print Overrides */
    @media print {
      body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 10pt !important;
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
      .flow-step-card, .comp-card {
        border-color: #999 !important;
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
        <i class="fa-solid fa-file-shield" style="color:var(--emerald);font-size:18px"></i>
        <span>Master Blueprint &amp; Dokumen SOP KKA Digital (Edisi Terpadu 2026)</span>
      </div>
    </div>
    <div class="actions">
      <a href="<?= url('dashboard') ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
      <a href="<?= asset('assets/docs/BLUEPRINT_WORKFLOW_KKA_DIGITAL_2026.pdf') ?>" download class="btn btn-download" title="Unduh Berkas PDF Resmi Siap Arsip">
        <i class="fa-solid fa-file-pdf"></i> Unduh File PDF
      </a>
      <button onclick="window.print()" class="btn btn-primary" title="Cetak atau Simpan sebagai PDF melalui dialog peramban">
        <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
      </button>
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
          Komplek Perkantoran Terpadu Bagansiapiapi, Batu Enam, Kabupaten Rokan Hilir, Provinsi Riau<br>
          Portal Resmi: <b>arsipdigital-inspektorat.com</b> · Email Resmi Pengawasan: <b>teamirban4@gmail.com</b>
        </p>
      </div>
      <div class="kop-logo">
        <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rohil">
      </div>
    </div>

    <!-- Judul Dokumen Master -->
    <div class="doc-title-block">
      <h1>Master Blueprint &amp; Standar Operasional Prosedur (SOP)</h1>
      <div class="doc-subtitle">Siklus Pengawasan Pengelolaan Keuangan Kepenghuluan (KKA Digital ADTT)</div>
      <div class="doc-meta">Nomor Registrasi: BP-SOP/KKA-QA/INSP-ROHIL/2026 · Berlaku Efektif: Tahun Anggaran 2026</div>
    </div>

    <!-- 1. APA ITU BLUEPRINT VS WORKFLOW? -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-circle-question"></i>
        <span>1. Pemahaman Konsep: Blueprint vs. Workflow</span>
      </div>
      <p>
        Dalam tata kelola sistem informasi pemerintahan modern (GovTech), sering kali timbul pertanyaan mengenai keterkaitan antara <b>Blueprint</b> dan <b>Workflow</b>:
      </p>

      <div class="comparison-grid">
        <div class="comp-card blueprint">
          <h4><i class="fa-solid fa-compass-drafting"></i> Blueprint (Cetak Biru Arsitektur)</h4>
          <p style="font-size:12px;margin-bottom:8px"><b>"The What &amp; How It Is Built"</b> — Gambar rancang bangun menyeluruh dari keseluruhan sistem:</p>
          <ul>
            <li><b>Fondasi Teknologi:</b> PHP 8.2+ Modern MVC, MySQL Database Relasional, Vanilla JS ES6+.</li>
            <li><b>Struktur Data:</b> Skema database, entitas relasional ID, integritas kunci asing.</li>
            <li><b>Tata Kelola Hak Akses:</b> Role-Based Access Control (RBAC) 8 tingkatan jabatan.</li>
            <li><b>Infrastruktur Cloud:</b> Integrasi Google Drive API &amp; sistem arsip otomatis.</li>
            <li><b>Standar Keamanan:</b> Protokol <i>Audit Data Freeze</i>, Anti-CSRF, dan Hashing password.</li>
          </ul>
        </div>

        <div class="comp-card workflow">
          <h4><i class="fa-solid fa-arrows-split-up-and-left"></i> Workflow (Alur Kerja Operasional)</h4>
          <p style="font-size:12px;margin-bottom:8px"><b>"The Who, When &amp; Step-by-Step Flow"</b> — Urutan langkah operasional pemeriksaan:</p>
          <ul>
            <li><b>Tahap 1:</b> Usulan Nota Dinas (ND) &amp; Disposisi Elektronik Inspektur.</li>
            <li><b>Tahap 2:</b> Registrasi Surat Tugas (SPT) ber-QR Code &amp; Matriks PKA 8 Langkah.</li>
            <li><b>Tahap 3:</b> Uji Fisik Kas (Opname Kas Desa) &amp; KKA 14 Format Pengujian SPJ.</li>
            <li><b>Tahap 4:</b> Reviu Berjenjang (Ketua Tim &rarr; Dalnis &rarr; Irban) &amp; KTP 5 Unsur.</li>
            <li><b>Tahap 5:</b> Penerbitan LHP Otomatis &amp; Pemantauan Tindak Lanjut (TLHP 60 Hari).</li>
          </ul>
        </div>
      </div>

      <div class="callout callout-emerald">
        <i class="fa-solid fa-circle-check"></i> <b>Kesimpulan Hubungan:</b> Workflow adalah bagian penting di dalam Blueprint. Blueprint mendefinisikan infrastruktur dan aturan sistem, sedangkan Workflow adalah pelaksanaan tahapan kerja nyata yang dijalankan oleh para auditor di dalam sistem tersebut.
      </div>
    </div>

    <!-- 2. LANDASAN HUKUM & STANDAR AUDIT -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-scale-balanced"></i>
        <span>2. Landasan Hukum &amp; Standar Pemeriksaan</span>
      </div>
      <p>Penyusunan KKA Digital dan tata kelola pengawasan ini berlandaskan pada regulasi resmi:</p>
      <ul style="margin-left: 20px; line-height: 1.7">
        <li><b>Undang-Undang Nomor 6 Tahun 2014</b> tentang Desa sebagaimana telah diubah terakhir dengan UU No. 3 Tahun 2024.</li>
        <li><b>Peraturan Pemerintah Nomor 12 Tahun 2017</b> tentang Pembinaan dan Pengawasan Penyelenggaraan Pemerintahan Daerah.</li>
        <li><b>Permendagri Nomor 73 Tahun 2020</b> tentang Pengawasan Pengelolaan Keuangan Desa oleh APIP.</li>
        <li><b>Standar Audit Intern Pemerintah Indonesia (SAIPI)</b> yang diterbitkan oleh AAIPI.</li>
        <li><b>Standar Pemeriksaan Keuangan Negara (SPKN)</b> Peraturan BPK-RI Nomor 1 Tahun 2017.</li>
      </ul>
    </div>

    <!-- 3. BLUEPRINT ARSITEKTUR TEKNOLOGI -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-server"></i>
        <span>3. Blueprint Arsitektur Sistem &amp; Keamanan Data</span>
      </div>
      <table class="sop-table">
        <thead>
          <tr>
            <th style="width: 25%">Komponen Arsitektur</th>
            <th style="width: 35%">Implementasi Teknologi</th>
            <th>Fungsi &amp; Jaminan Pengawasan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><b>Core Engine</b></td>
            <td>PHP 8.2+ Native High-Performance MVC Architecture</td>
            <td>Menjamin kecepatan eksekusi tinggi, ringan, zero external bloated dependencies, stabil di lingkungan server enterprise.</td>
          </tr>
          <tr>
            <td><b>Database Relasional</b></td>
            <td>MySQL / MariaDB Enterprise Engine dengan InnoDB</td>
            <td>Integritas referensial penuh antar tabel (Kepenghuluan, Sesi KKA, SPT, Opname Kas, Temuan, dan TLHP) melalui kunci asing relasional.</td>
          </tr>
          <tr>
            <td><b>Cloud Storage Sync</b></td>
            <td>Google Drive API v3 (Service Account &amp; OAuth 2.0)</td>
            <td>Pencadangan otomatis naskah LHP, BAP Opname Kas, dan matriks temuan ke akun resmi <code>teamirban4@gmail.com</code> secara terstruktur.</td>
          </tr>
          <tr>
            <td><b>Audit Data Freeze</b></td>
            <td>State Machine Penguncian Record Dinamis</td>
            <td>Membekukan angka rincian belanja secara absolut saat KKA diajukan ke Ketua Tim/Dalnis untuk mencegah manipulasi data di tengah proses pemeriksaan.</td>
          </tr>
          <tr>
            <td><b>Autentikasi &amp; Validasi</b></td>
            <td>Bcrypt Hash, CSRF Protection, Validasi TTE QR</td>
            <td>Login aman menggunakan NIP 18 digit atau username E-Reviu, serta verifikasi keaslian dokumen melalui pemindaian QR Code resmi.</td>
          </tr>
          <tr>
            <td><b>Antarmuka (UI/UX)</b></td>
            <td>GovTech Deep Emerald + AI-Style Collapsible Sidebar</td>
            <td>Desain modern prestisius, responsif mobile/desktop, serta fleksibilitas menyembunyikan menu (Ctrl+B) untuk area kerja tabel audit layar penuh.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 4. MATRIKS PERAN & WEWENANG (RBAC) -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-users-gear"></i>
        <span>4. Matriks Peran &amp; Kewenangan Pengguna (RBAC 8 Peran)</span>
      </div>
      <table class="sop-table">
        <thead>
          <tr>
            <th style="width: 20%">Peran / Pejabat</th>
            <th style="width: 25%">Tingkat Wewenang</th>
            <th>Kewenangan Utama dalam Sistem</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><b>Inspektur Daerah</b></td>
            <td>Pimpinan Tertinggi APIP (Top Executive)</td>
            <td>
              • Menelaah dan memberikan Lembar Disposisi Elektronik atas Nota Dinas (ND).<br>
              • Mengesahkan Surat Perintah Tugas (SPT) dan Naskah Final LHP.<br>
              • Memantau Dashboard Eksekutif real-time capaian pengawasan se-Kabupaten.
            </td>
          </tr>
          <tr>
            <td><b>Inspektur Pembantu (Irban IV)</b></td>
            <td>Penanggung Jawab Wilayah / Supervisi</td>
            <td>
              • Mengajukan usulan Nota Dinas pengawasan kepenghuluan binaan.<br>
              • Melakukan kendali mutu tingkat akhir naskah LHP dan tindak lanjut.<br>
              • Mengelola folder penyimpanan cloud resmi wilayah Irban IV.
            </td>
          </tr>
          <tr>
            <td><b>Bagian Perencanaan / Operator SPT</b></td>
            <td>Administrasi Penugasan APIP</td>
            <td>
              • Menerbitkan registrasi penomoran resmi Surat Tugas (SPT).<br>
              • Memetakan susunan tim pemeriksa (Dalnis, Ketua Tim, Anggota).
            </td>
          </tr>
          <tr>
            <td><b>Pengendali Teknis (Dalnis)</b></td>
            <td>Quality Assurance (Auditor Madya)</td>
            <td>
              • Menyetujui matriks Program Kerja Audit (PKA 8 langkah).<br>
              • Reviu Berjenjang Tingkat II: Menguji pemenuhan kriteria SAIPI &amp; SPKN.<br>
              • <b>Mengesahkan Sesi KKA secara Final</b> untuk dasar naskah LHP.
            </td>
          </tr>
          <tr>
            <td><b>Ketua Tim (KT)</b></td>
            <td>Quality Control (Auditor Muda)</td>
            <td>
              • Memimpin pelaksanaan pemeriksaan di lapangan.<br>
              • Reviu Berjenjang Tingkat I: Meneliti keabsahan kwitansi, SPP, dan uji fisik.<br>
              • Menyusun Konsep Temuan Pemeriksaan (KTP 5 Unsur) dan draf LHP.
            </td>
          </tr>
          <tr>
            <td><b>Anggota Tim / Auditor</b></td>
            <td>Pelaksana Lapangan (Auditor Terampil)</td>
            <td>
              • Mengisi Berita Acara Pemeriksaan Kas (Opname Kas Tunai &amp; Bank).<br>
              • Mengentri rincian belanja pada 14 format standar KKA fisik &amp; SPJ.<br>
              • Mengunggah bukti dokumen lampiran sah dan mengajukan reviu.
            </td>
          </tr>
          <tr>
            <td><b>Administrator Sistem</b></td>
            <td>Pemelihara Sistem &amp; Master Data</td>
            <td>
              • Memelihara master desa, kecamatan, dan akun pengguna.<br>
              • Manajemen backup database dan konektivitas API cloud.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 5. WORKFLOW LENGKAP 5 PILAR AUDIT -->
    <div class="section">
      <div class="section-head">
        <i class="fa-solid fa-route"></i>
        <span>5. Alur Kerja Komprehensif (5 Pilar Siklus Pengawasan Terpadu)</span>
      </div>
      <p>
        Sistem mengintegrasikan seluruh siklus pengawasan desa mulai dari pra-audit hingga penyelesaian kerugian desa tanpa putus (*end-to-end*):
      </p>

      <div class="flow-grid">
        <!-- Pilar 1 -->
        <div class="flow-step-card" style="border-top: 3.5px solid #0284c7">
          <div class="num-badge" style="background:#0284c7">1</div>
          <h5><i class="fa-solid fa-file-signature" style="color:#0284c7"></i> Pra-Audit &amp; Penugasan</h5>
          <p>
            • Irban IV menyusun Nota Dinas (ND) rencana audit.<br>
            • Inspektur memberikan disposisi elektronik.<br>
            • Terbit Surat Perintah Tugas (SPT) resmi ber-QR.<br>
            • Dalnis mengesahkan Matriks Program Kerja Audit (PKA).
          </p>
          <div class="meta">Output: Dokumen SPT Sah &amp; Matriks PKA</div>
        </div>

        <!-- Pilar 2 -->
        <div class="flow-step-card" style="border-top: 3.5px solid var(--emerald)">
          <div class="num-badge" style="background:var(--emerald)">2</div>
          <h5><i class="fa-solid fa-money-bill-transfer" style="color:var(--emerald)"></i> Pemeriksaan Kas (Opname)</h5>
          <p>
            • Uji fisik uang tunai di brankas bendahara.<br>
            • Rekonsiliasi rekening koran Bank Riau Kepri.<br>
            • Pencocokan Saldo Buku Kas Umum (BKU).<br>
            • Penerbitan Berita Acara Pemeriksaan Kas (BAP) resmi.
          </p>
          <div class="meta">Output: BAP Kas &amp; Deteksi Tekor Kas</div>
        </div>

        <!-- Pilar 3 -->
        <div class="flow-step-card" style="border-top: 3.5px solid #4f46e5">
          <div class="num-badge" style="background:#4f46e5">3</div>
          <h5><i class="fa-solid fa-clipboard-check" style="color:#4f46e5"></i> Uji KKA &amp; Reviu Berjenjang</h5>
          <p>
            • Entri rincian belanja APBDes pada 14 format standar.<br>
            • Uji fisik volume proyek, kwitansi, dan kepatuhan pajak.<br>
            • Data Freeze otomatis saat diajukan ke Ketua Tim.<br>
            • Telaah Ketua Tim &rarr; Pengesahan Final oleh Dalnis.
          </p>
          <div class="meta">Output: KKA Sah Berjenjang</div>
        </div>

        <!-- Pilar 4 -->
        <div class="flow-step-card" style="border-top: 3.5px solid var(--accent)">
          <div class="num-badge" style="background:var(--accent)">4</div>
          <h5><i class="fa-solid fa-file-shield" style="color:var(--accent)"></i> Temuan (KTP) &amp; Naskah LHP</h5>
          <p>
            • Perumusan Temuan KTP (Kondisi, Kriteria, Sebab, Akibat, Rekomendasi).<br>
            • Sistem otomatis merangkum data menjadi Naskah LHP Bab I s.d IV.<br>
            • Format standar Bookman Old Style Rohil siap cetak.<br>
            • Sinkronisasi otomatis file PDF ke Google Drive resmi.
          </p>
          <div class="meta">Output: LHP Final &amp; Backup Drive</div>
        </div>

        <!-- Pilar 5 -->
        <div class="flow-step-card" style="border-top: 3.5px solid #dc2626">
          <div class="num-badge" style="background:#dc2626">5</div>
          <h5><i class="fa-solid fa-clock-rotate-left" style="color:#dc2626"></i> Tindak Lanjut (TLHP 60 Hari)</h5>
          <p>
            • Countdown otomatis batas waktu 60 hari kalender.<br>
            • Pencatatan setoran pengembalian kas desa via STS.<br>
            • Pemantauan Recovery Rate (% pemulihan kas desa).<br>
            • Verifikasi APIP: Sesuai, Belum Sesuai, atau Tuntas.
          </p>
          <div class="meta">Output: Kas Desa Pulih 100%</div>
        </div>
      </div>
    </div>

    <!-- 6. PROTOKOL PENYIMPANAN CLOUD GOOGLE DRIVE -->
    <div class="section">
      <div class="section-head">
        <i class="fa-brands fa-google-drive"></i>
        <span>6. Standar Struktur Folder Google Drive Resmi APIP</span>
      </div>
      <p>
        Setiap berkas audit yang disahkan otomatis tersinkronisasi ke Google Drive resmi <code>teamirban4@gmail.com</code> dengan struktur hierarki folder baku:
      </p>

      <div style="background:#f8fafc;border:1px solid var(--slate-200);border-radius:8px;padding:14px 18px;font-family:'JetBrains Mono',monospace;font-size:11.5px;line-height:1.7;color:var(--slate-800)">
        📁 <b>KKA_DIGITAL_ROHIL</b> [Folder Induk Utama]<br>
        &nbsp;&nbsp;└── 📁 <b>01_IRBAN_IV</b> [Wilayah Inspektur Pembantu IV]<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📁 <b>TA 2026</b> [Tahun Anggaran Berjalan]<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📁 <b>Kepenghuluan_Bantaian</b><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📁 <b>01_DOKUMEN_PERSIAPAN</b> (Nota Dinas &amp; SPT Sah)<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📁 <b>02_OPNAME_KAS</b> (BAP Pemeriksaan Kas Fisik &amp; Bank)<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📁 <b>03_KKA_RINCIAN</b> (Berkas Kertas Kerja &amp; Bukti SPJ)<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📁 <b>04_LHP_FINAL</b> (Naskah Laporan Hasil Pengawasan PDF)<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📁 <b>05_TINDAK_LANJUT</b> (Bukti Setor STS &amp; Rekomendasi)
      </div>
    </div>

    <!-- 7. LEMBAR PENGESAHAN DOKUMEN -->
    <div class="signature-row">
      <div class="sig-box">
        <div>Ditetapkan di: Bagansiapiapi</div>
        <div>Pada tanggal: <?= tgl_id(date('Y-m-d')) ?></div>
        <div style="font-weight:700;margin-top:6px">INSPEKTUR DAERAH<br>KABUPATEN ROKAN HILIR</div>
        <div class="sig-space"></div>
        <div style="font-weight:800;text-decoration:underline">H. SARMAN SYAHRONI, ST., M.IP., CGCAE</div>
        <div style="font-size:11.5px;color:var(--slate-600)">Pembina Utama Muda / IV.c</div>
        <div style="font-size:11.5px;color:var(--slate-600)">NIP. 19790615 200212 1 007</div>
      </div>
    </div>

  </div>

</body>
</html>
