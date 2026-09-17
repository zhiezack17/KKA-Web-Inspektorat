<?php
class RincianController {
    private Auth $auth;
    public function __construct(Auth $auth) { $this->auth = $auth; $auth->require(); }

    /** Pastikan sesi induk milik user (atau admin) dan tidak terkunci. */
    private function assertSesiCanEdit(int $sesiId): array {
        $sesi = DB::one('SELECT id, created_by, status FROM kka_sesi WHERE id = ?', [$sesiId]);
        if (!sesi_is_owned($this->auth, $sesi)) {
            flash('error', 'Anda tidak memiliki akses ke sesi audit ini.');
            redirect('sesi');
        }
        $status = $sesi['status'] ?? 'DRAFT';
        if (in_array($status, ['REVIEW_KETUA', 'REVIEW_DALNIS', 'SELESAI_FINAL'])) {
            $info = kka_status_info($status);
            flash('error', 'KKA sedang dalam tahap ' . $info['label'] . ' (data terkunci). Perubahan rincian belanja tidak diizinkan.');
            redirect('sesi/show?id=' . $sesiId);
        }
        return $sesi;
    }

    public function store(): void {
        only_post(); csrf_check();
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);
        $uraian = trim((string) input('uraian'));
        if (!$sesiId || $uraian === '') {
            flash('error', 'Uraian belanja wajib diisi.');
            redirect('sesi/show?id=' . $sesiId);
        }
        $next = (int) DB::scalar('SELECT COALESCE(MAX(urutan),0)+1 FROM kka_rincian WHERE sesi_id = ?', [$sesiId]);
        $potongPpn = !empty(input('potong_ppn')) ? 1 : 0;
        $nomPpn    = parse_money(input('nominal_ppn', 0));
        $potongPph = trim((string) input('potong_pph')) ?: null;
        $nomPph    = parse_money(input('nominal_pph', 0));
        $statusPajak = trim((string) input('status_pajak', 'TIDAK_TERUTANG'));
        if (!in_array($statusPajak, ['TIDAK_TERUTANG', 'SUDAH_SETOR', 'BELUM_SETOR'])) {
            $statusPajak = 'TIDAK_TERUTANG';
        }
        $ntpn = trim((string) input('ntpn')) ?: null;

        DB::insert('kka_rincian', [
            'sesi_id'         => $sesiId,
            'urutan'          => $next,
            'uraian'          => $uraian,
            'pagu_anggaran'   => parse_money(input('pagu_anggaran', 0)),
            'biaya_dikwitansi'=> parse_money(input('biaya_dikwitansi', 0)),
            'realisasi'       => parse_money(input('realisasi', 0)),
            'penerima'        => trim((string) input('penerima')) ?: null,
            'keterangan'      => trim((string) input('keterangan')) ?: null,
            'potong_ppn'      => $potongPpn,
            'nominal_ppn'     => $nomPpn,
            'potong_pph'      => $potongPph,
            'nominal_pph'     => $nomPph,
            'status_pajak'    => $statusPajak,
            'ntpn'            => $ntpn,
        ]);
        flash('success', 'Rincian belanja ditambahkan.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function update(): void {
        only_post(); csrf_check();
        $id     = (int) input('id');
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);
        $uraian = trim((string) input('uraian'));
        // Pastikan rincian milik sesi yang sesuai (cegah update arbitrary)
        $row = DB::one('SELECT id FROM kka_rincian WHERE id = ? AND sesi_id = ?', [$id, $sesiId]);
        if (!$row) { flash('error', 'Rincian tidak ditemukan.'); redirect('sesi/show?id=' . $sesiId); }
        if ($uraian === '') { flash('error', 'Uraian belanja wajib diisi.'); redirect('sesi/show?id=' . $sesiId); }

        $potongPpn = !empty(input('potong_ppn')) ? 1 : 0;
        $nomPpn    = parse_money(input('nominal_ppn', 0));
        $potongPph = trim((string) input('potong_pph')) ?: null;
        $nomPph    = parse_money(input('nominal_pph', 0));
        $statusPajak = trim((string) input('status_pajak', 'TIDAK_TERUTANG'));
        if (!in_array($statusPajak, ['TIDAK_TERUTANG', 'SUDAH_SETOR', 'BELUM_SETOR'])) {
            $statusPajak = 'TIDAK_TERUTANG';
        }
        $ntpn = trim((string) input('ntpn')) ?: null;

        DB::update('kka_rincian', [
            'uraian'          => $uraian,
            'pagu_anggaran'   => parse_money(input('pagu_anggaran', 0)),
            'biaya_dikwitansi'=> parse_money(input('biaya_dikwitansi', 0)),
            'realisasi'       => parse_money(input('realisasi', 0)),
            'penerima'        => trim((string) input('penerima')) ?: null,
            'keterangan'      => trim((string) input('keterangan')) ?: null,
            'potong_ppn'      => $potongPpn,
            'nominal_ppn'     => $nomPpn,
            'potong_pph'      => $potongPph,
            'nominal_pph'     => $nomPph,
            'status_pajak'    => $statusPajak,
            'ntpn'            => $ntpn,
        ], ['id' => $id]);
        flash('success', 'Rincian diperbarui.');
        redirect('sesi/show?id=' . $sesiId);
    }

    public function delete(): void {
        only_post(); csrf_check();
        $id = (int) input('id');
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);
        // Pastikan rincian milik sesi yang sesuai (cegah delete arbitrary)
        $row = DB::one('SELECT id FROM kka_rincian WHERE id = ? AND sesi_id = ?', [$id, $sesiId]);
        if (!$row) { flash('error', 'Rincian tidak ditemukan.'); redirect('sesi/show?id=' . $sesiId); }
        DB::delete('kka_rincian', ['id' => $id]);
        flash('success', 'Rincian dihapus.');
        redirect('sesi/show?id=' . $sesiId);
    }

    /** Unduh template CSV/Excel baku untuk pengisian audit offline di lapangan */
    public function downloadTemplate(): void {
        $sesiId = (int) input('sesi_id');
        $sesi = DB::one('
            SELECT s.*, d.nama AS desa_nama, k.nama AS kecamatan_nama, b.nama AS bidang_nama
            FROM kka_sesi s
            JOIN kka_desa d ON d.id = s.desa_id
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            JOIN kka_bidang b ON b.id = s.bidang_id
            WHERE s.id = ?', [$sesiId]);
        if (!$sesi) { http_response_code(404); exit('Sesi tidak ditemukan'); }
        if (!sesi_is_owned($this->auth, $sesi)) { http_response_code(403); exit('Akses ditolak'); }

        $filename = 'Template_KKA_' . preg_replace('/[^A-Za-z0-9]/', '_', $sesi['desa_nama']) . '_S' . $sesi['semester'] . '_' . $sesi['tahun_anggaran'] . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM agar Excel di Windows membaca huruf dan angka secara akurat
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header identitas penugasan (kolom A ringkas agar tidak melebarkan kolom No di Excel)
        fputcsv($out, ['KKA', 'INSPEKTORAT KABUPATEN ROKAN HILIR - PENGISIAN OFFLINE']);
        fputcsv($out, ['Desa', $sesi['desa_nama']]);
        fputcsv($out, ['Kecamatan', $sesi['kecamatan_nama']]);
        fputcsv($out, ['Bidang', $sesi['bidang_nama']]);
        fputcsv($out, ['Objek', $sesi['objek_audit']]);
        fputcsv($out, ['Tahun', $sesi['tahun_anggaran'] . ' (Semester ' . $sesi['semester'] . ')']);
        fputcsv($out, ['Petunjuk', 'Isi data belanja mulai baris ke-9. Baris contoh boleh ditimpa/dihapus. Angka bisa tanpa titik atau dengan Rp.']);
        fputcsv($out, []); // Baris kosong pemisah

        // Baris Header Tabel Kolom Baku
        fputcsv($out, [
            'No',
            'Uraian Belanja *',
            'Pagu Anggaran (Rp)',
            'Nilai Kwitansi (Rp) *',
            'Realisasi Fisik (Rp) *',
            'Nama Penerima / Toko Rekanan',
            'Keterangan / Bukti Pajak'
        ]);

        // Baris Contoh Pengisian
        fputcsv($out, [
            '1',
            'Pembangunan Saluran Drainase Dusun I',
            '50000000',
            '48500000',
            '48500000',
            'Toko Bangunan Berkah Mandiri',
            'Bukti kwitansi lengkap, PPN/PPh disetor'
        ]);

        fclose($out);
        exit;
    }

    /** Impor rincian belanja dari file Excel (.xlsx) atau CSV */
    public function importExcel(): void {
        only_post(); csrf_check();
        $sesiId = (int) input('sesi_id');
        $this->assertSesiCanEdit($sesiId);

        if (empty($_FILES['file_excel']['name']) || empty($_FILES['file_excel']['tmp_name'])) {
            flash('error', 'Silakan pilih file Excel (.xlsx) atau CSV untuk diimpor.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $file     = $_FILES['file_excel'];
        $tmpPath  = $file['tmp_name'];
        $origName = $file['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'csv', 'xls'])) {
            flash('error', 'Format file tidak didukung. Harap unggah file .xlsx atau .csv.');
            redirect('sesi/show?id=' . $sesiId);
        }

        require_once __DIR__ . '/../lib/SimpleXLSX.php';
        $xlsx = SimpleXLSX::parse($tmpPath);
        if (!$xlsx) {
            flash('error', 'Gagal membaca file Excel/CSV. Pastikan format file valid.');
            redirect('sesi/show?id=' . $sesiId);
        }

        $rows = $xlsx->rows();
        if (empty($rows)) {
            flash('error', 'File Excel/CSV kosong.');
            redirect('sesi/show?id=' . $sesiId);
        }

        // Cari baris header tabel
        $headerRowIdx = -1;
        $colMap = [
            'uraian'    => 1,
            'pagu'      => 2,
            'kwi'       => 3,
            'real'      => 4,
            'penerima'  => 5,
            'ket'       => 6,
        ];

        foreach ($rows as $idx => $r) {
            $hasUraian = false;
            $hasFinancial = false;
            foreach ($r as $cVal) {
                $cLower = strtolower(trim((string)$cVal));
                if (strpos($cLower, 'uraian') !== false || strpos($cLower, 'rincian') !== false) {
                    $hasUraian = true;
                }
                if (strpos($cLower, 'pagu') !== false || strpos($cLower, 'kwitansi') !== false || strpos($cLower, 'kuitansi') !== false || strpos($cLower, 'realisasi') !== false) {
                    $hasFinancial = true;
                }
            }
            if ($hasUraian && $hasFinancial) {
                $headerRowIdx = $idx;
                foreach ($r as $cIdx => $cVal) {
                    $cLower = strtolower(trim((string)$cVal));
                    if (strpos($cLower, 'uraian') !== false || strpos($cLower, 'rincian') !== false) {
                        $colMap['uraian'] = $cIdx;
                    } elseif (strpos($cLower, 'pagu') !== false) {
                        $colMap['pagu'] = $cIdx;
                    } elseif (strpos($cLower, 'kwitansi') !== false || strpos($cLower, 'kuitansi') !== false || strpos($cLower, 'biaya') !== false) {
                        $colMap['kwi'] = $cIdx;
                    } elseif (strpos($cLower, 'realisasi') !== false) {
                        $colMap['real'] = $cIdx;
                    } elseif (strpos($cLower, 'penerima') !== false || strpos($cLower, 'rekanan') !== false || strpos($cLower, 'toko') !== false) {
                        $colMap['penerima'] = $cIdx;
                    } elseif (strpos($cLower, 'keterangan') !== false || strpos($cLower, 'pajak') !== false || strpos($cLower, 'catatan') !== false) {
                        $colMap['ket'] = $cIdx;
                    }
                }
                break;
            }
        }

        $dataStartIdx = ($headerRowIdx !== -1) ? ($headerRowIdx + 1) : 0;

        // Opsi replace / ganti seluruh data lama
        $modeReplace = (int) input('mode_replace', 0);
        if ($modeReplace === 1) {
            DB::delete('kka_rincian', ['sesi_id' => $sesiId]);
            $urutan = 1;
        } else {
            $urutan = (int) DB::scalar('SELECT COALESCE(MAX(urutan),0)+1 FROM kka_rincian WHERE sesi_id = ?', [$sesiId]);
        }

        $imported = 0;
        $count = count($rows);

        for ($i = $dataStartIdx; $i < $count; $i++) {
            $r = $rows[$i];
            $uraian = trim((string)($r[$colMap['uraian']] ?? ''));

            // Abaikan baris kosong, baris komentar (#), header, atau baris petunjuk/summary
            if ($uraian === '' || str_starts_with($uraian, '#')) continue;
            $uLower = strtolower($uraian);
            if (in_array($uLower, ['uraian', 'uraian belanja', 'uraian / rincian belanja', 'uraian belanja *', 'jumlah', 'total', 'subtotal', 'no'])) continue;
            if (str_starts_with($uLower, 'isi data belanja') || str_starts_with($uLower, 'petunjuk') || str_starts_with($uLower, '# template')) continue;

            $rawPagu = $r[$colMap['pagu']] ?? 0;
            $rawKwi  = $r[$colMap['kwi']] ?? 0;
            $rawReal = $r[$colMap['real']] ?? 0;

            $pagu = $this->parseMoneySmart($rawPagu);
            $kwi  = $this->parseMoneySmart($rawKwi);
            $real = $this->parseMoneySmart($rawReal);
            $penerima = trim((string)($r[$colMap['penerima']] ?? '')) ?: null;
            $ket      = trim((string)($r[$colMap['ket']] ?? '')) ?: null;

            DB::insert('kka_rincian', [
                'sesi_id'          => $sesiId,
                'urutan'           => $urutan++,
                'uraian'           => $uraian,
                'pagu_anggaran'    => $pagu,
                'biaya_dikwitansi' => $kwi,
                'realisasi'        => $real,
                'penerima'         => $penerima,
                'keterangan'       => $ket,
            ]);
            $imported++;
        }

        if ($imported > 0) {
            flash('success', 'Berhasil mengimpor ' . $imported . ' baris rincian belanja dari file Excel/CSV.');
        } else {
            flash('warning', 'Tidak ada data belanja yang diimpor. Pastikan kolom "Uraian Belanja" sudah terisi.');
        }

        redirect('sesi/show?id=' . $sesiId);
    }

    /** Parsing nilai uang yang aman untuk format angka Excel maupun format teks Rupiah Indonesia */
    private function parseMoneySmart($v): float {
        if (is_int($v) || is_float($v)) return (float)$v;
        $s = trim((string)$v);
        if ($s === '') return 0.0;
        // Jika angka murni (misal 50000000 atau 50000000.50)
        if (preg_match('/^-?\d+(\.\d+)?$/', $s)) {
            return (float)$s;
        }
        return parse_money($s);
    }
}
