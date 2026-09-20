<?php
class Auth {
    private ?array $user = null;

    public function __construct() {
        if (!empty($_SESSION['uid'])) {
            $this->user = DB::one(
                'SELECT id, nama, email, username, role, nip, jabatan, is_active FROM kka_users WHERE id = ? LIMIT 1',
                [$_SESSION['uid']]
            );
            if (!$this->user || (int)$this->user['is_active'] === 0) {
                $this->logout();
            }
        }
    }

    public function check(): bool { return $this->user !== null; }
    public function user(): ?array { return $this->user; }
    public function id(): ?int { return $this->user['id'] ?? null; }
    public function isAdmin(): bool { return ($this->user['role'] ?? '') === 'admin'; }
    public function isDalnis(): bool {
        $r = $this->user['role'] ?? '';
        $j = $this->user['jabatan'] ?? '';
        return $r === 'dalnis' || stripos($j, 'Madya') !== false || $r === 'admin';
    }
    public function isKetua(): bool {
        $r = $this->user['role'] ?? '';
        $j = $this->user['jabatan'] ?? '';
        return stripos($j, 'Muda') !== false || $r === 'admin' || $r === 'dalnis';
    }
    public function isIrban(): bool {
        $r = $this->user['role'] ?? '';
        $j = $this->user['jabatan'] ?? '';
        return $r === 'irban' || stripos($j, 'Inspektur Pembantu') !== false || stripos($j, 'Irban') !== false || $r === 'admin';
    }
    public function isInspektur(): bool {
        $r = $this->user['role'] ?? '';
        $j = $this->user['jabatan'] ?? '';
        return $r === 'inspektur' || (stripos($j, 'Inspektur') !== false && stripos($j, 'Pembantu') === false);
    }
    public function isOperatorSpt(): bool {
        $r = $this->user['role'] ?? '';
        if ($r === 'admin') return false;
        $j = $this->user['jabatan'] ?? '';
        return $r === 'operator_spt' || stripos($j, 'Surat Perintah') !== false || stripos($j, 'Perencanaan') !== false;
    }
    public function role(): string { return $this->user['role'] ?? 'auditor'; }

    public function attempt(string $identifier, string $password): bool {
        $identifier = trim($identifier);
        if ($identifier === '') return false;

        $cleanNip = preg_replace('/\s+/', '', $identifier);
        $lower = strtolower($identifier);

        $u = DB::one(
            'SELECT * FROM kka_users 
             WHERE (
                 LOWER(email) = ? 
                 OR LOWER(username) = ? 
                 OR REPLACE(nip, " ", "") = ? 
                 OR nip = ?
                 OR (role = "inspektur" AND ? IN ("inspektur", "sarmansyahroni", "sarman", "inspektur daerah", "inspekturdaerah", "h. sarman syahroni, st., m.ip", "h. sarman syahroni"))
                 OR (role = "operator_spt" AND ? IN ("operator_spt", "operatorspt", "spt", "perencanaan", "bagian_spt", "bagianspt"))
             ) 
             LIMIT 1',
            [$lower, $lower, $cleanNip, $identifier, $lower, $lower]
        );
        if (!$u || (int)$u['is_active'] === 0) return false;
        if (!password_verify($password, $u['password_hash'])) return false;
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        $this->user = $u;
        return true;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->user = null;
    }

    public function require(): void {
        if (!$this->check()) {
            flash('error', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
    }

    public function requireAdmin(): void {
        $this->require();
        if (!$this->isAdmin()) {
            http_response_code(403);
            exit('Akses ditolak. Hanya untuk Administrator.');
        }
    }
}
