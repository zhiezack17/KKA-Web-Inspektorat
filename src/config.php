<?php
/**
 * Konfigurasi KKA API production.
 * Simpan sebagai /www/wwwroot/kka/src/config.php setelah backup.
 * Semua credential wajib berasal dari .env server; tidak ada fallback password.
 */
declare(strict_types=1);

function kka_load_env(string $path): array {
    if (!is_file($path)) return [];
    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) $value = substr($value, 1, -1);
        $env[$key] = $value;
    }
    return $env;
}

$rootDir = dirname(__DIR__);
$envFile = $rootDir . '/.env';
$env = kka_load_env($envFile);

$required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($required as $key) {
    if (!array_key_exists($key, $env) || trim((string)$env[$key]) === '') {
        error_log('[KKA] Missing required environment variable: ' . $key);
        throw new RuntimeException('Konfigurasi server belum lengkap.');
    }
}

return [
    'root_dir' => $rootDir,
    'app_name' => $env['APP_NAME'] ?? 'Kertas Kerja Audit',
    'app_url' => $env['APP_URL'] ?? '',
    'app_env' => $env['APP_ENV'] ?? 'production',
    'app_debug' => filter_var($env['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'db_host' => $env['DB_HOST'],
    'db_port' => (int)($env['DB_PORT'] ?? 3306),
    'db_name' => $env['DB_NAME'],
    'db_user' => $env['DB_USER'],
    'db_pass' => $env['DB_PASS'],
    'admin_email' => $env['ADMIN_EMAIL'] ?? '',
    'admin_password' => $env['ADMIN_PASSWORD'] ?? '',
    'admin_nama' => $env['ADMIN_NAMA'] ?? 'Administrator',
    'session_name' => $env['SESSION_NAME'] ?? 'kka_sess',
    'session_lifetime' => max(900, (int)($env['SESSION_LIFETIME'] ?? 7200)),
    'mobile_api_allowed_origins' => $env['MOBILE_API_ALLOWED_ORIGINS'] ?? '',
    'mobile_api_access_ttl' => max(900, min((int)($env['MOBILE_API_ACCESS_TTL'] ?? 1800), 3600)),
    'upload_dir' => $rootDir . '/public/uploads',
    'allowed_mimes' => [
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
    ],
    'max_upload_mb' => 10,

    // Google Drive Integration
    'gdrive_enabled' => filter_var($env['GOOGLE_DRIVE_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'gdrive_client_id' => $env['GOOGLE_OAUTH_CLIENT_ID'] ?? '',
    'gdrive_client_secret' => $env['GOOGLE_OAUTH_CLIENT_SECRET'] ?? '',
    'gdrive_refresh_token' => $env['GOOGLE_OAUTH_REFRESH_TOKEN'] ?? '',
    'gdrive_parent_folder_name' => $env['GOOGLE_DRIVE_PARENT_FOLDER_NAME'] ?? 'KKA DIGITAL INSPEKTORAT',
    'gdrive_parent_folder_id' => !empty($env['GOOGLE_DRIVE_FOLDER_ID']) ? $env['GOOGLE_DRIVE_FOLDER_ID'] : null,
    'gdrive_account_email' => $env['GOOGLE_DRIVE_ACCOUNT_EMAIL'] ?? 'teamirban4@gmail.com',
];

