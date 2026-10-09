<?php $title = 'Form KKA & DPP PIA'; $current = '/pia/lhp'; partial('head', compact('title')); ?>
<?php partial('sidebar', compact('current')); ?>
<main class="main">
<?php partial('topbar', ['title' => 'Isi KKA & DPP PIA', 'icon' => 'fa-solid fa-pen-to-square']); ?>
<div class="content">
<?php partial('flash'); ?>
<div class="page-head" style="display:flex;justify-content:space-between;align-items:center">
  <div>
    <a href="<?= url('pia/lhp') ?>" class="btn btn-sm btn-secondary" style="margin-bottom:8px"><i class="fa-solid fa-arrow-left mr-2"></i>Kembali</a>
    <h2>Isi KKA & DPP PIA</h2>
    <p>SPT: <b><?= e($spt['no_spt']) ?></b> (<?= e($spt['desa_nama']) ?>)</p>
  </div>
  <?php if (!empty($lhp['id'])): ?>
    <a href="<?= url('pia/print-lhp?spt_id=' . $spt['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
      <i class="fa-solid fa-print mr-2"></i>Cetak LHP & DPP
    </a>
  <?php endif; ?>
</div>
<form action="<?= url('pia/lhp/store') ?>" method="POST" class="card p-6">
  <?= csrf_field() ?>
  <input type="hidden" name="spt_id" value="<?= $spt['id'] ?>">
  <input type="hidden" name="nota_dinas_id" value="<?= $spt['nota_dinas_id'] ?? ($spt['pia_nd_id'] ?? '') ?>">
  
  <h3 style="font-size:16px;font-weight:bold;margin-bottom:12px;border-bottom:1px solid #ccc">A. KERTAS KERJA AUDIT (KKA) PIA</h3>
  <div style="margin-bottom:20px">
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Daftar Regulasi & Juknis Terkait</label>
      <textarea name="daftar_regulasi" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="3" placeholder="Sebutkan Perbup/Permendes/UU yang relevan..."><?= e($lhp['daftar_regulasi'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Indikasi Masalah / Informasi Awal</label>
      <textarea name="indikasi_masalah" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="3" placeholder="Pengaduan masyarakat, temuan media, anomali data..."><?= e($lhp['indikasi_masalah'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Titik Kritis Risiko (Risk Points)</label>
      <textarea name="titik_kritis_risiko" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="3" placeholder="Identifikasi letak kelemahan pengendalian atau potensi fraud..."><?= e($lhp['titik_kritis_risiko'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Ceklis Pengumpulan Data Tambahan</label>
      <textarea name="ceklis_data_tambahan" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="3" placeholder="Daftar dokumen yang harus disiapkan entitas (APBDes, SPJ, dll)..."><?= e($lhp['ceklis_data_tambahan'] ?? '') ?></textarea>
    </div>
  </div>

  <h3 style="font-size:16px;font-weight:bold;margin-bottom:12px;border-bottom:1px solid #ccc">B. LAPORAN HASIL PENELAAHAN & DESAIN PENUGASAN (DPP)</h3>
  <div style="margin-bottom:20px">
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Hasil Penelaahan</label>
      <textarea name="hasil_penelaahan" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="4" placeholder="Rangkuman hasil analisis informasi awal..."><?= e($lhp['hasil_penelaahan'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Simpulan & Rekomendasi PIA</label>
      <textarea name="simpulan_rekomendasi" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="3" placeholder="Kesimpulan kelayakan untuk diaudit..."><?= e($lhp['simpulan_rekomendasi'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Sasaran Pengawasan (Jika Lanjut Audit)</label>
      <textarea name="dpp_sasaran" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="3" placeholder="Tentative Objective / Sasaran... "><?= e($lhp['dpp_sasaran'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom: 20px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px;">Metodologi & Langkah Kerja (Jika Lanjut Audit)</label>
      <textarea name="dpp_metodologi" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;" rows="3" placeholder="Wawancara, Cek Fisik, Analisis Dokumen..."><?= e($lhp['dpp_metodologi'] ?? '') ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom: 20px; background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:12px;">
      <label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px; color:#b45309;">
        <i class="fa-solid fa-triangle-exclamation"></i> Hambatan / Kendala di Lapangan
      </label>
      <textarea name="kendala_lapangan" class="form-control" style="width: 100%; border: 1px solid #fcd34d; border-radius: 4px; padding: 8px; font-family: inherit; background:#fff" rows="3" placeholder="Tuliskan hambatan/kendala saat penelaahan/lapangan (misal: perangkat desa sulit dihubungi, dokumen SPJ belum lengkap, cuaca/akses medan, dll)..."><?= e($lhp['kendala_lapangan'] ?? '') ?></textarea>
      <small style="color:#78350f;font-size:11.5px;margin-top:4px;display:block">Catatan hambatan ini akan langsung dapat dipantau oleh Irban dan Inspektur.</small>
    </div>
    <div class="form-group" style="margin-bottom: 20px; padding: 15px; background: #fff5f5; border-left: 4px solid #ef4444; border-radius: 4px;"><label class="form-label" style="display: block; font-weight: bold; margin-bottom: 8px; color: #b91c1c;">Keputusan Final Inspektur</label>
      <select name="keputusan_inspektur" class="form-control" style="width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 8px; font-family: inherit;">
        <option value="BELUM_DIPUTUSKAN" <?= ($lhp['keputusan_inspektur'] ?? '') == 'BELUM_DIPUTUSKAN' ? 'selected' : '' ?>>Belum Diputuskan</option>
        <option value="LAYAK_AUDIT" <?= ($lhp['keputusan_inspektur'] ?? '') == 'LAYAK_AUDIT' ? 'selected' : '' ?>>Go (Layak Audit / Terbitkan ND ADTT)</option>
        <option value="ARSIP" <?= ($lhp['keputusan_inspektur'] ?? '') == 'ARSIP' ? 'selected' : '' ?>>No-Go (Arsipkan / Tidak Cukup Bukti)</option>
      </select>
    </div>
  </div>
  
  <div style="text-align:right">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save mr-2"></i>Simpan KKA & DPP</button>
  </div>
</form>
</div></main>
<?php partial('foot'); ?>
