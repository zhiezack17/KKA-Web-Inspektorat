<?php
partial('head', ['title' => 'Pratinjau Konsep LHP Kepenghuluan ' . $desa['nama'] . ' TA ' . $tahun]);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-file-contract" style="color:#059669"></i>
          Konsep Laporan Hasil Pengawasan (LHP)
        </h2>
        <p>Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan Kepenghuluan <?= e($desa['nama']) ?>, Kec. <?= e($desa['kecamatan_nama']) ?> TA <?= $tahun ?>.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="<?= url('lhp?tahun=' . $tahun) ?>" class="btn btn-outline">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar LHP
        </a>
        <a href="<?= url('print/lhp?desa_id=' . $desa['id'] . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-primary" style="background:#059669;border-color:#059669">
          <i class="fa-solid fa-print"></i> Cetak / Ekspor PDF Naskah LHP
        </a>
      </div>
    </div>

    <!-- Ringkasan Kartu Atas -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;margin-bottom:20px">
      <div class="card" style="padding:14px 18px;border-left:4px solid var(--emerald-600)">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:600">TOTAL PAGU DIUJI</div>
        <div style="font-size:18px;font-weight:800;color:var(--slate-800);margin-top:4px"><?= rupiah($totalPagu) ?></div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid #2563eb">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:600">TOTAL REALISASI DIUJI</div>
        <div style="font-size:18px;font-weight:800;color:#1d4ed8;margin-top:4px"><?= rupiah($totalRealisasi) ?></div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid #16a34a">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:600">TOTAL BUKTI KUITANSI</div>
        <div style="font-size:18px;font-weight:800;color:#15803d;margin-top:4px"><?= rupiah($totalKuitansi) ?></div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid <?= $totalSelisih < 0 ? '#dc2626' : '#16a34a' ?>">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:600">SELISIH BELANJA</div>
        <div style="font-size:18px;font-weight:800;color:<?= $totalSelisih < 0 ? '#dc2626' : '#15803d' ?>;margin-top:4px"><?= rupiah($totalSelisih) ?></div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid #d97706">
        <div style="font-size:11.5px;color:var(--slate-500);font-weight:600">TEMUAN (KTP)</div>
        <div style="font-size:18px;font-weight:800;color:#b45309;margin-top:4px"><?= count($daftarTemuan) ?> Butir (<?= rupiah($totalNominalTemuan) ?>)</div>
      </div>
    </div>

    <!-- Naskah LHP Container -->
    <div class="card" style="max-width:960px;margin:0 auto;padding:32px 40px;background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.06);line-height:1.6">
      
      <!-- COVER / KOP LHP -->
      <div style="text-align:center;border-bottom:3px double #000;padding-bottom:16px;margin-bottom:24px">
        <img src="<?= asset('img/logo-rohil.png') ?>" alt="Logo Rohil" style="width:70px;height:auto;margin-bottom:6px">
        <h3 style="margin:0;font-size:15px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px">PEMERINTAH KABUPATEN ROKAN HILIR</h3>
        <h2 style="margin:2px 0 0;font-size:18px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#0f172a">INSPEKTORAT DAERAH</h2>
        <p style="margin:4px 0 0;font-size:11px;color:#475569">Jl. Perkantoran Bagansiapiapi &bull; Email: inspektorat@rohilkab.go.id &bull; Kode Pos: 28912</p>
      </div>

      <div style="text-align:center;margin-bottom:28px">
        <h3 style="margin:0;font-size:16px;font-weight:800;text-transform:uppercase;text-decoration:underline">
          LAPORAN HASIL PENGAWASAN (LHP)
        </h3>
        <div style="font-size:13px;font-weight:700;color:#1e293b;margin-top:6px">
          AUDIT DENGAN TUJUAN TERTENTU (ADTT) ATAS PENGELOLAAN KEUANGAN KEPENGHULUAN <?= e($desa['nama']) ?><br>
          KECAMATAN <?= e($desa['kecamatan_nama']) ?> KABUPATEN ROKAN HILIR<br>
          TAHUN ANGGARAN <?= $tahun ?>
        </div>
        <div style="font-size:12px;color:#64748b;margin-top:4px">
          NOMOR : <?= e($spt['no_lha'] ?? ('700/LHA-INSP/' . $tahun . '/' . sprintf('%03d', $desa['id']))) ?>
        </div>
      </div>

      <!-- RINGKASAN EKSEKUTIF -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:18px 20px;margin-bottom:28px">
        <h4 style="margin:0 0 10px;font-size:14px;color:#0f172a;text-transform:uppercase;font-weight:800;border-bottom:1px solid #cbd5e1;padding-bottom:6px">
          RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)
        </h4>
        <p style="margin:0 0 10px;font-size:13px;color:#334155;text-align:justify">
          Berdasarkan Surat Perintah Tugas Inspektur Daerah Kabupaten Rokan Hilir Nomor: <b><?= e($spt['no_spt'] ?? '...........................') ?></b> tanggal <b><?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '..............' ?></b>, Tim Pemeriksa telah melakukan Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan Kepenghuluan <?= e($desa['nama']) ?> Kecamatan <?= e($desa['kecamatan_nama']) ?> Tahun Anggaran <?= $tahun ?>.
        </p>
        <p style="margin:0 0 12px;font-size:13px;color:#334155;text-align:justify">
          Dari hasil pengujian terhadap bukti pertanggungjawaban (SPJ) dan verifikasi fisik di lapangan atas realisasi belanja sebesar <b><?= rupiah($totalRealisasi) ?></b>, Tim Pengawasan mengidentifikasi <b><?= count($daftarTemuan) ?> butir Pokok Temuan Pemeriksaan</b> dengan total nilai ketidaksesuaian/indikasi kerugian kas desa sebesar <b><?= rupiah($totalNominalTemuan) ?></b>.
        </p>
      </div>

      <!-- BAB I -->
      <div style="margin-bottom:26px">
        <h4 style="margin:0 0 8px;font-size:14px;font-weight:800;color:#0f172a;border-bottom:1.5px solid #0f172a;padding-bottom:4px">
          BAB I : INFORMASI UMUM PENUGASAN
        </h4>
        <table style="width:100%;font-size:13px;border-collapse:collapse;margin-top:8px">
          <tr><td style="width:25%;padding:3px 0;vertical-align:top"><b>1. Dasar Penugasan</b></td><td style="width:2%">:</td><td style="padding:3px 0">Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Kabupaten Rokan Hilir Tahun <?= $tahun ?> &bull; Surat Perintah Tugas Nomor <?= e($spt['no_spt'] ?? '-') ?> tanggal <?= !empty($spt['tgl_spt']) ? tgl_id($spt['tgl_spt']) : '-' ?>.</td></tr>
          <tr><td style="padding:3px 0;vertical-align:top"><b>2. Tujuan Pengawasan</b></td><td>:</td><td style="padding:3px 0"><?= e($spt['tujuan'] ?? 'Memberikan keyakinan memadai atas ketaatan, efisiensi, dan efektivitas pengelolaan keuangan serta kepatuhan administrasi belanja Kepenghuluan.') ?></td></tr>
          <tr><td style="padding:3px 0;vertical-align:top"><b>3. Ruang Lingkup</b></td><td>:</td><td style="padding:3px 0">Pengujian aspek keuangan tertentu, kepatuhan perpajakan belanja desa, dan opname fisik pekerjaan pembangunan desa Tahun Anggaran <?= $tahun ?>.</td></tr>
          <tr>
            <td style="padding:3px 0;vertical-align:top"><b>4. Susunan Tim</b></td><td>:</td>
            <td style="padding:3px 0">
              <div>1. Penanggung Jawab : <?= e($inspektur['nama']) ?> (Inspektur Daerah)</div>
              <div>2. Wakil Penanggung Jawab : <?= e($spt['wakil_pj_nama'] ?? '-') ?> (Inspektur Pembantu)</div>
              <div>3. Pengendali Teknis : <?= e($spt['dalnis_nama'] ?? '-') ?></div>
              <div>4. Ketua Tim : <?= e($spt['ketua_tim_nama'] ?? '-') ?></div>
              <?php if (!empty($anggotaList)): ?>
                <div>5. Anggota Tim : 
                  <?= implode(', ', array_map(fn($a) => e($a['nama'] ?? ''), $anggotaList)) ?>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </div>

      <!-- BAB II -->
      <div style="margin-bottom:26px">
        <h4 style="margin:0 0 8px;font-size:14px;font-weight:800;color:#0f172a;border-bottom:1.5px solid #0f172a;padding-bottom:4px">
          BAB II : GAMBARAN PENGELOLAAN KEUANGAN KEPENGHULUAN
        </h4>
        <p style="margin:0 0 10px;font-size:13px;color:#334155">
          Realisasi pengeluaran kas belanja APBDesa Kepenghuluan <?= e($desa['nama']) ?> Tahun Anggaran <?= $tahun ?> yang dilakukan uji petik adalah sebagai berikut:
        </p>
        
        <table class="table" style="width:100%;font-size:12px;margin-bottom:14px">
          <thead>
            <tr style="background:#f1f5f9">
              <th style="width:30px">No</th>
              <th>Bidang Belanja APBDesa</th>
              <th class="num">Pagu Anggaran</th>
              <th class="num">Realisasi Uji</th>
              <th class="num">Bukti Kuitansi</th>
              <th class="num">Selisih</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rekapBidang)): ?>
              <tr><td colspan="6" style="text-align:center">Belum ada rincian belanja.</td></tr>
            <?php else: $nb=1; foreach ($rekapBidang as $rb): 
              $selB = (float)$rb['realisasi'] - (float)$rb['kuitansi'];
            ?>
              <tr>
                <td><?= $nb++ ?></td>
                <td><b><?= e($rb['nama']) ?></b></td>
                <td class="num"><?= rupiah($rb['pagu']) ?></td>
                <td class="num"><?= rupiah($rb['realisasi']) ?></td>
                <td class="num"><?= rupiah($rb['kuitansi']) ?></td>
                <td class="num" style="color:<?= $selB < 0 ? '#dc2626' : '#15803d' ?>;font-weight:700"><?= rupiah($selB) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <tfoot style="font-weight:bold;background:#f8fafc">
            <tr>
              <td colspan="2">TOTAL BELANJA DIUJI</td>
              <td class="num"><?= rupiah($totalPagu) ?></td>
              <td class="num"><?= rupiah($totalRealisasi) ?></td>
              <td class="num"><?= rupiah($totalKuitansi) ?></td>
              <td class="num" style="color:<?= $totalSelisih < 0 ? '#dc2626' : '#15803d' ?>"><?= rupiah($totalSelisih) ?></td>
            </tr>
          </tfoot>
        </table>

        <!-- Evaluasi Pajak -->
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px 14px;font-size:12.5px;color:#166534">
          <b>Evaluasi Kepatuhan Pajak Belanja:</b>
          Total Pajak PPN Diuji: <b><?= rupiah($rekapPajak['total_ppn'] ?? 0) ?></b> &bull; 
          Total Pajak PPh: <b><?= rupiah($rekapPajak['total_pph'] ?? 0) ?></b> &bull; 
          Sudah Disetor: <b><?= rupiah($rekapPajak['pajak_disetor'] ?? 0) ?></b> &bull; 
          Tunggakan Belum Setor: <b style="color:#dc2626"><?= rupiah($rekapPajak['pajak_belum_setor'] ?? 0) ?></b>
        </div>
      </div>

      <!-- BAB III -->
      <div style="margin-bottom:26px">
        <h4 style="margin:0 0 8px;font-size:14px;font-weight:800;color:#0f172a;border-bottom:1.5px solid #0f172a;padding-bottom:4px">
          BAB III : HASIL PENGAWASAN, POKOK TEMUAN &amp; REKOMENDASI
        </h4>
        <?php if (empty($daftarTemuan)): ?>
          <p style="font-size:13px;color:#64748b;font-style:italic">
            Berdasarkan hasil pengujian atas bukti pertanggungjawaban yang disampaikan, secara material tidak ditemukan penyimpangan yang berindikasi merugikan kas desa.
          </p>
        <?php else: $nt=1; foreach ($daftarTemuan as $t): ?>
          <div style="margin-bottom:20px;padding:14px 18px;border:1px solid #e2e8f0;border-radius:8px;background:#fafafa">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:8px">
              <strong style="font-size:13.5px;color:#0f172a">
                <?= $nt++ ?>. <?= e($t['judul']) ?>
              </strong>
              <span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700"><?= e($t['nomor_temuan']) ?></span>
            </div>
            
            <?php if ((float)$t['nominal'] > 0): ?>
              <div style="font-size:12.5px;color:#dc2626;font-weight:700;margin-bottom:8px">
                Nilai Ketidaksesuaian / Kerugian: <?= rupiah($t['nominal']) ?>
              </div>
            <?php endif; ?>

            <div style="font-size:12.5px;color:#334155;line-height:1.5">
              <div style="margin-bottom:6px"><b>a. Kondisi:</b><br><?= nl2br(e($t['kondisi'])) ?></div>
              <div style="margin-bottom:6px"><b>b. Kriteria:</b><br><?= nl2br(e($t['kriteria'])) ?></div>
              <div style="margin-bottom:6px"><b>c. Sebab:</b><br><?= nl2br(e($t['sebab'])) ?></div>
              <div style="margin-bottom:6px"><b>d. Akibat:</b><br><?= nl2br(e($t['akibat'])) ?></div>
              <div style="margin-bottom:6px;color:#065f46"><b>e. Rekomendasi:</b><br><?= nl2br(e($t['rekomendasi'])) ?></div>
              <?php if (!empty($t['tanggapan_auditi'])): ?>
                <div style="background:#f1f5f9;padding:8px;border-radius:4px;margin-top:6px;font-style:italic">
                  <b>Tanggapan Pihak Auditi:</b> <?= e($t['tanggapan_auditi']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- BAB IV -->
      <div style="margin-bottom:32px">
        <h4 style="margin:0 0 8px;font-size:14px;font-weight:800;color:#0f172a;border-bottom:1.5px solid #0f172a;padding-bottom:4px">
          BAB IV : KESIMPULAN &amp; PENUTUP
        </h4>
        <p style="margin:0 0 10px;font-size:13px;color:#334155;text-align:justify">
          Demikian Laporan Hasil Pengawasan (LHP) Audit Dengan Tujuan Tertentu (ADTT) atas Pengelolaan Keuangan Kepenghuluan <?= e($desa['nama']) ?> Kecamatan <?= e($desa['kecamatan_nama']) ?> ini disusun sebagai bahan evaluasi dan perbaikan tata kelola keuangan desa. Diharapkan Pj. Penghulu beserta jajaran segera menindaklanjuti rekomendasi yang termuat dalam laporan ini selambat-lambatnya 60 (enam puluh) hari kalender sejak laporan ini diterima.
        </p>
      </div>

      <!-- LEMBAR TANDA TANGAN & PENGESAHAN -->
      <div style="border-top:1.5px solid #000;padding-top:20px;margin-top:30px">
        <table style="width:100%;font-size:12px;border-collapse:collapse">
          <tr>
            <td style="width:50%;text-align:center;vertical-align:top;padding-bottom:50px">
              Mengetahui,<br>
              <b>Inspektur Pembantu (Irban)</b><br><br><br><br>
              <b><u><?= e($spt['wakil_pj_nama'] ?? 'MARWAN, M.T') ?></u></b><br>
              NIP. <?= !empty($spt['wakil_pj_nip']) ? e($spt['wakil_pj_nip']) : '19770727 200212 1 005' ?>
            </td>
            <td style="width:50%;text-align:center;vertical-align:top;padding-bottom:50px">
              Bagansiapiapi, <?= tgl_id(date('Y-m-d')) ?><br>
              <b>Ketua Tim Pemeriksa</b><br><br><br><br>
              <b><u><?= e($spt['ketua_tim_nama'] ?? '............................................') ?></u></b><br>
              NIP. <?= !empty($spt['ketua_tim_nip']) ? e($spt['ketua_tim_nip']) : '............................................' ?>
            </td>
          </tr>
          <tr>
            <td colspan="2" style="text-align:center;vertical-align:top;padding-top:20px">
              Mengesahkan,<br>
              <b>INSPEKTUR DAERAH KABUPATEN ROKAN HILIR</b><br><br><br><br><br>
              <b style="font-size:13px"><u><?= e($inspektur['nama']) ?></u></b><br>
              NIP. <?= e($inspektur['nip']) ?>
            </td>
          </tr>
        </table>
      </div>

    </div>
  </div>
</main>

<?php partial('foot'); ?>
