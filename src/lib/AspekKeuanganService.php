<?php
declare(strict_types=1);

/**
 * Service Analisis Aspek Keuangan & Deteksi Ketekoran Kas/Pajak
 * Mengadopsi Metodologi Siswaskeudes (BPKP - Kemendagri) & Standar Audit APIP
 * Inspektorat Kabupaten Rokan Hilir
 */
class AspekKeuanganService {

    /**
     * Hitung ringkasan Aspek Keuangan untuk satu Desa dan Tahun Anggaran
     */
    public static function getAnalisisDesa(int $desaId, int $tahun): array {
        // 1. Data Desa & Kecamatan
        $desa = DB::one("
            SELECT d.id, d.nama AS desa_nama, k.id AS kec_id, k.nama AS kecamatan_nama
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            WHERE d.id = ?
        ", [$desaId]);

        if (!$desa) {
            return [];
        }

        // 2. Data BAP Opname Kas Terakhir
        $opname = DB::one("
            SELECT * FROM kka_opname_kas
            WHERE desa_id = ? AND tahun_anggaran = ?
            ORDER BY tgl_pemeriksaan DESC, id DESC LIMIT 1
        ", [$desaId, $tahun]);

        $kasBku        = (float)($opname['saldo_bku'] ?? 0);
        $kasBank       = (float)($opname['saldo_bank'] ?? 0);
        $kasFisik      = (float)($opname['total_kas_fisik'] ?? 0);
        $kasRiil       = (float)($opname['total_kas_riil'] ?? ($kasBank + $kasFisik));
        $selisihKas    = (float)($opname['selisih_kas'] ?? ($kasRiil - $kasBku));
        $hasOpname     = !empty($opname);

        $statusKas = 'BELUM_OPNAME';
        $tekorKasNominal = 0.0;
        if ($hasOpname) {
            if ($selisihKas < -0.01) {
                $statusKas = 'TEKOR_KAS';
                $tekorKasNominal = abs($selisihKas);
            } elseif ($selisihKas > 0.01) {
                $statusKas = 'LEBIH';
            } else {
                $statusKas = 'COCOK';
            }
        }

        // 3. Analisis Kepatuhan Pajak Belanja (dari kka_rincian & kka_sesi)
        $rincianPajak = DB::all("
            SELECT 
                r.id, r.uraian, r.biaya_dikwitansi, r.potong_ppn, r.nominal_ppn,
                r.potong_pph, r.nominal_pph, r.status_pajak, r.ntpn,
                s.id AS sesi_id, s.objek_audit
            FROM kka_rincian r
            JOIN kka_sesi s ON s.id = r.sesi_id
            WHERE s.desa_id = ? AND s.tahun_anggaran = ?
        ", [$desaId, $tahun]);

        $totalBelanjaKwitansi  = 0.0;
        $totalBelanjaDiatas2Jt = 0;
        $totalKwitansiDipotong = 0;
        $totalKwitansiDisetor  = 0;
        $totalPajakDipungut    = 0.0;
        $totalPajakDisetor     = 0.0;
        $totalPajakBelumSetor  = 0.0;
        $daftarPajakTerutang   = [];

        foreach ($rincianPajak as $r) {
            $kw = (float)$r['biaya_dikwitansi'];
            $totalBelanjaKwitansi += $kw;

            if ($kw >= 2000000) {
                $totalBelanjaDiatas2Jt++;
            }

            $pajakRow = (float)$r['nominal_ppn'] + (float)$r['nominal_pph'];
            if ($pajakRow > 0) {
                $totalKwitansiDipotong++;
                $totalPajakDipungut += $pajakRow;

                $ntpnClean = trim((string)($r['ntpn'] ?? ''));
                $isSetor = ($r['status_pajak'] === 'SUDAH_SETOR') || ($ntpnClean !== '');

                if ($isSetor) {
                    $totalPajakDisetor += $pajakRow;
                    $totalKwitansiDisetor++;
                } else {
                    $totalPajakBelumSetor += $pajakRow;
                    $daftarPajakTerutang[] = [
                        'uraian'       => $r['uraian'],
                        'objek_audit'  => $r['objek_audit'],
                        'nominal_ppn'  => (float)$r['nominal_ppn'],
                        'nominal_pph'  => (float)$r['nominal_pph'],
                        'total_pajak'  => $pajakRow,
                        'status'       => $r['status_pajak'],
                        'sesi_id'      => $r['sesi_id']
                    ];
                }
            }
        }

        $statusPajak = 'TERTIB';
        if ($totalPajakBelumSetor > 0) {
            $statusPajak = 'TEKOR_PAJAK';
        } elseif ($totalPajakDipungut <= 0) {
            $statusPajak = 'NIHIL_PAJAK';
        }

        $persenSetorPajak = $totalPajakDipungut > 0 
            ? round(($totalPajakDisetor / $totalPajakDipungut) * 100, 1) 
            : 100.0;

        // 4. Analisis Proporsi Belanja APBDes (Kriteria Siswaskeudes Maks 30% Belanja Operasional)
        $bidangRows = DB::all("
            SELECT 
                b.id, b.urutan, b.urutan AS kode, b.nama,
                COALESCE(SUM(r.biaya_dikwitansi), 0) AS total_belanja,
                COUNT(DISTINCT s.id) AS jumlah_sesi
            FROM kka_bidang b
            LEFT JOIN kka_sesi s ON s.bidang_id = b.id AND s.desa_id = ? AND s.tahun_anggaran = ?
            LEFT JOIN kka_rincian r ON r.sesi_id = s.id
            GROUP BY b.id, b.urutan, b.nama
            ORDER BY b.urutan ASC
        ", [$desaId, $tahun]);

        $belanjaOperasional = 0.0;
        $totalBelanjaBidang = 0.0;
        foreach ($bidangRows as $b) {
            $amt = (float)$b['total_belanja'];
            $totalBelanjaBidang += $amt;
            // Bidang 1: Penyelenggaraan Pemerintahan Desa (Belanja Operasional & Siltap)
            if ((int)$b['urutan'] === 1 || stripos($b['nama'], 'Penyelenggaraan') !== false) {
                $belanjaOperasional += $amt;
            }
        }

        $persenOperasional = $totalBelanjaBidang > 0 
            ? round(($belanjaOperasional / $totalBelanjaBidang) * 100, 1) 
            : 0.0;

        $statusProporsi = 'WAJAR';
        if ($persenOperasional > 30.0) {
            $statusProporsi = 'MELEBIHI_BATAS';
        }

        // 5. Kalkulasi Skor Risiko Keuangan Terpadu (Siswaskeudes 0 - 100)
        $skorRisiko = 0;
        $faktorRisiko = [];

        if ($statusKas === 'TEKOR_KAS') {
            $skorRisiko += 40;
            $faktorRisiko[] = 'Terjadi ketekoran kas bendahara sebesar ' . rupiah($tekorKasNominal);
        } elseif ($statusKas === 'BELUM_OPNAME') {
            $skorRisiko += 15;
            $faktorRisiko[] = 'Pemeriksaan fisik kas (Opname Kas) belum dilakukan';
        }

        if ($statusPajak === 'TEKOR_PAJAK') {
            $skorRisiko += 35;
            $faktorRisiko[] = 'Terdapat kewajiban perpajakan belanja belum disetor (tanpa NTPN) sebesar ' . rupiah($totalPajakBelumSetor);
        }

        if ($statusProporsi === 'MELEBIHI_BATAS') {
            $skorRisiko += 25;
            $faktorRisiko[] = 'Proporsi belanja operasional ' . $persenOperasional . '% melebihi batas maksimal 30%';
        }

        $kategoriRisiko = 'RENDAH';
        if ($skorRisiko > 60) {
            $kategoriRisiko = 'TINGGI';
        } elseif ($skorRisiko >= 25) {
            $kategoriRisiko = 'SEDANG';
        }

        return [
            'desa'                  => $desa,
            'tahun'                 => $tahun,
            'has_opname'            => $hasOpname,
            'opname'                => $opname,
            // Kas
            'kas_bku'               => $kasBku,
            'kas_bank'              => $kasBank,
            'kas_fisik'             => $kasFisik,
            'kas_riil'              => $kasRiil,
            'selisih_kas'           => $selisihKas,
            'status_kas'            => $statusKas,
            'tekor_kas_nominal'     => $tekorKasNominal,
            // Pajak
            'total_belanja_audit'   => $totalBelanjaKwitansi,
            'belanja_diatas_2jt'    => $totalBelanjaDiatas2Jt,
            'kwitansi_dipotong'     => $totalKwitansiDipotong,
            'kwitansi_disetor'      => $totalKwitansiDisetor,
            'pajak_dipotong'        => $totalPajakDipungut,
            'pajak_disetor'         => $totalPajakDisetor,
            'pajak_belum_setor'     => $totalPajakBelumSetor,
            'persen_setor_pajak'    => $persenSetorPajak,
            'status_pajak'          => $statusPajak,
            'daftar_pajak_terutang' => $daftarPajakTerutang,
            // Proporsi Belanja
            'bidang_breakdown'      => $bidangRows,
            'belanja_operasional'   => $belanjaOperasional,
            'total_belanja_bidang'  => $totalBelanjaBidang,
            'persen_operasional'    => $persenOperasional,
            'status_proporsi'       => $statusProporsi,
            // Skor Terpadu
            'skor_risiko'           => $skorRisiko,
            'kategori_risiko'       => $kategoriRisiko,
            'faktor_risiko'         => $faktorRisiko,
            'total_potensi_kerugian'=> $tekorKasNominal + $totalPajakBelumSetor
        ];
    }

    /**
     * Rekapitulasi Aspek Keuangan seluruh desa untuk satu tahun anggaran
     */
    public static function getRekapSeluruhDesa(int $tahun): array {
        $daftarDesa = DB::all("
            SELECT d.id, d.nama, k.nama AS kecamatan
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            WHERE d.id IN (
                SELECT DISTINCT desa_id FROM kka_sesi WHERE tahun_anggaran = ?
                UNION
                SELECT DISTINCT desa_id FROM kka_opname_kas WHERE tahun_anggaran = ?
            )
            ORDER BY k.nama ASC, d.nama ASC
        ", [$tahun, $tahun]);

        $hasil = [];
        foreach ($daftarDesa as $d) {
            $hasil[] = self::getAnalisisDesa((int)$d['id'], $tahun);
        }
        return $hasil;
    }
}
