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
      <p>Siklus pengawasan keuangan desa terpadu &amp; akuntabel berbasis digital dari Pra-Audit hingga Tindak Lanjut.</p>
      
      <!-- 5 PILAR SIKLUS PENGAWASAN CARD -->
      <div class="pilar-card" onclick="openPilarModal()" title="Klik untuk memperbesar diagram alur pengawasan">
        <div style="position:relative;overflow:hidden;border-radius:8px">
          <img src="<?= asset('img/5_pilar_siklus_pengawasan.jpg') ?>" alt="5 Pilar Siklus Pengawasan Terpadu" style="width:100%;height:auto;display:block;border-radius:8px;transition:transform .3s ease" class="pilar-thumb">
          <div class="pilar-overlay" style="position:absolute;inset:0;background:rgba(2,44,34,0.45);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .25s ease;border-radius:8px">
            <span style="background:rgba(0,0,0,0.85);color:#fde68a;font-size:11.5px;font-weight:700;padding:6px 14px;border-radius:20px;display:flex;align-items:center;gap:6px;backdrop-filter:blur(4px);border:1px solid rgba(250,204,21,0.4)">
              <i class="fa-solid fa-magnifying-glass-plus"></i> Klik untuk Memperbesar
            </span>
          </div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding:2px 4px;font-size:11px;color:#a7f3d0">
          <span style="font-weight:600"><i class="fa-solid fa-circle-nodes" style="color:#fbbf24"></i> 5 Pilar Siklus Pengawasan Terpadu</span>
          <span style="color:#fde68a;font-weight:600;display:flex;align-items:center;gap:4px">
            <i class="fa-solid fa-expand"></i> Zoom HD
          </span>
        </div>
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

<!-- LIGHTBOX MODAL 5 PILAR HD -->
<div id="pilarModal" class="pilar-modal" onclick="if(event.target === this) closePilarModal()" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(2,44,34,0.88);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px">
  <div style="position:relative;max-width:1020px;width:100%;background:#064e3b;border:2px solid rgba(250,204,21,0.5);border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,0.6);overflow:hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;background:rgba(0,0,0,0.35);border-bottom:1px solid rgba(255,255,255,0.1)">
      <div style="color:#fde68a;font-weight:700;font-size:13.5px;display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-shield-halved" style="color:#fbbf24"></i> 5 Pilar Siklus Pengawasan Terpadu (End-to-End) — APIP Inspektorat Rohil 2026
      </div>
      <button type="button" onclick="closePilarModal()" style="background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:16px;width:32px;height:32px;border-radius:50%;cursor:pointer;display:grid;place-items:center;transition:background .2s" title="Tutup (ESC)">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div style="padding:14px;background:#022c22;display:grid;place-items:center">
      <img src="<?= asset('img/5_pilar_siklus_pengawasan.jpg') ?>" alt="5 Pilar Siklus Pengawasan Terpadu" style="max-width:100%;max-height:80vh;object-fit:contain;border-radius:8px;display:block;box-shadow:0 10px 30px rgba(0,0,0,0.5)">
    </div>
  </div>
</div>

<script>
function openPilarModal() {
  const m = document.getElementById('pilarModal');
  if (m) {
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}
function closePilarModal() {
  const m = document.getElementById('pilarModal');
  if (m) {
    m.style.display = 'none';
    document.body.style.overflow = '';
  }
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closePilarModal();
});
</script>

<?php partial('foot'); ?>
