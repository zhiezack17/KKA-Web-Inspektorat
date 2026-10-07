<?php
/**
 * AuditTrail - Pencatatan Jejak Keputusan & Log Audit Resmi
 * Inspektorat Kabupaten Rokan Hilir - KKA Digital
 * Memenuhi Standar Pengawasan Intern Pemerintah (APIP) & Tindak Lanjut F02/F03/F04
 */

declare(strict_types=1);

class AuditTrail
{
    private static bool $tableChecked = false;

    private static function ensureTable(): void
    {
        if (self::$tableChecked) {
            return;
        }

        try {
            DB::q("
                CREATE TABLE IF NOT EXISTS `kka_audit_trail` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `entity_type` VARCHAR(50) NOT NULL,
                    `entity_id` INT UNSIGNED NOT NULL,
                    `user_id` INT UNSIGNED DEFAULT NULL,
                    `user_nama` VARCHAR(150) DEFAULT NULL,
                    `user_role` VARCHAR(50) DEFAULT NULL,
                    `action` VARCHAR(50) NOT NULL,
                    `old_status` VARCHAR(50) DEFAULT NULL,
                    `new_status` VARCHAR(50) DEFAULT NULL,
                    `keterangan` TEXT DEFAULT NULL,
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX (`entity_type`, `entity_id`),
                    INDEX (`created_at`),
                    INDEX (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            self::$tableChecked = true;
        } catch (Throwable $e) {
            error_log('AuditTrail::ensureTable failed: ' . $e->getMessage());
        }
    }

    /**
     * Catat aksi pengesahan, reviu, perubahan status, atau penguncian data.
     */
    public static function record(
        string $entityType,
        int $entityId,
        string $action,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?string $keterangan = null,
        ?array $user = null
    ): void {
        self::ensureTable();

        try {
            if ($user === null) {
                if (isset($_SESSION['uid'])) {
                    $uid = (int) $_SESSION['uid'];
                    $user = DB::one('SELECT id, nama, role FROM kka_users WHERE id = ?', [$uid]);
                }
            }

            $userId   = $user['id'] ?? null;
            $userNama = $user['nama'] ?? 'System';
            $userRole = $user['role'] ?? 'guest';
            $ip       = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            DB::insert('kka_audit_trail', [
                'entity_type' => strtolower($entityType),
                'entity_id'   => $entityId,
                'user_id'     => $userId ? (int)$userId : null,
                'user_nama'   => $userNama,
                'user_role'   => $userRole,
                'action'      => strtoupper($action),
                'old_status'  => $oldStatus,
                'new_status'  => $newStatus,
                'keterangan'  => $keterangan,
                'ip_address'  => substr($ip, 0, 45),
            ]);
        } catch (Throwable $e) {
            error_log(sprintf(
                '[AUDIT_TRAIL] Gagal mencatat log [%s #%d action: %s]: %s',
                $entityType,
                $entityId,
                $action,
                $e->getMessage()
            ));
        }
    }
}
