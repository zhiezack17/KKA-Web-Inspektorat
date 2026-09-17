<?php
class PrintController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    public function sesi(): void {
        $id = (int) input('id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            WHERE s.id = ?', [$id]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Anda tidak memiliki akses ke data audit ini.'); }
        $rincian = DB::all('SELECT * FROM kka_rincian WHERE sesi_id = ? ORDER BY urutan, id', [$id]);
        $totals = [
            'pagu'      => (float) $sesi['pagu_anggaran'],
            'pagu_rinci'=> array_sum(array_column($rincian, 'pagu_anggaran')),
            'dikwitansi'=> array_sum(array_column($rincian, 'biaya_dikwitansi')),
            'realisasi' => array_sum(array_column($rincian, 'realisasi')),
        ];
        $totals['selisih'] = $totals['realisasi'] - $totals['dikwitansi'];
        view('print/sesi', compact('sesi','rincian','totals'));
    }

    public function reviu(): void {
        $id = (int) input('id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            WHERE s.id = ?', [$id]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Anda tidak memiliki akses ke data audit ini.'); }
        $rincian = DB::all('SELECT * FROM kka_rincian WHERE sesi_id = ? ORDER BY urutan, id', [$id]);
        $totals = [
            'pagu'      => (float) $sesi['pagu_anggaran'],
            'pagu_rinci'=> array_sum(array_column($rincian, 'pagu_anggaran')),
            'dikwitansi'=> array_sum(array_column($rincian, 'biaya_dikwitansi')),
            'realisasi' => array_sum(array_column($rincian, 'realisasi')),
        ];
        $totals['selisih'] = $totals['realisasi'] - $totals['dikwitansi'];

        $creatorUser = !empty($sesi['created_by']) ? DB::one('SELECT id, nama, nip, jabatan FROM kka_users WHERE id = ?', [$sesi['created_by']]) : null;
        $ketuaUser = !empty($sesi['ketua_tim_id']) ? DB::one('SELECT id, nama, nip, jabatan FROM kka_users WHERE id = ?', [$sesi['ketua_tim_id']]) : null;
        $dalnisUser = !empty($sesi['dalnis_id']) ? DB::one('SELECT id, nama, nip, jabatan FROM kka_users WHERE id = ?', [$sesi['dalnis_id']]) : null;

        view('print/reviu', compact('sesi','rincian','totals','creatorUser','ketuaUser','dalnisUser'));
    }

    /** Cetak Lembar Kendali Mutu LHA / Routing Slip Map Kuning */
    public function routingSlip(): void {
        $id = (int) input('id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            WHERE s.id = ?', [$id]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Anda tidak memiliki akses ke data audit ini.'); }

        $creatorUser = !empty($sesi['created_by']) ? DB::one('SELECT id, nama, nip, jabatan FROM kka_users WHERE id = ?', [$sesi['created_by']]) : null;
        $ketuaUser   = !empty($sesi['ketua_tim_id']) ? DB::one('SELECT id, nama, nip, jabatan FROM kka_users WHERE id = ?', [$sesi['ketua_tim_id']]) : null;
        $dalnisUser  = !empty($sesi['dalnis_id']) ? DB::one('SELECT id, nama, nip, jabatan FROM kka_users WHERE id = ?', [$sesi['dalnis_id']]) : null;
        $irbanUser   = !empty($sesi['irban_id']) ? DB::one('SELECT id, nama, nip, jabatan FROM kka_users WHERE id = ?', [$sesi['irban_id']]) : null;

        $sharedWith = DB::all('
            SELECT u.id, u.nama, u.nip, u.jabatan
            FROM kka_sesi_share sh
            JOIN kka_users u ON u.id = sh.user_id
            WHERE sh.sesi_id = ?
            ORDER BY u.id', [$id]);

        view('print/routing_slip', compact('sesi', 'creatorUser', 'ketuaUser', 'dalnisUser', 'irbanUser', 'sharedWith'));
    }

    public function exportExcel(): void {
        $id = (int) input('id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama,
                   b.nama AS bidang_nama, sb.nama AS sub_bidang_nama,
                   uk.nama AS ketua_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            LEFT JOIN kka_users uk ON uk.id = s.ketua_tim_id
            WHERE s.id = ?', [$id]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Anda tidak memiliki akses ke data audit ini.'); }
        // Lepaskan kunci sesi sebelum membangun & mengirim file Excel (operasi
        // berat/read-only) agar tidak menahan request lain dari user yang sama.
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        $rincian = DB::all('SELECT * FROM kka_rincian WHERE sesi_id = ? ORDER BY urutan, id', [$id]);

        $filename = 'KKA_' . preg_replace('/[^A-Za-z0-9]/', '_', $sesi['desa_nama']) . '_S' . $sesi['semester'] . '_' . $sesi['tahun_anggaran'] . '.xls';
        $this->outputExcelHeader($filename);
        $this->renderSesiExcel($sesi, $rincian);
        exit;
    }

    public function exportRekap(): void {
        $tahun    = (int) input('tahun', 0);
        $bidId    = (int) input('bidang', 0);
        $subBidId = (int) input('sub_bidang', 0);
        $kecId    = (int) input('kecamatan', 0);

        $where = '1=1'; $p = [];
        if ($tahun > 0)     { $where .= ' AND s.tahun_anggaran = ?';   $p[] = $tahun; }
        if ($bidId > 0)     { $where .= ' AND s.bidang_id = ?';         $p[] = $bidId; }
        if ($subBidId > 0)  { $where .= ' AND s.sub_bidang_id = ?';     $p[] = $subBidId; }
        if ($kecId > 0)     { $where .= ' AND d.kecamatan_id = ?';      $p[] = $kecId; }

        // Isolasi data: auditor hanya mengekspor rekap miliknya, admin semua
        [$ow, $op] = owner_where($this->auth);
        $where .= $ow; $p = array_merge($p, $op);
        // Lepaskan kunci sesi sebelum membangun & mengirim file rekap (berat/read-only).
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

        // Rekap per (Sub Bidang, Kecamatan, Tahun) - konsisten dengan tampilan layar
        $rows = DB::all("
            SELECT
                COALESCE(sb.nama, '(Tanpa Sub Bidang)') AS sub_bidang,
                COALESCE(b.nama, '')                    AS bidang,
                k.nama                                  AS kecamatan,
                s.tahun_anggaran                        AS tahun,
                SUM(s.pagu_anggaran)                    AS pagu,
                COALESCE(SUM(rinc.dikwitansi_sesi),0)   AS dikwitansi,
                COALESCE(SUM(rinc.realisasi_sesi),0)    AS realisasi,
                COUNT(DISTINCT s.id)                    AS jumlah_sesi
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            LEFT JOIN kka_sub_bidang sb ON sb.id = s.sub_bidang_id
            LEFT JOIN (
                SELECT sesi_id,
                       SUM(biaya_dikwitansi) AS dikwitansi_sesi,
                       SUM(realisasi) AS realisasi_sesi
                FROM kka_rincian
                GROUP BY sesi_id
            ) rinc ON rinc.sesi_id = s.id
            WHERE $where
            GROUP BY sub_bidang, bidang, k.nama, s.tahun_anggaran
            ORDER BY b.urutan, sb.nama, k.nama, s.tahun_anggaran DESC
        ", $p);

        $bidangNama = $bidId > 0 ? (DB::scalar('SELECT nama FROM kka_bidang WHERE id = ?', [$bidId]) ?: '') : 'SEMUA BIDANG';
        $filename = 'Rekap_KKA_' . ($tahun ?: 'semua') . '_' . date('Ymd_His') . '.xls';
        $this->outputExcelHeader($filename);
        $this->renderRekapExcel($rows, $bidangNama, $tahun);
        exit;
    }

    private function outputExcelHeader(string $filename): void {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo "\xEF\xBB\xBF"; // BOM UTF-8 agar excel baca akurat
    }

    private function renderSesiExcel(array $s, array $rincian): void {
        // Disusun dalam 1 tabel terpadu 8 kolom (A s.d H) agar kolom Excel sejajar sempurna:
        // Col A: No, Col B: Uraian, Col C: Pagu, Col D: Realisasi, Col E: Dikwitansi, Col F: Selisih, Col G: Penerima, Col H: Keterangan.
        // Bagian metadata atas menggunakan Colspan (2+2+2+2 = 8 kolom) sehingga kolom No dan Uraian tidak terdistorsi.
        $tahun = (int)$s['tahun_anggaran'];
        $tgl_buat   = !empty($s['tanggal_dibuat']) ? date('d/m/Y', strtotime($s['tanggal_dibuat'])) : '..../..../....';
        $tgl_review = !empty($s['tgl_reviu_ketua']) ? date('d/m/Y', strtotime($s['tgl_reviu_ketua'])) : (!empty($s['tanggal_review']) ? date('d/m/Y', strtotime($s['tanggal_review'])) : '..../..../....');

        [$pureNamaBuat] = split_nama_nip($s['dibuat_oleh'] ?? '');
        [$pureNamaReview] = split_nama_nip($s['ketua_nama'] ?: ($s['direview_oleh'] ?? ''));
        $nama_buat   = $pureNamaBuat ?: '...........................';
        $nama_review = $pureNamaReview ?: '...........................';

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="utf-8">';
        echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>KKA</x:Name><x:WorksheetOptions><x:Print><x:ValidPrinterInfo/><x:PaperSizeIndex>9</x:PaperSizeIndex><x:Orientation>Portrait</x:Orientation></x:Print></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        echo '<style>
            @page { size: A4 portrait; margin: 12mm 12mm 14mm 12mm; mso-page-orientation: portrait; }
            body { font-family:"Times New Roman", Times, serif; color:#000; font-size:10pt; }
            td, th { vertical-align:top; }
            .lbl { font-weight:bold; }
            .kop-title { font-size:12pt; font-weight:bold; text-align:center; }
            .kop-sub { font-size:16pt; font-weight:bold; text-align:center; }
            .kop-address { font-size:9pt; text-align:center; }
        </style>';
        echo '</head>';
        echo '<body>';

        echo '<table border="0" cellpadding="3" cellspacing="0" style="width:100%;border-collapse:collapse">';
        echo '<col style="width:35pt">';   // Col A: No
        echo '<col style="width:260pt">';  // Col B: Uraian / Rincian Belanja
        echo '<col style="width:95pt">';   // Col C: Pagu Anggaran
        echo '<col style="width:95pt">';   // Col D: Realisasi
        echo '<col style="width:95pt">';   // Col E: Biaya Dikwitansi
        echo '<col style="width:95pt">';   // Col F: Selisih
        echo '<col style="width:110pt">';  // Col G: Penerima
        echo '<col style="width:125pt">';  // Col H: Keterangan

        // KOP PEMKAB ROHIL
        echo '<tr><td colspan="8" class="kop-title">PEMERINTAH KABUPATEN ROKAN HILIR</td></tr>';
        echo '<tr><td colspan="8" class="kop-sub">INSPEKTORAT</td></tr>';
        echo '<tr><td colspan="8" class="kop-address">Komplek Perkantoran Batu 6 Jl. Lintas Pesisir Sungai Rokan, Kec. Bangko - Bagansiapiapi</td></tr>';
        echo '<tr><td colspan="8" class="kop-address">Telp. (0767) 2700270 &middot; Email: inspektorat@rohilkab.go.id</td></tr>';
        echo '<tr><td colspan="8" style="border-bottom:3px double #000;height:4px">&nbsp;</td></tr>';
        echo '<tr><td colspan="8">&nbsp;</td></tr>';

        // JUDUL KKA
        echo '<tr><td colspan="8" align="center" style="font-size:13pt;font-weight:bold;text-decoration:underline">KERTAS KERJA AUDIT (KKA)</td></tr>';
        echo '<tr><td colspan="8" align="center" style="font-size:11pt">PENGELUARAN KEUANGAN KEPENGHULUAN &mdash; Tahun Anggaran ' . $tahun . '</td></tr>';
        echo '<tr><td colspan="8">&nbsp;</td></tr>';

        // TABEL IDENTITAS (2 + 2 + 2 + 2 = 8 KOLOM TERPADU)
        echo '<tr>'
           . '<td colspan="2" class="lbl">Kepenghuluan / Desa</td>'
           . '<td colspan="2">: ' . e($s['desa_nama']) . ' (Kec. ' . e($s['kecamatan_nama']) . ')</td>'
           . '<td colspan="2" class="lbl">No. KKA</td>'
           . '<td colspan="2">: ' . e($s['no_kka'] ?: '-') . '</td>'
           . '</tr>';

        echo '<tr>'
           . '<td colspan="2" class="lbl">Objek Audit</td>'
           . '<td colspan="2">: ' . e($s['objek_audit'] ?: '-') . '</td>'
           . '<td colspan="2" class="lbl">Ref. PKA</td>'
           . '<td colspan="2">: ' . e($s['ref_kka'] ?: '-') . '</td>'
           . '</tr>';

        echo '<tr>'
           . '<td colspan="2" class="lbl">Masa Audit</td>'
           . '<td colspan="2">: Semester ' . (int)$s['semester'] . ' Tahun ' . $tahun . '</td>'
           . '<td colspan="2" class="lbl">Disusun oleh (Auditor)</td>'
           . '<td colspan="2">: ' . e($nama_buat) . '</td>'
           . '</tr>';

        echo '<tr>'
           . '<td colspan="2" class="lbl">Bidang</td>'
           . '<td colspan="2" style="font-size:9pt">: ' . e($s['bidang_nama']) . '</td>'
           . '<td colspan="2" class="lbl">Tgl / Paraf</td>'
           . '<td colspan="2">: ' . e($tgl_buat) . '</td>'
           . '</tr>';

        echo '<tr>'
           . '<td colspan="2" class="lbl">Sub Bidang</td>'
           . '<td colspan="2" style="font-size:9pt">: ' . e($s['sub_bidang_nama'] ?: '-') . '</td>'
           . '<td colspan="2" class="lbl">Direview oleh (Ketua Tim)</td>'
           . '<td colspan="2">: ' . e($nama_review) . '</td>'
           . '</tr>';

        echo '<tr>'
           . '<td colspan="2" class="lbl">Kegiatan</td>'
           . '<td colspan="2">: ' . e($s['kegiatan'] ?: '-') . '</td>'
           . '<td colspan="2" class="lbl">Tgl / Paraf</td>'
           . '<td colspan="2">: ' . e($tgl_review) . '</td>'
           . '</tr>';

        echo '<tr>'
           . '<td colspan="2" class="lbl">Pagu Anggaran</td>'
           . '<td colspan="6" style="font-weight:bold">: Rp ' . number_format((float)$s['pagu_anggaran'],0,',','.') . '</td>'
           . '</tr>';

        echo '<tr><td colspan="8">&nbsp;</td></tr>';

        // HEADER TABEL RINCIAN BELANJA (8 KOLOM)
        echo '<tr>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">No</th>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">Uraian / Rincian Belanja</th>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">Pagu Anggaran (Rp)</th>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">Realisasi (Rp)</th>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">Biaya Dikwitansi (Rp)</th>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">Selisih (Rp)</th>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">Penerima</th>
                <th style="border:1px solid #000;background-color:#dcfce7;font-weight:bold;text-align:center;padding:5px">Keterangan</th>
              </tr>';

        $tp = $tk = $tr = $ts = 0;
        $no = 1;
        if (empty($rincian)) {
            echo '<tr><td colspan="8" align="center" style="border:1px solid #000;padding:15px;color:#666">- Belum ada rincian belanja -</td></tr>';
        } else {
            foreach ($rincian as $r) {
                $sel = (float)$r['realisasi'] - (float)$r['biaya_dikwitansi'];
                $tp += (float)$r['pagu_anggaran'];
                $tk += (float)$r['biaya_dikwitansi'];
                $tr += (float)$r['realisasi'];
                $ts += $sel;
                echo '<tr>
                    <td align="center" style="border:1px solid #000;padding:4px 6px">' . ($no++) . '</td>
                    <td style="border:1px solid #000;padding:4px 6px">' . e($r['uraian']) . '</td>
                    <td align="right" style="border:1px solid #000;padding:4px 6px">' . number_format($r['pagu_anggaran'],0,',','.') . '</td>
                    <td align="right" style="border:1px solid #000;padding:4px 6px">' . number_format($r['realisasi'],0,',','.') . '</td>
                    <td align="right" style="border:1px solid #000;padding:4px 6px">' . number_format($r['biaya_dikwitansi'],0,',','.') . '</td>
                    <td align="right" style="border:1px solid #000;padding:4px 6px">' . number_format($sel,0,',','.') . '</td>
                    <td style="border:1px solid #000;padding:4px 6px">' . e($r['penerima'] ?: '-') . '</td>
                    <td style="border:1px solid #000;padding:4px 6px">' . e($r['keterangan'] ?: '-') . '</td>
                  </tr>';
            }
            echo '<tr style="background:#f3f4f6;font-weight:bold">
                    <td colspan="2" align="center" style="border:1px solid #000;padding:5px">JUMLAH</td>
                    <td align="right" style="border:1px solid #000;padding:5px">' . number_format($tp,0,',','.') . '</td>
                    <td align="right" style="border:1px solid #000;padding:5px">' . number_format($tr,0,',','.') . '</td>
                    <td align="right" style="border:1px solid #000;padding:5px">' . number_format($tk,0,',','.') . '</td>
                    <td align="right" style="border:1px solid #000;padding:5px">' . number_format($ts,0,',','.') . '</td>
                    <td colspan="2" style="border:1px solid #000;padding:5px"></td>
                  </tr>';
        }

        // KESIMPULAN & SUMBER DATA (TANPA BLOK TANDA TANGAN DI BAWAH)
        echo '<tr><td colspan="8">&nbsp;</td></tr>';
        echo '<tr><td colspan="8" style="font-size:11pt;font-weight:bold"><u>KESIMPULAN AUDIT:</u></td></tr>';
        echo '<tr><td colspan="8" style="font-size:10pt;line-height:1.5">' . nl2br(e($s['kesimpulan'] ?: '-')) . '</td></tr>';
        echo '<tr><td colspan="8">&nbsp;</td></tr>';
        echo '<tr><td colspan="8" style="font-size:11pt;font-weight:bold"><u>SUMBER DATA:</u></td></tr>';
        echo '<tr><td colspan="8" style="font-size:10pt;line-height:1.5">' . nl2br(e($s['sumber_data'] ?: '-')) . '</td></tr>';
        echo '</table>';

        echo '</body></html>';
    }

    private function renderRekapExcel(array $rows, string $bidangNama = 'SEMUA BIDANG', int $tahun = 0): void {
        echo '<html><head><meta charset="utf-8"></head><body style="font-family:Arial">';
        echo '<h2 style="text-align:center;margin:0 0 4px">REKAP KERTAS KERJA AUDIT - PER DESA</h2>';
        echo '<div style="text-align:center;margin-bottom:12px;font-size:12px">Inspektorat Kabupaten Rokan Hilir</div>';
        echo '<table cellpadding="4" style="margin-bottom:10px;font-size:12px">';
        echo '<tr><td><b>Bidang</b></td><td>:</td><td>' . e($bidangNama) . '</td></tr>';
        if ($tahun > 0) {
            echo '<tr><td><b>Tahun Anggaran</b></td><td>:</td><td>' . $tahun . '</td></tr>';
        }
        echo '</table>';
        echo '<table border="1" cellpadding="6" style="border-collapse:collapse">';
        echo '<thead><tr style="background:#10b981;color:white">
                <th>No</th><th>Sub Bidang</th><th>Kecamatan</th><th>Tahun</th>
                <th>Jumlah Sesi</th><th>Pagu (Rp)</th><th>Realisasi (Rp)</th>
                <th>Dikwitansi (Rp)</th><th>Selisih (Rp)</th><th>Keterangan</th>
              </tr></thead><tbody>';
        $tp=$tk=$tr=$ts=0; $no=1;
        if (empty($rows)) {
            echo '<tr><td colspan="10" align="center">- Belum ada data -</td></tr>';
        }
        foreach ($rows as $r) {
            $sel = (float)$r['realisasi'] - (float)$r['dikwitansi'];
            $tp += (float)$r['pagu']; $tk += (float)$r['dikwitansi'];
            $tr += (float)$r['realisasi']; $ts += $sel;
            echo '<tr>
                <td align="center">' . ($no++) . '</td>
                <td>' . e($r['sub_bidang']) . '</td>
                <td>' . e($r['kecamatan']) . '</td>
                <td align="center">' . (int)$r['tahun'] . '</td>
                <td align="center">' . (int)$r['jumlah_sesi'] . '</td>
                <td align="right">' . number_format($r['pagu'],0,',','.') . '</td>
                <td align="right">' . number_format($r['realisasi'],0,',','.') . '</td>
                <td align="right">' . number_format($r['dikwitansi'],0,',','.') . '</td>
                <td align="right">' . number_format($sel,0,',','.') . '</td>
                <td>&mdash;</td>
              </tr>';
        }
        echo '<tr style="background:#f0f0f0;font-weight:bold">
                <td colspan="5" align="center">JUMLAH</td>
                <td align="right">' . number_format($tp,0,',','.') . '</td>
                <td align="right">' . number_format($tr,0,',','.') . '</td>
                <td align="right">' . number_format($tk,0,',','.') . '</td>
                <td align="right">' . number_format($ts,0,',','.') . '</td>
                <td></td>
              </tr>';
        echo '</tbody></table></body></html>';
    }
}
