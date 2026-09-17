<?php
/**
 * Helper umum.
 */

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string {
    $base = rtrim($GLOBALS['app_base_url'] ?? '', '/');
    if ($path === '') return $base . '/';
    if (str_starts_with($path, '/')) return $base . $path;
    return $base . '/' . $path;
}

function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals(csrf_token(), (string)$token)) {
        http_response_code(419);
        exit('CSRF token tidak valid. Silakan refresh halaman.');
    }
}

function flash(string $type, string $msg): void {
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_pull(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function rupiah($val, bool $withSymbol = true): string {
    $val = (float)$val;
    $out = number_format($val, 0, ',', '.');
    return $withSymbol ? 'Rp ' . $out : $out;
}

function parse_money($v): float {
    // Hanya nilai numeric asli (int/float dari kode) yang boleh langsung dicast.
    // String seperti "800.000" is_numeric()==true tapi maksudnya 800 ribu, bukan 800.
    if (is_int($v) || is_float($v)) return (float)$v;
    $v = preg_replace('/[^\d,.\-]/', '', (string)$v);
    // hilangkan separator ribuan (titik) lalu ganti koma jadi titik
    $v = str_replace('.', '', $v);
    $v = str_replace(',', '.', $v);
    return $v === '' ? 0 : (float)$v;
}

function tgl_id(?string $date): string {
    if (!$date) return '-';
    $ts = strtotime($date);
    if (!$ts) return '-';
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function view(string $tpl, array $data = []): void {
    $cfg = $GLOBALS['cfg'];
    $auth = $GLOBALS['auth'] ?? null;
    extract($data, EXTR_SKIP);
    $file = $cfg['root_dir'] . '/src/views/' . $tpl . '.php';
    if (!is_file($file)) {
        http_response_code(500);
        exit('Template tidak ditemukan: ' . e($tpl));
    }
    require $file;
}

function partial(string $tpl, array $data = []): void {
    view('partials/' . $tpl, $data);
}

function only_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }
}

function input(string $key, $default = null) {
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    if (is_string($v)) $v = trim($v);
    return $v;
}

function kka_status_info(?string $status): array {
    $st = strtoupper((string)($status ?: 'DRAFT'));
    switch ($st) {
        case 'REVIEW_KETUA':
            return [
                'code'  => 'REVIEW_KETUA',
                'label' => 'Reviu Ketua Tim',
                'bg'    => '#fef3c7',
                'color' => '#b45309',
                'border'=> '#fde68a',
                'icon'  => 'fa-solid fa-user-clock',
                'desc'  => 'Sedang ditelaah dan diverifikasi oleh Ketua Tim',
            ];
        case 'REVIEW_DALNIS':
            return [
                'code'  => 'REVIEW_DALNIS',
                'label' => 'Reviu Dalnis',
                'bg'    => '#e0e7ff',
                'color' => '#4338ca',
                'border'=> '#c7d2fe',
                'icon'  => 'fa-solid fa-user-check',
                'desc'  => 'Menunggu persetujuan & pengesahan Pengendali Teknis',
            ];
        case 'PERLU_REVISI':
            return [
                'code'  => 'PERLU_REVISI',
                'label' => 'Perlu Revisi',
                'bg'    => '#fee2e2',
                'color' => '#b91c1c',
                'border'=> '#fecaca',
                'icon'  => 'fa-solid fa-triangle-exclamation',
                'desc'  => 'KKA dikembalikan dengan catatan perbaikan',
            ];
        case 'SELESAI_FINAL':
            return [
                'code'  => 'SELESAI_FINAL',
                'label' => 'Sah / Disetujui',
                'bg'    => '#dcfce7',
                'color' => '#15803d',
                'border'=> '#bbf7d0',
                'icon'  => 'fa-solid fa-circle-check',
                'desc'  => 'KKA telah disetujui & disahkan oleh Pengendali Teknis',
            ];
        case 'DRAFT':
        default:
            return [
                'code'  => 'DRAFT',
                'label' => 'Draft',
                'bg'    => '#f1f5f9',
                'color' => '#475569',
                'border'=> '#cbd5e1',
                'icon'  => 'fa-solid fa-file-pen',
                'desc'  => 'Masih dalam proses penyusunan oleh Auditor',
            ];
    }
}

function kka_status_badge(?string $status, bool $withIcon = true): string {
    $info = kka_status_info($status);
    $ico = $withIcon ? '<i class="' . $info['icon'] . '" style="margin-right:5px"></i>' : '';
    return '<span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;line-height:1.2;background:' . $info['bg'] . ';color:' . $info['color'] . ';border:1px solid ' . $info['border'] . '">' . $ico . e($info['label']) . '</span>';
}

/**
 * ==========================================================
 * Isolasi data antar-pengguna (data ownership).
 * Admin melihat SEMUA data; auditor melihat data
 * miliknya sendiri (kolom kka_sesi.created_by), sesi di mana
 * ia ditunjuk sebagai Ketua Tim atau Dalnis, atau yang dibagikan.
 * ==========================================================
 */

/**
 * Fragmen WHERE untuk membatasi query pada data milik user (kecuali admin).
 * Termasuk sesi di mana user menjadi Ketua Tim, Dalnis, atau dishare.
 * $col adalah kolom created_by pada tabel kka_sesi ber-alias (mis. "s.created_by").
 */
function owner_where($auth, string $col = 's.created_by'): array {
    if ($auth && ($auth->isAdmin() || $auth->isInspektur() || $auth->isIrban() || $auth->isOperatorSpt())) return ['', []];
    $uid = $auth ? (int) $auth->id() : 0;
    $alias = strpos($col, '.') !== false ? substr($col, 0, strpos($col, '.')) : $col;
    $u = $auth ? $auth->user() : null;
    $nama = $u ? trim($u['nama'] ?? '') : '';

    if ($nama !== '') {
        return [
            " AND ($col = ? OR $alias.ketua_tim_id = ? OR $alias.dalnis_id = ? 
                   OR $alias.direview_oleh LIKE ? 
                   OR $alias.dievaluasi_oleh LIKE ? 
                   OR $alias.id IN (SELECT sesi_id FROM kka_sesi_share WHERE user_id = ?))",
            [$uid, $uid, $uid, '%' . $nama . '%', '%' . $nama . '%', $uid],
        ];
    }

    return [
        " AND ($col = ? OR $alias.ketua_tim_id = ? OR $alias.dalnis_id = ? OR $alias.id IN (SELECT sesi_id FROM kka_sesi_share WHERE user_id = ?))",
        [$uid, $uid, $uid, $uid],
    ];
}

/**
 * True jika user boleh mengakses sesi (admin, pembuat, ketua tim, dalnis, ATAU penerima berbagi).
 * $sesi harus memuat 'created_by' dan salah satu dari 'sesi_id' atau 'id'
 * (id sesi). Bila keduanya ada, 'sesi_id' diutamakan agar aman untuk baris
 * hasil JOIN (mis. lampiran yang punya id sendiri).
 */
function sesi_is_owned($auth, ?array $sesi): bool {
    if (!$sesi || !$auth) return false;
    if ($auth->isAdmin() || $auth->isInspektur() || $auth->isIrban() || $auth->isOperatorSpt()) return true;
    $uid = (int) $auth->id();
    if ((int) ($sesi['created_by'] ?? 0) === $uid) return true;
    if ((int) ($sesi['ketua_tim_id'] ?? 0) === $uid) return true;
    if ((int) ($sesi['dalnis_id'] ?? 0) === $uid) return true;
    $u = $auth->user();
    $nama = trim($u['nama'] ?? '');
    if ($nama !== '') {
        if (!empty($sesi['direview_oleh']) && stripos($sesi['direview_oleh'], $nama) !== false) return true;
        if (!empty($sesi['dievaluasi_oleh']) && stripos($sesi['dievaluasi_oleh'], $nama) !== false) return true;
    }
    $sid = (int) ($sesi['sesi_id'] ?? $sesi['id'] ?? 0);
    if ($sid > 0) {
        return (bool) DB::scalar(
            'SELECT 1 FROM kka_sesi_share WHERE sesi_id = ? AND user_id = ? LIMIT 1',
            [$sid, $uid]
        );
    }
    return false;
}

/** Hentikan akses (redirect) bila sesi tidak ada atau bukan milik user. */
function guard_sesi($auth, ?array $sesi, string $redirectTo = 'sesi'): void {
    if (!$sesi) {
        flash('error', 'Sesi audit tidak ditemukan.');
        redirect($redirectTo);
    }
    if (!sesi_is_owned($auth, $sesi)) {
        http_response_code(403);
        flash('error', 'Anda tidak memiliki akses ke data audit ini.');
        redirect($redirectTo);
    }
}

/**
 * Render grouped options (<optgroup>) untuk penunjukan Ketua Tim & Dalnis.
 */
function render_user_options(array $users, ?int $selectedId = null, string $preferredRole = 'all'): string {
    $irban  = [];
    $dalnis = [];
    $muda   = [];
    $lain   = [];
    $admin  = [];

    foreach ($users as $u) {
        $r = $u['role'] ?? 'auditor';
        $j = (string)($u['jabatan'] ?? '');
        if ($r === 'admin') {
            $admin[] = $u;
        } elseif ($r === 'irban' || stripos($j, 'Inspektur Pembantu') !== false || stripos($j, 'Irban') !== false) {
            $irban[] = $u;
        } elseif ($r === 'dalnis' || stripos($j, 'Madya') !== false) {
            $dalnis[] = $u;
        } elseif (stripos($j, 'Muda') !== false) {
            $muda[] = $u;
        } else {
            $lain[] = $u;
        }
    }

    $buildOpts = function(array $list, ?int $sel) {
        $html = '';
        foreach ($list as $u) {
            $isSelected = ($sel !== null && (int)$u['id'] === (int)$sel) ? ' selected' : '';
            $nipTxt = !empty($u['nip']) ? ' (NIP. ' . e($u['nip']) . ')' : '';
            $jabTxt = !empty($u['jabatan']) ? ' — ' . e($u['jabatan']) : '';
            $html .= '<option value="' . (int)$u['id'] . '"'
                   . ' data-nama="' . e($u['nama']) . '"'
                   . ' data-nip="' . e($u['nip'] ?? '') . '"'
                   . ' data-jabatan="' . e($u['jabatan'] ?? '') . '"'
                   . $isSelected . '>'
                   . e($u['nama']) . $nipTxt . $jabTxt
                   . '</option>';
        }
        return $html;
    };

    $out = '';
    if ($preferredRole === 'irban') {
        if (!empty($irban))  $out .= '<optgroup label="Inspektur Pembantu (Irban)">' . $buildOpts($irban, $selectedId) . '</optgroup>';
        if (!empty($dalnis)) $out .= '<optgroup label="Auditor Madya / Dalnis">' . $buildOpts($dalnis, $selectedId) . '</optgroup>';
        if (!empty($admin))  $out .= '<optgroup label="Administrator">' . $buildOpts($admin, $selectedId) . '</optgroup>';
    } elseif ($preferredRole === 'dalnis') {
        if (!empty($dalnis)) $out .= '<optgroup label="Pengendali Teknis / Dalnis (Auditor Madya)">' . $buildOpts($dalnis, $selectedId) . '</optgroup>';
        if (!empty($admin))  $out .= '<optgroup label="Administrator">' . $buildOpts($admin, $selectedId) . '</optgroup>';
        if (!empty($muda))   $out .= '<optgroup label="Auditor Muda">' . $buildOpts($muda, $selectedId) . '</optgroup>';
        if (!empty($lain))   $out .= '<optgroup label="Auditor Pertama / Lainnya">' . $buildOpts($lain, $selectedId) . '</optgroup>';
    } elseif ($preferredRole === 'ketua') {
        if (!empty($muda))   $out .= '<optgroup label="Ketua Tim (Auditor Muda)">' . $buildOpts($muda, $selectedId) . '</optgroup>';
        if (!empty($dalnis)) $out .= '<optgroup label="Auditor Madya / Dalnis">' . $buildOpts($dalnis, $selectedId) . '</optgroup>';
        if (!empty($lain))   $out .= '<optgroup label="Auditor Pertama / Lainnya">' . $buildOpts($lain, $selectedId) . '</optgroup>';
        if (!empty($admin))  $out .= '<optgroup label="Administrator">' . $buildOpts($admin, $selectedId) . '</optgroup>';
    } else {
        if (!empty($irban))  $out .= '<optgroup label="Inspektur Pembantu (Irban)">' . $buildOpts($irban, $selectedId) . '</optgroup>';
        if (!empty($dalnis)) $out .= '<optgroup label="Auditor Madya / Dalnis">' . $buildOpts($dalnis, $selectedId) . '</optgroup>';
        if (!empty($muda))   $out .= '<optgroup label="Auditor Muda">' . $buildOpts($muda, $selectedId) . '</optgroup>';
        if (!empty($lain))   $out .= '<optgroup label="Auditor Pertama / Terampil">' . $buildOpts($lain, $selectedId) . '</optgroup>';
        if (!empty($admin))  $out .= '<optgroup label="Administrator">' . $buildOpts($admin, $selectedId) . '</optgroup>';
    }

    return $out;
}

/**
 * Memisahkan string nama pejabat dan NIP jika tersimpan dalam satu teks gabungan
 * Contoh: "ABU BAKAR, SE (NIP. 196805121990031005)" -> ['ABU BAKAR, SE', '196805121990031005']
 */
function split_nama_nip(?string $rawNama, ?string $rawNip = ''): array {
    $rawNama = trim((string)$rawNama);
    $rawNip  = trim((string)$rawNip);
    if ($rawNama === '') return ['', $rawNip ?: '-'];

    // 1. Format kurung: "Nama Pejabat (NIP. 196805121990031005)" atau "(NIP 1968...)"
    if (preg_match('/^(.*?)\s*\(\s*NIP[\.:\s]*([0-9\s]+)\s*\)$/i', $rawNama, $m)) {
        $pureNama = trim($m[1]);
        $extractedNip = preg_replace('/[^0-9]/', '', $m[2]);
        if ($rawNip === '' || $rawNip === '-') {
            $rawNip = $extractedNip;
        }
        return [$pureNama, $rawNip];
    }

    // 2. Format tanpa kurung di ujung: "Nama Pejabat - NIP. 196805121990031005"
    if (preg_match('/^(.*?)\s*[-–]?\s*NIP[\.:\s]+([0-9\s]{10,25})$/i', $rawNama, $m)) {
        $pureNama = trim($m[1]);
        $extractedNip = preg_replace('/[^0-9]/', '', $m[2]);
        if ($rawNip === '' || $rawNip === '-') {
            $rawNip = $extractedNip;
        }
        return [$pureNama, $rawNip];
    }

    return [$rawNama, $rawNip ?: '-'];
}

/**
 * Format NIP standar BKN: 19760810 200312 1 004
 */
function format_nip(?string $nip): string {
    $clean = preg_replace('/[^0-9]/', '', (string)$nip);
    if (strlen($clean) === 18) {
        return substr($clean, 0, 8) . ' ' . substr($clean, 8, 6) . ' ' . substr($clean, 14, 1) . ' ' . substr($clean, 15, 3);
    }
    return $nip ?: '-';
}

/**
 * Format nama sapaan akrab & santun untuk Dashboard
 */
function sapaan_nama(?string $nama, ?string $role = null): string {
    $nama = trim((string)$nama);
    if ($nama === '') return 'Auditor';
    
    if ($role === 'operator_spt') {
        return 'Bagian Perencanaan & Evaluasi';
    }

    // Hilangkan gelar belakang setelah koma pertama (misal: ", ST., M.IP" atau ", S.Pi, M.Si")
    $clean = trim(preg_replace('/,.*$/', '', $nama));

    // Rapikan kapitalisasi jika ALL CAPS
    if ($clean === strtoupper($clean)) {
        $clean = ucwords(strtolower($clean));
        // Pastikan singkatan gelar depan tetap rapi (H., Hj., Drs., Dra., Ir., Dr.)
        $clean = preg_replace_callback('/^(H\.|Hj\.|Drs\.|Dra\.|Ir\.|Dr\.)\s*/i', function($m) {
            return ucfirst($m[1]) . ' ';
        }, $clean);
    }

    if ($role === 'inspektur') {
        return 'Pak Inspektur (' . $clean . ')';
    }

    return $clean;
}

/**
 * Konversi angka hari/angka kecil ke kata terbilang Indonesia
 */
function terbilang_angka(int $n): string {
    $dasar = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
    if ($n < 0) return 'minus ' . terbilang_angka(abs($n));
    if ($n < 12) return $dasar[$n];
    if ($n < 20) return $dasar[$n - 10] . ' belas';
    if ($n < 100) return $dasar[(int)($n / 10)] . ' puluh' . ($n % 10 ? ' ' . $dasar[$n % 10] : '');
    return (string)$n;
}
