<?php 
$title = 'Pengujian Aspek Keuangan Desa - KKA Digital'; 
partial('head', compact('title'));
partial('sidebar');
?>

<main class="main" data-testid="page-aspek-keuangan">
  <?php partial('topbar', ['title' => 'Pengujian Aspek Keuangan Desa', 'icon' => 'fa-solid fa-calculator']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <!-- 1. HEADER & FILTER KEPENGHULUAN -->
    <div class="page-head" style="margin-bottom:20px;background:linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);border:1px solid #e2e8f0;border-radius:14px;padding:16px 22px;box-shadow:0 1px 3px rgba(0,0,0,0.03);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div>
        <div style="display:flex;align-items:center;gap:10px">
          <span class="badge" style="background:#065f46;color:#a7f3d0;font-size:11px;font-weight:700">
            <i class="fa-solid fa-shield-halved" style="margin-right:4px"></i> STANDAR PENGAWASAN KEUANGAN DESA
          </span>
          <span style="font-size:12px;color:#64748b">Pengawasan Kinerja Keuangan Desa 2026</span>
        </div>
        <h2 style="font-size:20px;font-weight:800;letter-spacing:-0.3px;color:#0f172a;margin:6px 0 2px">
          Uji Keseimbangan Kas &amp; Kepatuhan Pajak Belanja
        </h2>
        <p style="color:#64748b;font-size:12.5px;margin:0">
          Mendeteksi otomatis potensi <strong>Ketekoran Kas Tunai/Bank</strong>, <strong>Tunggakan Pajak Belum Setor (NTPN)</strong>, dan <strong>Proporsi Belanja 30%</strong>.
        </p>
      </div>

      <!-- Form Filter Desa & Tahun -->
      <form method="get" action="<?= url('aspek-keuangan') ?>" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <select name="desa_id" class="input" style="font-size:12.5px;font-weight:700;padding:7px 12px;min-width:220px;border-radius:8px" onchange="this.form.submit()">
          <?php foreach ($daftarDesa as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $d['id'] == $desaId ? 'selected' : '' ?>>
              Kepenghuluan <?= e($d['nama']) ?> (Kec. <?= e($d['kecamatan']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <select name="tahun" class="input" style="font-size:12.5px;font-weight:700;padding:7px 12px;width:105px;border-radius:8px" onchange="this.form.submit()">
          <?php foreach ($daftarTahun as $t): ?>
            <option value="<?= $t['tahun'] ?>" <?= $t['tahun'] == $tahun ? 'selected' : '' ?>>
              TA <?= (int)$t['tahun'] ?>
            </option>
          <?php endforeach; ?>
        </select>

        <?php if (!empty($analisis)): ?>
          <a href="<?= url('print/aspek-keuangan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-outline" style="border-color:#0f766e;color:#0f766e;font-weight:700;padding:7px 14px;border-radius:8px" title="Cetak Lembar Pengujian A4 Resmi">
            <i class="fa-solid fa-print"></i> Cetak Lembar Uji (A4)
          </a>
        <?php endif; ?>
      </form>
    </div>

    <?php if (empty($analisis)): ?>
      <div class="empty" style="padding:50px 20px;text-align:center;background:#fff;border-radius:12px;border:1px solid #e2e8f0">
        <i class="fa-solid fa-folder-open" style="font-size:42px;color:#94a3b8;margin-bottom:12px"></i>
        <h4 style="margin:0;font-size:16px;color:#334155">Belum Ada Data Transaksi untuk Desa Ini</h4>
        <p style="margin:6px 0 0;font-size:13px;color:#64748b">Pilih kepenghuluan lain yang telah memiliki sesi audit belanja atau BAP pemeriksaan kas.</p>
      </div>
    <?php else: ?>

      <!-- 2. KARTU HASIL IDENTIFIKASI ASPEK KEUANGAN (3 PILAR UTAMA) -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(310px, 1fr));gap:16px;margin-bottom:22px">
        
        <!-- KARTU 1: HASIL UJI KAS (KETEKORAN KAS) -->
        <div class="card" style="background:#fff;border:1.5px solid <?= $analisis['status_kas'] === 'TEKOR_KAS' ? '#fca5a5' : ($analisis['status_kas'] === 'COCOK' ? '#86efac' : '#cbd5e1') ?>;border-radius:12px;padding:18px;box-shadow:0 2px 6px rgba(0,0,0,0.04);position:relative">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div>
              <span style="font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">ASPEK 1: POSISI KAS DESA</span>
              <h3 style="margin:3px 0 0;font-size:15px;font-weight:800;color:#0f172a">Uji Keseimbangan Buku vs Riil</h3>
            </div>
            <?php if ($analisis['status_kas'] === 'TEKOR_KAS'): ?>
              <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-weight:800;font-size:11px">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:3px"></i> KETEKORAN KAS
              </span>
            <?php elseif ($analisis['status_kas'] === 'COCOK'): ?>
              <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-weight:800;font-size:11px">
                <i class="fa-solid fa-circle-check" style="margin-right:3px"></i> KAS COCOK (TERTIB)
              </span>
            <?php elseif ($analisis['status_kas'] === 'LEBIH'): ?>
              <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-weight:800;font-size:11px">
                <i class="fa-solid fa-info-circle" style="margin-right:3px"></i> KAS LEBIH
              </span>
            <?php else: ?>
              <span class="badge" style="background:#f1f5f9;color:#475569;font-weight:700;font-size:11px">BELUM OPNAME</span>
            <?php endif; ?>
          </div>

          <div style="background:<?= $analisis['status_kas'] === 'TEKOR_KAS' ? '#fff1f2' : '#f8fafc' ?>;border-radius:10px;padding:12px;margin-bottom:14px;border:1px solid <?= $analisis['status_kas'] === 'TEKOR_KAS' ? '#fecdd3' : '#f1f5f9' ?>">
            <div style="font-size:11px;color:#64748b;font-weight:600">Selisih Kas Riil terhadap BKU:</div>
            <div style="font-size:22px;font-weight:800;color:<?= $analisis['status_kas'] === 'TEKOR_KAS' ? '#dc2626' : ($analisis['status_kas'] === 'COCOK' ? '#059669' : '#0f172a') ?>;margin:4px 0">
              <?= $analisis['selisih_kas'] < 0 ? '-' . rupiah(abs($analisis['selisih_kas'])) : rupiah($analisis['selisih_kas']) ?>
            </div>
            <div style="font-size:11.5px;color:<?= $analisis['status_kas'] === 'TEKOR_KAS' ? '#991b1b' : '#047857' ?>;font-weight:600">
              <?php if ($analisis['status_kas'] === 'TEKOR_KAS'): ?>
                Terjadi selisih kurang fisik kas sebesar <strong><?= rupiah($analisis['tekor_kas_nominal']) ?></strong>. Wajib ditindaklanjuti via STS Kas Desa.
              <?php elseif ($analisis['status_kas'] === 'COCOK'): ?>
                Seluruh saldo menurut BKU sesuai 100% dengan fisik di brankas &amp; rekening bank.
              <?php else: ?>
                Belum ada Berita Acara Pemeriksaan Kas (Opname Kas) untuk tahun anggaran ini.
              <?php endif; ?>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:11.5px">
            <div style="background:#f8fafc;padding:8px;border-radius:6px;border:1px solid #f1f5f9">
              <span style="color:#64748b;display:block;font-size:10px;font-weight:700">SALDO MENURUT BKU:</span>
              <strong style="color:#0f172a"><?= rupiah($analisis['kas_bku']) ?></strong>
            </div>
            <div style="background:#f8fafc;padding:8px;border-radius:6px;border:1px solid #f1f5f9">
              <span style="color:#64748b;display:block;font-size:10px;font-weight:700">TOTAL KAS RIIL:</span>
              <strong style="color:#0f172a"><?= rupiah($analisis['kas_riil']) ?></strong>
            </div>
            <div style="background:#f8fafc;padding:8px;border-radius:6px;border:1px solid #f1f5f9">
              <span style="color:#64748b;display:block;font-size:10px;font-weight:700">KAS DI BANK (REK):</span>
              <strong style="color:#2563eb"><?= rupiah($analisis['kas_bank']) ?></strong>
            </div>
            <div style="background:#f8fafc;padding:8px;border-radius:6px;border:1px solid #f1f5f9">
              <span style="color:#64748b;display:block;font-size:10px;font-weight:700">KAS FISIK BRANKAS:</span>
              <strong style="color:#059669"><?= rupiah($analisis['kas_fisik']) ?></strong>
            </div>
          </div>

          <?php if (!empty($analisis['opname'])): ?>
            <div style="margin-top:10px;padding-top:8px;border-top:1px solid #f1f5f9;font-size:10.5px;color:#64748b;display:flex;justify-content:space-between">
              <span>Ref: BAP <?= e($analisis['opname']['no_bap']) ?></span>
              <span><?= date('d/m/Y', strtotime($analisis['opname']['tgl_pemeriksaan'])) ?></span>
            </div>
          <?php endif; ?>
        </div>

        <!-- KARTU 2: HASIL UJI PAJAK (KETEKORAN PAJAK) -->
        <div class="card" style="background:#fff;border:1.5px solid <?= $analisis['status_pajak'] === 'TEKOR_PAJAK' ? '#fde68a' : '#86efac' ?>;border-radius:12px;padding:18px;box-shadow:0 2px 6px rgba(0,0,0,0.04)">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div>
              <span style="font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">ASPEK 2: KEWAJIBAN PERPAJAKAN</span>
              <h3 style="margin:3px 0 0;font-size:15px;font-weight:800;color:#0f172a">Kepatuhan Setor Pajak Belanja</h3>
            </div>
            <?php if ($analisis['status_pajak'] === 'TEKOR_PAJAK'): ?>
              <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:800;font-size:11px">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:3px"></i> KETEKORAN PAJAK
              </span>
            <?php elseif ($analisis['status_pajak'] === 'TERTIB'): ?>
              <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-weight:800;font-size:11px">
                <i class="fa-solid fa-circle-check" style="margin-right:3px"></i> PAJAK TERTIB 100%
              </span>
            <?php else: ?>
              <span class="badge" style="background:#f1f5f9;color:#64748b;font-weight:700;font-size:11px">NIHIL PAJAK</span>
            <?php endif; ?>
          </div>

          <div style="background:<?= $analisis['status_pajak'] === 'TEKOR_PAJAK' ? '#fffbeb' : '#f8fafc' ?>;border-radius:10px;padding:12px;margin-bottom:14px;border:1px solid <?= $analisis['status_pajak'] === 'TEKOR_PAJAK' ? '#fde68a' : '#f1f5f9' ?>">
            <div style="font-size:11px;color:#64748b;font-weight:600">Pajak Belanja Terutang (Tanpa NTPN):</div>
            <div style="font-size:22px;font-weight:800;color:<?= $analisis['pajak_belum_setor'] > 0 ? '#d97706' : '#059669' ?>;margin:4px 0">
              <?= rupiah($analisis['pajak_belum_setor']) ?>
            </div>
            <div style="font-size:11.5px;color:<?= $analisis['pajak_belum_setor'] > 0 ? '#92400e' : '#047857' ?>;font-weight:600">
              <?php if ($analisis['pajak_belum_setor'] > 0): ?>
                Terdapat <strong><?= count($analisis['daftar_pajak_terutang']) ?> bukti transaksi</strong> yang pajaknya telah dipotong namun belum tervalidasi NTPN.
              <?php else: ?>
                Seluruh potongan pajak PPN &amp; PPh belanja telah disetorkan lunas ke Kas Negara.
              <?php endif; ?>
            </div>
          </div>

          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px">
              <span style="color:#64748b">Tingkat Penyetoran Kas Negara:</span>
              <strong style="color:<?= $analisis['persen_setor_pajak'] >= 100 ? '#059669' : '#d97706' ?>"><?= $analisis['persen_setor_pajak'] ?>%</strong>
            </div>
            <div style="height:7px;background:#f1f5f9;border-radius:99px;overflow:hidden">
              <div style="width:<?= min(100, $analisis['persen_setor_pajak']) ?>%;height:100%;background:<?= $analisis['persen_setor_pajak'] >= 100 ? '#059669' : '#f59e0b' ?>;border-radius:99px"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:11.5px">
            <div style="background:#f8fafc;padding:8px;border-radius:6px;border:1px solid #f1f5f9">
              <span style="color:#64748b;display:block;font-size:10px;font-weight:700">TOTAL DIPOTONG:</span>
              <strong style="color:#0f172a"><?= rupiah($analisis['pajak_dipotong']) ?></strong>
            </div>
            <div style="background:#f8fafc;padding:8px;border-radius:6px;border:1px solid #f1f5f9">
              <span style="color:#64748b;display:block;font-size:10px;font-weight:700">SUDAH SETOR (NTPN):</span>
              <strong style="color:#059669"><?= rupiah($analisis['pajak_disetor']) ?></strong>
            </div>
          </div>
        </div>

        <!-- KARTU 3: PROPORSI BELANJA OPERASIONAL (30%) & SKOR SISWASKEUDES -->
        <div class="card" style="background:#fff;border:1.5px solid <?= $analisis['status_proporsi'] === 'MELEBIHI_BATAS' ? '#fde68a' : '#bfdbfe' ?>;border-radius:12px;padding:18px;box-shadow:0 2px 6px rgba(0,0,0,0.04)">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div>
              <span style="font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">ASPEK 3: PROPORSI APBDES</span>
              <h3 style="margin:3px 0 0;font-size:15px;font-weight:800;color:#0f172a">Batas Operasional (Maks 30%)</h3>
            </div>
            <?php if ($analisis['status_proporsi'] === 'MELEBIHI_BATAS'): ?>
              <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:800;font-size:11px">
                > 30% (PERINGATAN)
              </span>
            <?php else: ?>
              <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-weight:800;font-size:11px">
                &le; 30% (WAJAR)
              </span>
            <?php endif; ?>
          </div>

          <div style="background:#f8fafc;border-radius:10px;padding:12px;margin-bottom:14px;border:1px solid #f1f5f9">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:11px;color:#64748b;font-weight:600">Realisasi Belanja Operasional (Bidang 1):</span>
              <strong style="font-size:15px;color:<?= $analisis['status_proporsi'] === 'MELEBIHI_BATAS' ? '#d97706' : '#059669' ?>">
                <?= $analisis['persen_operasional'] ?>%
              </strong>
            </div>
            <div style="height:7px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin:6px 0">
              <div style="width:<?= min(100, $analisis['persen_operasional']) ?>%;height:100%;background:<?= $analisis['status_proporsi'] === 'MELEBIHI_BATAS' ? '#d97706' : '#2563eb' ?>;border-radius:99px"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:10.5px;color:#64748b">
              <span>Realisasi: <?= rupiah($analisis['belanja_operasional']) ?></span>
              <span>Batas Max: 30%</span>
            </div>
          </div>

          <!-- KESIMPULAN SKOR RISIKO KEUANGAN -->
          <div style="border-top:1px solid #f1f5f9;padding-top:10px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
              <span style="font-size:11.5px;font-weight:700;color:#0f172a">Skor Risiko Keuangan:</span>
              <span class="badge" style="background:<?= $analisis['kategori_risiko'] === 'TINGGI' ? '#fee2e2' : ($analisis['kategori_risiko'] === 'SEDANG' ? '#fef3c7' : '#ecfdf5') ?>;color:<?= $analisis['kategori_risiko'] === 'TINGGI' ? '#991b1b' : ($analisis['kategori_risiko'] === 'SEDANG' ? '#92400e' : '#065f46') ?>;font-weight:800;font-size:11px">
                <?= $analisis['skor_risiko'] ?> / 100 &bull; RISIKO <?= $analisis['kategori_risiko'] ?>
              </span>
            </div>
            <?php if (!empty($analisis['faktor_risiko'])): ?>
              <ul style="margin:4px 0 0;padding-left:16px;font-size:11px;color:#64748b">
                <?php foreach ($analisis['faktor_risiko'] as $fr): ?>
                  <li><?= e($fr) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div style="font-size:11px;color:#059669;font-weight:600">Seluruh indikator kinerja keuangan terpantau tertib dan akuntabel.</div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- 3. DETAIL PENGUJIAN: KETEKORAN PAJAK & POSISI KAS -->
      <?php if (!empty($analisis['daftar_pajak_terutang'])): ?>
        <div class="card" style="background:#fff;border:1px solid #fde68a;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.03);margin-bottom:22px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div>
              <h3 style="margin:0;font-size:15px;font-weight:800;color:#92400e;display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-file-invoice-dollar" style="color:#d97706"></i>
                Rincian Bukti Belanja dengan Ketekoran Pajak (Belum Disetor ke Kas Negara)
              </h3>
              <p style="margin:2px 0 0;font-size:12px;color:#78350f">
                Daftar kuitansi pengeluaran yang telah dipotong pajak namun tidak memiliki bukti validasi NTPN bank.
              </p>
            </div>
            <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:800;font-size:11px">
              Total Tunggakan: <?= rupiah($analisis['pajak_belum_setor']) ?>
            </span>
          </div>

          <div class="table-responsive" style="border:1px solid #fde68a;border-radius:8px;overflow:hidden">
            <table class="table" style="width:100%;margin-bottom:0;font-size:12px">
              <thead style="background:#fffbeb;border-bottom:2px solid #fde68a;font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase">
                <tr>
                  <th style="padding:8px 10px;text-align:center;width:40px">No</th>
                  <th style="padding:8px 10px;text-align:left">Uraian Transaksi Belanja</th>
                  <th style="padding:8px 10px;text-align:left">Objek Pemeriksaan</th>
                  <th style="padding:8px 10px;text-align:right">PPN (Rp)</th>
                  <th style="padding:8px 10px;text-align:right">PPh (Rp)</th>
                  <th style="padding:8px 10px;text-align:right">Total Pajak (Rp)</th>
                  <th style="padding:8px 10px;text-align:center;width:110px">Status Setor</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($analisis['daftar_pajak_terutang'] as $pt): ?>
                  <tr style="border-bottom:1px solid #fef3c7">
                    <td style="padding:8px 10px;text-align:center;color:#64748b"><?= $no++ ?></td>
                    <td style="padding:8px 10px;text-align:left;font-weight:600;color:#0f172a"><?= e($pt['uraian']) ?></td>
                    <td style="padding:8px 10px;text-align:left;color:#475569"><?= e($pt['objek_audit']) ?></td>
                    <td style="padding:8px 10px;text-align:right;color:#0f172a"><?= rupiah($pt['nominal_ppn']) ?></td>
                    <td style="padding:8px 10px;text-align:right;color:#0f172a"><?= rupiah($pt['nominal_pph']) ?></td>
                    <td style="padding:8px 10px;text-align:right;font-weight:800;color:#dc2626"><?= rupiah($pt['total_pajak']) ?></td>
                    <td style="padding:8px 10px;text-align:center">
                      <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700">BELUM SETOR</span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot style="background:#fffbeb;font-weight:800;color:#92400e">
                <tr>
                  <td colspan="5" style="padding:8px 10px;text-align:right">TOTAL KETEKORAN PAJAK BELANJA:</td>
                  <td style="padding:8px 10px;text-align:right;color:#dc2626;font-size:13px"><?= rupiah($analisis['pajak_belum_setor']) ?></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      <?php endif; ?>

      <!-- 4. TABEL REKAPITULASI ASPEK KEUANGAN SELURUH KEPENGHULUAN (KABUPATEN ROKAN HILIR) -->
      <div class="card" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f1f5f9">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-list-check" style="color:#2563eb"></i>
              Matriks Pengawasan Kinerja Keuangan Desa se-Kabupaten Rokan Hilir (TA <?= $tahun ?>)
            </h3>
            <p style="margin:3px 0 0;font-size:12px;color:#64748b">
              Peta kompilasi kondisi kas riil, ketekoran kas, kepatuhan pajak, dan skor risiko per desa berdasarkan data audit aktif.
            </p>
          </div>
        </div>

        <div class="table-responsive" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
          <table class="table" style="width:100%;margin-bottom:0;font-size:12.5px;border-collapse:collapse">
            <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:11px;font-weight:700;text-transform:uppercase;color:#475569">
              <tr>
                <th style="padding:10px 12px;text-align:center;width:36px">No</th>
                <th style="padding:10px 12px;text-align:left">Kepenghuluan (Desa)</th>
                <th style="padding:10px 12px;text-align:right">Saldo Kas BKU</th>
                <th style="padding:10px 12px;text-align:right">Kas Riil (Fisik+Bank)</th>
                <th style="padding:10px 12px;text-align:center">Uji Kas Desa</th>
                <th style="padding:10px 12px;text-align:right">Pajak Belum Setor</th>
                <th style="padding:10px 12px;text-align:center">Operasional (30%)</th>
                <th style="padding:10px 12px;text-align:center">Skor Risiko</th>
                <th style="padding:10px 12px;text-align:center;width:90px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $num = 1; foreach ($rekapDesa as $rd): ?>
                <tr style="border-bottom:1px solid #f1f5f9;<?= $rd['desa']['id'] == $desaId ? 'background:#eff6ff' : '' ?>">
                  <td style="padding:10px 12px;text-align:center;color:#64748b;font-weight:600"><?= $num++ ?></td>
                  <td style="padding:10px 12px;text-align:left">
                    <div style="font-weight:700;color:#0f172a;font-size:13px">
                      <a href="<?= url('aspek-keuangan?desa_id=' . $rd['desa']['id'] . '&tahun=' . $tahun) ?>" style="color:inherit;text-decoration:none" class="hover:text-primary">
                        <?= e($rd['desa']['desa_nama']) ?>
                      </a>
                    </div>
                    <div style="font-size:11px;color:#64748b">Kec. <?= e($rd['desa']['kecamatan_nama']) ?></div>
                  </td>
                  <td style="padding:10px 12px;text-align:right;font-weight:600;color:#334155">
                    <?= rupiah($rd['kas_bku']) ?>
                  </td>
                  <td style="padding:10px 12px;text-align:right;font-weight:700;color:#0f172a">
                    <?= rupiah($rd['kas_riil']) ?>
                  </td>
                  <td style="padding:10px 12px;text-align:center">
                    <?php if ($rd['status_kas'] === 'TEKOR_KAS'): ?>
                      <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-weight:700;font-size:10.5px">
                        Tekor: <?= rupiah($rd['tekor_kas_nominal']) ?>
                      </span>
                    <?php elseif ($rd['status_kas'] === 'COCOK'): ?>
                      <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-weight:700;font-size:10.5px">
                        Cocok (Nihil)
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:10.5px">Belum Opname</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:10px 12px;text-align:right;font-weight:700;color:<?= $rd['pajak_belum_setor'] > 0 ? '#d97706' : '#059669' ?>">
                    <?= $rd['pajak_belum_setor'] > 0 ? rupiah($rd['pajak_belum_setor']) : '<span style="color:#059669;font-weight:600">Lunas / Tertib</span>' ?>
                  </td>
                  <td style="padding:10px 12px;text-align:center;font-weight:600">
                    <span style="color:<?= $rd['status_proporsi'] === 'MELEBIHI_BATAS' ? '#dc2626' : '#047857' ?>">
                      <?= $rd['persen_operasional'] ?>%
                    </span>
                  </td>
                  <td style="padding:10px 12px;text-align:center">
                    <span class="badge" style="background:<?= $rd['kategori_risiko'] === 'TINGGI' ? '#fee2e2' : ($rd['kategori_risiko'] === 'SEDANG' ? '#fef3c7' : '#ecfdf5') ?>;color:<?= $rd['kategori_risiko'] === 'TINGGI' ? '#991b1b' : ($rd['kategori_risiko'] === 'SEDANG' ? '#92400e' : '#065f46') ?>;font-weight:800;font-size:10.5px">
                      <?= $rd['skor_risiko'] ?> (<?= $rd['kategori_risiko'] ?>)
                    </span>
                  </td>
                  <td style="padding:10px 12px;text-align:center">
                    <a href="<?= url('aspek-keuangan?desa_id=' . $rd['desa']['id'] . '&tahun=' . $tahun) ?>" class="btn btn-sm btn-ghost" title="Buka Analisis Detail">
                      <i class="fa-solid fa-arrow-up-right-from-square" style="color:#2563eb"></i>
                    </a>
                    <a href="<?= url('print/aspek-keuangan?desa_id=' . $rd['desa']['id'] . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-sm btn-ghost" title="Cetak Lembar Uji">
                      <i class="fa-solid fa-print" style="color:#0f766e"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php endif; ?>
  </div>
</main>

<?php partial('foot'); ?>
