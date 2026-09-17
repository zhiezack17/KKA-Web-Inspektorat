<?php
declare(strict_types=1);

/**
 * Lightweight Zero-Dependency XLSX & CSV Reader for KKA Inspektorat Rohil
 * Mendukung parsing file .xlsx (OpenXML) dan .csv tanpa library eksternal.
 */
class SimpleXLSX {
    private array $rows = [];
    private string $error = '';

    public static function parse(string $filename): ?self {
        $parser = new self();
        if ($parser->load($filename)) {
            return $parser;
        }
        return null;
    }

    public function rows(): array {
        return $this->rows;
    }

    public function error(): string {
        return $this->error;
    }

    public function load(string $filename): bool {
        $this->rows = [];
        $this->error = '';

        if (!file_exists($filename) || !is_readable($filename)) {
            $this->error = 'File tidak ditemukan atau tidak dapat dibaca.';
            return false;
        }

        // Cek apakah file adalah CSV
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'csv') {
            return $this->loadCsv($filename);
        }

        // Coba buka sebagai ZIP (.xlsx)
        $zip = new ZipArchive();
        if ($zip->open($filename) !== true) {
            // Fallback: mungkin CSV yang diberi ekstensi .xlsx atau file teks
            return $this->loadCsv($filename);
        }

        // 1. Baca Shared Strings (xl/sharedStrings.xml)
        $sharedStrings = [];
        $ssIndex = $zip->locateName('xl/sharedStrings.xml', ZipArchive::FL_NODIR);
        if ($ssIndex !== false) {
            $ssXmlContent = $zip->getFromIndex($ssIndex);
            if ($ssXmlContent !== false) {
                $xml = simplexml_load_string($ssXmlContent);
                if ($xml && isset($xml->si)) {
                    foreach ($xml->si as $val) {
                        if (isset($val->t)) {
                            $sharedStrings[] = (string)$val->t;
                        } elseif (isset($val->r)) {
                            // Rich text run
                            $txt = '';
                            foreach ($val->r as $r) {
                                $txt .= (string)$r->t;
                            }
                            $sharedStrings[] = $txt;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }
        }

        // 2. Cari worksheet utama (sheet1.xml)
        $sheetContent = null;
        $sheetNames = ['xl/worksheets/sheet1.xml', 'xl/worksheets/Sheet1.xml'];
        foreach ($sheetNames as $sn) {
            $idx = $zip->locateName($sn, ZipArchive::FL_NODIR);
            if ($idx !== false) {
                $sheetContent = $zip->getFromIndex($idx);
                break;
            }
        }

        if ($sheetContent === null) {
            // Coba cari file sheet apapun
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#xl/worksheets/sheet\d+\.xml#i', $name)) {
                    $sheetContent = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetContent === null) {
            $this->error = 'Worksheet Excel tidak ditemukan di dalam file.';
            return false;
        }

        // 3. Parse Sheet XML
        $xml = simplexml_load_string($sheetContent);
        if (!$xml || !isset($xml->sheetData)) {
            $this->error = 'Format worksheet Excel tidak valid.';
            return false;
        }

        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int)$row['r'];
            $cells = [];

            foreach ($row->c as $cell) {
                $cellRef = (string)$cell['r'];
                $cellType = (string)$cell['t']; // 's' = shared string, 'b' = bool, '' = number, 'str' = inline formula string
                $val = isset($cell->v) ? (string)$cell->v : '';

                if ($cellType === 's') {
                    $valIndex = (int)$val;
                    $val = $sharedStrings[$valIndex] ?? '';
                } elseif ($cellType === 'inlineStr' && isset($cell->is->t)) {
                    $val = (string)$cell->is->t;
                }

                // Kolom dari ref (misal 'A1' -> 'A', 'AA1' -> 'AA')
                $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                $colIndex = self::columnLetterToIndex($colLetters);
                $cells[$colIndex] = $val;
            }

            if (!empty($cells)) {
                // Susun array terurut berdasarkan indeks kolom
                $maxCol = max(array_keys($cells));
                $rowArr = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $rowArr[$c] = $cells[$c] ?? '';
                }
                $this->rows[] = $rowArr;
            }
        }

        return true;
    }

    private function loadCsv(string $filename): bool {
        $handle = fopen($filename, 'r');
        if (!$handle) {
            $this->error = 'Gagal membuka file CSV.';
            return false;
        }

        // Deteksi delimiter: koma, titik koma, atau tab
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            $this->rows[] = $data;
        }
        fclose($handle);
        return true;
    }

    private static function columnLetterToIndex(string $letter): int {
        $letter = strtoupper($letter);
        $len = strlen($letter);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($letter[$i]) - 64);
        }
        return $num - 1; // 0-based
    }
}

