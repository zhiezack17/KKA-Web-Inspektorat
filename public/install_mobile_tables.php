<?php
/**
 * Installer tabel KKA Mobile API.
 * Setelah dijalankan, HAPUS file ini dari server demi keamanan!
 */
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

echo "<pre style='font-family:monospace;font-size:14px;line-height:1.8;'>";
echo str_repeat("=", 60) . "\n";
echo "   INSTALLER TABEL KKA MOBILE API\n";
echo str_repeat("=", 60) . "\n\n";

$tables = [
    'kka_api_tokens' => "
        CREATE TABLE IF NOT EXISTS `kka_api_tokens` (
          `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT UNSIGNED NOT NULL,
          `token` VARCHAR(160) NOT NULL UNIQUE,
          `device_name` VARCHAR(100) DEFAULT NULL,
          `expires_at` DATETIME DEFAULT NULL,
          `last_used_at` DATETIME DEFAULT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          CONSTRAINT fk_token_user FOREIGN KEY (`user_id`) REFERENCES `kka_users`(`id`) ON DELETE CASCADE,
          INDEX (`user_id`), INDEX (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'kka_sesi_share' => "
        CREATE TABLE IF NOT EXISTS `kka_sesi_share` (
          `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          `sesi_id` INT UNSIGNED NOT NULL,
          `user_id` INT UNSIGNED NOT NULL,
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          CONSTRAINT fk_share_sesi FOREIGN KEY (`sesi_id`) REFERENCES `kka_sesi`(`id`) ON DELETE CASCADE,
          CONSTRAINT fk_share_user FOREIGN KEY (`user_id`) REFERENCES `kka_users`(`id`) ON DELETE CASCADE,
          UNIQUE KEY (`sesi_id`, `user_id`),
          INDEX (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
];

foreach ($tables as $name => $sql) {
    echo "[+] Membuat tabel $name ... ";
    try {
        DB::q($sql);
        $safeName = addslashes($name);
        $row = DB::one("SHOW TABLES LIKE '$safeName'");
        $ok = ($row && !empty($row));
        if (!$ok) {
            $row2 = DB::scalar("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$safeName'");
            $ok = ((int)$row2 > 0);
        }
        echo ($ok
            ? "<span style='color:green;font-weight:bold'>BERHASIL ✅</span>"
            : "<span style='color:orange'>SELESAI (silakan cek di daftar tabel) ⚠️</span>") . "\n";
    } catch (Throwable $e) {
        echo "<span style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }
}

echo "\n[🔍] Verifikasi semua tabel yang tersedia di database kka_db:\n";
try {
    $all = DB::all("SHOW TABLES");
    foreach ($all as $r) {
        $tname = array_values((array)$r)[0] ?? '';
        if ($tname !== '') {
            $mark = in_array($tname, ['kka_api_tokens', 'kka_sesi_share']) ? '⭐' : '  ';
            echo "   $mark - $tname\n";
        }
    }
} catch (Throwable $e) {
    echo "   (Tidak bisa menampilkan daftar tabel: " . htmlspecialchars($e->getMessage()) . ")\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ SELESAI! " . str_repeat("⚠️", 3) . " SEGERA HAPUS FILE INI!\n";
echo "   Cara hapus: aaPanel → File Manager → public/ → hapus install_mobile_tables.php\n";
echo str_repeat("=", 60) . "\n";
echo "</pre>";