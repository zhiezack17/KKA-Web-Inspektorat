<?php $title = 'Profil Saya - KKA'; $u = $auth->user(); ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>
<main class="main">
  <div class="topbar"><div class="crumb"><i class="fa-solid fa-user"></i> <b>Profil Saya</b></div></div>
  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head"><div><h2>Profil &amp; Keamanan Akun</h2></div></div>
    <div class="card" style="max-width:640px">
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
        <div class="section-title"><i class="fa-solid fa-lock"></i> Ganti Password (opsional)</div>
        <div class="row">
          <div class="field"><label>Password Lama</label><input type="password" name="old_password" class="input"></div>
          <div class="field"><label>Password Baru</label><input type="password" name="new_password" class="input" minlength="6"></div>
        </div>
        <div style="text-align:right"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan</button></div>
      </form>
    </div>
  </div>
</main>
<?php partial('foot'); ?>
