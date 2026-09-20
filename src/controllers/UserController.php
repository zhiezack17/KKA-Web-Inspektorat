<?php
class UserController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function index(): void {
        $this->auth->requireAdmin();
        $users = DB::all('SELECT id, nama, email, username, role, nip, jabatan, is_active, created_at FROM kka_users ORDER BY role = "admin" DESC, role = "dalnis" DESC, nama ASC');
        view('users/index', compact('users'));
    }

    public function store(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $email = strtolower(trim((string) input('email')));
        $nama  = trim((string) input('nama'));
        $pass  = (string) input('password');
        $role = in_array(input('role'), ['admin', 'dalnis', 'irban', 'auditor'], true) ? input('role') : 'auditor';
        $user  = strtolower(trim((string) input('username'))) ?: null;

        if ($nama === '' || $email === '' || strlen($pass) < 6) {
            flash('error', 'Nama, email, dan password (min 6 karakter) wajib diisi.');
            redirect('users');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Email tidak valid.'); redirect('users');
        }
        try {
            DB::insert('kka_users', [
                'nama'          => $nama,
                'email'         => $email,
                'username'      => $user,
                'password_hash' => password_hash($pass, PASSWORD_BCRYPT),
                'role'          => $role,
                'nip'           => trim((string) input('nip')) ?: null,
                'jabatan'       => trim((string) input('jabatan')) ?: null,
                'is_active'     => 1,
            ]);
            flash('success', 'Pengguna baru berhasil dibuat.');
        } catch (Throwable $e) {
            flash('error', 'Email/Username/NIP sudah dipakai atau gagal disimpan.');
        }
        redirect('users');
    }

    public function update(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $id = (int) input('id');
        if (!$id) { flash('error', 'Data tidak valid.'); redirect('users'); }

        $role = in_array(input('role'), ['admin', 'dalnis', 'irban', 'auditor'], true) ? input('role') : 'auditor';
        $user = strtolower(trim((string) input('username'))) ?: null;

        $data = [
            'nama'      => trim((string) input('nama')),
            'username'  => $user,
            'role'      => $role,
            'nip'       => trim((string) input('nip')) ?: null,
            'jabatan'   => trim((string) input('jabatan')) ?: null,
            'is_active' => (int) input('is_active', 1) === 1 ? 1 : 0,
        ];
        $pass = (string) input('password');
        if ($pass !== '') {
            if (strlen($pass) < 6) { flash('error','Password minimal 6 karakter.'); redirect('users'); }
            $data['password_hash'] = password_hash($pass, PASSWORD_BCRYPT);
        }
        DB::update('kka_users', $data, ['id' => $id]);
        flash('success', 'Data pengguna diperbarui.');
        redirect('users');
    }

    public function delete(): void {
        $this->auth->requireAdmin(); only_post(); csrf_check();
        $id = (int) input('id');
        if ($id === $this->auth->id()) {
            flash('error', 'Tidak bisa menghapus akun sendiri.');
        } else {
            DB::delete('kka_users', ['id' => $id]);
            flash('success', 'Pengguna dihapus.');
        }
        redirect('users');
    }

    public function profile(): void {
        view('users/profile');
    }

    public function updateProfile(): void {
        only_post(); csrf_check();
        $id = $this->auth->id();
        $u = DB::one('SELECT * FROM kka_users WHERE id = ?', [$id]);
        if (!$u) { flash('error', 'User tidak ditemukan.'); redirect('profile'); }

        $data = [
            'nama'    => trim((string) input('nama')),
            'nip'     => trim((string) input('nip')) ?: null,
            'jabatan' => trim((string) input('jabatan')) ?: null,
        ];

        // Upload Foto Profil jika dilampirkan
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed, true) && $file['size'] <= 5 * 1024 * 1024) {
                $targetDir = __DIR__ . '/../../public/assets/img/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $uname = strtolower(trim((string)$u['username']));
                if ($uname !== '') {
                    $targetFile = $targetDir . $uname . '.jpg';
                    move_uploaded_file($file['tmp_name'], $targetFile);
                    flash('success', 'Foto profil berhasil diperbarui.');
                }
            } else {
                flash('error', 'Format foto harus JPG/PNG/WEBP (maks 5 MB).');
            }
        }

        $oldPass = (string) input('old_password');
        $newPass = (string) input('new_password');
        if ($newPass !== '') {
            if (!$u || !password_verify($oldPass, $u['password_hash'])) {
                flash('error', 'Password lama salah.'); redirect('profile');
            }
            if (strlen($newPass) < 6) { flash('error','Password baru minimal 6 karakter.'); redirect('profile'); }
            $data['password_hash'] = password_hash($newPass, PASSWORD_BCRYPT);
        }
        DB::update('kka_users', $data, ['id' => $id]);
        flash('success', 'Profil berhasil disimpan.');
        redirect('profile');
    }
}
