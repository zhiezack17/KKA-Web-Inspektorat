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

        $ignoreWords = [
            'laporan realisasi', 'pemerintah kabupaten', 'kepenghuluan', 'tahun anggaran',
            'kode rekening', 'uraian', 'anggaran (rp)', 'realisasi (rp)', 'lebih / (kurang)',
            'jumlah belanja', 'total belanja', 'surplus', 'defisit', 'sisa lebih',
            'pendapatan desa', 'pembiayaan', 'jumlah pendapatan', 'jumlah pembiayaan',
            'halaman', 'lampiran', 'peraturan desa', 'bupati rokan hilir', 'inspektorat',
            'printed by', 'realisasi s.d'
        ];

        $currentBidang = null;
        $currentSubBidang = null;
        $currentKodeKegiatan = null;
        $currentKegiatan = null;
        $pendingKegiatanTitle = false;

        foreach ($lines as $line) {
            $lineTrim = trim($line);
            if ($lineTrim === '') continue;

            $lLower = strtolower($lineTrim);

            // Filter header halaman
            $isHeader = false;
            foreach ($ignoreWords as $iw) {
                if (strpos($lLower, $iw) !== false && !preg_match('/\d{1,3}(\.\d{3})+/', $lineTrim)) {
                    $isHeader = true;
                    break;
                }
            }
            if ($isHeader) continue;

            // 1. Deteksi Judul BIDANG (1 s.d 5)
            if (preg_match('/^(?:[0-4]?[1-5])?\s*BIDANG\s+PENYELENGGARA(?:A?N)?\s+PEMERINTAH/i', $lineTrim)) {
                $currentBidang = 1;
                $currentSubBidang = null;
                $currentKegiatan = null;
                continue;
            } elseif (preg_match('/^(?:[0-4]?[1-5])?\s*BIDANG\s+PELAKSANAAN\s+PEMBANGUNAN/i', $lineTrim)) {
                $currentBidang = 2;
                $currentSubBidang = null;
                $currentKegiatan = null;
                continue;
            } elseif (preg_match('/^(?:[0-4]?[1-5])?\s*BIDANG\s+PEMBINAAN\s+KEMASYARAKAT/i', $lineTrim)) {
                $currentBidang = 3;
                $currentSubBidang = null;
                $currentKegiatan = null;
                continue;
            } elseif (preg_match('/^(?:[0-4]?[1-5])?\s*BIDANG\s+PEMBERDAYAAN\s+MASYARAKAT/i', $lineTrim)) {
                $currentBidang = 4;
                $currentSubBidang = null;
                $currentKegiatan = null;
                continue;
            } elseif (preg_match('/^(?:[0-4]?[1-5])?\s*BIDANG\s+PENANGGULANGAN\s+BENCANA/i', $lineTrim) || preg_match('/KEADAAN\s+DARURAT.*MENDESAK/i', $lineTrim)) {
                $currentBidang = 5;
                $currentSubBidang = null;
                $currentKegiatan = null;
                continue;
            }

            // 2. Deteksi SUB BIDANG (misal: "2.1 Sub Bidang Pendidikan", "1.1 Penyelenggaraan Belanja Siltap")
            if (preg_match('/^\s*([1-5]\.[0-9]+)\s+(?:Sub\s+Bidang\s+)?(.*)$/i', $lineTrim, $mSub)) {
                $subCode = $mSub[1];
                $subText = trim($mSub[2]);
                if (strpos($subCode, '5.') !== 0) {
                    $fDigit = (int) substr($subCode, 0, 1);
                    if ($fDigit >= 1 && $fDigit <= 5) {
                        $currentBidang = $fDigit;
                        $cleanSubName = preg_replace('/\s{2,}.*$/', '', $subText);
                        $cleanSubName = trim(preg_replace('/[\d\.,\-]/', '', $cleanSubName));
                        if (strlen($cleanSubName) > 3) {
                            $currentSubBidang = (stripos($cleanSubName, 'Sub Bidang') === false ? 'Sub Bidang ' : '') . $cleanSubName;
                            $currentKegiatan = null;
                            continue;
                        }
                    }
                }
            }

            // 3. Deteksi BARIS JUDUL KEGIATAN (misal: "2.1.1 Penyelenggaraan 46.600.000,00", "2.1.8 Pengelolaan Perpustakaan...")
            if (preg_match('/^\s*([1-5]\.[0-9]+\.[0-9]+)\s+(.*)$/', $lineTrim, $mAct)) {
                $actCode = $mAct[1];
                $restAct = trim($mAct[2]);
                
                // Cek jika baris ini BUKAN baris belanja leaf 5.x.x
                if (!preg_match('/\b5\.\d+\./', $restAct)) {
                    $fDigit = (int) substr($actCode, 0, 1);
                    if ($fDigit >= 1 && $fDigit <= 5) {
                        $currentBidang = $fDigit;
                        $cleanActTitle = preg_replace('/\s{2,}[\d\.,\-]+.*$/', '', $restAct);
                        $cleanActTitle = trim(preg_replace('/[\d\.,\-]+$/', '', $cleanActTitle));
                        $currentKodeKegiatan = $actCode;
                        $currentKegiatan = $cleanActTitle;
                        $pendingKegiatanTitle = true;
                        continue;
                    }
                }
            }

            // Kelanjutan nama kegiatan jika terpotong 2 baris (misal "PAUD/TKITPATKAITPQM adrasah Non-Formal")
            if ($pendingKegiatanTitle && !preg_match('/\b5\.\d+\./', $lineTrim) && !preg_match('/^\s*([0-9\.]+)\s+/', $lineTrim)) {
                if (!preg_match('/[\d\.,\-]{4,}/', $lineTrim) && preg_match('/[a-zA-Z]/', $lineTrim)) {
                    $currentKegiatan .= ' ' . trim($lineTrim);
                    $pendingKegiatanTitle = false;
                    continue;
                } else {
                    $pendingKegiatanTitle = false;
                }
            }

            // 4. Deteksi RINCIAN BELANJA LEAF (Akun 5.x.x.xx)
            $cleanLine = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\xEF\xBF\xBD]/', ' ', $lineTrim);

            // Cari kode rekening belanja 5.x
            if (preg_match('/^(?:([0-5]\.[0-9\.]+)\s+)?.*?\b(5\.\d+(?:\.\d+)*\.?)\s+(.*)$/', $cleanLine, $mLeaf)) {
                $inlineActCode = $mLeaf[1] ?: null;
                $accCode = rtrim($mLeaf[2], '.');
                $afterAcc = $mLeaf[3];

                if ($inlineActCode && strpos($inlineActCode, '5.') !== 0) {
                    $fDigit = (int) substr($inlineActCode, 0, 1);
                    if ($fDigit >= 1 && $fDigit <= 5) {
                        $currentBidang = $fDigit;
                        $currentKodeKegiatan = $inlineActCode;
                    }
                }

                $accParts = explode('.', $accCode);
                if (count($accParts) < 4) {
                    continue; // Skip subtotal grup (5.1, 5.2, 5.2.1)
                }

                // Normalisasi format uang hanya pada bagian kanan ($afterAcc)
                $normAfterAcc = self::normalizeMoneyString($afterAcc);

                if (preg_match_all('/(?<=\s|^)(?:[\/\\\])?(\-?\d{1,3}(?:\.\d{3})+(?:,\d{2})?|\-?\d+,\d{2}|\b0,00\b|\b0\b)(?=\s|$|[a-zA-Z\x80-\xFF])/u', $normAfterAcc, $numMatches, PREG_OFFSET_CAPTURE)) {
                    $matchedTokens = $numMatches[1];
                    $firstOffset = null;
                    $validNums = [];

                    foreach ($matchedTokens as $tok) {
                        $valStr = $tok[0];
                        $offset = $tok[1];
                        if (strpos($valStr, '.') !== false || strpos($valStr, ',') !== false || $valStr === '0') {
                            if ($firstOffset === null) {
                                $firstOffset = $offset;
                            }
                            $validNums[] = self::parseIndoMoney($valStr);
                        }
                    }

                    if ($firstOffset !== null && !empty($validNums)) {
                        $cleanUraian = trim(substr($normAfterAcc, 0, $firstOffset));
                        $cleanUraian = trim($cleanUraian, " \t\n\r\0\x0B.-_=\\/\\:,;");

                        if (!empty($cleanUraian) && preg_match('/[a-zA-Z]/', $cleanUraian)) {
                            $pagu = $validNums[0] ?? 0.0;
                            $real = (count($validNums) >= 2) ? $validNums[1] : 0.0;

                            if ($pagu > 0 || $real > 0) {
                                $bId = $currentBidang ?: 1;
                                $kegCode = $currentKodeKegiatan ?: "$bId.1.1";
                                $kegTitle = $currentKegiatan ?: ($currentSubBidang ?: "Kegiatan $kegCode");

                                $items[] = [
                                    'bidang_id'        => $bId,
                                    'sub_bidang'       => $currentSubBidang,
                                    'kode_kegiatan'    => $kegCode,
                                    'kegiatan'         => $kegTitle,
                                    'kode_rekening'    => $accCode,
                                    'uraian'           => $cleanUraian,
                                    'pagu_anggaran'    => $pagu,
                                    'realisasi'        => $real,
                                    'biaya_dikwitansi' => $real,
                                    'penerima'         => null,
                                    'keterangan'       => "[$accCode] $kegTitle"
                                ];
                            }
                        }
                    }
                }
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
     * Normalisasi spasi dan token angka dari hasil OCR (misal: "13. 780.000" atau "1 1 . 1 2 9 . 5 00 , 00")
     */
    public static function normalizeMoneyString(string $str): string {
        // Satukan angka terpecah spasi per digit
        $str = preg_replace_callback('/\b(?:\d\s+)+\d+(?:\s*\.\s*(?:\d\s+)*\d+)*(?:\s*,\s*(?:\d\s+)*\d+)?\b/', function($m) {
            return preg_replace('/\s+/', '', $m[0]);
        }, $str);

        // Satukan titik ribuan yang terpisah spasi
        $str = preg_replace('/(\d{1,3})\.\s*(\d{3})\b/', '$1.$2', $str);
        $str = preg_replace('/(\d{1,3})\.\s*(\d{3})\b/', '$1.$2', $str);

        // Satukan koma desimal yang terpisah spasi
        $str = preg_replace('/(\d+)\s*,\s*(\d{2})\b/', '$1,$2', $str);

        return $str;
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
