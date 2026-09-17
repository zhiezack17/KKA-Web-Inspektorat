<?php $title = 'Login - KKA Inspektorat Rokan Hilir'; $body_class = 'login'; ?>
<?php partial('head', compact('title','body_class')); ?>

<div class="login-card" data-testid="login-card">
  <div class="login-left">
    <div class="logos">
      <div class="lg"><img src="<?= asset('img/logo-rohil.png') ?>" alt="Rohil"></div>
      <div class="lg"><img src="<?= asset('img/logo-inspektorat.png') ?>" alt="Inspektorat"></div>
      <div class="txt">
        <b>Pemerintah Kabupaten Rokan Hilir</b>
        Inspektorat Daerah
      </div>
    </div>

    <div class="hero">
      <h2>Kertas Kerja <span class="gold">Audit Digital</span><br>Inspektorat Rohil</h2>
      <p>Sistem dokumentasi audit pengeluaran keuangan kepenghuluan berbasis digital — aman, terstruktur, dan terintegrasi.</p>
      <div class="feats">
        <div><i class="fa-solid fa-shield-halved"></i> Akses internal khusus auditor & admin</div>
        <div><i class="fa-solid fa-file-pdf"></i> Cetak KKA siap tanda tangan</div>
        <div><i class="fa-solid fa-chart-column"></i> Rekapitulasi anggaran vs realisasi per desa</div>
      </div>
    </div>

    <div class="foot">© <?= date('Y') ?> Inspektorat Kabupaten Rokan Hilir · arsipdigital-inspektorat.com</div>
  </div>

  <div class="login-right">
    <h1>Masuk ke Akun</h1>
    <p class="sub">Gunakan NIP (18 digit), Username E-Reviu, atau Email terdaftar.</p>

    <?php partial('flash'); ?>

    <form method="post" action="<?= url('login') ?>" data-testid="login-form" autocomplete="off">
      <?= csrf_field() ?>
      <div class="field">
        <label>NIP, Username, atau Email <span class="req">*</span></label>
        <input type="text" name="login" required class="input" placeholder="18 Digit NIP / Username / Email"
               value="<?= e($_POST['login'] ?? $_POST['email'] ?? '') ?>" autofocus data-testid="login-identifier">
        <small style="color:var(--slate-500);font-size:11px;margin-top:4px;display:block">
          <i class="fa-solid fa-id-card"></i> Masukkan NIP 18 digit (bisa tanpa spasi) atau akun E-Reviu.
        </small>
      </div>
      <div class="field">
        <label>Password <span class="req">*</span></label>
        <input type="password" name="password" required class="input" placeholder="••••••••" data-testid="login-password">
      </div>
      <button type="submit" class="btn btn-primary" data-testid="login-submit">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk
      </button>
    </form>

    <div class="help" style="font-size:12px;line-height:1.5">
      Password standar: <code>12345678</code> (Dapat diubah di menu Profil setelah masuk).<br>
      Kendala login? Hubungi Administrator Inspektorat.
    </div>
  </div>
</div>

<?php partial('foot'); ?>
