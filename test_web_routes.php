<?php
declare(strict_types=1);

/**
 * Test Web Routes & Controller Actions (Subprocess Isolated)
 * Menguji setiap route dan controller secara terisolasi sehingga redirect/exit tidak memutus pengujian.
 */

require_once __DIR__ . '/src/bootstrap.php';

echo "========================================================\n";
echo "🧪 PENGUJIAN MENYELURUH SETIAP ROUTE & CONTROLLER\n";
echo "Tanggal Uji: " . date('Y-m-d H:i:s') . "\n";
echo "========================================================\n\n";

$roles = ['admin', 'inspektur', 'irban', 'dalnis', 'ketua_tim', 'auditor', 'operator_spt', 'operator_tl'];

// Jika role ketua_tim tidak ada di kka_users, buat alias atau cek apakah ada ketua
$ketuaUser = DB::one("SELECT * FROM kka_users WHERE role = 'ketua_tim' LIMIT 1");
if (!$ketuaUser) {
    // Cek apakah ada role ketua atau auditor yang bertindak sebagai ketua
    $ketuaUser = DB::one("SELECT * FROM kka_users WHERE role = 'ketua' LIMIT 1") 
              ?? DB::one("SELECT * FROM kka_users WHERE role = 'auditor' LIMIT 1");
}

// Data sample IDs
$sampleDesaId   = (int)(DB::val("SELECT id FROM kka_desa LIMIT 1") ?? 1);
$sampleSesiId   = (int)(DB::val("SELECT id FROM kka_sesi LIMIT 1") ?? 1);
$sampleSptId    = (int)(DB::val("SELECT id FROM kka_spt LIMIT 1") ?? 1);
$sampleNdId     = (int)(DB::val("SELECT id FROM kka_nota_dinas LIMIT 1") ?? 1);
$samplePkaId    = (int)(DB::val("SELECT id FROM kka_pka LIMIT 1") ?? 1);
$sampleOpnameId = (int)(DB::val("SELECT id FROM kka_opname_kas LIMIT 1") ?? 1);
$sampleTemuanId = (int)(DB::val("SELECT id FROM kka_temuan LIMIT 1") ?? 1);
$samplePiaNdId  = (int)(DB::val("SELECT id FROM kka_pia_nd LIMIT 1") ?? 1);
$samplePiaSptId = (int)(DB::val("SELECT id FROM kka_pia_spt LIMIT 1") ?? 1);
$samplePiaLhpId = (int)(DB::val("SELECT id FROM kka_pia_lhp LIMIT 1") ?? 1);

$routesToTest = [
    ['Dashboard (Admin)', 'DashboardController', 'index', 'admin', []],
    ['Dashboard (Inspektur)', 'DashboardController', 'index', 'inspektur', []],
    ['Dashboard (Irban)', 'DashboardController', 'index', 'irban', []],
    ['Dashboard (Dalnis)', 'DashboardController', 'index', 'dalnis', []],
    ['Dashboard (Ketua Tim)', 'DashboardController', 'index', 'auditor', []],
    ['Dashboard (Auditor)', 'DashboardController', 'index', 'auditor', []],
    ['Dashboard (Operator SPT)', 'DashboardController', 'index', 'operator_spt', []],
    ['Dashboard (Operator TL)', 'DashboardController', 'index', 'operator_tl', []],
    ['Panduan Workflow', 'DashboardController', 'workflow', 'auditor', []],

    ['Penugasan: Nota Dinas (Irban)', 'PenugasanController', 'notaDinas', 'irban', []],
    ['Penugasan: Buat ND (Irban)', 'PenugasanController', 'notaDinasCreate', 'irban', []],
    ['Penugasan: SPT (Inspektur)', 'PenugasanController', 'spt', 'inspektur', []],
    ['Penugasan: Buat SPT (Operator SPT)', 'PenugasanController', 'sptCreate', 'operator_spt', ['nd_id' => $sampleNdId]],
    ['Penugasan: Matriks PKA (Ketua Tim)', 'PenugasanController', 'pka', 'auditor', []],
    ['Penugasan: Cetak ND', 'PenugasanController', 'printNotaDinas', 'irban', ['id' => $sampleNdId]],
    ['Penugasan: Cetak SPT', 'PenugasanController', 'printSpt', 'inspektur', ['id' => $sampleSptId]],
    ['Penugasan: Cetak PKA', 'PenugasanController', 'printPka', 'auditor', ['id' => $sampleSptId]],

    ['PIA: Nota Dinas (Irban)', 'PiaController', 'notaDinas', 'irban', []],
    ['PIA: Buat ND (Irban)', 'PiaController', 'notaDinasCreate', 'irban', []],
    ['PIA: SPT (Inspektur)', 'PiaController', 'spt', 'inspektur', []],
    ['PIA: LHP (Ketua Tim)', 'PiaController', 'lhp', 'auditor', []],
    ['PIA: Cetak ND', 'PiaController', 'printNotaDinas', 'irban', ['id' => $samplePiaNdId]],
    ['PIA: Cetak SPT', 'PiaController', 'printSpt', 'inspektur', ['id' => $samplePiaSptId]],

    ['Opname Kas: Index (Auditor)', 'OpnameKasController', 'index', 'auditor', []],
    ['Opname Kas: Create (Auditor)', 'OpnameKasController', 'create', 'auditor', []],
    ['Opname Kas: Edit (Auditor)', 'OpnameKasController', 'edit', 'auditor', ['id' => $sampleOpnameId]],
    ['Opname Kas: Cetak BAP', 'OpnameKasController', 'print', 'auditor', ['id' => $sampleOpnameId]],

    ['Aspek Keuangan: Index (Auditor)', 'AspekKeuanganController', 'index', 'auditor', ['desa_id' => $sampleDesaId]],
    ['Aspek Keuangan: Cetak', 'AspekKeuanganController', 'print', 'auditor', ['desa_id' => $sampleDesaId]],

    ['KKA Sesi: Index (Auditor)', 'SesiController', 'index', 'auditor', []],
    ['KKA Sesi: Create (Auditor)', 'SesiController', 'create', 'auditor', []],
    ['KKA Sesi: Show Detail (Admin)', 'SesiController', 'show', 'admin', ['id' => $sampleSesiId]],
    ['KKA Sesi: Edit (Admin)', 'SesiController', 'edit', 'admin', ['id' => $sampleSesiId]],
    ['KKA Sesi: Print KKA', 'PrintController', 'sesi', 'admin', ['id' => $sampleSesiId]],
    ['KKA Sesi: Print Reviu', 'PrintController', 'reviu', 'dalnis', ['id' => $sampleSesiId]],

    ['Temuan: Index (Auditor)', 'TemuanController', 'index', 'auditor', []],
    ['Temuan: Create (Auditor)', 'TemuanController', 'create', 'auditor', []],
    ['Temuan: Edit (Auditor)', 'TemuanController', 'edit', 'auditor', ['id' => $sampleTemuanId]],
    ['Temuan: Matriks', 'TemuanController', 'matriks', 'auditor', ['desa_id' => $sampleDesaId]],
    ['Temuan: Cetak NHP', 'TemuanController', 'nhp', 'auditor', ['desa_id' => $sampleDesaId]],
    ['Temuan: Cetak P2HP', 'TemuanController', 'p2hp', 'auditor', ['desa_id' => $sampleDesaId]],
    ['Temuan: Cetak BA Kesepakatan', 'TemuanController', 'baKesepakatan', 'auditor', ['desa_id' => $sampleDesaId]],

    ['LHP: Index (Inspektur)', 'LhpController', 'index', 'inspektur', []],
    ['LHP: Show (Inspektur)', 'LhpController', 'show', 'inspektur', ['desa_id' => $sampleDesaId]],
    ['LHP: Edit Narasi (Auditor)', 'LhpController', 'edit', 'auditor', ['desa_id' => $sampleDesaId]],
    ['LHP: Cetak Naskah', 'LhpController', 'print', 'inspektur', ['desa_id' => $sampleDesaId]],

    ['Routing Slip: Index (Dalnis)', 'RoutingSlipController', 'index', 'dalnis', []],
    ['Routing Slip: Show (Dalnis)', 'RoutingSlipController', 'show', 'dalnis', ['desa_id' => $sampleDesaId]],

    ['TLHP: Index (Operator TL)', 'TlhpController', 'index', 'operator_tl', []],
    ['TLHP: Matriks Rekap', 'TlhpController', 'matriks', 'operator_tl', []],
    ['TLHP: Laporan Rekap', 'TlhpController', 'rekap', 'inspektur', []],

    ['Google Drive: Index (Admin)', 'GoogleDriveController', 'index', 'admin', []],
    ['Google Drive: Test (Admin)', 'GoogleDriveController', 'test', 'admin', []],

    ['Master Desa: Index (Admin)', 'DesaController', 'index', 'admin', []],
    ['Master KKA: Index (Admin)', 'MasterKkaController', 'index', 'admin', []],
    ['Manajemen User (Admin)', 'UserController', 'index', 'admin', []],
    ['Profil Pengguna', 'UserController', 'profile', 'auditor', []],
    ['Rekapitulasi KKA (Admin)', 'RekapController', 'index', 'admin', []],
    ['Simulasi Peran (Switch User)', 'AuthController', 'switchUser', 'admin', ['user' => 'marwan', 'csrf' => 'test_token']],
    ['Kembali ke Admin (Restore User)', 'AuthController', 'switchUser', 'admin', ['user' => 'restore', 'csrf' => 'test_token']],
];

$passed = 0;
$failed = 0;
$issues = [];

foreach ($routesToTest as [$label, $ctrlClass, $action, $role, $params]) {
    $u = DB::one("SELECT * FROM kka_users WHERE role = ? AND is_active = 1 LIMIT 1", [$role]);
    if (!$u) {
        $u = DB::one("SELECT * FROM kka_users WHERE is_active = 1 LIMIT 1");
    }

    $paramsJson = base64_encode(json_encode($params));
    $userJson   = base64_encode(json_encode($u));

    // Kode worker per-route
    $workerCode = '
        error_reporting(E_ALL);
        ini_set("display_errors", "1");
        require_once "' . __DIR__ . '/src/bootstrap.php";
        
        $user = json_decode(base64_decode("' . $userJson . '"), true);
        $params = json_decode(base64_decode("' . $paramsJson . '"), true);
        
        $_SESSION["uid"] = $user["id"];
        $_SESSION["user"] = $user;
        $_SESSION["_csrf"] = "test_token";
        $_GET = $params;
        $_POST = [];
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";
        
        $auth = new Auth();
        $ctrl = new ' . $ctrlClass . '($auth);
        $ctrl->' . $action . '();
    ';

    $descriptorSpec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    $process = proc_open("php", $descriptorSpec, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], '<?php ' . $workerCode);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // Periksa error fatal atau exception
        $hasFatal = false;
        $errMsg = '';
        if ($exitCode !== 0 && !empty($stderr)) {
            $hasFatal = true;
            $errMsg = trim($stderr);
        } elseif (preg_match('/(Fatal error|Parse error|Uncaught Exception|SQLSTATE\[[0-9A-Z]+\]:)/i', $stdout . $stderr, $m)) {
            $hasFatal = true;
            $errMsg = $m[0] . ' (terdeteksi pada output)';
        }

        if ($hasFatal) {
            echo sprintf("%-40s ❌ ERROR: %s\n", $label, substr($errMsg, 0, 100));
            $failed++;
            $issues[] = "$label: $errMsg";
        } else {
            echo sprintf("%-40s ✅ BERHASIL (%d bytes)\n", $label, strlen($stdout));
            $passed++;
        }
    }
}

echo "\n========================================================\n";
echo "📊 HASIL PENGUJIAN ROUTE:\n";
echo "Berhasil : $passed\n";
echo "Gagal    : $failed\n";
echo "========================================================\n";

if ($failed > 0) {
    echo "\nDAFTAR ERROR YANG HARUS DIPERBAIKI:\n";
    foreach ($issues as $idx => $iss) {
        echo ($idx + 1) . ". $iss\n";
    }
}
