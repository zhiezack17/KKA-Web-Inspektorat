<?php
declare(strict_types=1);

require __DIR__ . '/../../src/api_bootstrap.php';

$apiAuth = $GLOBALS['apiAuth'];

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================
// 🔥 PRIORITAS #1: FORCE API PATH dari Interceptor (QUERY STRING / ROUTE LAYER)
// (100% WORK tanpa .htaccess / mod_rewrite apapun!)
// ============================================================
$forcedPath = $GLOBALS['__KKA_FORCE_API_PATH__'] ?? $GLOBALS['__KKA_FORCE_API_ROUTE__'] ?? null;
$path = '/';
$segments = [];
if (is_string($forcedPath) && $forcedPath !== '') {
    $p = rtrim($forcedPath, '/');
    if ($p === '' || $p === '/api') $p = '/';
    if (strncmp($p, '/api', 4) === 0) {
        $p = (string)substr($p, 4);
        $p = '/' . ltrim($p, '/');
        $p = rtrim($p, '/');
        if ($p === '') $p = '/';
    }
    $path = $p;
    $segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
} else {
    // ============================================================
    // PRIORITAS #2: QUERY STRING _api / r / route (fallback)
    // ============================================================
    foreach (['_api', 'r', 'route', '_route', 'path', 'p'] as $gk) {
        if (isset($_GET[$gk]) && is_string($_GET[$gk]) && $_GET[$gk] !== '') {
            $gv = trim($_GET[$gk]);
            if (strncmp($gv, '/api', 4) === 0) $gv = '/' . ltrim(substr($gv, 4), '/');
            if (strncmp($gv, 'api', 3) === 0) $gv = '/' . substr($gv, 3);
            if ($gv === '' || $gv === '/') { $path = '/'; $segments = []; break; }
            if ($gv[0] !== '/') $gv = '/' . $gv;
            $gv = rtrim($gv, '/');
            $path = $gv;
            $segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
            break;
        }
    }
    // ============================================================
    // PRIORITAS #3: URI / PATH INFO / SCRIPT NAME (fallback terakir)
    // ============================================================
    if ($segments === []) {
        $tryUris = array_filter([
            $_SERVER['PATH_INFO'] ?? null, $_SERVER['ORIG_PATH_INFO'] ?? null,
            $_SERVER['REQUEST_URI'] ?? null, $_SERVER['ORIG_REQUEST_URI'] ?? null,
            $_SERVER['REDIRECT_URL'] ?? null, $_SERVER['PHP_SELF'] ?? null,
        ]);
        $path = '/';
        foreach ($tryUris as $candidate) {
            if (!is_string($candidate) || $candidate === '') continue;
            $cleaned = rawurldecode(strtok($candidate, '?'));
            if ($cleaned === false || $cleaned === '') continue;
            $cleaned = preg_replace('#^[^/]*?/api(?:/|$)#', '/', $cleaned);
            if ($cleaned === null) continue;
            $cleaned = rtrim($cleaned, '/');
            if ($cleaned === '' || $cleaned === '/api') $cleaned = '/';
            if (strlen($cleaned) > 0) {
                $path = $cleaned;
                break;
            }
        }
        if ($path === '/' || $path === '') {
            foreach ($tryUris as $candidate) {
                if (!is_string($candidate)) continue;
                $segs = array_values(array_filter(explode('/', rawurldecode(strtok($candidate, '?'))), fn($s) => $s !== ''));
                if (count($segs) > 0 && strcasecmp($segs[0], 'api') === 0) array_shift($segs);
                if (count($segs) > 0) {
                    $path = '/' . implode('/', $segs);
                    break;
                }
            }
        }
        $segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
    }
}

function api_segments(): array { global $segments; return $segments; }
function api_segment(int $n, $default = null) {
    global $segments;
    return $segments[$n] ?? $default;
}

function api_owner_where($apiAuth, string $col = 's.created_by'): array {
    if ($apiAuth && $apiAuth->isAdmin()) return ['', []];
    $uid = $apiAuth ? (int)$apiAuth->id() : 0;
    $alias = strpos($col, '.') !== false ? substr($col, 0, strpos($col, '.')) : $col;
    return [
        " AND ($col = ? OR $alias.id IN (SELECT sesi_id FROM kka_sesi_share WHERE user_id = ?))",
        [$uid, $uid],
    ];
}

function api_sesi_is_owned($apiAuth, ?array $sesi): bool {
    if (!$sesi || !$apiAuth) return false;
    if ($apiAuth->isAdmin()) return true;
    $uid = (int)$apiAuth->id();
    if ((int)($sesi['created_by'] ?? 0) === $uid) return true;
    $sid = (int)($sesi['sesi_id'] ?? $sesi['id'] ?? 0);
    if ($sid > 0) {
        return (bool)DB::scalar(
            'SELECT 1 FROM kka_sesi_share WHERE sesi_id = ? AND user_id = ? LIMIT 1',
            [$sid, $uid]
        );
    }
    return false;
}

try {
    if ($segments === [] || $segments[0] === 'index.php') {
        api_response(200, true, 'KKA Mobile API v1.0 - Service berjalan', [
            'name' => 'KKA Mobile API',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoints' => [
                'auth' => ['POST /auth/login', 'POST /auth/logout', 'GET /auth/me'],
                'dashboard' => ['GET /dashboard'],
                'sesi' => ['GET /sesi', 'GET /sesi/{id}', 'POST /sesi', 'PUT /sesi/{id}', 'DELETE /sesi/{id}'],
                'rincian' => ['GET /sesi/{id}/rincian', 'POST /sesi/{id}/rincian', 'PUT /rincian/{id}', 'DELETE /rincian/{id}'],
                'lampiran' => ['GET /sesi/{id}/lampiran', 'POST /sesi/{id}/lampiran', 'DELETE /lampiran/{id}'],
                'master' => ['GET /kecamatan', 'GET /desa', 'GET /bidang', 'GET /bidang/{id}/sub-bidang'],
                'users' => ['GET /users', 'GET /profile', 'PUT /profile', 'PUT /profile/password'],
            ]
        ]);
    }

    $resource = $segments[0] ?? '';
    $subRes1  = $segments[1] ?? null;
    $subRes2  = $segments[2] ?? null;
    $subRes3  = $segments[3] ?? null;

    if ($resource === 'auth') {
        require __DIR__ . '/../../src/api/AuthApi.php';
        exit;
    }

    if ($resource === 'dashboard') {
        require __DIR__ . '/../../src/api/DashboardApi.php';
        exit;
    }

    if ($resource === 'sesi') {
        require __DIR__ . '/../../src/api/SesiApi.php';
        exit;
    }

    if ($resource === 'rincian') {
        require __DIR__ . '/../../src/api/RincianApi.php';
        exit;
    }

    if ($resource === 'lampiran') {
        require __DIR__ . '/../../src/api/LampiranApi.php';
        exit;
    }

    if ($resource === 'kecamatan' || $resource === 'desa' || $resource === 'bidang') {
        require __DIR__ . '/../../src/api/MasterApi.php';
        exit;
    }

    if ($resource === 'users' || $resource === 'profile') {
        require __DIR__ . '/../../src/api/UsersApi.php';
        exit;
    }

    if ($resource === 'rekap') {
        require __DIR__ . '/../../src/api/RekapApi.php';
        exit;
    }

    api_response(404, false, 'Endpoint tidak ditemukan: ' . $path);

} catch (Throwable $e) {
    global $cfg;
    $detail = $cfg['app_debug'] ? $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() : '';
    api_response(500, false, 'Internal server error' . ($detail ? " - $detail" : ''));
}
