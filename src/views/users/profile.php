<?php $title = 'Profil Saya - KKA'; $u = $auth->user(); ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>
<main class="main">
  <div class="topbar"><div class="crumb"><i class="fa-solid fa-user"></i> <b>Profil Saya</b></div></div>
  <div class="content">
    <?php partial('flash'); ?>

    <?php if (!empty($_SESSION['must_change_password'])): ?>
      <div style="background:linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);border:1px solid #fecdd3;border-left:5px solid #e11d48;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 4px 12px rgba(225,29,72,0.12)">
        <div style="display:flex;align-items:flex-start;gap:14px">
          <div style="width:42px;height:42px;border-radius:50%;background:#ffe4e6;color:#e11d48;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#9f1239">Peringatan Keamanan: Wajib Ubah Password Standar</h3>
            <p style="margin:4px 0 0;font-size:12.5px;color:#881337;line-height:1.5">
              Akun Anda saat ini masih menggunakan password standar bawaan (12345678). Demi menjaga keamanan data dan integritas dokumen pemeriksaan Inspektorat Kabupaten Rokan Hilir, <strong>Anda wajib membuat password baru yang aman</strong> sebelum dapat melanjutkan mengakses menu pengawasan.
            </p>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="page-head"><div><h2>Profil &amp; Keamanan Akun</h2></div></div>
    <div class="card" style="max-width:640px;<?= !empty($_SESSION['must_change_password']) ? 'border:2px solid #fbbf24;box-shadow:0 6px 20px rgba(245,158,11,0.15)' : '' ?>">
      <form method="post" action="<?= url('profile/update') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div style="display:flex;align-items:center;gap:18px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid var(--slate-200)">
          <?php $avatarUrl = user_avatar_url($u); ?>
          <?php if ($avatarUrl): ?>
            <img src="<?= $avatarUrl ?>" alt="Foto Profil" style="width:78px;height:78px;border-radius:50%;object-fit:cover;border:3px solid #0284c7;box-shadow:0 4px 8px rgba(0,0,0,0.12);flex-shrink:0">
          <?php else: ?>
            <div style="width:78px;height:78px;border-radius:50%;background:#0284c7;color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;flex-shrink:0">
              <?= e(strtoupper(mb_substr($u['nama'] ?? 'U', 0, 1))) ?>
            </div>
          <?php endif; ?>
          <div>
            <h3 style="margin:0;font-size:16px;font-weight:700;color:var(--slate-800)"><?= e($u['nama']) ?></h3>
            <div style="font-size:12px;color:var(--slate-500);margin-top:2px"><?= e($u['jabatan'] ?? '') ?></div>
            <div style="display:flex;align-items:center;gap:8px;margin-top:8px">
              <span class="badge" style="background:#dbeafe;color:#1e40af"><?= strtoupper($u['role']) ?></span>
              <label style="font-size:11.5px;font-weight:700;color:#0284c7;cursor:pointer;display:inline-flex;align-items:center;gap:5px;background:#f0f9ff;padding:3px 9px;border-radius:6px;border:1px solid #bae6fd">
                <i class="fa-solid fa-camera"></i> Ganti Foto
                <input type="file" name="foto" accept="image/*" style="display:none" onchange="document.getElementById('fotoPreviewInfo').textContent = this.files[0] ? this.files[0].name : ''">
              </label>
              <span id="fotoPreviewInfo" style="font-size:11px;color:#059669;font-weight:600"></span>
            </div>
          </div>
        </div>
        <div class="field"><label>Email</label><input type="email" value="<?= e($u['email']) ?>" disabled class="input" style="background:var(--slate-100)"></div>
        <div class="field"><label>Nama Lengkap</label><input type="text" name="nama" required class="input" value="<?= e($u['nama']) ?>"></div>
        <div class="row">
          <div class="field"><label>NIP</label><input type="text" name="nip" class="input" value="<?= e((string)$u['nip']) ?>"></div>
          <div class="field"><label>Jabatan</label><input type="text" name="jabatan" class="input" value="<?= e((string)$u['jabatan']) ?>"></div>
        </div>
        
        <div class="section-title" style="<?= !empty($_SESSION['must_change_password']) ? 'color:#b45309;font-weight:800;background:#fef3c7;padding:8px 12px;border-radius:8px;border:1px solid #fde68a' : '' ?>">
          <i class="fa-solid fa-lock" style="<?= !empty($_SESSION['must_change_password']) ? 'color:#d97706' : '' ?>"></i> 
          <?= !empty($_SESSION['must_change_password']) ? 'Wajib Ubah Password Baru Sekarang' : 'Ganti Password (opsional)' ?>
        </div>
        <div class="row" style="<?= !empty($_SESSION['must_change_password']) ? 'background:#fffbeb;padding:12px;border-radius:8px;border:1px dashed #f59e0b;margin-bottom:14px' : '' ?>">
          <div class="field">
            <label>Password Lama / Standar <?= !empty($_SESSION['must_change_password']) ? '<span class="req">*</span>' : '' ?></label>
            <input type="password" name="old_password" class="input" <?= !empty($_SESSION['must_change_password']) ? 'required value="12345678"' : '' ?> placeholder="Password saat ini">
            <?php if (!empty($_SESSION['must_change_password'])): ?>
              <small style="color:#b45309;font-size:11px;margin-top:3px;display:block">Otomatis terisi password bawaan <code>12345678</code></small>
            <?php endif; ?>
          </div>
          <div class="field">
            <label>Password Baru <?= !empty($_SESSION['must_change_password']) ? '<span class="req">*</span>' : '' ?></label>
            <input type="password" name="new_password" class="input" minlength="6" <?= !empty($_SESSION['must_change_password']) ? 'required autofocus placeholder="Minimal 6 karakter baru"' : 'placeholder="Kosongkan jika tidak diganti"' ?>>
            <small style="color:#64748b;font-size:11px;margin-top:3px;display:block">Gunakan kombinasi huruf/angka yang aman.</small>
          </div>
        </div>
        <div style="text-align:right">
          <button class="btn btn-primary" type="submit" style="<?= !empty($_SESSION['must_change_password']) ? 'background:#d97706;border-color:#b45309;font-weight:800;padding:10px 22px' : '' ?>">
            <i class="fa-solid fa-shield-check"></i> <?= !empty($_SESSION['must_change_password']) ? 'Simpan &amp; Aktifkan Akun' : 'Simpan Perubahan' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</main>
<?php partial('foot'); ?>
