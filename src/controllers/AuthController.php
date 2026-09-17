<?php
class AuthController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; }

    public function home(): void {
        if ($this->auth->check()) redirect('dashboard');
        redirect('login');
    }

    public function login(): void {
        if ($this->auth->check()) redirect('dashboard');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $identifier = trim((string)input('login', input('email', '')));
            $pwd        = (string)input('password', '');
            if ($identifier === '' || $pwd === '') {
                flash('error', 'NIP / Username / Email dan password wajib diisi.');
            } elseif ($this->auth->attempt($identifier, $pwd)) {
                flash('success', 'Selamat datang, ' . $this->auth->user()['nama'] . '!');
                redirect('dashboard');
            } else {
                flash('error', 'NIP / Username / Email atau password salah, atau akun dinonaktifkan.');
            }
        }
        view('auth/login');
    }

    public function logout(): void {
        $this->auth->logout();
        flash('success', 'Anda telah keluar.');
        redirect('login');
    }

    public function switchUser(): void {
        $this->auth->require();
        $target = trim((string)input('user', ''));
        if ($target !== '') {
            $u = DB::one("SELECT id, nama, role, jabatan FROM kka_users WHERE username = ? AND is_active = 1", [$target]);
            if ($u) {
                $_SESSION['uid'] = (int)$u['id'];
                flash('success', "Beralih peran ke: {$u['nama']} (" . strtoupper($u['role']) . " - " . ($u['jabatan'] ?? '') . ")");
            } else {
                flash('error', "Pengguna '$target' tidak ditemukan.");
            }
        }
        $redirect = $_SERVER['HTTP_REFERER'] ?? url('dashboard');
        header('Location: ' . $redirect);
        exit;
    }
}
