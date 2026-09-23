<?php
declare(strict_types=1);

/**
 * Controller Aspek Keuangan & Deteksi Ketekoran Kas/Pajak (Metode Siswaskeudes)
 * Inspektorat Kabupaten Rokan Hilir
 */
class AspekKeuanganController {
    private Auth $auth;

    public function __construct(Auth $auth) {
        $this->auth = $auth;
        $this->auth->require();

        if ($this->auth->isOperatorSpt()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Perencanaan (Operator SPT) difokuskan pada pengelolaan administrasi SPT.');
            redirect('penugasan/spt');
        }
        if ($this->auth->isOperatorTl()) {
            flash('warning', 'Akses dibatasi: Peran Bagian Tindak Lanjut (TLHP) difokuskan pada pemantauan hasil tindak lanjut rekomendasi.');
            redirect('tlhp');
        }
    }

    /**
     * Halaman Utama Analisis Aspek Keuangan Desa
     */
    public function index(): void {
        $defaultTahun = (int) DB::val("SELECT MAX(tahun_anggaran) FROM kka_sesi") ?: (int)date('Y');
        $tahun = (int) input('tahun', $defaultTahun);
        $desaId = (int) input('desa_id', 0);

        // Jika desa_id belum dipilih, pilih desa pertama yang memiliki data audit
        if ($desaId === 0) {
            $desaId = (int) DB::val("
                SELECT desa_id FROM kka_sesi WHERE tahun_anggaran = ? LIMIT 1
            ", [$tahun]) ?: ((int) DB::val("SELECT id FROM kka_desa LIMIT 1") ?: 0);
        }

        // Daftar Desa untuk dropdown pilihan
        $daftarDesa = DB::all("
            SELECT d.id, d.nama, k.nama AS kecamatan
            FROM kka_desa d
            JOIN kka_kecamatan k ON k.id = d.kecamatan_id
            ORDER BY k.nama ASC, d.nama ASC
        ");

        // Daftar Tahun Anggaran yang tersedia
        $daftarTahun = DB::all("
            SELECT DISTINCT tahun_anggaran AS tahun FROM kka_sesi
            UNION
            SELECT DISTINCT tahun_anggaran AS tahun FROM kka_opname_kas
            ORDER BY tahun DESC
        ");
        if (empty($daftarTahun)) {
            $daftarTahun = [['tahun' => (int)date('Y')]];
        }

        // Analisis untuk desa terpilih
        $analisis = $desaId > 0 ? AspekKeuanganService::getAnalisisDesa($desaId, $tahun) : [];

        // Rekapitulasi seluruh desa pada tahun berjalan
        $rekapDesa = AspekKeuanganService::getRekapSeluruhDesa($tahun);

        view('aspek_keuangan/index', compact(
            'tahun',
            'desaId',
            'daftarDesa',
            'daftarTahun',
            'analisis',
            'rekapDesa'
        ));
    }

    /**
     * Cetak Lembar Uji Aspek Keuangan Desa (A4 Standar Rohil)
     */
    public function print(): void {
        $desaId = (int) input('desa_id', 0);
        $tahun  = (int) input('tahun', (int)date('Y'));

        if ($desaId <= 0) {
            flash('error', 'Pilih desa terlebih dahulu.');
            redirect('aspek-keuangan');
        }

        $analisis = AspekKeuanganService::getAnalisisDesa($desaId, $tahun);
        if (empty($analisis)) {
            flash('error', 'Data analisis keuangan desa tidak ditemukan.');
            redirect('aspek-keuangan');
        }

        // Ambil data penugasan SPT jika ada
        $spt = DB::one("
            SELECT * FROM kka_spt 
            WHERE desa_id = ? AND tahun_anggaran = ? 
            ORDER BY id DESC LIMIT 1
        ", [$desaId, $tahun]);

        view('aspek_keuangan/print', compact('analisis', 'spt', 'tahun'));
    }
}
