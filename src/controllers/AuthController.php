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
                $loggedInUser = $this->auth->user();
                if (($loggedInUser['role'] ?? '') === 'admin') {
                    $_SESSION['is_admin_master'] = true;
                } else {
                    unset($_SESSION['is_admin_master']);
                }

                // Deteksi jika pengguna login menggunakan password standar bawaan
                if ($pwd === '12345678') {
                    $_SESSION['must_change_password'] = true;
                    flash('warning', 'Pemberitahuan Keamanan: Anda masuk menggunakan password standar bawaan (12345678). Demi keamanan data pengawasan, silakan ubah password Anda di bawah ini sebelum melanjutkan.');
                    redirect('profile');
                    return;
                }

                unset($_SESSION['must_change_password']);
                flash('success', 'Selamat datang, ' . $loggedInUser['nama'] . '!');
                redirect('dashboard');
            } else {
                flash('error', 'NIP / Username / Email atau password salah, atau akun dinonaktifkan.');
            }
        }
        view('auth/login');
    }

    public function logout(): void {
        unset($_SESSION['is_admin_master']);
        $this->auth->logout();
        flash('success', 'Anda telah keluar.');
        redirect('login');
    }

    public function switchUser(): void {
        $this->auth->require();
        $curUser = $this->auth->user();
        if (($curUser['role'] ?? '') === 'admin') {
            $_SESSION['is_admin_master'] = true;
        }
        if (empty($_SESSION['is_admin_master'])) {
            flash('error', 'Akses ditolak: Fitur simulasi 1-klik hanya dapat digunakan melalui akun Administrator.');
            $redirect = $_SERVER['HTTP_REFERER'] ?? url('dashboard');
            header('Location: ' . $redirect);
            exit;
        }
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
