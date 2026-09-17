<?php
/**
 * KKA - Front controller / router
 * Inspektorat Kabupaten Rokan Hilir
 */

// ============================================================
// 🔥 KKA MOBILE API — INTERCEPTOR LAYER 0 (PALING AWAL! TIDAK TERGANTUNG REWRITE APAPUN!)
// 3 MODE DETEKSI (100% WORK DIMANAPUN):
//   A. CUSTOM HEADER:      X-KKA-API: 1          (Mobile App inject header ini di AXIOS INTERCEPTOR! PALING AMAN!)
//   B. ACCEPT JSON + BEARER (Axios default headers untuk panggilan API terautentikasi)
//   C. QUERY STRING:       ?_api=/desa           (fallback, jika headers tidak bisa diinject)
// ============================================================
function __kka_api_cors_and_headers(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type, Accept, Origin, X-Custom-Header, X-KKA-API');
    header('Access-Control-Expose-Headers: X-Total-Count, X-Pages, X-Per-Page');
    header('Access-Control-Max-Age: 86400');
    header('Vary: Origin');
    header('X-Robots-Tag: none');
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
    header('Content-Type: application/json; charset=utf-8');
}
$_kka_api_path = null;
// --- DETEKSY MODE A (PALING UTAMA DAN AMAN!): CUSTOM HEADER X-KKA-API ---
$header_kka_api = '';
foreach (['HTTP_X_KKA_API', 'X_KKA_API', 'X-KKA-API'] as $hk) {
    if (isset($_SERVER[$hk]) && is_string($_SERVER[$hk])) { $header_kka_api = $_SERVER[$hk]; break; }
}
if ($header_kka_api === '' && function_exists('getallheaders')) {
    $all_h = getallheaders();
    if (is_array($all_h)) {
        foreach ($all_h as $kn => $kv) {
            if (is_string($kn) && (strcasecmp($kn, 'X-KKA-API') === 0 || strcasecmp($kn, 'X_KKA_API') === 0)) {
                $header_kka_api = (string)$kv; break;
            }
        }
    }
}
if ($header_kka_api !== '' && $header_kka_api !== '0') {
    // DARI HEADER: path = ? cari dari query string _api, ATAU cari dari REQUEST_URI / PATH_INFO
    if (isset($_GET['_api']) && is_string($_GET['_api']) && $_GET['_api'] !== '') {
        $_kka_api_path = trim($_GET['_api']);
    } else {
        $uris_a = [
            $_SERVER['PATH_INFO'] ?? '', $_SERVER['ORIG_PATH_INFO'] ?? '',
            $_SERVER['REQUEST_URI'] ?? '', $_SERVER['ORIG_REQUEST_URI'] ?? '',
            $_SERVER['REDIRECT_URL'] ?? '', $_SERVER['PHP_SELF'] ?? '',
        ];
        foreach ($uris_a as $ua) {
            if (!is_string($ua) || $ua === '') continue;
            $uca = rawurldecode(strtok($ua, '?'));
            if ($uca === false || $uca === '') continue;
            $uca = rtrim($uca, '/');
            if ($uca === '' || $uca === '/') continue;
            $m = null;
            if (preg_match('#^(.*?)/api(?:/(.*)|$)#', $uca, $m)) {
                $sub_a = $m[2] ?? '';
                $_kka_api_path = $sub_a ? ('/' . ltrim($sub_a, '/')) : '/';
                break;
            }
        }
    }
}
// --- DETEKSY MODE B: Accept JSON + Authorization Bearer (Axios default API call) ---
if ($_kka_api_path === null) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (
        is_string($accept) && (stripos($accept, 'application/json') !== false || stripos($accept, '*/*') !== false)
        && is_string($auth) && stripos($auth, 'Bearer ') === 0
    ) {
        if (isset($_GET['_api']) && is_string($_GET['_api']) && $_GET['_api'] !== '') {
            $_kka_api_path = trim($_GET['_api']);
        }
    }
}
// --- DETEKSY MODE C: QUERY STRING _api / r / route ---
if ($_kka_api_path === null) {
    foreach (['_api', 'api', 'r', 'route', '_route', 'path', 'p'] as $gk) {
        if (isset($_GET[$gk]) && is_string($_GET[$gk]) && $_GET[$gk] !== '') {
            $gv = trim($_GET[$gk]);
            if ($gv === '' || $gv === '0') continue;
            if (
                str_starts_with($gv, '/')
                || str_starts_with($gv, 'api/') || $gv === 'api'
                || in_array($gv, ['/kecamatan','/desa','/bidang','/dashboard','/sesi','/rekap','/auth/login','/auth/me','/profile'])
            ) {
                $_kka_api_path = $gv;
                break;
            }
        }
    }
}
// --- DETEKSY MODE D (URI RAW, selalu): ---
if ($_kka_api_path === null) {
    $uris_d = [
        $_SERVER['REQUEST_URI'] ?? '', $_SERVER['ORIG_REQUEST_URI'] ?? '',
        $_SERVER['PATH_INFO'] ?? '', $_SERVER['ORIG_PATH_INFO'] ?? '',
        $_SERVER['REDIRECT_URL'] ?? '', $_SERVER['PHP_SELF'] ?? '',
    ];
    foreach ($uris_d as $ud) {
        if (!is_string($ud) || $ud === '') continue;
        $ucd = rawurldecode(strtok($ud, '?'));
        if ($ucd === false || $ucd === '') continue;
        $ucd = rtrim($ucd, '/');
        if ($ucd === '' || $ucd === '/') continue;
        $md = null;
        if (preg_match('#^(.*?)/api(?:/(.*)|$)#', $ucd, $md)) {
            $sub_d = $md[2] ?? '';
            $_kka_api_path = $sub_d ? ('/' . ltrim($sub_d, '/')) : '/';
            break;
        }
    }
}
// --- EXECUTE API ---
if ($_kka_api_path !== null && is_string($_kka_api_path) && $_kka_api_path !== '') {
    // NORMALIZE: strip /api prefix, ensure diawali /
    if (strncmp($_kka_api_path, '/api/', 5) === 0) $_kka_api_path = substr($_kka_api_path, 4);
    else if ($_kka_api_path === '/api') $_kka_api_path = '/';
    else if (strncmp($_kka_api_path, 'api/', 4) === 0) $_kka_api_path = '/' . substr($_kka_api_path, 4);
    else if ($_kka_api_path === 'api') $_kka_api_path = '/';
    $_kka_api_path = '/' . ltrim($_kka_api_path, '/');
    $_kka_api_path = rtrim($_kka_api_path, '/');
    if ($_kka_api_path === '') $_kka_api_path = '/';
    $GLOBALS['__KKA_FORCE_API_PATH__'] = $_kka_api_path;
    __kka_api_cors_and_headers();
    require __DIR__ . '/api/index.php';
    exit;
}

require_once __DIR__ . '/../src/bootstrap.php';

// Tentukan route dari path setelah base
$reqUri  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$basePath = $GLOBALS['app_base_url'];
$route   = '/' . ltrim(substr($reqUri, strlen($basePath)), '/');
$route   = rtrim($route, '/');
if ($route === '') $route = '/';

// ============================================================
// KKA MOBILE API — INTERCEPTOR LAYER 2 (SETELAH ROUTE PARSE!)
// Menangkap semua conflict names /desa, /kecamatan di Web App routes
// ============================================================
if (strncmp($route, '/api/', 5) === 0 || $route === '/api') {
    $_p = ($route === '/api') ? '/' : substr($route, 4);
    $_p = '/' . ltrim($_p, '/');
    $_p = rtrim($_p, '/');
    if ($_p === '') $_p = '/';
    $GLOBALS['__KKA_FORCE_API_PATH__'] = $_p;
    __kka_api_cors_and_headers();
    require __DIR__ . '/api/index.php';
    exit;
}

// Routing manual sederhana
// Format: [Controller, action]
$routes = [
    '/'                       => ['AuthController', 'home'],
    '/login'                  => ['AuthController', 'login'],
    '/logout'                 => ['AuthController', 'logout'],
    '/switch-role'            => ['AuthController', 'switchUser'],
    '/switch-user'            => ['AuthController', 'switchUser'],

    '/dashboard'              => ['DashboardController', 'index'],

    '/desa'                   => ['DesaController', 'index'],
    '/desa/store'             => ['DesaController', 'store'],
    '/desa/update'            => ['DesaController', 'update'],
    '/desa/delete'            => ['DesaController', 'delete'],
    '/kecamatan/store'        => ['DesaController', 'storeKec'],

    '/sesi'                   => ['SesiController', 'index'],
    '/sesi/create'            => ['SesiController', 'create'],
    '/sesi/store'             => ['SesiController', 'store'],
    '/sesi/show'              => ['SesiController', 'show'],
    '/sesi/edit'              => ['SesiController', 'edit'],
    '/sesi/update'            => ['SesiController', 'update'],
    '/sesi/delete'            => ['SesiController', 'delete'],
    '/sesi/sub-bidang'        => ['SesiController', 'subBidangJson'],
    '/sesi/ajukan'            => ['SesiController', 'ajukan'],
    '/sesi/reviu-ketua'       => ['SesiController', 'reviuKetua'],
    '/sesi/reviu-dalnis'      => ['SesiController', 'reviuDalnis'],
    '/sesi/update-routing-slip' => ['SesiController', 'updateRoutingSlip'],

    '/rincian/store'          => ['RincianController', 'store'],
    '/rincian/update'         => ['RincianController', 'update'],
    '/rincian/delete'         => ['RincianController', 'delete'],
    '/rincian/template'       => ['RincianController', 'downloadTemplate'],
    '/rincian/import'         => ['RincianController', 'importExcel'],

    '/lampiran/upload'        => ['LampiranController', 'upload'],
    '/lampiran/delete'        => ['LampiranController', 'delete'],
    '/lampiran/download'      => ['LampiranController', 'download'],

    '/rekap'                  => ['RekapController', 'index'],
    '/rekap/data'             => ['RekapController', 'data'],

    '/master'                 => ['MasterKkaController', 'index'],
    '/master/create'          => ['MasterKkaController', 'create'],
    '/master/store'           => ['MasterKkaController', 'store'],
    '/master/edit'            => ['MasterKkaController', 'edit'],
    '/master/update'          => ['MasterKkaController', 'update'],
    '/master/delete'          => ['MasterKkaController', 'delete'],
    '/master/upload-foto'     => ['MasterKkaController', 'uploadFoto'],
    '/master/delete-foto'     => ['MasterKkaController', 'deleteFoto'],
    '/master/foto'            => ['MasterKkaController', 'foto'],
    '/master/preview'         => ['MasterKkaController', 'preview'],
    '/master/export'          => ['MasterKkaController', 'export'],
    '/master/template'        => ['MasterKkaController', 'downloadTemplate'],

    '/print/sesi'             => ['PrintController', 'sesi'],
    '/print/reviu'            => ['PrintController', 'reviu'],
    '/print/routing-slip'     => ['PrintController', 'routingSlip'],
    '/export/sesi'            => ['PrintController', 'exportExcel'],
    '/export/rekap'           => ['PrintController', 'exportRekap'],

    '/users'                  => ['UserController', 'index'],
    '/users/store'            => ['UserController', 'store'],
    '/users/update'           => ['UserController', 'update'],
    '/users/delete'           => ['UserController', 'delete'],
    '/profile'                => ['UserController', 'profile'],
    '/profile/update'         => ['UserController', 'updateProfile'],

    '/panduan-workflow'       => ['DashboardController', 'workflow'],

    // Alur Pra-Audit: Nota Dinas, SPT, dan Matriks PKA
    '/penugasan/nota-dinas'           => ['PenugasanController', 'notaDinas'],
    '/penugasan/nota-dinas/create'    => ['PenugasanController', 'notaDinasCreate'],
    '/penugasan/nota-dinas/store'     => ['PenugasanController', 'notaDinasStore'],
    '/penugasan/nota-dinas/edit'      => ['PenugasanController', 'notaDinasEdit'],
    '/penugasan/nota-dinas/update'    => ['PenugasanController', 'notaDinasUpdate'],
    '/penugasan/nota-dinas/delete'    => ['PenugasanController', 'notaDinasDelete'],
    '/penugasan/nota-dinas/disposisi' => ['PenugasanController', 'notaDinasDisposisi'],

    '/penugasan/spt'                  => ['PenugasanController', 'spt'],
    '/penugasan/spt/create'           => ['PenugasanController', 'sptCreate'],
    '/penugasan/spt/store'            => ['PenugasanController', 'sptStore'],
    '/penugasan/spt/sahkan'           => ['PenugasanController', 'sptSahkan'],

    '/penugasan/pka'                  => ['PenugasanController', 'pka'],
    '/penugasan/pka/show'             => ['PenugasanController', 'pkaShow'],
    '/penugasan/pka/update'           => ['PenugasanController', 'pkaUpdate'],
    '/penugasan/pka/approve'          => ['PenugasanController', 'pkaApprove'],

    '/print/nota-dinas'               => ['PenugasanController', 'printNotaDinas'],
    '/print/spt'                      => ['PenugasanController', 'printSpt'],
    '/print/pka'                      => ['PenugasanController', 'printPka'],

    // Modul Konsep Temuan Pemeriksaan (KTP 5 Unsur)
    '/temuan'                         => ['TemuanController', 'index'],
    '/temuan/create'                  => ['TemuanController', 'create'],
    '/temuan/store'                   => ['TemuanController', 'store'],
    '/temuan/edit'                    => ['TemuanController', 'edit'],
    '/temuan/update'                  => ['TemuanController', 'update'],
    '/temuan/delete'                  => ['TemuanController', 'delete'],
    '/print/matriks-temuan'           => ['TemuanController', 'matriks'],

    // Modul Laporan Hasil Pengawasan (LHP) Otomatis Desa
    '/lhp'                            => ['LhpController', 'index'],
    '/lhp/show'                       => ['LhpController', 'show'],
    '/print/lhp'                      => ['LhpController', 'print'],
];

if (!isset($routes[$route])) {
    http_response_code(404);
    view('errors/404');
    exit;
}

[$controllerName, $action] = $routes[$route];
$controllerClass = $controllerName;
$controller = new $controllerClass($auth);
$controller->$action();
