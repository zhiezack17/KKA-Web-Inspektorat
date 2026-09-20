<?php $title = 'Manajemen Pengguna - KKA'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<style>
  .badge.role-dalnis { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-weight: 700; }
  .badge.role-admin { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700; }
  .badge.role-auditor { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; font-weight: 600; }
</style>

<main class="main" data-testid="page-users">
  <?php partial('topbar', ['title' => 'Manajemen Akun & Pengguna', 'icon' => 'fa-solid fa-users-gear']); ?>

  <div class="content">
    <?php partial('flash'); ?>
    <div class="page-head">
      <div><h2>Manajemen Pengguna</h2><p>Kelola akun Pegawai Inspektorat (Auditor, Dalnis, &amp; Administrator).</p></div>
      <button class="btn btn-primary" onclick="document.getElementById('m-user').style.display='flex'" data-testid="btn-add-user"><i class="fa-solid fa-user-plus"></i> Tambah Pengguna</button>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Nama &amp; Email</th><th>Username</th><th>NIP</th><th>Jabatan</th><th>Role</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr data-testid="user-row-<?= $u['id'] ?>">
              <td>
                <div style="font-weight:700;color:var(--slate-800)"><?= e($u['nama']) ?></div>
                <div style="font-size:12px;color:var(--slate-500)"><?= e($u['email']) ?></div>
              </td>
              <td><?= !empty($u['username']) ? '<code>'.e($u['username']).'</code>' : '<span style="color:var(--slate-400)">-</span>' ?></td>
              <td><span style="font-family:monospace;font-size:12px"><?= e($u['nip'] ?: '-') ?></span></td>
              <td><?= e($u['jabatan'] ?: '-') ?></td>
              <td><span class="badge role-<?= e($u['role']) ?>"><?= e(strtoupper($u['role'])) ?></span></td>
              <td><?= $u['is_active']?'<span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0">Aktif</span>':'<span class="badge slate">Nonaktif</span>' ?></td>
              <td>
                <button class="btn btn-outline btn-sm" onclick='openEdit(<?= json_encode($u, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' data-testid="edit-user-<?= $u['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                <?php if ($u['id'] !== $auth->id()): ?>
                <form method="post" action="<?= url('users/delete') ?>" onsubmit="return confirm('Hapus pengguna <?= e($u['nama']) ?>?')" style="display:inline">
                  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn btn-outline btn-sm" style="color:var(--red-600);border-color:#fecaca"><i class="fa-solid fa-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="modal-bg" id="m-user" style="display:none">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-head"><h3>Tambah Pengguna</h3><button class="x" onclick="document.getElementById('m-user').style.display='none'">×</button></div>
    <form method="post" action="<?= url('users/store') ?>" data-testid="form-add-user">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="field"><label>Nama Lengkap <span class="req">*</span></label><input type="text" name="nama" required class="input" placeholder="Nama lengkap beserta gelar"></div>
        <div class="row">
          <div class="field"><label>Email <span class="req">*</span></label><input type="email" name="email" required class="input" placeholder="email@inspektorat.rohilkab.go.id"></div>
          <div class="field"><label>Username (E-Reviu)</label><input type="text" name="username" class="input" placeholder="Username login"></div>
        </div>
        <div class="row">
          <div class="field"><label>NIP (18 digit)</label><input type="text" name="nip" class="input" placeholder="19XXXXXXXXXXXXXX"></div>
          <div class="field"><label>Jabatan</label><input type="text" name="jabatan" class="input" placeholder="cth: Auditor Madya / Auditor Muda"></div>
        </div>
        <div class="row">
          <div class="field"><label>Role Sistem <span class="req">*</span></label>
            <select name="role" required class="select">
              <option value="auditor">Auditor (Anggota Tim / Ketua Tim)</option>
              <option value="dalnis">Dalnis (Auditor Madya / Pengendali Teknis)</option>
              <option value="irban">Irban (Inspektur Pembantu / Wakil Penanggung Jawab)</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
          <div class="field"><label>Password <span class="req">*</span></label><input type="password" name="password" required class="input" minlength="6" placeholder="Min. 6 karakter"></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('m-user').style.display='none'">Batal</button>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-bg" id="m-edit" style="display:none">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-head"><h3>Edit Pengguna</h3><button class="x" onclick="document.getElementById('m-edit').style.display='none'">×</button></div>
    <form method="post" action="<?= url('users/update') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="e-id">
      <div class="modal-body">
        <div class="field"><label>Nama Lengkap</label><input type="text" name="nama" id="e-nama" required class="input"></div>
        <div class="row">
          <div class="field"><label>Email (Read-only)</label><input type="email" id="e-email" class="input" readonly style="background:#f1f5f9"></div>
          <div class="field"><label>Username (E-Reviu)</label><input type="text" name="username" id="e-user" class="input"></div>
        </div>
        <div class="row">
          <div class="field"><label>NIP (18 digit)</label><input type="text" name="nip" id="e-nip" class="input"></div>
          <div class="field"><label>Jabatan</label><input type="text" name="jabatan" id="e-jab" class="input"></div>
        </div>
        <div class="row">
          <div class="field"><label>Role Sistem</label>
            <select name="role" id="e-role" class="select">
              <option value="auditor">Auditor (Anggota Tim / Ketua Tim)</option>
              <option value="dalnis">Dalnis (Auditor Madya / Pengendali Teknis)</option>
              <option value="irban">Irban (Inspektur Pembantu / Wakil Penanggung Jawab)</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
          <div class="field"><label>Status Akun</label>
            <select name="is_active" id="e-act" class="select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
          </div>
        </div>
        <div class="field"><label>Password Baru <small style="color:var(--slate-500)">(kosongkan jika tidak diganti)</small></label><input type="password" name="password" class="input" minlength="6" placeholder="Ketik password baru jika ingin mengubah"></div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('m-edit').style.display='none'">Batal</button>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(u){
  document.getElementById('e-id').value = u.id;
  document.getElementById('e-nama').value = u.nama;
  document.getElementById('e-email').value = u.email || '';
  document.getElementById('e-user').value = u.username || '';
  document.getElementById('e-nip').value = u.nip || '';
  document.getElementById('e-jab').value = u.jabatan || '';
  document.getElementById('e-role').value = u.role;
  document.getElementById('e-act').value = u.is_active;
  document.getElementById('m-edit').style.display = 'flex';
}
</script>

<?php partial('foot'); ?>
