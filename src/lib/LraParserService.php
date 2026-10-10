<?php

class LraParserService {
    
    /**
     * Parse file dokumen LRA / APBDesa (PDF atau Excel/CSV) menjadi array rincian belanja
     * 
     * @param string $filePath Path file fisik sementara
     * @param string $origName Nama asli file dari client
     * @return array Array berisi associative item rincian
     */
    public static function parse(string $filePath, string $origName): array {
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        
        if ($ext === 'pdf') {
            return self::parsePdf($filePath);
        } elseif (in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return self::parseExcel($filePath);
        } else {
            throw new Exception("Format file .$ext tidak didukung. Harap unggah file PDF atau Excel (.xlsx/.csv).");
        }
    }

    /**
     * Ekstraksi teks dari PDF menggunakan pdftotext (Poppler), Python pypdf, atau fallback pure PHP stream
     */
    public static function extractPdfText(string $pdfPath): string {
        $output = '';

        // 1. Coba pdftotext dengan shell_exec
        if (function_exists('shell_exec')) {
            $cmdPoppler = 'pdftotext -layout ' . escapeshellarg($pdfPath) . ' - 2>/dev/null';
            $output = (string) @shell_exec($cmdPoppler);
            if (strlen(trim($output)) > 30) {
                return $output;
            }

            // Fallback Python pypdf via shell_exec
            $pyCode = 'import pypdf, sys
try:
    reader = pypdf.PdfReader(sys.argv[1])
    for page in reader.pages:
        t = page.extract_text()
        if t: print(t)
except Exception:
    pass
';
            $cmdPython = 'python3 -c ' . escapeshellarg($pyCode) . ' ' . escapeshellarg($pdfPath) . ' 2>/dev/null';
            $outputPy = (string) @shell_exec($cmdPython);
            if (strlen(trim($outputPy)) > 30) {
                return $outputPy;
            }
        } elseif (function_exists('exec')) {
            // Coba via exec jika shell_exec diblokir
            $cmdPoppler = 'pdftotext -layout ' . escapeshellarg($pdfPath) . ' - 2>/dev/null';
            $lines = [];
            @exec($cmdPoppler, $lines);
            if (!empty($lines)) {
                $output = implode("\n", $lines);
                if (strlen(trim($output)) > 30) {
                    return $output;
                }
            }
        }

        // 2. Pure PHP stream decoder jika sistem operasi membatasi shell_exec
        $raw = @file_get_contents($pdfPath);
        if ($raw) {
            $pureText = self::extractRawPdfStreams($raw);
            if (strlen(trim($pureText)) > 30) {
                return $pureText;
            }
        }

        // 3. Fallback Tesseract OCR jika file merupakan hasil scan gambar (scanned PDF)
        if (function_exists('shell_exec')) {
            $ocrText = self::ocrPdfWithTesseract($pdfPath);
            if (strlen(trim($ocrText)) > 30) {
                return $ocrText;
            }
        }

        return (string)$output;
    }

    /**
     * Fallback OCR menggunakan pdftoppm dan Tesseract untuk dokumen scan gambar
     */
    public static function ocrPdfWithTesseract(string $pdfPath): string {
        $whichTess = trim((string) @shell_exec('which tesseract 2>/dev/null'));
        if (!$whichTess) return '';

        $tmpDir = sys_get_temp_dir() . '/kka_ocr_' . uniqid();
        @mkdir($tmpDir, 0777, true);

        // Render PDF pages as PNG (maksimal 12 halaman pertama, 120 DPI untuk kecepatan & ketajaman optimal)
        $ppmCmd = 'pdftoppm -png -r 120 -l 12 ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tmpDir . '/pg') . ' 2>/dev/null';
        @shell_exec($ppmCmd);

        $pngs = glob($tmpDir . '/pg-*.png');
        if (empty($pngs)) {
            @shell_exec('rm -rf ' . escapeshellarg($tmpDir));
            return '';
        }

        natsort($pngs);
        $fullText = '';
        foreach ($pngs as $png) {
            $tessCmd = 'tesseract ' . escapeshellarg($png) . ' stdout -l ind+eng --psm 6 2>/dev/null';
            $t = (string) @shell_exec($tessCmd);
            if ($t) {
                $fullText .= $t . "\n";
            }
        }

        @shell_exec('rm -rf ' . escapeshellarg($tmpDir));
        return $fullText;
    }

    /**
     * Pure PHP stream decoder untuk PDF jika sistem operasi membatasi process execution
     */
    private static function extractRawPdfStreams(string $content): string {
        $text = '';
        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $uncompressed = @gzuncompress($stream);
                if ($uncompressed === false) {
                    $uncompressed = $stream;
                }
                if (preg_match_all('/\((.*?)\)\s*T[jd]/s', $uncompressed, $tMatches)) {
                    $text .= implode(' ', $tMatches[1]) . "\n";
                }
            }
        }
        return $text;
    }

    /**
     * Parse teks dari dokumen PDF LRA Kepenghuluan (Siskeudes / APBDesa)
     * Mengelompokkan item belanja secara cerdas per Bidang 1-5,
     * menyaring pendapatan/pembiayaan, dan hanya mengambil item leaf (rincian objek)
     * agar pagu anggaran tidak terduplikasi 3-4x lipat.
     */
    public static function parsePdf(string $pdfPath): array {
        $rawText = self::extractPdfText($pdfPath);
        if (empty(trim($rawText))) {
            throw new Exception("Gagal membaca teks dari file PDF. Pastikan file PDF bukan hasil scan murni tanpa OCR.");
        }

        $lines = explode("\n", $rawText);
        $items = [];
        $fallbackKegiatans = [];

        $ignoreWords = [
            'laporan realisasi', 'pemerintah kabupaten', 'kepenghuluan', 'tahun anggaran',
            'kode rekening', 'uraian', 'anggaran (rp)', 'realisasi (rp)', 'lebih / (kurang)',
            'jumlah belanja', 'total belanja', 'surplus', 'defisit', 'sisa lebih',
            'pendapatan desa', 'pembiayaan', 'jumlah pendapatan', 'jumlah pembiayaan',
            'halaman', 'lampiran', 'peraturan desa', 'bupati rokan hilir', 'inspektorat'
        ];

        $isInBelanja = false;
        $currentBidang = null;
        $currentKegiatan = '';

        foreach ($lines as $line) {
            $lineTrim = trim($line);
            if ($lineTrim === '') continue;

            // Lewatkan baris tanpa alfabet (header kolom seperti '2 3', atau angka kolom terpotong)
            if (!preg_match('/[a-zA-Z]/', $lineTrim)) continue;

            $lLower = strtolower($lineTrim);

            // Deteksi Akun Pendapatan (Akun 4)
            if ((strpos($lLower, 'pendapatan desa') !== false || strpos($lLower, 'pendapatan transfer') !== false) && !$isInBelanja) {
                continue;
            }

            // Batas Awal Belanja
            if (strpos($lLower, 'belanja') !== false && !preg_match('/\d{1,3}(\.\d{3})+/', $lineTrim)) {
                $isInBelanja = true;
            }

            // Batas Akhir Belanja (Pembiayaan / SILPA)
            if ((strpos($lLower, 'pembiayaan') !== false || strpos($lLower, 'penerimaan pembiayaan') !== false || strpos($lLower, 'silpa') !== false) && !preg_match('/\d{1,3}(\.\d{3})+/', $lineTrim)) {
                $isInBelanja = false;
            }

            // Abaikan kata kunci header
            $isHeader = false;
            foreach ($ignoreWords as $iw) {
                if (strpos($lLower, $iw) !== false && !preg_match('/\d{1,3}(\.\d{3})+/', $lineTrim)) {
                    $isHeader = true;
                    break;
                }
            }
            if ($isHeader) continue;

            // Deteksi Bidang dari judul Bidang Siskeudes
            if (strpos($lLower, 'penyelenggaraan pemerintahan') !== false) {
                $isInBelanja = true;
                $currentBidang = 1;
            } elseif (strpos($lLower, 'pelaksanaan pembangunan') !== false) {
                $isInBelanja = true;
                $currentBidang = 2;
            } elseif (strpos($lLower, 'pembinaan kemasyarakatan') !== false || strpos($lLower, 'pembinaan kemasyaratan') !== false) {
                $isInBelanja = true;
                $currentBidang = 3;
            } elseif (strpos($lLower, 'pemberdayaan masyarakat') !== false) {
                $isInBelanja = true;
                $currentBidang = 4;
            } elseif (strpos($lLower, 'penanggulangan bencana') !== false || strpos($lLower, 'keadaan darurat') !== false || strpos($lLower, 'mendesak desa') !== false) {
                $isInBelanja = true;
                $currentBidang = 5;
            }

            // Pola penangkapan baris data tabel keuangan:
            // "1.1.01   5.1.1.01. Penghasilan Tetap Kepala Desa   15.000.000,00   12.000.000,00   3.000.000,00"
            if (preg_match('/^(.*?)\s{2,}([\d\.,\-]+)\s+([\d\.,\-]+)(?:\s+([\d\.,\-]+))?$/', $lineTrim, $m)) {
                $rawUraian = trim($m[1]);
                $num1 = self::parseIndoMoney($m[2]);
                $num2 = self::parseIndoMoney($m[3]);

                // Ekstrak kode kolom pertama jika ada
                $code = '';
                if (preg_match('/^([0-9\.]+)\s+(.*)$/', $rawUraian, $codeMatch)) {
                    $code = trim($codeMatch[1], '. ');
                    $cleanUraian = trim($codeMatch[2]);
                } else {
                    $cleanUraian = $rawUraian;
                }

                // Discard jika tidak mengandung huruf atau hanya nomor kolom
                if (!preg_match('/[a-zA-Z]/', $cleanUraian)) continue;
                if (preg_match('/^\s*[\d\s\.\-]+\s*$/', $cleanUraian)) continue;

                // Filter Akun 4 (Pendapatan) dan Akun 6 (Pembiayaan)
                if (strpos($code, '4.') === 0 && !$isInBelanja) continue;
                if (strpos($code, '6.') === 0) {
                    $isInBelanja = false;
                    continue;
                }

                // Deteksi Bidang dari Kode Rekening Siskeudes (Kolom 1)
                $codeNormalized = ltrim($code, '0');
                if ($code === '1' || $code === '01' || strpos($code, '1.') === 0 || strpos($code, '01.') === 0 || strpos($codeNormalized, '1.') === 0) {
                    $isInBelanja = true;
                    $currentBidang = 1;
                } elseif ($code === '2' || $code === '02' || strpos($code, '2.') === 0 || strpos($code, '02.') === 0 || strpos($codeNormalized, '2.') === 0) {
                    $isInBelanja = true;
                    $currentBidang = 2;
                } elseif ($code === '3' || $code === '03' || strpos($code, '3.') === 0 || strpos($code, '03.') === 0 || strpos($codeNormalized, '3.') === 0) {
                    $isInBelanja = true;
                    $currentBidang = 3;
                } elseif ($isInBelanja && ($code === '4' || $code === '04' || strpos($code, '4.') === 0 || strpos($code, '04.') === 0 || strpos($codeNormalized, '4.') === 0)) {
                    $currentBidang = 4;
                } elseif ($isInBelanja && ($code === '5' || $code === '05' || strpos($code, '5.') === 0 || strpos($code, '05.') === 0 || strpos($codeNormalized, '5.') === 0)) {
                    $currentBidang = 5;
                }

                if (!$isInBelanja || !$currentBidang) continue;

                $uLower = strtolower($cleanUraian);

                // Lewatkan baris total / summary / judul tanpa rincian
                if (in_array($uLower, ['jumlah', 'total', 'sub total', 'subtotal', 'belanja', 'pendapatan', 'pembiayaan'])) continue;
                if (str_starts_with($uLower, 'jumlah ') || str_starts_with($uLower, 'total ')) continue;
                if (str_starts_with($uLower, 'bidang ') || str_starts_with($uLower, 'sub bidang ')) continue;

                // Tentukan pagu dan realisasi
                $pagu = $num1;
                $real = $num2;
                if ($pagu <= 0 && $real <= 0) continue;

                // Deteksi Kode Rekening Akun Belanja (5.x.x.xx) di Uraian
                $accCode = '';
                $leafUraian = $cleanUraian;
                if (preg_match('/^(5\.\d+(\.\d+)*)\.?\s+(.*)$/', $cleanUraian, $accMatch)) {
                    $accCode = rtrim($accMatch[1], '.');
                    $leafUraian = trim($accMatch[3]);
                }

                if ($accCode === '') {
                    // Ini adalah judul Kegiatan (misal: "Penyediaan Penghasilan Tetap...")
                    $currentKegiatan = $cleanUraian;
                    // Simpan sebagai fallback jika di bawahnya tidak ada kode 5.x.x.xx
                    $fallbackKegiatans[] = [
                        'bidang_id'        => $currentBidang,
                        'kode_rekening'    => $code,
                        'kegiatan'         => $cleanUraian,
                        'uraian'           => $cleanUraian,
                        'pagu_anggaran'    => $pagu,
                        'realisasi'        => $real,
                        'biaya_dikwitansi' => $real,
                        'penerima'         => null,
                        'keterangan'       => ($code ? "[$code] " : '') . 'Kegiatan: ' . $cleanUraian
                    ];
                    continue;
                }

                // Cek level akun:
                // 5.x = Kelompok Belanja (Belanja Pegawai, Belanja Modal, dll) -> SKIP (Subtotal)
                // 5.x.x = Jenis Belanja -> SKIP (Subtotal)
                // 5.x.x.xx = Objek / Rincian Belanja -> LEAF ITEM (IMPORT!)
                $accParts = explode('.', $accCode);
                if (count($accParts) < 4) {
                    // Parent subtotal, lewati agar anggaran tidak terduplikasi 3-4x lipat
                    continue;
                }

                // Item Belanja Rinci Murni (Leaf)
                $items[] = [
                    'bidang_id'        => $currentBidang,
                    'kode_rekening'    => $accCode,
                    'kegiatan'         => $currentKegiatan,
                    'uraian'           => $leafUraian,
                    'pagu_anggaran'    => $pagu,
                    'realisasi'        => $real,
                    'biaya_dikwitansi' => $real,
                    'penerima'         => null,
                    'keterangan'       => "[$accCode] " . ($currentKegiatan ? "Kegiatan: $currentKegiatan" : 'Impor LRA PDF')
                ];
            }
        }

        // Jika tidak ada item leaf 5.x.x.xx (misal file ringkasan LRA per kegiatan), gunakan fallback kegiatan
        if (empty($items) && !empty($fallbackKegiatans)) {
            return $fallbackKegiatans;
        }

        return $items;
    }

    /**
     * Parse dokumen Excel / CSV menggunakan SimpleXLSX
     */
    public static function parseExcel(string $excelPath): array {
        require_once __DIR__ . '/SimpleXLSX.php';
        $xlsx = SimpleXLSX::parse($excelPath);
        if (!$xlsx) {
            throw new Exception("Gagal membaca file Excel/CSV. Pastikan format file valid.");
        }

        $rows = $xlsx->rows();
        if (empty($rows)) {
            throw new Exception("File Excel/CSV kosong.");
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
            'bidang'    => -1,
            'kode'      => -1,
        ];

        foreach ($rows as $idx => $r) {
            $hasUraian = false;
            $hasFinancial = false;
            foreach ($r as $cIdx => $cVal) {
                $cLower = strtolower(trim((string)$cVal));
                if (strpos($cLower, 'uraian') !== false || strpos($cLower, 'rincian') !== false || strpos($cLower, 'kegiatan') !== false) {
                    $hasUraian = true;
                }
                if (strpos($cLower, 'pagu') !== false || strpos($cLower, 'anggaran') !== false || strpos($cLower, 'kwitansi') !== false || strpos($cLower, 'kuitansi') !== false || strpos($cLower, 'realisasi') !== false) {
                    $hasFinancial = true;
                }
            }
            if ($hasUraian && $hasFinancial) {
                $headerRowIdx = $idx;
                foreach ($r as $cIdx => $cVal) {
                    $cLower = strtolower(trim((string)$cVal));
                    if (strpos($cLower, 'uraian') !== false || strpos($cLower, 'rincian') !== false || strpos($cLower, 'kegiatan') !== false) {
                        $colMap['uraian'] = $cIdx;
                    } elseif (strpos($cLower, 'pagu') !== false || strpos($cLower, 'anggaran') !== false) {
                        $colMap['pagu'] = $cIdx;
                    } elseif (strpos($cLower, 'kwitansi') !== false || strpos($cLower, 'kuitansi') !== false || strpos($cLower, 'biaya') !== false) {
                        $colMap['kwi'] = $cIdx;
                    } elseif (strpos($cLower, 'realisasi') !== false) {
                        $colMap['real'] = $cIdx;
                    } elseif (strpos($cLower, 'penerima') !== false || strpos($cLower, 'rekanan') !== false || strpos($cLower, 'toko') !== false) {
                        $colMap['penerima'] = $cIdx;
                    } elseif (strpos($cLower, 'keterangan') !== false || strpos($cLower, 'pajak') !== false || strpos($cLower, 'catatan') !== false) {
                        $colMap['ket'] = $cIdx;
                    } elseif (strpos($cLower, 'bidang') !== false) {
                        $colMap['bidang'] = $cIdx;
                    } elseif (strpos($cLower, 'kode') !== false) {
                        $colMap['kode'] = $cIdx;
                    }
                }
                break;
            }
        }

        $dataStartIdx = ($headerRowIdx !== -1) ? ($headerRowIdx + 1) : 0;
        $items = [];
        $count = count($rows);
        $currBidangExcel = 1;

        for ($i = $dataStartIdx; $i < $count; $i++) {
            $r = $rows[$i];
            $uraian = trim((string)($r[$colMap['uraian']] ?? ''));

            if ($uraian === '' || str_starts_with($uraian, '#')) continue;
            if (!preg_match('/[a-zA-Z]/', $uraian)) continue;

            $uLower = strtolower($uraian);
            if (in_array($uLower, ['uraian', 'uraian belanja', 'uraian / rincian belanja', 'uraian belanja *', 'jumlah', 'total', 'subtotal', 'no'])) continue;
            if (str_starts_with($uLower, 'isi data belanja') || str_starts_with($uLower, 'petunjuk') || str_starts_with($uLower, '# template')) continue;
            
            // Deteksi bidang jika ada di teks uraian
            if (strpos($uLower, 'bidang penyelenggaraan pemerintahan') !== false) { $currBidangExcel = 1; continue; }
            if (strpos($uLower, 'bidang pelaksanaan pembangunan') !== false) { $currBidangExcel = 2; continue; }
            if (strpos($uLower, 'bidang pembinaan kemasyarakatan') !== false) { $currBidangExcel = 3; continue; }
            if (strpos($uLower, 'bidang pemberdayaan masyarakat') !== false) { $currBidangExcel = 4; continue; }
            if (strpos($uLower, 'bidang penanggulangan bencana') !== false) { $currBidangExcel = 5; continue; }

            if (str_starts_with($uLower, 'jumlah belanja') || str_starts_with($uLower, 'total belanja')) continue;

            $rawPagu = $r[$colMap['pagu']] ?? 0;
            $rawKwi  = $r[$colMap['kwi']] ?? 0;
            $rawReal = $r[$colMap['real']] ?? 0;

            $pagu = self::parseIndoMoney($rawPagu);
            $real = self::parseIndoMoney($rawReal);
            $kwi  = self::parseIndoMoney($rawKwi);

            if ($kwi <= 0 && $real > 0) {
                $kwi = $real;
            }

            if ($pagu <= 0 && $real <= 0 && $kwi <= 0) continue;

            $bidangId = $currBidangExcel;
            if ($colMap['bidang'] !== -1) {
                $bVal = trim((string)($r[$colMap['bidang']] ?? ''));
                if (preg_match('/^(\d)/', $bVal, $bm)) {
                    $bidangId = (int)$bm[1];
                }
            }

            $kodeCol = ($colMap['kode'] !== -1) ? trim((string)($r[$colMap['kode']] ?? '')) : '';
            if ($kodeCol !== '' && preg_match('/^(\d)/', $kodeCol, $km)) {
                $bidangId = (int)$km[1];
            }

            $penerima = trim((string)($r[$colMap['penerima']] ?? '')) ?: null;
            $ket      = trim((string)($r[$colMap['ket']] ?? '')) ?: 'Impor Excel LRA';

            $items[] = [
                'bidang_id'        => $bidangId,
                'kode_rekening'    => $kodeCol,
                'kegiatan'         => '',
                'uraian'           => $uraian,
                'pagu_anggaran'    => $pagu,
                'realisasi'        => $real,
                'biaya_dikwitansi' => $kwi,
                'penerima'         => $penerima,
                'keterangan'       => $ket
            ];
        }

        return $items;
    }

    /**
     * Parsing nilai uang fleksibel: support format angka Excel, Rupiah Indonesia, maupun angka polos
     */
    public static function parseIndoMoney($val): float {
        if (is_int($val) || is_float($val)) return (float)$val;
        $str = trim((string)$val);
        if ($str === '' || $str === '-') return 0.0;
        
        // Cek jika angka murni (cth: 50000000 atau 50000000.50)
        if (preg_match('/^-?\d+(\.\d+)?$/', $str)) {
            return (float)$str;
        }

        // Bersihkan Rp, spasi, dll
        $str = preg_replace('/[^\d.,-]/', '', $str);
        if ($str === '' || $str === '-') return 0.0;

        if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (strpos($str, ',') !== false) {
            $parts = explode(',', $str);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, '.') !== false) {
            $parts = explode('.', $str);
            if (count($parts) > 2 || (count($parts) === 2 && strlen($parts[1]) === 3)) {
                $str = str_replace('.', '', $str);
            }
        }

        return (float)$str;
    }
}
