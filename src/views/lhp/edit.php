<?php
partial('head', ['title' => 'Edit Narasi Naskah LHP - Kepenghuluan ' . $desa['nama']]);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-pen-to-square" style="color:#059669"></i>
          Kustomisasi Narasi Naskah LHP
        </h2>
        <p>Edit kalimat pengantar, ruang lingkup, gambaran umum, serta kesimpulan naskah LHP Kepenghuluan <?= e($desa['nama']) ?> TA <?= $tahun ?>.</p>
      </div>
      <div style="display:flex;gap:10px">
        <a href="<?= url('lhp/show?desa_id=' . $desa['id'] . '&tahun=' . $tahun) ?>" class="btn btn-outline">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Pratinjau
        </a>
      </div>
    </div>

    <!-- Info Singkat Angka Dinamis -->
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px;background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;font-size:12.5px;color:#334155">
      <div><b>Kepenghuluan:</b> <?= e($desa['nama']) ?> (Kec. <?= e($desa['kecamatan_nama']) ?>)</div>
      <div>&bull;</div>
      <div><b>Nomor SPT:</b> <?= e($spt['no_spt'] ?? '-') ?></div>
      <div>&bull;</div>
      <div><b>Total Belanja Diuji:</b> <span style="font-weight:700;color:#0369a1"><?= rupiah($totalRealisasi) ?></span></div>
      <div>&bull;</div>
      <div><b>Temuan Fisik/SPJ:</b> <span style="font-weight:700;color:#dc2626"><?= count($daftarTemuan) ?> Butir (<?= rupiah($totalNominalTemuan) ?>)</span></div>
      <?php if (!empty($narasiFinal['is_customized'])): ?>
        <div style="margin-left:auto">
          <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700">
            <i class="fa-solid fa-check"></i> Narasi Telah Dikustomisasi
          </span>
        </div>
      <?php endif; ?>
    </div>

    <form method="POST" action="<?= url('lhp/update') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="desa_id" value="<?= $desa['id'] ?>">
      <input type="hidden" name="tahun_anggaran" value="<?= $tahun ?>">

      <!-- RINGKASAN EKSEKUTIF -->
      <div class="card" style="padding:22px;margin-bottom:20px">
        <h3 style="margin:0 0 8px;font-size:14px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-file-lines" style="color:#059669"></i>
          RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)
        </h3>
        <p style="margin:0 0 12px;font-size:12px;color:var(--slate-500)">
          Paragraf ikhtisar penugasan dan gambaran singkat hasil pengawasan untuk Pimpinan Daerah (Bupati &amp; Inspektur).
        </p>
        <textarea name="ringkasan_eksekutif" class="form-control" rows="5" style="line-height:1.6;font-size:13px" required><?= e($narasiFinal['ringkasan_eksekutif'] ?? '') ?></textarea>
      </div>

      <!-- BAB I -->
      <div class="card" style="padding:22px;margin-bottom:20px">
        <h3 style="margin:0 0 8px;font-size:14px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-book-open" style="color:#0284c7"></i>
          BAB I : INFORMASI UMUM PENUGASAN
        </h3>
        <p style="margin:0 0 16px;font-size:12px;color:var(--slate-500)">
          Kustomisasi dasar penugasan, tujuan pemeriksaan, serta ruang lingkup dan batasan pengawasan APIP.
        </p>

        <div style="display:grid;gap:16px">
          <div>
            <label class="form-label" style="font-weight:700">1. Dasar Penugasan</label>
            <textarea name="dasar_penugasan" class="form-control" rows="3" style="line-height:1.5;font-size:13px"><?= e($narasiFinal['dasar_penugasan'] ?? '') ?></textarea>
          </div>

          <div>
            <label class="form-label" style="font-weight:700">2. Tujuan Pengawasan / Audit</label>
            <textarea name="tujuan_pengawasan" class="form-control" rows="2" style="line-height:1.5;font-size:13px"><?= e($narasiFinal['tujuan_pengawasan'] ?? '') ?></textarea>
          </div>

          <div>
            <label class="form-label" style="font-weight:700">3. Ruang Lingkup Pengawasan</label>
            <textarea name="ruang_lingkup" class="form-control" rows="2" style="line-height:1.5;font-size:13px"><?= e($narasiFinal['ruang_lingkup'] ?? '') ?></textarea>
          </div>

          <div>
            <label class="form-label" style="font-weight:700">4. Batasan Tanggung Jawab &amp; Pengawasan</label>
            <textarea name="batasan_pengawasan" class="form-control" rows="2" style="line-height:1.5;font-size:13px"><?= e($narasiFinal['batasan_pengawasan'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- BAB II -->
      <div class="card" style="padding:22px;margin-bottom:20px">
        <h3 style="margin:0 0 8px;font-size:14px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-chart-pie" style="color:#7c3aed"></i>
          BAB II : GAMBARAN PENGELOLAAN KEUANGAN KEPENGHULUAN
        </h3>
        <p style="margin:0 0 12px;font-size:12px;color:var(--slate-500)">
          Kalimat pengantar sebelum tabel rincian belanja per bidang dan evaluasi kepatuhan pajak.
        </p>
        <textarea name="gambaran_umum" class="form-control" rows="3" style="line-height:1.5;font-size:13px"><?= e($narasiFinal['gambaran_umum'] ?? '') ?></textarea>
      </div>

      <!-- BAB IV -->
      <div class="card" style="padding:22px;margin-bottom:20px">
        <h3 style="margin:0 0 8px;font-size:14px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-flag-checkered" style="color:#d97706"></i>
          BAB IV : KESIMPULAN &amp; SARAN PENUTUP
        </h3>
        <p style="margin:0 0 16px;font-size:12px;color:var(--slate-500)">
          Pernyataan kesimpulan umum tim pemeriksa dan kewajiban tindak lanjut 60 hari oleh auditi.
        </p>

        <div style="display:grid;gap:16px">
          <div>
            <label class="form-label" style="font-weight:700">Kesimpulan Tim Pemeriksa APIP</label>
            <textarea name="kesimpulan" class="form-control" rows="4" style="line-height:1.6;font-size:13px"><?= e($narasiFinal['kesimpulan'] ?? '') ?></textarea>
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Saran &amp; Rekomendasi Penutup (Langkah Prioritas)</label>
            <textarea name="saran_penutup" class="form-control" rows="3" style="line-height:1.5;font-size:13px"><?= e($narasiFinal['saran_penutup'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- TOMBOL SIMPAN -->
      <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:40px">
        <a href="<?= url('lhp/show?desa_id=' . $desa['id'] . '&tahun=' . $tahun) ?>" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary" style="background:#059669;border-color:#059669;padding:10px 24px;font-size:14px;font-weight:700">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Narasi LHP
        </button>
      </div>

    </form>
  </div>
</main>

<?php partial('foot'); ?>
