<?php $title = 'Dashboard Eksekutif - KKA Digital Inspektorat'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-dashboard">
  <?php partial('topbar', ['title' => 'Dashboard Eksekutif Pengawasan', 'icon' => 'fa-solid fa-gauge-high']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <!-- Welcome & Page Head -->
    <div class="page-head" style="margin-bottom:18px">
      <div>
        <h2 style="font-size:22px;font-weight:800;letter-spacing:-0.3px;color:#0f172a">
          Selamat Datang, <?= e(sapaan_nama($auth->user()['nama'], $auth->user()['role'])) ?> 👋
        </h2>
        <p style="color:#64748b;font-size:13.5px;margin-top:2px">
          Sistem Pengawasan Terpadu Kertas Kerja Audit (KKA) Digital &mdash; Inspektorat Kabupaten Rokan Hilir.
        </p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <?php if ($auth->isInspektur()): ?>
          <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-primary" style="background:#b45309;border:none">
            <i class="fa-solid fa-pen-nib"></i> Lembar Disposisi Inspektur
          </a>
          <a href="<?= url('lhp') ?>" class="btn btn-outline" style="border-color:#0f766e;color:#0f766e">
            <i class="fa-solid fa-file-shield"></i> Laporan Hasil Audit (LHP)
          </a>
        <?php elseif ($auth->isOperatorSpt()): ?>
          <a href="<?= url('penugasan/spt/create') ?>" class="btn btn-primary" style="background:#059669;border:none">
            <i class="fa-solid fa-file-signature"></i> Terbitkan SPT Baru
          </a>
        <?php else: ?>
          <a href="<?= url('penugasan/nota-dinas/create') ?>" class="btn btn-outline" style="border-color:#2563eb;color:#2563eb">
            <i class="fa-solid fa-envelope-open-text"></i> Usulkan Tim (ND)
          </a>
          <a href="<?= url('sesi') ?>" class="btn btn-primary">
            <i class="fa-solid fa-clipboard-list"></i> Kertas Kerja (KKA)
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Alert Khusus Pimpinan (Inspektur & Operator) -->
    <?php if ($auth->isInspektur()): ?>
      <?php 
        $pendingNd = (int) DB::val("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DIAJUKAN_INSPEKTUR'");
        $pendingSpt = (int) DB::val("SELECT COUNT(*) FROM kka_spt WHERE status = 'DRAFT'");
      ?>
      <?php if ($pendingNd > 0 || $pendingSpt > 0): ?>
        <div style="background:linear-gradient(135deg, #fffbeb, #fef3c7); border:1px solid #fde68a; border-left:5px solid #d97706; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:16px; box-shadow:0 2px 6px rgba(217,119,6,0.08);">
          <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:42px; height:42px; border-radius:50%; background:#fde68a; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fa-solid fa-bell" style="font-size:18px; color:#b45309;"></i>
            </div>
            <div>
              <div style="font-weight:700; color:#92400e; font-size:14px;">Agenda Tindakan Pimpinan:</div>
              <div style="font-size:13px; color:#78350f; margin-top:2px;">
                <?php if ($pendingNd > 0): ?>Ada <strong style="color:#b45309"><?= $pendingNd ?></strong> Nota Dinas menunggu persetujuan / disposisi Anda. <?php endif; ?>
                <?php if ($pendingSpt > 0): ?>Ada <strong style="color:#0f766e"><?= $pendingSpt ?></strong> Surat Tugas (SPT) menunggu pengesahan digital TTE. <?php endif; ?>
              </div>
            </div>
          </div>
          <div style="display:flex; gap:8px; flex-shrink:0;">
            <?php if ($pendingNd > 0): ?>
              <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-sm" style="background:#b45309; color:#fff; border:none; font-weight:700; box-shadow:0 2px 4px rgba(180,83,9,0.25);"><i class="fa-solid fa-pen-nib"></i> Buka Disposisi ND</a>
            <?php endif; ?>
            <?php if ($pendingSpt > 0): ?>
              <a href="<?= url('penugasan/spt') ?>" class="btn btn-sm" style="background:#0f766e; color:#fff; border:none; font-weight:700; box-shadow:0 2px 4px rgba(15,118,110,0.25);"><i class="fa-solid fa-signature"></i> Sahkan SPT</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php elseif ($auth->isOperatorSpt()): ?>
      <?php 
        $readyNd = (int) DB::val("SELECT COUNT(*) FROM kka_nota_dinas WHERE status = 'DISETUJUI' AND id NOT IN (SELECT COALESCE(nota_dinas_id,0) FROM kka_spt)");
      ?>
      <?php if ($readyNd > 0): ?>
        <div style="background:linear-gradient(135deg, #ecfdf5, #d1fae5); border:1px solid #a7f3d0; border-left:5px solid #059669; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:16px; box-shadow:0 2px 6px rgba(5,150,105,0.08);">
          <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:42px; height:42px; border-radius:50%; background:#a7f3d0; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fa-solid fa-clipboard-check" style="font-size:18px; color:#047857;"></i>
            </div>
            <div>
              <div style="font-weight:700; color:#065f46; font-size:14px;">Agenda Penerbitan SPT:</div>
              <div style="font-size:13px; color:#047857; margin-top:2px;">Ada <strong><?= $readyNd ?></strong> Nota Dinas telah disetujui Inspektur dan siap diterbitkan Surat Tugas (SPT).</div>
            </div>
          </div>
          <a href="<?= url('penugasan/spt/create') ?>" class="btn btn-sm" style="background:#059669; color:#fff; border:none; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(5,150,105,0.25);"><i class="fa-solid fa-plus"></i> Terbitkan SPT Sekarang</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- VISUAL PIPELINE ALUR PENGAWASAN HILIR KE HULU -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;margin-bottom:22px;box-shadow:0 1px 3px rgba(0,0,0,0.04)">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
        <span style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.5px;display:flex;align-items:center;gap:6px">
          <i class="fa-solid fa-diagram-project" style="color:#2563eb"></i> Pipeline Siklus Audit Digital Terpadu
        </span>
        <span style="font-size:11px;color:#64748b">Standar SPKN BPK-RI &amp; Pedoman Kendali Mutu BPKP</span>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:12px">
        <!-- Tahap 1 -->
        <a href="<?= url('penugasan/nota-dinas') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;border-top:3px solid #6366f1;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:10.5px;font-weight:700;color:#6366f1;text-transform:uppercase">1. Pra-Audit (ND)</span>
            <i class="fa-solid fa-envelope-open-text" style="color:#6366f1;font-size:12px"></i>
          </div>
          <div style="font-size:17px;font-weight:800;color:#0f172a"><?= $stats['nd_total'] ?> Dokumen</div>
          <div style="font-size:11px;color:#64748b;margin-top:2px"><?= $pipeline['nd_diajukan'] ?> Menunggu &bull; <?= $pipeline['nd_disetujui'] ?> Disetujui</div>
        </a>

        <!-- Tahap 2 -->
        <a href="<?= url('penugasan/spt') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;border-top:3px solid #0f766e;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:10.5px;font-weight:700;color:#0f766e;text-transform:uppercase">2. Surat Tugas (SPT)</span>
            <i class="fa-solid fa-file-signature" style="color:#0f766e;font-size:12px"></i>
          </div>
          <div style="font-size:17px;font-weight:800;color:#0f172a"><?= $stats['spt_total'] ?> SPT Sah</div>
          <div style="font-size:11px;color:#64748b;margin-top:2px">Matriks PKA &amp; TTE Digital</div>
        </a>

        <!-- Tahap 3 -->
        <a href="<?= url('sesi') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;border-top:3px solid #0284c7;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:10.5px;font-weight:700;color:#0284c7;text-transform:uppercase">3. Uji Lapangan KKA</span>
            <i class="fa-solid fa-clipboard-check" style="color:#0284c7;font-size:12px"></i>
          </div>
          <div style="font-size:17px;font-weight:800;color:#0f172a"><?= $stats['sesi'] ?> Sesi Audit</div>
          <div style="font-size:11px;color:#64748b;margin-top:2px">Uji Fisik, SPJ &amp; Pajak</div>
        </a>

        <!-- Tahap 4 -->
        <a href="<?= url('temuan') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;border-top:3px solid #d97706;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:10.5px;font-weight:700;color:#d97706;text-transform:uppercase">4. Konsep Temuan (KTP)</span>
            <i class="fa-solid fa-file-circle-exclamation" style="color:#d97706;font-size:12px"></i>
          </div>
          <div style="font-size:17px;font-weight:800;color:#0f172a"><?= $stats['temuan_total'] ?> Temuan</div>
          <div style="font-size:11px;color:#64748b;margin-top:2px">5 Unsur &amp; Rekomendasi</div>
        </a>

        <!-- Tahap 5 -->
        <a href="<?= url('lhp') ?>" style="display:block;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;border-top:3px solid #16a34a;transition:all 0.2s ease" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:10.5px;font-weight:700;color:#16a34a;text-transform:uppercase">5. LHP Otomatis</span>
            <i class="fa-solid fa-file-shield" style="color:#16a34a;font-size:12px"></i>
          </div>
          <div style="font-size:17px;font-weight:800;color:#0f172a"><?= max(1, $pipeline['lhp_desa']) ?> Desa Siap</div>
          <div style="font-size:11px;color:#64748b;margin-top:2px">Bab I s.d IV Format Rohil</div>
        </a>
      </div>
    </div>

    <!-- 4 KARTU INDIKATOR EKSEKUTIF UTAMA -->
    <div class="stats-grid" style="margin-bottom:24px">
      <!-- Card 1: Total Belanja Diaudit -->
      <div class="stat blue" data-testid="stat-anggaran">
        <div>
          <div class="label">Total Belanja Diaudit</div>
          <div class="value money"><?= rupiah($stats['dikwitansi']) ?></div>
          <div class="sub">Pagu: <?= rupiah($stats['anggaran']) ?> &bull; Realisasi: <?= rupiah($stats['realisasi']) ?></div>
        </div>
        <div class="ico"><i class="fa-solid fa-money-bill-wave"></i></div>
      </div>

      <!-- Card 2: Potensi Pemulihan Kas Desa -->
      <div class="stat rose" data-testid="stat-pemulihan">
        <div>
          <div class="label">Potensi Pemulihan Kas Desa</div>
          <div class="value money" style="color:#e11d48">
            <?= rupiah(max($stats['selisih'], $stats['temuan_nominal'])) ?>
          </div>
          <div class="sub">
            <?= $stats['temuan_total'] ?> Temuan SPKN &bull; Selisih Fisik Kwitansi
          </div>
        </div>
        <div class="ico" style="background:#ffe4e6;color:#e11d48"><i class="fa-solid fa-hand-holding-dollar"></i></div>
      </div>

      <!-- Card 3: Uji Kepatuhan Pajak Belanja -->
      <div class="stat amber" data-testid="stat-pajak">
        <div>
          <div class="label">Kepatuhan Pajak Belanja Desa</div>
          <div class="value money" style="color:#d97706"><?= rupiah($stats['pajak_sudah_setor'] + $stats['pajak_belum_setor']) ?></div>
          <div class="sub">
            <span style="color:#059669;font-weight:700">Setor: <?= rupiah($stats['pajak_sudah_setor']) ?></span> &bull; 
            <span style="color:#dc2626;font-weight:700">Belum: <?= rupiah($stats['pajak_belum_setor']) ?></span>
          </div>
        </div>
        <div class="ico"><i class="fa-solid fa-receipt"></i></div>
      </div>

      <!-- Card 4: Dokumen Pengawasan Sah -->
      <div class="stat" style="border-left:4px solid #10b981" data-testid="stat-dokumen">
        <div>
          <div class="label">Dokumen Pengawasan Sah</div>
          <div class="value"><?= $stats['spt_total'] ?> SPT &bull; <?= $stats['desa'] ?> Desa</div>
          <div class="sub"><?= $stats['kec'] ?> Kecamatan se-Kabupaten Rokan Hilir</div>
        </div>
        <div class="ico" style="background:#d1fae5;color:#059669"><i class="fa-solid fa-stamp"></i></div>
      </div>
    </div>

    <!-- MAIN CONTENT: 2fr 1fr GRID -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
      <!-- LEFT: DAFTAR DESA & TEMUAN -->
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f1f5f9">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0f172a">
              <i class="fa-solid fa-building-columns" style="color:#2563eb;margin-right:6px"></i>
              Objek Pemeriksaan Kepenghuluan (Desa)
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">Ringkasan hasil audit fisik belanja dan nilai temuan per desa binaan.</p>
          </div>
          <a href="<?= url('sesi') ?>" class="btn btn-ghost btn-sm">Semua Sesi <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <?php if (empty($perDesa)): ?>
          <div class="empty"><i class="fa-regular fa-folder-open"></i><h4>Belum ada sesi audit</h4><p>Mulai buat sesi audit pertama Anda.</p></div>
        <?php else: ?>
          <div class="list-grid">
            <?php foreach (array_slice($perDesa, 0, 7) as $d): ?>
              <div class="list-card" style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px">
                <div style="display:flex;align-items:center;gap:12px">
                  <div class="ico" style="background:#e0f2fe;color:#0284c7"><i class="fa-solid fa-building-columns"></i></div>
                  <div>
                    <div style="font-weight:700;font-size:13.5px;color:#0f172a">
                      <a href="<?= url('sesi?desa=' . $d['id']) ?>" style="color:inherit;text-decoration:none">
                        <?= e($d['desa']) ?>
                      </a>
                      <span class="badge" style="background:#f1f5f9;color:#475569;font-size:10.5px;margin-left:6px">
                        Kec. <?= e($d['kecamatan']) ?>
                      </span>
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px">
                      Pagu: <b><?= rupiah($d['pagu']) ?></b> &bull; <?= (int)$d['jumlah'] ?> Sesi Audit (TA <?= $d['tahun_terakhir'] ?>)
                    </div>
                  </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;text-align:right">
                  <?php if ((float)$d['selisih_fisik'] > 0 || (int)$d['jml_temuan'] > 0): ?>
                    <div>
                      <div style="font-size:11.5px;font-weight:800;color:#e11d48">
                        Selisih: <?= rupiah(max((float)$d['selisih_fisik'], (float)$d['nominal_temuan'])) ?>
                      </div>
                      <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700">
                        <i class="fa-solid fa-triangle-exclamation"></i> Ada Temuan
                      </span>
                    </div>
                  <?php else: ?>
                    <div>
                      <div style="font-size:11.5px;font-weight:700;color:#059669">Nihil Selisih</div>
                      <span class="badge" style="background:#ecfdf5;color:#065f46;font-size:10px;font-weight:700">
                        <i class="fa-solid fa-check"></i> Tertib
                      </span>
                    </div>
                  <?php endif; ?>

                  <a href="<?= url('lhp/show?desa_id=' . $d['id'] . '&tahun=' . $d['tahun_terakhir']) ?>" class="btn btn-ghost btn-sm" title="Buka LHP Desa" style="padding:5px 8px">
                    <i class="fa-solid fa-file-shield" style="color:#0f766e"></i>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: KEPATUHAN PAJAK & SEBARAN BIDANG -->
      <div style="display:flex;flex-direction:column;gap:18px">
        <!-- Card Kepatuhan Pajak -->
        <div class="card" style="background:linear-gradient(to bottom, #ffffff, #f8fafc)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <h3 style="margin:0;font-size:14.5px;font-weight:800;color:#0f172a">
              <i class="fa-solid fa-receipt" style="color:#d97706;margin-right:6px"></i> Kepatuhan Pajak Belanja
            </h3>
            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10.5px">PPN &amp; PPh</span>
          </div>

          <?php 
            $totPajak = $stats['pajak_sudah_setor'] + $stats['pajak_belum_setor'];
            $pctSetor = $totPajak > 0 ? round(($stats['pajak_sudah_setor'] / $totPajak) * 100) : 100;
          ?>
          <div style="margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px">
              <span style="color:#64748b">Persentase Penyetoran Kas Negara:</span>
              <strong style="color:#059669"><?= $pctSetor ?>%</strong>
            </div>
            <div style="height:9px;background:#fee2e2;border-radius:99px;overflow:hidden;display:flex">
              <div style="width:<?= $pctSetor ?>%;background:#059669;height:100%;transition:width 0.3s"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:11.5px">
            <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:8px">
              <div style="color:#065f46;font-size:10.5px;font-weight:700">SUDAH SETOR (NTPN)</div>
              <div style="color:#047857;font-size:13px;font-weight:800;margin-top:2px"><?= rupiah($stats['pajak_sudah_setor']) ?></div>
            </div>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px">
              <div style="color:#991b1b;font-size:10.5px;font-weight:700">BELUM SETOR (TERUTANG)</div>
              <div style="color:#dc2626;font-size:13px;font-weight:800;margin-top:2px"><?= rupiah($stats['pajak_belum_setor']) ?></div>
            </div>
          </div>
        </div>

        <!-- Card Sebaran Bidang APBDesa -->
        <div class="card">
          <h3 style="margin:0 0 12px;font-size:14.5px;font-weight:800;color:#0f172a">
            <i class="fa-solid fa-chart-pie" style="color:#6366f1;margin-right:6px"></i> Sebaran Sesi per Bidang
          </h3>
          <div style="display:flex;flex-direction:column;gap:10px">
            <?php
              $jmlArr = array_column($perBidang, 'jumlah');
              $maxJml = $jmlArr ? max(1, max($jmlArr)) : 1;
              foreach ($perBidang as $b):
                $pct = round(((int)$b['jumlah'] / $maxJml) * 100);
            ?>
              <div>
                <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:4px">
                  <span style="color:var(--slate-600);font-weight:600"><?= e(mb_strimwidth($b['nama'],0,38,'…')) ?></span>
                  <span style="color:#0f172a;font-weight:700"><?= (int)$b['jumlah'] ?> sesi</span>
                </div>
                <div style="height:6px;background:var(--slate-100);border-radius:99px;overflow:hidden">
                  <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,#3b82f6,#10b981);border-radius:99px"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Pintasan Cepat Dokumen Cetak Resmi -->
        <div class="card" style="background:#f8fafc;border:1px dashed #cbd5e1">
          <div style="font-size:12px;font-weight:800;color:#334155;margin-bottom:8px;text-transform:uppercase">
            <i class="fa-solid fa-print" style="color:#475569"></i> Dokumen Resmi Pengawasan
          </div>
          <div style="display:flex;flex-direction:column;gap:6px">
            <a href="<?= url('print/spt?id=2') ?>" target="_blank" class="btn btn-ghost btn-sm" style="justify-content:flex-start;font-size:12px">
              <i class="fa-solid fa-file-signature" style="color:#0f766e"></i> Cetak Surat Tugas (SPT) Rohil
            </a>
            <a href="<?= url('print/pka?id=2') ?>" target="_blank" class="btn btn-ghost btn-sm" style="justify-content:flex-start;font-size:12px">
              <i class="fa-solid fa-list-check" style="color:#2563eb"></i> Cetak Matriks PKA (KM.6 BPKP)
            </a>
            <a href="<?= url('print/matriks-temuan?desa_id=69&tahun=2025') ?>" target="_blank" class="btn btn-ghost btn-sm" style="justify-content:flex-start;font-size:12px">
              <i class="fa-solid fa-file-circle-exclamation" style="color:#d97706"></i> Cetak Matriks Temuan 5 Unsur
            </a>
            <a href="<?= url('print/lhp?desa_id=69&tahun=2025') ?>" target="_blank" class="btn btn-ghost btn-sm" style="justify-content:flex-start;font-size:12px">
              <i class="fa-solid fa-file-shield" style="color:#16a34a"></i> Cetak LHP Standar Bookman Rohil
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
