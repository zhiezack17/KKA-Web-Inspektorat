<?php
declare(strict_types=1);

/**
 * DashboardController - Role-Based Operational Command Center
 * Inspektorat Kabupaten Rokan Hilir
 * Mengimplementasikan Rekomendasi Dashboard KKA Digital (Analisis Tim Pengembang):
 * - Inspektur: Ringkasan strategis, cakupan 183 desa, antrean keputusan (ND, SPT, LHP), risiko tinggi.
 * - Irban: Pengawasan wilayah, sesi stagnan >7 hari, tren temuan, antrean review.
 * - Dalnis: Kendali mutu teknis, antrean review KKA, revisi terbuka, SOP checklist QA.
 * - Ketua Tim: Ruang kendali tim, progres KKA 5 bidang, kelengkapan uji & bukti, antrean submit Dalnis.
 * - Anggota Tim (Auditor): "Tugas Saya Hari Ini" (KKA aktif, belanja belum verif fisik, kwitansi kosong, pajak belum setor).
 * - Perencanaan (Operator SPT): Realisasi PKPT, antrean penerbitan SPT dari ND disetujui, kontrol nomor terakhir.
 * - Evlap (Operator TLHP): Monitoring TLHP 60 hari, recovery kas desa (STS Bank), antrean verifikasi bukti.
 * - Admin: Overview eksekutif penuh + Switcher Role Preview untuk verifikasi/demo seluruh peran.
 */
class DashboardController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $auth->require();
    }

    public function index(): void {
        $user = $this->auth->user();
        $uid  = (int)$this->auth->id();

        // 1. Filter Global: Tahun Anggaran, Semester, Kecamatan, Desa
        $filterTahun     = input('tahun', '');
        $filterSemester  = input('semester', '');
        $filterKecamatan = (int) input('kecamatan_id', 0);
        $filterDesa      = (int) input('desa_id', 0);

        // Switcher Role Preview khusus Admin (untuk simulasi/verifikasi peran)
        $viewAs = input('view_as', '');
        $allowedRoles = ['admin', 'inspektur', 'irban', 'dalnis', 'ketua', 'auditor', 'operator_spt', 'operator_tl'];

        if ($this->auth->isAdmin() && in_array($viewAs, $allowedRoles, true)) {
            $activeRole = $viewAs;
        } else {
            if ($this->auth->isAdmin()) {
                $activeRole = 'admin';
            } elseif ($this->auth->isInspektur()) {
                $activeRole = 'inspektur';
            } elseif ($this->auth->isIrban()) {
                $activeRole = 'irban';
            } elseif ($this->auth->isDalnis()) {
                $activeRole = 'dalnis';
            } elseif ($this->auth->isKetua()) {
                $activeRole = 'ketua';
            } elseif ($this->auth->isOperatorSpt()) {
                $activeRole = 'operator_spt';
            } elseif ($this->auth->isOperatorTl()) {
                $activeRole = 'operator_tl';
            } else {
                $activeRole = 'auditor';
            }
        }

        // 2. Daftar Opsi Filter
        $daftarTahun = DB::all("
            SELECT DISTINCT tahun FROM (
                SELECT tahun_anggaran AS tahun FROM kka_sesi
                UNION
                SELECT tahun_anggaran AS tahun FROM kka_temuan
                UNION
                SELECT CAST(YEAR(CURRENT_DATE) AS SIGNED) AS tahun
            ) t ORDER BY tahun DESC
        ");
        $daftarTahun = array_column($daftarTahun, 'tahun');

        $daftarKecamatan = DB::all("SELECT id, nama FROM kka_kecamatan ORDER BY nama ASC");
        
        $whereDesaOpt = $filterKecamatan > 0 ? "WHERE kecamatan_id = $filterKecamatan" : "";
        $daftarDesa = DB::all("SELECT id, nama, kecamatan_id FROM kka_desa $whereDesaOpt ORDER BY nama ASC");

        // 3. Bangun Klausa Filter Query untuk Sesi & Rincian
        [$ow, $op] = owner_where($this->auth);
        
        // Jika sedang preview role auditor / ketua / dalnis saat login sebagai admin
        if ($this->auth->isAdmin() && $activeRole === 'auditor') {
            // Contoh filter data milik akun auditor
            $ow = " AND (s.created_by = $uid OR s.id IN (SELECT sesi_id FROM kka_sesi_share WHERE user_id = $uid))";
            $op = [];
        }

        $filterSql = "";
        $filterParams = [];

        if ($filterTahun !== '' && is_numeric($filterTahun)) {
            $filterSql .= " AND s.tahun_anggaran = ?";
            $filterParams[] = (int)$filterTahun;
        }
        if ($filterSemester !== '' && in_array($filterSemester, ['1', '2'], true)) {
            $filterSql .= " AND s.semester = ?";
            $filterParams[] = (int)$filterSemester;
        }
        if ($filterKecamatan > 0) {
            $filterSql .= " AND d.kecamatan_id = ?";
            $filterParams[] = $filterKecamatan;
        }
        if ($filterDesa > 0) {
            $filterSql .= " AND s.desa_id = ?";
            $filterParams[] = $filterDesa;
        }

        // Gabungan klausa where sesi
        $sesiWhere = "WHERE 1=1 $ow $filterSql";
        $sesiParams = array_merge($op, $filterParams);

        // 4. Metrik Eksekutif Utama (KPI Cards dengan Konteks)
        $totalDesaKabupaten = (int) DB::scalar("SELECT COUNT(*) FROM kka_desa"); // Standar 183 Desa Rohil
        $totalKecamatan = (int) DB::scalar("SELECT COUNT(*) FROM kka_kecamatan"); // 18 Kecamatan

        $desaDiaudit = (int) DB::scalar("
            SELECT COUNT(DISTINCT s.desa_id) 
            FROM kka_sesi s 
            JOIN kka_desa d ON d.id = s.desa_id 
            $sesiWhere
        ", $sesiParams);

        $totalSesi = (int) DB::scalar("
            SELECT COUNT(s.id) 
            FROM kka_sesi s 
            JOIN kka_desa d ON d.id = s.desa_id 
            $sesiWhere
        ", $sesiParams);

        $totalPagu = (float) DB::scalar("
            SELECT COALESCE(SUM(s.pagu_anggaran),0) 
            FROM kka_sesi s 
            JOIN kka_desa d ON d.id = s.desa_id 
            $sesiWhere
        ", $sesiParams);

        $totalDikwitansi = (float) DB::scalar("
            SELECT COALESCE(SUM(r.biaya_dikwitansi),0) 
            FROM kka_rincian r 
            JOIN kka_sesi s ON s.id = r.sesi_id 
            JOIN kka_desa d ON d.id = s.desa_id 
            $sesiWhere
        ", $sesiParams);

        $totalRealisasi = (float) DB::scalar("
            SELECT COALESCE(SUM(r.realisasi),0) 
            FROM kka_rincian r 
            JOIN kka_sesi s ON s.id = r.sesi_id 
            JOIN kka_desa d ON d.id = s.desa_id 
            $sesiWhere
        ", $sesiParams);

        // Temuan & Pajak
        $whereTemuan = "WHERE 1=1";
        $paramsTemuan = [];
        if ($filterTahun !== '' && is_numeric($filterTahun)) {
            $whereTemuan .= " AND t.tahun_anggaran = ?";
            $paramsTemuan[] = (int)$filterTahun;
        }
        if ($filterDesa > 0) {
            $whereTemuan .= " AND t.desa_id = ?";
            $paramsTemuan[] = $filterDesa;
        }

        $totalTemuan = (int) DB::scalar("SELECT COUNT(*) FROM kka_temuan t $whereTemuan", $paramsTemuan);
        $nominalTemuan = (float) DB::scalar("SELECT COALESCE(SUM(nominal),0) FROM kka_temuan t $whereTemuan", $paramsTemuan);

        $pajakSetor = (float) DB::scalar("
            SELECT COALESCE(SUM(r.nominal_ppn + r.nominal_pph),0) 
            FROM kka_rincian r 
            JOIN kka_sesi s ON s.id = r.sesi_id 
            JOIN kka_desa d ON d.id = s.desa_id 
            $sesiWhere AND r.status_pajak = 'SUDAH_SETOR'
        ", $sesiParams);

        $pajakBelumSetor = (float) DB::scalar("
            SELECT COALESCE(SUM(r.nominal_ppn + r.nominal_pph),0) 
            FROM kka_rincian r 
            JOIN kka_sesi s ON s.id = r.sesi_id 
            JOIN kka_desa d ON d.id = s.desa_id 
            $sesiWhere AND r.status_pajak = 'BELUM_SETOR'
        ", $sesiParams);

        $selisihFisik = max(0, $totalDikwitansi - $totalRealisasi);
        $potensiPemulihan = max($selisihFisik, $nominalTemuan);

        $stats = [
            'desa_total'        => $totalDesaKabupaten,
            'kec_total'         => $totalKecamatan,
            'desa_diaudit'      => $desaDiaudit,
            'cakupan_persen'    => $totalDesaKabupaten > 0 ? round(($desaDiaudit / $totalDesaKabupaten) * 100, 1) : 0,
            'sesi_total'        => $totalSesi,
            'pagu_total'        => $totalPagu,
            'dikwitansi'        => $totalDikwitansi,
            'realisasi'         => $totalRealisasi,
            'selisih_fisik'     => $selisihFisik,
            'potensi_pemulihan' => $potensiPemulihan,
            'temuan_total'      => $totalTemuan,
            'temuan_nominal'    => $nominalTemuan,
            'pajak_setor'       => $pajakSetor,
            'pajak_belum_setor' => $pajakBelumSetor,
            'nd_total'          => (int) DB::scalar("SELECT COUNT(*) FROM kka_nota_dinas"),
            'spt_total'         => (int) DB::scalar("SELECT COUNT(*) FROM kka_spt WHERE status = 'DITERBITKAN'"),
        ];

        // 5. Data Pipeline Alur Pengawasan Hulu-Hilir
        $pipeline = [
            'nd_diajukan'   => (int) DB::scalar("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DIAJUKAN_INSPEKTUR'"),
            'nd_disetujui'  => (int) DB::scalar("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DISETUJUI'"),
            'spt_aktif'     => (int) DB::scalar("SELECT COUNT(*) FROM kka_spt WHERE status = 'DITERBITKAN'"),
            'sesi_kka'      => $totalSesi,
            'temuan_final'  => (int) DB::scalar("SELECT COUNT(*) FROM kka_temuan WHERE status = 'FINAL_LHP'"),
            'lhp_desa'      => (int) DB::scalar("SELECT COUNT(DISTINCT desa_id) FROM kka_lhp_narasi WHERE status_lhp = 'DISAHKAN_INSPEKTUR'"),
        ];

        // 6. Ringkasan Per Desa (Daftar Obyek Pemeriksaan)
        $perDesa = DB::all("
            SELECT d.id, d.nama AS desa, k.nama AS kecamatan,
                   COUNT(s.id)                         AS jumlah,
                   COALESCE(SUM(s.pagu_anggaran),0)    AS pagu,
                   COALESCE((SELECT t.tahun_anggaran FROM kka_temuan t WHERE t.desa_id = d.id ORDER BY t.tahun_anggaran DESC LIMIT 1), MAX(s.tahun_anggaran)) AS tahun_terakhir,
                   (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = d.id) AS jml_temuan,
                   (SELECT COALESCE(SUM(t.nominal),0) FROM kka_temuan t WHERE t.desa_id = d.id) AS nominal_temuan,
                   (SELECT COALESCE(SUM(CASE WHEN (r.biaya_dikwitansi - r.realisasi) > 0 THEN (r.biaya_dikwitansi - r.realisasi) ELSE 0 END),0)
                    FROM kka_rincian r JOIN kka_sesi s2 ON s2.id = r.sesi_id WHERE s2.desa_id = d.id) AS selisih_fisik
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_sesi s ON s.desa_id = d.id
            $sesiWhere
            GROUP BY d.id, d.nama, k.nama
            ORDER BY jml_temuan DESC, selisih_fisik DESC, jumlah DESC, d.nama ASC
        ", $sesiParams);

        // 7. Sebaran Sesi Per Bidang APBDes
        $perBidang = DB::all("
            SELECT b.nama, COUNT(s.id) AS jumlah
            FROM kka_bidang b
            LEFT JOIN kka_sesi s ON s.bidang_id = b.id $ow $filterSql
            LEFT JOIN kka_desa d ON d.id = s.desa_id
            GROUP BY b.id, b.nama ORDER BY b.urutan
        ", array_merge($op, $filterParams));

        // 7b. DATA MONITORING RADAR 5 IRBAN & PERINGATAN HARI PENUGASAN (Executive Center)
        $irbanConfig = [
            1 => ['no' => 'I',   'nama' => 'RIZQIA PUTRI, S.STP.,M.Si.,QRMP', 'jabatan' => 'Inspektur Pembantu I',   'warna' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
            2 => ['no' => 'II',  'nama' => 'YUNISMAN, S.Pi, M.Si',            'jabatan' => 'Inspektur Pembantu II',  'warna' => '#059669', 'bg' => '#ecfdf5', 'border' => '#a7f3d0'],
            3 => ['no' => 'III', 'nama' => 'AFRINRA SAPUTRA, ST',             'jabatan' => 'Inspektur Pembantu III', 'warna' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
            4 => ['no' => 'IV',  'nama' => 'MARWAN, M.T',                     'jabatan' => 'Inspektur Pembantu IV',  'warna' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fde68a'],
            5 => ['no' => 'V',   'nama' => 'RUSMAILIS, SP',                   'jabatan' => 'Inspektur Pembantu V',   'warna' => '#db2777', 'bg' => '#fdf2f8', 'border' => '#fbcfe8'],
        ];

        $allActiveSpt = DB::all("
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   (SELECT COUNT(*) FROM kka_sesi sesi WHERE sesi.desa_id = s.desa_id) AS jml_sesi_kka,
                   (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = s.desa_id) AS jml_temuan,
                   (SELECT kendala_lapangan FROM kka_sesi sesi WHERE sesi.desa_id = s.desa_id AND kendala_lapangan IS NOT NULL AND kendala_lapangan != '' ORDER BY sesi.id DESC LIMIT 1) AS kendala_lapangan
            FROM kka_spt s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = s.kecamatan_id
            ORDER BY s.id DESC
        ");

        $sptMonitoring = [];
        $today = date('Y-m-d');
        $warningCounts = [
            'total'   => count($allActiveSpt),
            'aman'    => 0,
            'waspada' => 0,
            'overdue' => 0,
            'selesai' => 0,
        ];

        foreach ($allActiveSpt as $s) {
            $tglMulai = !empty($s['tgl_spt']) ? $s['tgl_spt'] : ($s['created_at'] ? date('Y-m-d', strtotime($s['created_at'])) : $today);
            $durasiHari = (int)($s['lama_hari'] ?: 10);
            $tglSelesai = date('Y-m-d', strtotime("$tglMulai + $durasiHari days"));
            $diffDays = (int) floor((strtotime($tglSelesai) - strtotime($today)) / 86400);

            if ($s['status'] === 'SELESAI') {
                $statusWaktu = 'SELESAI';
                $badgeColor = '#10b981';
                $badgeBg = '#d1fae5';
                $labelWaktu = 'Selesai';
                $warningCounts['selesai']++;
            } elseif ($diffDays < 0) {
                $statusWaktu = 'OVERDUE';
                $badgeColor = '#ef4444';
                $badgeBg = '#fee2e2';
                $labelWaktu = 'Terlambat ' . abs($diffDays) . ' Hari';
                $warningCounts['overdue']++;
            } elseif ($diffDays <= 3) {
                $statusWaktu = 'WASPADA';
                $badgeColor = '#d97706';
                $badgeBg = '#fef3c7';
                $labelWaktu = 'Sisa ' . $diffDays . ' Hari (H-' . $diffDays . ')';
                $warningCounts['waspada']++;
            } else {
                $statusWaktu = 'AMAN';
                $badgeColor = '#059669';
                $badgeBg = '#ecfdf5';
                $labelWaktu = 'Sisa ' . $diffDays . ' Hari';
                $warningCounts['aman']++;
            }

            $s['tgl_selesai_est'] = $tglSelesai;
            $s['sisa_hari']       = $diffDays;
            $s['status_waktu']    = $statusWaktu;
            $s['badge_color']     = $badgeColor;
            $s['badge_bg']        = $badgeBg;
            $s['label_waktu']     = $labelWaktu;

            $sptMonitoring[] = $s;
        }

        $irbanRadar = [];
        foreach ($irbanConfig as $idx => $cfg) {
            $sptIrban = array_filter($sptMonitoring, function($row) use ($cfg) {
                $wpj = $row['wakil_pj_nama'] ?? '';
                return (stripos($wpj, $cfg['nama']) !== false)
                    || (stripos($wpj, 'Irban ' . $cfg['no']) !== false)
                    || (stripos($wpj, 'Pembantu ' . $cfg['no']) !== false);
            });

            $totalSpt = count($sptIrban);
            $berjalan = count(array_filter($sptIrban, fn($r) => $r['status'] !== 'SELESAI'));
            $selesai = count(array_filter($sptIrban, fn($r) => $r['status'] === 'SELESAI'));
            $overdue = count(array_filter($sptIrban, fn($r) => $r['status_waktu'] === 'OVERDUE'));
            $waspada = count(array_filter($sptIrban, fn($r) => $r['status_waktu'] === 'WASPADA'));

            $irbanRadar[$idx] = array_merge($cfg, [
                'total_spt'  => $totalSpt,
                'berjalan'   => $berjalan,
                'selesai'    => $selesai,
                'overdue'    => $overdue,
                'waspada'    => $waspada,
                'daftar_spt' => array_slice(array_values($sptIrban), 0, 3)
            ]);
        }

        // 8. DATASET KHUSUS SESUAI PERAN (Role-Specific Operasional)

        // A. DATA UNTUK INSPEKTUR: Antrean Keputusan Pimpinan & Risiko Tinggi
        $inspekturData = [];
        if ($activeRole === 'inspektur' || $activeRole === 'admin') {
            $inspekturData['pending_nd'] = DB::all("
                SELECT nd.id, nd.no_nd, nd.tgl_nd, nd.tujuan, d.nama AS desa_nama, k.nama AS kecamatan_nama, nd.lama_hari, nd.ketua_tim_nama
                FROM kka_nota_dinas nd
                JOIN kka_desa d ON d.id = nd.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                WHERE nd.status = 'DIAJUKAN_INSPEKTUR'
                ORDER BY nd.id DESC LIMIT 5
            ");
            $inspekturData['pending_spt'] = DB::all("
                SELECT spt.id, spt.no_spt, spt.tgl_spt, spt.tujuan, d.nama AS desa_nama, k.nama AS kecamatan_nama, spt.lama_hari, spt.ketua_tim_nama
                FROM kka_spt spt
                JOIN kka_desa d ON d.id = spt.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                WHERE spt.status IN ('DRAFT', 'MENUNGGU_TTD')
                ORDER BY spt.id DESC LIMIT 5
            ");
            $inspekturData['pending_lhp'] = DB::all("
                SELECT lhp.id, lhp.desa_id, lhp.tahun_anggaran, d.nama AS desa_nama, k.nama AS kecamatan_nama, lhp.status_lhp, lhp.dalnis_nama, lhp.irban_nama
                FROM kka_lhp_narasi lhp
                JOIN kka_desa d ON d.id = lhp.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                WHERE lhp.status_lhp IN ('TELAAH_IRBAN', 'DRAFT')
                ORDER BY lhp.id DESC LIMIT 5
            ");
            $inspekturData['top_risk'] = DB::all("
                SELECT d.id, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                       (SELECT COUNT(*) FROM kka_temuan t WHERE t.desa_id = d.id) AS jml_temuan,
                       (SELECT COALESCE(SUM(t.nominal),0) FROM kka_temuan t WHERE t.desa_id = d.id) AS nominal_temuan,
                       (SELECT COALESCE(SUM(CASE WHEN (r.biaya_dikwitansi - r.realisasi) > 0 THEN (r.biaya_dikwitansi - r.realisasi) ELSE 0 END),0)
                        FROM kka_rincian r JOIN kka_sesi s2 ON s2.id = r.sesi_id WHERE s2.desa_id = d.id) AS selisih_fisik
                FROM kka_desa d
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                WHERE d.id IN (SELECT DISTINCT desa_id FROM kka_sesi)
                ORDER BY nominal_temuan DESC, selisih_fisik DESC
                LIMIT 5
            ");
        }

        // B. DATA UNTUK IRBAN: Pengawasan Wilayah & Sesi Stagnan > 7 Hari
        $irbanData = [];
        if ($activeRole === 'irban' || $activeRole === 'admin') {
            $irbanData['sesi_stagnan'] = DB::all("
                SELECT s.id, s.objek_audit, s.tahun_anggaran, s.status, s.updated_at, d.nama AS desa_nama, b.nama AS bidang_nama,
                       DATEDIFF(NOW(), s.updated_at) AS hari_terhenti
                FROM kka_sesi s
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                WHERE s.status != 'SELESAI_FINAL' AND s.updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY s.updated_at ASC LIMIT 6
            ");
            $irbanData['telaah_lhp'] = DB::all("
                SELECT lhp.id, lhp.desa_id, lhp.tahun_anggaran, d.nama AS desa_nama, k.nama AS kecamatan_nama, lhp.status_lhp, lhp.updated_at
                FROM kka_lhp_narasi lhp
                JOIN kka_desa d ON d.id = lhp.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                WHERE lhp.status_lhp = 'TELAAH_IRBAN'
                ORDER BY lhp.id DESC LIMIT 5
            ");
        }

        // C. DATA UNTUK DALNIS: Antrean Review KKA, Revisi Terbuka, SOP Kendali Mutu
        $dalnisData = [];
        if ($activeRole === 'dalnis' || $activeRole === 'admin') {
            $dalnisData['antrean_review'] = DB::all("
                SELECT s.id, s.objek_audit, s.tahun_anggaran, s.status, s.updated_at, d.nama AS desa_nama, b.nama AS bidang_nama,
                       s.tgl_spt_selesai, s.dibuat_oleh,
                       (SELECT COUNT(*) FROM kka_rincian r WHERE r.sesi_id = s.id) AS total_rincian
                FROM kka_sesi s
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                WHERE s.status IN ('REVIEW_DALNIS', 'REVIEW_KETUA')
                ORDER BY s.updated_at DESC LIMIT 6
            ");
            $dalnisData['revisi_terbuka'] = DB::all("
                SELECT s.id, s.objek_audit, s.tahun_anggaran, s.catatan_reviu_dalnis, d.nama AS desa_nama, b.nama AS bidang_nama, s.updated_at
                FROM kka_sesi s
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                WHERE s.status = 'PERLU_REVISI'
                ORDER BY s.updated_at DESC LIMIT 5
            ");
            $dalnisData['qa_checklist'] = [
                'tanpa_kwitansi' => (int) DB::scalar("SELECT COUNT(*) FROM kka_rincian WHERE biaya_dikwitansi = 0"),
                'selisih_fisik'  => (int) DB::scalar("SELECT COUNT(*) FROM kka_rincian WHERE (biaya_dikwitansi - realisasi) > 0"),
                'pajak_nunggak'  => (int) DB::scalar("SELECT COUNT(*) FROM kka_rincian WHERE status_pajak = 'BELUM_SETOR' AND (nominal_ppn > 0 OR nominal_pph > 0)"),
            ];
        }

        // D. DATA UNTUK KETUA TIM: Ruang Kendali Tim & Progres 5 Bidang
        $ketuaData = [];
        if ($activeRole === 'ketua' || $activeRole === 'admin') {
            $ketuaData['sesi_tim'] = DB::all("
                SELECT s.id, s.objek_audit, s.tahun_anggaran, s.status, s.updated_at, d.nama AS desa_nama, b.nama AS bidang_nama,
                       (SELECT COUNT(*) FROM kka_rincian r WHERE r.sesi_id = s.id) AS total_rincian,
                       (SELECT COUNT(*) FROM kka_rincian r WHERE r.sesi_id = s.id AND r.realisasi > 0) AS rincian_valid
                FROM kka_sesi s
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                WHERE s.status IN ('DRAFT', 'PERLU_REVISI')
                ORDER BY s.updated_at DESC LIMIT 6
            ");
            $ketuaData['siap_review'] = DB::all("
                SELECT s.id, s.objek_audit, s.tahun_anggaran, d.nama AS desa_nama, b.nama AS bidang_nama
                FROM kka_sesi s
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                WHERE s.status = 'DRAFT' AND (SELECT COUNT(*) FROM kka_rincian r WHERE r.sesi_id = s.id) > 0
                ORDER BY s.id DESC LIMIT 5
            ");
        }

        // E. DATA UNTUK AUDITOR: "Tugas Saya Hari Ini"
        $auditorData = [];
        if ($activeRole === 'auditor' || $activeRole === 'ketua' || $activeRole === 'admin') {
            $auditorData['sesi_aktif'] = DB::all("
                SELECT s.id, s.objek_audit, s.tahun_anggaran, s.status, s.updated_at, d.nama AS desa_nama, b.nama AS bidang_nama,
                       (SELECT COUNT(*) FROM kka_rincian r WHERE r.sesi_id = s.id) AS total_rincian
                FROM kka_sesi s
                JOIN kka_desa d ON d.id = s.desa_id
                JOIN kka_bidang b ON b.id = s.bidang_id
                WHERE (s.created_by = ? OR s.id IN (SELECT sesi_id FROM kka_sesi_share WHERE user_id = ?))
                ORDER BY s.updated_at DESC LIMIT 5
            ", [$uid, $uid]);

            // Jika admin sedang preview auditor dan belum punya sesi pribadi, ambil sesi sample
            if (empty($auditorData['sesi_aktif']) && $this->auth->isAdmin()) {
                $auditorData['sesi_aktif'] = DB::all("
                    SELECT s.id, s.objek_audit, s.tahun_anggaran, s.status, s.updated_at, d.nama AS desa_nama, b.nama AS bidang_nama,
                           (SELECT COUNT(*) FROM kka_rincian r WHERE r.sesi_id = s.id) AS total_rincian
                    FROM kka_sesi s
                    JOIN kka_desa d ON d.id = s.desa_id
                    JOIN kka_bidang b ON b.id = s.bidang_id
                    ORDER BY s.updated_at DESC LIMIT 5
                ");
            }

            $auditorData['belanja_belum_fisik'] = DB::all("
                SELECT r.id, r.sesi_id, r.uraian, r.biaya_dikwitansi, s.objek_audit, s.tahun_anggaran, d.nama AS desa_nama
                FROM kka_rincian r
                JOIN kka_sesi s ON s.id = r.sesi_id
                JOIN kka_desa d ON d.id = s.desa_id
                WHERE (r.realisasi = 0 OR r.realisasi IS NULL)
                ORDER BY r.id DESC LIMIT 5
            ");

            $auditorData['pajak_belum_setor'] = DB::all("
                SELECT r.id, r.sesi_id, r.uraian, (r.nominal_ppn + r.nominal_pph) AS total_pajak, s.objek_audit, d.nama AS desa_nama
                FROM kka_rincian r
                JOIN kka_sesi s ON s.id = r.sesi_id
                JOIN kka_desa d ON d.id = s.desa_id
                WHERE r.status_pajak = 'BELUM_SETOR' AND (r.nominal_ppn > 0 OR r.nominal_pph > 0)
                ORDER BY r.id DESC LIMIT 5
            ");

            $auditorData['draft_temuan'] = DB::all("
                SELECT t.id, t.nomor_temuan, t.judul, t.nominal, d.nama AS desa_nama
                FROM kka_temuan t
                JOIN kka_desa d ON d.id = t.desa_id
                WHERE t.status = 'DRAFT'
                ORDER BY t.id DESC LIMIT 5
            ");
        }

        // F. DATA UNTUK OPERATOR SPT (Perencanaan)
        $sptData = [];
        if ($activeRole === 'operator_spt' || $activeRole === 'admin') {
            $sptData['ready_nd'] = DB::all("
                SELECT nd.id, nd.no_nd, nd.tgl_nd, nd.tujuan, d.nama AS desa_nama, k.nama AS kecamatan_nama, nd.lama_hari, nd.ketua_tim_nama
                FROM kka_nota_dinas nd
                JOIN kka_desa d ON d.id = nd.desa_id
                JOIN kka_kecamatan k ON k.id = d.kecamatan_id
                WHERE nd.status = 'DISETUJUI' AND nd.id NOT IN (SELECT COALESCE(nota_dinas_id,0) FROM kka_spt)
                ORDER BY nd.id DESC LIMIT 6
            ");
            $sptData['last_spt'] = DB::one("
                SELECT spt.id, spt.no_spt, spt.tgl_spt, d.nama AS desa_nama, spt.ketua_tim_nama
                FROM kka_spt spt
                JOIN kka_desa d ON d.id = spt.desa_id
                WHERE spt.status = 'DITERBITKAN'
                ORDER BY spt.id DESC LIMIT 1
            ");
            $sptData['spt_berjalan'] = DB::all("
                SELECT spt.id, spt.no_spt, spt.tgl_spt, spt.tgl_selesai, d.nama AS desa_nama, spt.ketua_tim_nama,
                       DATEDIFF(spt.tgl_selesai, CURRENT_DATE) AS sisa_hari
                FROM kka_spt spt
                JOIN kka_desa d ON d.id = spt.desa_id
                WHERE spt.status = 'DITERBITKAN' AND spt.tgl_selesai >= CURRENT_DATE
                ORDER BY spt.tgl_selesai ASC LIMIT 5
            ");
        }

        // G. DATA UNTUK OPERATOR TLHP (Evlap)
        $tlhpData = [];
        if ($activeRole === 'operator_tl' || $activeRole === 'inspektur' || $activeRole === 'admin') {
            $tlhpSummary = DB::one("
                SELECT 
                    COUNT(*) AS total_rekomendasi,
                    COALESCE(SUM(nominal_rekomendasi), 0) AS sum_rekomendasi,
                    COALESCE(SUM(nominal_disetor), 0) AS sum_disetor,
                    COALESCE(SUM(sisa_kerugian), 0) AS sum_sisa,
                    SUM(CASE WHEN status = 'TUNTAS' THEN 1 ELSE 0 END) AS jml_tuntas,
                    SUM(CASE WHEN status = 'PROSES' THEN 1 ELSE 0 END) AS jml_proses,
                    SUM(CASE WHEN status = 'BELUM' THEN 1 ELSE 0 END) AS jml_belum,
                    SUM(CASE WHEN DATEDIFF(batas_waktu_tl, CURRENT_DATE) < 0 AND status != 'TUNTAS' THEN 1 ELSE 0 END) AS jml_kadaluarsa,
                    SUM(CASE WHEN DATEDIFF(batas_waktu_tl, CURRENT_DATE) BETWEEN 0 AND 15 AND status != 'TUNTAS' THEN 1 ELSE 0 END) AS jml_mendekati
                FROM kka_tindak_lanjut
            ");

            $tlhpData['summary'] = $tlhpSummary ?: [
                'total_rekomendasi' => 0, 'sum_rekomendasi' => 0, 'sum_disetor' => 0, 'sum_sisa' => 0,
                'jml_tuntas' => 0, 'jml_proses' => 0, 'jml_belum' => 0, 'jml_kadaluarsa' => 0, 'jml_mendekati' => 0
            ];

            $tlhpData['urgent_list'] = DB::all("
                SELECT tl.id, tl.desa_id, tl.tahun_anggaran, tl.status, tl.nominal_rekomendasi, tl.sisa_kerugian,
                       tl.batas_waktu_tl, DATEDIFF(tl.batas_waktu_tl, CURRENT_DATE) AS sisa_hari,
                       d.nama AS desa_nama, t.nomor_temuan, t.judul AS temuan_judul
                FROM kka_tindak_lanjut tl
                JOIN kka_temuan t ON t.id = tl.temuan_id
                JOIN kka_desa d ON d.id = tl.desa_id
                WHERE tl.status != 'TUNTAS'
                ORDER BY tl.batas_waktu_tl ASC LIMIT 5
            ");

            $tlhpData['antrean_verif'] = DB::all("
                SELECT tl.id, tl.desa_id, tl.tahun_anggaran, tl.nominal_disetor, tl.no_bukti_setor, tl.tgl_setor,
                       d.nama AS desa_nama, t.nomor_temuan
                FROM kka_tindak_lanjut tl
                JOIN kka_temuan t ON t.id = tl.temuan_id
                JOIN kka_desa d ON d.id = tl.desa_id
                WHERE tl.dokumen_bukti IS NOT NULL AND tl.verifikasi_apip = 'BELUM_VERIFIKASI'
                ORDER BY tl.id DESC LIMIT 5
            ");
        }

        view('dashboard/index', compact(
            'stats', 'pipeline', 'perDesa', 'perBidang',
            'activeRole', 'viewAs',
            'daftarTahun', 'daftarKecamatan', 'daftarDesa',
            'filterTahun', 'filterSemester', 'filterKecamatan', 'filterDesa',
            'inspekturData', 'irbanData', 'dalnisData', 'ketuaData', 'auditorData', 'sptData', 'tlhpData',
            'irbanRadar', 'sptMonitoring', 'warningCounts'
        ));
    }

    public function workflow(): void {
        view('panduan/workflow');
    }
}
