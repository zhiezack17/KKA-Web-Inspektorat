<?php
partial('head', ['title' => 'Buat Konsep Temuan Pemeriksaan (KTP 5 Unsur)']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-file-circle-plus" style="color:#d97706"></i>
          Penyusunan Konsep Temuan Pemeriksaan (KTP)
        </h2>
        <p>Pengisian 5 Unsur Standar SPKN BPK-RI / Kendali Mutu BPKP untuk Bahan Naskah Hasil Pengawasan (NHP) &amp; LHP.</p>
      </div>
      <a href="<?= url('temuan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Temuan
      </a>
    </div>

    <div class="card" style="max-width:980px;margin:0 auto">
      <form method="post" action="<?= url('temuan/store') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="sesi_id" value="<?= $sesiId ?>">
        <input type="hidden" name="rincian_id" value="<?= $rincianId ?>">

        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#92400e;display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-circle-info" style="font-size:18px"></i>
          <div>
            Format ini telah disesuaikan dengan <b>5 Unsur Standar Pemeriksaan Keuangan Negara (SPKN)</b>: <i>Kondisi, Kriteria, Sebab, Akibat, dan Rekomendasi</i>.
            Data ini akan otomatis dirangkum ke dalam Dokumen LHP Desa resmi.
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:14px;margin-bottom:14px">
          <div class="field">
            <label>Desa / Kepenghuluan Objek Pemeriksaan <span class="req">*</span></label>
            <select name="desa_id" required class="input">
              <option value="">-- Pilih Kepenghuluan --</option>
              <?php foreach ($daftarDesa as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $desaId == $d['id'] ? 'selected' : '' ?>>
                  <?= e($d['nama']) ?> (Kec. <?= e($d['kecamatan']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Tahun Anggaran <span class="req">*</span></label>
            <input type="number" name="tahun_anggaran" class="input" value="<?= $tahun ?>" required>
          </div>

          <div class="field">
            <label>Nomor / Kode KTP <span class="req">*</span></label>
            <input type="text" name="nomor_temuan" class="input" value="<?= e($nomorTemuan) ?>" required placeholder="KTP-01">
          </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <label style="font-weight:700;color:var(--slate-800);margin:0">Pokok / Judul Temuan <span class="req">*</span></label>
          <button type="button" class="btn btn-sm" onclick="openBankTemuanModal()" style="background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%);color:#fff;font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 6px rgba(2,132,199,0.3)">
            <i class="fa-solid fa-book-bookmark"></i> Ambil dari Bank Temuan BPKP
          </button>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:14px;margin-bottom:18px">
          <div class="field" style="margin:0">
            <input type="text" name="judul" id="f_judul" class="input" value="<?= e($autoJudul) ?>" required placeholder="Cth: Belanja Semenisasi Sebesar Rp... Tidak Didukung Bukti Kuitansi Sah">
          </div>

          <div class="field" style="margin:0">
            <input type="text" name="bidang_nama" id="f_bidang_nama" class="input" value="<?= e($bidangNama) ?>" placeholder="Cth: Bidang 2: Pembangunan Desa">
          </div>

          <div class="field" style="margin:0">
            <input type="text" name="nominal" id="f_nominal" class="input" data-money value="<?= number_format((float)$autoNominal, 0, ',', '.') ?>" placeholder="0">
          </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid #f1f5f9;padding-bottom:6px;margin:24px 0 16px">
          <div style="font-weight:700;color:var(--slate-800);font-size:14px;text-transform:uppercase;letter-spacing:0.5px">
            5 UNSUR TEMUAN PEMERIKSAAN
          </div>
          <span style="font-size:12px;color:var(--slate-500)">Format Standar SPKN BPKP</span>
        </div>

        <!-- 1. KONDISI -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">1</span>
            Kondisi (Fakta / Apa yang Terjadi di Lapangan) <span class="req">*</span>
          </label>
          <textarea name="kondisi" id="f_kondisi" class="textarea" rows="4" required placeholder="Uraikan fakta hasil pemeriksaan fisik, dokumen SPJ, selisih kas, atau ketidaksesuaian yang ditemukan..."><?= e($autoKondisi) ?></textarea>
          <small style="color:var(--slate-500);font-size:11.5px">Paparkan angka realisasi, kuitansi, tanggal pengujian, dan pihak-pihak terkait secara objektif.</small>
        </div>

        <!-- 2. KRITERIA -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">2</span>
            Kriteria (Ketentuan Perundang-Undangan / Standar yang Dilanggar) <span class="req">*</span>
          </label>
          <textarea name="kriteria" id="f_kriteria" class="textarea" rows="3" required placeholder="Sebutkan pasal dan undang-undang/peraturan yang dilanggar (cth: Permendagri 20/2018, Perbup Rohil, RAB, dsb)..."><?= e($autoKriteria ?: "1. Permendagri Nomor 20 Tahun 2018 tentang Pengelolaan Keuangan Desa;\n2. Peraturan Bupati Rokan Hilir tentang Petunjuk Teknis Pengelolaan Keuangan Kepenghuluan.") ?></textarea>
        </div>

        <!-- 3. SEBAB -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">3</span>
            Sebab (Mengapa Kondisi Tersebut Terjadi / Kelemahan Sistem Pengendalian) <span class="req">*</span>
          </label>
          <textarea name="sebab" id="f_sebab" class="textarea" rows="3" required placeholder="Jelaskan alasan mengapa kondisi ini terjadi (cth: kelalaian bendahara, kurangnya pengendalian Penghulu, rekanan belum setor)..."><?= e($autoSebab ?: "1. Kelalaian Kaur Keuangan/Bendahara dalam melengkapi bukti pertanggungjawaban secara tertib;\n2. Kurangnya pengawasan dan pengendalian dari Pj. Penghulu atas pengeluaran kas desa.") ?></textarea>
        </div>

        <!-- 4. AKIBAT -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">4</span>
            Akibat (Dampak Finansial / Risiko Kerugian Kas Desa / Risiko Hukum) <span class="req">*</span>
          </label>
          <textarea name="akibat" id="f_akibat" class="textarea" rows="3" required placeholder="Dampak nyata atau potensi kerugian bagi keuangan kepenghuluan atau kepentingan masyarakat..."><?= e($autoAkibat) ?></textarea>
        </div>

        <!-- 5. REKOMENDASI -->
        <div class="field" style="margin-bottom:18px">
          <label style="font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:6px">
            <span class="badge" style="background:#0284c7;color:#fff">5</span>
            Rekomendasi (Perintah Tindak Lanjut Perbaikan / Pengembalian Kas) <span class="req">*</span>
          </label>
          <textarea name="rekomendasi" id="f_rekomendasi" class="textarea" rows="4" required placeholder="Uraikan rekomendasi konkret kepada Bupati / Camat / Penghulu (cth: teguran tertulis, setor ke Rekening Kas Desa, perbaiki fisik)..."><?= e($autoRekomendasi ?: "1. Memerintahkan Pj. Penghulu untuk memberikan teguran tertulis kepada aparatur terkait;\n2. Menginstruksikan kepada pihak yang bertanggung jawab untuk segera menyetorkan kembali dana/selisih ke Rekening Kas Desa (RKD).") ?></textarea>
        </div>

        <!-- TANGGAPAN AUDITI & STATUS -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:20px">
          <div style="font-weight:700;color:var(--slate-700);font-size:13px;margin-bottom:12px">
            KLARIFIKASI &amp; STATUS PEMBAHASAN
          </div>
          <div class="field" style="margin-bottom:12px">
            <label>Tanggapan / Klarifikasi dari Pihak Auditi (Penghulu / Kaur Keuangan)</label>
            <textarea name="tanggapan_auditi" class="textarea" rows="3" placeholder="Masukkan penjelasan auditi saat pembahasan Konsep Temuan (bila sudah ada)..."></textarea>
          </div>
          <div class="field" style="margin:0;max-width:300px">
            <label>Status Temuan</label>
            <select name="status" class="input">
              <option value="DRAFT">DRAFT (Penyusunan Tim)</option>
              <option value="DIBAHAS">DIBAHAS (Sudah dikonfirmasi ke Desa)</option>
              <option value="FINAL_LHP">FINAL (Siap Masuk ke Naskah LHP)</option>
            </select>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px">
          <a href="<?= url('temuan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" class="btn btn-ghost">Batal</a>
          <button type="submit" class="btn btn-primary" style="background:#d97706;border-color:#d97706;padding:10px 24px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Konsep Temuan
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<!-- MODAL BANK TEMUAN BPKP -->
<div id="modalBankTemuan" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:9999;backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:14px;max-width:920px;width:100%;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);overflow:hidden">
    <!-- Modal Header -->
    <div style="padding:16px 22px;background:linear-gradient(135deg, #0284c7 0%, #0369a1 100%);color:#fff;display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:10px">
        <i class="fa-solid fa-book-bookmark" style="font-size:20px"></i>
        <div>
          <h3 style="margin:0;font-size:16px;font-weight:700">Bank Temuan Standar BPKP &amp; Inspektorat</h3>
          <p style="margin:2px 0 0;font-size:11.5px;opacity:0.9">Pilih template temuan resmi untuk mengisi otomatis 5 Unsur Standar SPKN.</p>
        </div>
      </div>
      <button type="button" onclick="closeBankTemuanModal()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;opacity:0.85">&times;</button>
    </div>

    <!-- Filter & Search Bar -->
    <div style="padding:14px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;flex-direction:column;gap:10px">
      <div style="position:relative">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:11px;color:#94a3b8;font-size:13px"></i>
        <input type="text" id="bankSearchInput" onkeyup="filterBankTemuan()" placeholder="Cari judul temuan, kode BPKP, atau kata kunci..." class="input" style="padding-left:36px;height:38px;font-size:13px;background:#fff">
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap" id="bankCatPills">
        <button type="button" class="bank-pill active" onclick="setBankCat('ALL', this)">Semua Kategori</button>
        <button type="button" class="bank-pill" onclick="setBankCat('PEKERJAAN FISIK', this)">Fisik Konstruksi</button>
        <button type="button" class="bank-pill" onclick="setBankCat('PERPAJAKAN', this)">Perpajakan</button>
        <button type="button" class="bank-pill" onclick="setBankCat('KAS & KEUANGAN', this)">Kas &amp; Keuangan</button>
        <button type="button" class="bank-pill" onclick="setBankCat('PERTANGGUNGJAWABAN SPJ', this)">SPJ Belanja</button>
        <button type="button" class="bank-pill" onclick="setBankCat('ASET DESA', this)">Aset Desa</button>
        <button type="button" class="bank-pill" onclick="setBankCat('PENGADAAN & KEMAHALAN HARGA', this)">Kemahalan / PBJ</button>
      </div>
    </div>

    <!-- Template List -->
    <div style="padding:16px 22px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:12px" id="bankListContainer">
      <?php foreach (($bankTemuan ?? []) as $bt): ?>
        <div class="bank-item" data-kategori="<?= e($bt['kategori']) ?>" data-search="<?= strtolower(e($bt['judul'] . ' ' . $bt['kode'] . ' ' . $bt['kategori'])) ?>" style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#fff;transition:all 0.15s ease">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px">
            <div>
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                <span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:700;font-size:11px">Kode: <?= e($bt['kode']) ?></span>
                <span class="badge" style="background:#f1f5f9;color:#475569;font-size:11px"><?= e($bt['kategori']) ?></span>
              </div>
              <h4 style="margin:0;font-size:14px;font-weight:700;color:var(--slate-800);line-height:1.4"><?= e($bt['judul']) ?></h4>
            </div>
            <button type="button" onclick="applyBankTemplate(<?= htmlspecialchars(json_encode($bt), ENT_QUOTES, 'UTF-8') ?>)" class="btn btn-sm" style="background:#0284c7;color:#fff;border:none;padding:6px 14px;font-size:12px;font-weight:700;border-radius:6px;cursor:pointer;white-space:nowrap;flex-shrink:0">
              <i class="fa-solid fa-check"></i> Gunakan Template
            </button>
          </div>
          <div style="font-size:12px;color:var(--slate-600);line-height:1.5;background:#f8fafc;padding:8px 10px;border-radius:6px;border:1px solid #f1f5f9">
            <strong>Kondisi Singkat:</strong> <?= e(mb_strimwidth($bt['kondisi'], 0, 160, '...')) ?><br>
            <strong>Rekomendasi Utama:</strong> <?= e(mb_strimwidth($bt['rekomendasi'], 0, 140, '...')) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Modal Footer -->
    <div style="padding:12px 22px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--slate-500)">
      <span>Total <b><?= count($bankTemuan ?? []) ?></b> Template Standar Tersedia</span>
      <button type="button" onclick="closeBankTemuanModal()" class="btn btn-ghost" style="padding:6px 14px">Tutup</button>
    </div>
  </div>
</div>

<style>
.bank-pill {
  background: #fff;
  border: 1px solid #cbd5e1;
  color: #475569;
  font-size: 11.5px;
  padding: 4px 10px;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.bank-pill:hover {
  background: #f1f5f9;
}
.bank-pill.active {
  background: #0284c7;
  color: #fff;
  border-color: #0284c7;
  font-weight: 700;
}
.bank-item:hover {
  border-color: #0284c7 !important;
  box-shadow: 0 4px 12px rgba(2,132,199,0.08);
}
.pulse-highlight {
  animation: pulseGreen 1.2s ease;
}
@keyframes pulseGreen {
  0% { background-color: #dcfce7; }
  100% { background-color: transparent; }
}
</style>

<script>
let currentBankCat = 'ALL';

function openBankTemuanModal() {
  document.getElementById('modalBankTemuan').style.display = 'flex';
  document.getElementById('bankSearchInput').focus();
}

function closeBankTemuanModal() {
  document.getElementById('modalBankTemuan').style.display = 'none';
}

function setBankCat(cat, btn) {
  currentBankCat = cat;
  document.querySelectorAll('#bankCatPills .bank-pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  filterBankTemuan();
}

function filterBankTemuan() {
  const q = document.getElementById('bankSearchInput').value.toLowerCase().trim();
  const items = document.querySelectorAll('#bankListContainer .bank-item');

  items.forEach(el => {
    const itemCat = el.getAttribute('data-kategori');
    const itemSearch = el.getAttribute('data-search');

    const matchCat = (currentBankCat === 'ALL' || itemCat.toUpperCase().includes(currentBankCat));
    const matchQuery = (!q || itemSearch.includes(q));

    if (matchCat && matchQuery) {
      el.style.display = 'block';
    } else {
      el.style.display = 'none';
    }
  });
}

function applyBankTemplate(tpl) {
  // Ambil nama desa yang sedang dipilih di form
  const selDesa = document.querySelector('select[name="desa_id"]');
  let desaNama = 'Kepenghuluan';
  if (selDesa && selDesa.selectedIndex > 0) {
    desaNama = selDesa.options[selDesa.selectedIndex].text.split('(')[0].trim();
  }

  // Ganti placeholder {DESA} dengan nama desa riil
  const replaceDesa = (str) => {
    if (!str) return '';
    return str.replaceAll('{DESA}', desaNama);
  };

  document.getElementById('f_judul').value = replaceDesa(tpl.judul);
  document.getElementById('f_kondisi').value = replaceDesa(tpl.kondisi);
  document.getElementById('f_kriteria').value = tpl.kriteria || '';
  document.getElementById('f_sebab').value = tpl.sebab || '';
  document.getElementById('f_akibat').value = replaceDesa(tpl.akibat);
  document.getElementById('f_rekomendasi').value = replaceDesa(tpl.rekomendasi);

  if (tpl.kategori && !document.getElementById('f_bidang_nama').value) {
    document.getElementById('f_bidang_nama').value = tpl.kategori;
  }

  // Efek highlight visual
  ['f_judul', 'f_kondisi', 'f_kriteria', 'f_sebab', 'f_akibat', 'f_rekomendasi'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.classList.add('pulse-highlight');
      setTimeout(() => el.classList.remove('pulse-highlight'), 1200);
    }
  });

  closeBankTemuanModal();
  document.getElementById('f_nominal').focus();
}

// Tutup modal jika klik di luar
window.addEventListener('click', function(e) {
  const modal = document.getElementById('modalBankTemuan');
  if (e.target === modal) {
    closeBankTemuanModal();
  }
});
</script>

<?php partial('foot'); ?>
