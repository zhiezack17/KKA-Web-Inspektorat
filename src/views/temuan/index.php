<?php
partial('head', ['title' => 'Konsep Temuan Pemeriksaan (KTP 5 Unsur)']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar', ['title' => 'Konsep Temuan Pemeriksaan (KTP 5 Unsur)', 'icon' => 'fa-solid fa-file-circle-exclamation']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <?php if (!empty($pendingNhp)): ?>
      <!-- Antrean Telaah NHP Pra-Ekspose Khusus Inspektur / Admin -->
      <div class="card" style="padding:14px 18px;margin-bottom:18px;background:#f0f9ff;border:1px solid #bae6fd;border-left:4px solid #0284c7">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
          <div>
            <h4 style="margin:0;font-size:14px;color:#0369a1;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-file-shield" style="font-size:16px"></i>
              Antrean Telaah NHP Pra-Ekspose (<?= count($pendingNhp) ?> Usulan Menunggu Disetujui)
            </h4>
            <p style="margin:4px 0 0;font-size:12px;color:#075985">
              Tim Pemeriksa telah merampungkan pengujian dan mengajukan konsep temuan (NHP) untuk persetujuan ekspose ke pihak auditi.
            </p>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:10px;margin-top:12px">
          <?php foreach ($pendingNhp as $p): ?>
            <div style="background:#fff;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;display:flex;justify-content:space-between;align-items:center">
              <div>
                <strong style="color:#0f172a;font-size:13px"><?= e($p['desa_nama']) ?></strong>
                <div style="font-size:11.5px;color:#64748b">Kec. <?= e($p['kecamatan_nama']) ?> &bull; TA <?= $p['tahun_anggaran'] ?></div>
                <div style="font-size:11px;color:#0284c7;font-weight:600;margin-top:2px"><?= (int)$p['jml_temuan'] ?> Temuan &bull; <?= rupiah($p['total_nominal']) ?></div>
              </div>
              <a href="<?= url('temuan?desa_id=' . $p['desa_id'] . '&tahun=' . $p['tahun_anggaran']) ?>" class="btn btn-sm" style="background:#0284c7;color:#fff;font-weight:700">
                Telaah &raquo;
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-file-circle-exclamation" style="color:#d97706"></i>
          Konsep Temuan Pemeriksaan (KTP 5 Unsur)
        </h2>
        <p>Matriks Daftar Temuan Berdasarkan Standar SPKN BPK-RI &amp; Format P2HP Inspektorat Rokan Hilir (Kondisi, Kriteria, Sebab, Akibat, Rekomendasi).</p>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <?php if ($desaId > 0): ?>
          <a href="<?= url('print/p2hp?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-outline" style="border-color:#0284c7;color:#0284c7;background:#f0f9ff;font-weight:700">
            <i class="fa-solid fa-file-contract"></i> Cetak P2HP
          </a>
          <a href="<?= url('print/ba-kesepakatan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-outline" style="border-color:#059669;color:#059669;background:#ecfdf5;font-weight:700">
            <i class="fa-solid fa-handshake"></i> Cetak BA Kesepakatan
          </a>
          <a href="<?= url('print/matriks-temuan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-outline" style="border-color:#d97706;color:#d97706">
            <i class="fa-solid fa-print"></i> Matriks KTP
          </a>
        <?php endif; ?>
        <a href="<?= url('temuan/create' . ($desaId > 0 ? '?desa_id=' . $desaId . '&tahun=' . $tahun : '')) ?>" class="btn btn-primary" style="background:#d97706;border-color:#d97706">
          <i class="fa-solid fa-plus"></i> Buat Temuan Baru
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding:14px 18px;margin-bottom:18px">
      <form method="get" action="<?= url('temuan') ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
        <div class="field" style="margin:0;min-width:260px">
          <label style="font-size:12px;font-weight:600">Desa / Kepenghuluan</label>
          <select name="desa_id" class="input">
            <option value="0">-- Semua Desa / Kepenghuluan --</option>
            <?php foreach ($daftarDesa as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $desaId == $d['id'] ? 'selected' : '' ?>>
                <?= e($d['nama']) ?> (Kec. <?= e($d['kecamatan']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field" style="margin:0;width:120px">
          <label style="font-size:12px;font-weight:600">Tahun</label>
          <input type="number" name="tahun" class="input" value="<?= $tahun ?>">
        </div>

        <div class="field" style="margin:0;min-width:160px">
          <label style="font-size:12px;font-weight:600">Status Pembahasan</label>
          <select name="status" class="input">
            <option value="">Semua Status</option>
            <option value="DRAFT" <?= $status === 'DRAFT' ? 'selected' : '' ?>>Draft</option>
            <option value="DIBAHAS" <?= $status === 'DIBAHAS' ? 'selected' : '' ?>>Dibahas dgn Auditi</option>
            <option value="FINAL_LHP" <?= $status === 'FINAL_LHP' ? 'selected' : '' ?>>Masuk Final LHP</option>
          </select>
        </div>

        <button type="submit" class="btn btn-outline" style="height:38px"><i class="fa-solid fa-filter"></i> Filter</button>
        <?php if ($desaId > 0 || $status !== ''): ?>
          <a href="<?= url('temuan') ?>" class="btn btn-ghost" style="height:38px">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <?php if ($desaId > 0 && $spt): ?>
      <?php 
        $stNhp = $spt['status_nhp'] ?? 'DRAFT';
      ?>
      <!-- Banner Alur P2HP & BA Kesepakatan Pra-Ekspose -->
      <div class="card" style="padding:16px 20px;margin-bottom:18px;border-radius:10px;<?= $stNhp === 'DISETUJUI_EKSPOSE' ? 'background:#f0fdf4;border:1px solid #bbf7d0;border-left:5px solid #16a34a' : ($stNhp === 'DIAJUKAN_INSPEKTUR' ? 'background:#eff6ff;border:1px solid #bfdbfe;border-left:5px solid #2563eb' : ($stNhp === 'PERBAIKAN' ? 'background:#fffbeb;border:1px solid #fde68a;border-left:5px solid #d97706' : 'background:#f8fafc;border:1px solid #e2e8f0;border-left:5px solid #64748b')) ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
          <div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
              <span style="font-size:13px;font-weight:800;text-transform:uppercase;color:var(--slate-800)">
                STATUS ALUR P2HP &amp; BERITA ACARA KESEPAKATAN (PRA-EKSPOSE)
              </span>
              <?php if ($stNhp === 'DISETUJUI_EKSPOSE'): ?>
                <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:800;font-size:11px"><i class="fa-solid fa-circle-check"></i> DISAHKAN UNTUK EKSPOSE (TTE SAH)</span>
              <?php elseif ($stNhp === 'DIAJUKAN_INSPEKTUR'): ?>
                <span class="badge" style="background:#dbeafe;color:#1e40af;font-weight:800;font-size:11px"><i class="fa-solid fa-hourglass-half"></i> MENUNGGU TELAAH INSPEKTUR</span>
              <?php elseif ($stNhp === 'PERBAIKAN'): ?>
                <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:800;font-size:11px"><i class="fa-solid fa-triangle-exclamation"></i> PERLU PERBAIKAN DARI INSPEKTUR</span>
              <?php else: ?>
                <span class="badge" style="background:#f1f5f9;color:#475569;font-weight:800;font-size:11px"><i class="fa-solid fa-file-pen"></i> DRAFT (BELUM DIAJUKAN)</span>
              <?php endif; ?>
            </div>
            <p style="margin:6px 0 0;font-size:12.5px;color:var(--slate-600)">
              Pemeriksaan Kepenghuluan <b><?= e($spt['desa_nama']) ?></b> TA <b><?= e($spt['tahun_anggaran']) ?></b> &bull; SPT No: <b><?= e($spt['no_spt'] ?: 'DRAFT') ?></b>
            </p>
          </div>

          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <?php if ($stNhp === 'DISETUJUI_EKSPOSE'): ?>
              <a href="<?= url('print/p2hp?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-sm" style="background:#16a34a;color:#fff;font-weight:700">
                <i class="fa-solid fa-print"></i> Cetak P2HP Sah (TTE)
              </a>
              <a href="<?= url('print/ba-kesepakatan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-sm" style="background:#059669;color:#fff;font-weight:700">
                <i class="fa-solid fa-handshake"></i> Cetak BA Kesepakatan
              </a>
            <?php else: ?>
              <a href="<?= url('print/p2hp?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-color:#0284c7;color:#0284c7">
                <i class="fa-solid fa-eye"></i> Pratinjau P2HP
              </a>
              <a href="<?= url('print/ba-kesepakatan?desa_id=' . $desaId . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-color:#059669;color:#059669">
                <i class="fa-solid fa-handshake"></i> Pratinjau BA
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($stNhp === 'DISETUJUI_EKSPOSE'): ?>
          <div style="margin-top:12px;padding:10px 14px;background:#ffffff;border:1px solid #bbf7d0;border-radius:6px;font-size:12px;color:#166534">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
              <div><b>Disetujui Oleh:</b> <?= e($spt['disetujui_oleh_nhp'] ?? 'Inspektur Daerah') ?> (<?= !empty($spt['tgl_disetujui_nhp']) ? tgl_id($spt['tgl_disetujui_nhp']) : '-' ?>)</div>
              <div><b>Kode Barcode TTE:</b> <code style="font-family:monospace;font-weight:700"><?= e($spt['tte_barcode_nhp'] ?? '-') ?></code></div>
            </div>
            <?php if (!empty($spt['catatan_inspektur_nhp'])): ?>
              <div style="margin-top:6px;font-style:italic"><b>Catatan/Arahan Inspektur:</b> "<?= e($spt['catatan_inspektur_nhp']) ?>"</div>
            <?php endif; ?>
          </div>
        <?php elseif ($stNhp === 'PERBAIKAN'): ?>
          <div style="margin-top:12px;padding:10px 14px;background:#ffffff;border:1px solid #fde68a;border-radius:6px;font-size:12px;color:#92400e">
            <div style="font-weight:700;margin-bottom:4px"><i class="fa-solid fa-circle-exclamation"></i> Catatan / Koreksi dari Inspektur:</div>
            <div>"<?= e($spt['catatan_inspektur_nhp'] ?: 'Mohon perbaiki rumusan temuan sebelum ekspose.') ?>"</div>
          </div>
          <form method="post" action="<?= url('temuan/ajukan-nhp') ?>" style="margin-top:10px">
            <?= csrf_field() ?>
            <input type="hidden" name="spt_id" value="<?= $spt['id'] ?>">
            <button type="submit" class="btn btn-sm" style="background:#d97706;color:#fff;font-weight:700">
              <i class="fa-solid fa-paper-plane"></i> Ajukan Ulang ke Inspektur Setelah Revisi
            </button>
          </form>
        <?php elseif ($stNhp === 'DIAJUKAN_INSPEKTUR'): ?>
          <div style="margin-top:12px;padding:10px 14px;background:#ffffff;border:1px solid #bfdbfe;border-radius:6px;font-size:12px;color:#1e40af">
            <div><b>Diajukan Oleh:</b> <?= e($spt['diajukan_oleh_nhp'] ?? 'Ketua Tim') ?> pada tanggal <?= !empty($spt['tgl_pengajuan_nhp']) ? tgl_id($spt['tgl_pengajuan_nhp']) : '-' ?>.</div>
          </div>

          <?php if ($this->auth->isInspektur() || $this->auth->isAdmin()): ?>
            <!-- FORM TELAAH & PENGESAHAN KHUSUS INSPEKTUR -->
            <form method="post" action="<?= url('temuan/approve-nhp') ?>" style="margin-top:12px;background:#ffffff;border:1px solid #cbd5e1;padding:12px 14px;border-radius:8px">
              <?= csrf_field() ?>
              <input type="hidden" name="spt_id" value="<?= $spt['id'] ?>">
              <label style="font-size:12px;font-weight:700;color:#0f172a;display:block;margin-bottom:4px">
                <i class="fa-solid fa-pen-fancy"></i> Lembar Telaah &amp; Catatan Inspektur:
              </label>
              <textarea name="catatan_inspektur" class="input" rows="2" style="font-size:12px" placeholder="Tuliskan arahan, pengurangan temuan, atau koreksi sebelum ekspose (opsional jika langsung disetujui)..."></textarea>
              <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap">
                <button type="submit" name="action" value="setujui" class="btn" style="background:#16a34a;color:#fff;font-weight:700">
                  <i class="fa-solid fa-stamp"></i> Setujui &amp; Sahkan P2HP &amp; BA (TTE Barcode Otomatis)
                </button>
                <button type="submit" name="action" value="kembalikan" class="btn" style="background:#d97706;color:#fff;font-weight:700" onclick="return confirm('Kembalikan naskah P2HP & BA Kesepakatan ini ke Tim Pemeriksa untuk perbaikan?')">
                  <i class="fa-solid fa-rotate-left"></i> Kembalikan untuk Perbaikan
                </button>
              </div>
            </form>
          <?php else: ?>
            <div style="margin-top:8px;font-size:12px;color:#475569;font-style:italic">
              <i class="fa-solid fa-hourglass-start"></i> Dokumen P2HP &amp; BA Kesepakatan sedang dalam proses telaah pimpinan. Harap tunggu persetujuan Inspektur sebelum menggelar Forum Ekspose dengan Pj. Penghulu/Desa.
            </div>
          <?php endif; ?>
        <?php else: ?>
          <!-- DRAFT BELUM DIAJUKAN -->
          <div style="margin-top:10px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div style="font-size:12px;color:#475569">
              Kertas kerja, daftar temuan, dan naskah P2HP di bawah siap diajukan kepada Inspektur Daerah untuk mendapatkan telaah dan persetujuan pra-ekspose.
            </div>
            <form method="post" action="<?= url('temuan/ajukan-nhp') ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="spt_id" value="<?= $spt['id'] ?>">
              <button type="submit" class="btn btn-sm" style="background:#0284c7;color:#fff;font-weight:700">
                <i class="fa-solid fa-paper-plane"></i> Ajukan P2HP &amp; BA ke Inspektur untuk Telaah Pra-Ekspose
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Summary Box -->
    <?php
      $countDraft = 0; $countDibahas = 0; $countFinal = 0;
      foreach ($daftarTemuan as $itemT) {
        if ($itemT['status'] === 'FINAL_LHP') $countFinal++;
        elseif ($itemT['status'] === 'DIBAHAS') $countDibahas++;
        else $countDraft++;
      }
    ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;margin-bottom:18px">
      <div class="card" style="padding:14px 18px;border-left:4px solid #d97706">
        <div style="font-size:12px;color:var(--slate-500);font-weight:700;text-transform:uppercase">TOTAL TEMUAN TERDATA</div>
        <div style="font-size:22px;font-weight:800;color:var(--slate-800);margin-top:4px"><?= count($daftarTemuan) ?> Butir</div>
        <div style="font-size:11px;color:var(--slate-500);margin-top:4px">Standar SPKN &bull; 5 Unsur Pemeriksaan</div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid #ef4444">
        <div style="font-size:12px;color:var(--slate-500);font-weight:700;text-transform:uppercase">TOTAL NILAI KERUGIAN / SELISIH</div>
        <div style="font-size:20px;font-weight:800;color:#dc2626;margin-top:4px"><?= rupiah($totalNominal) ?></div>
        <div style="font-size:11px;color:#dc2626;margin-top:4px;font-weight:600">Potensi Pemulihan Kas Desa</div>
      </div>
      <div class="card" style="padding:14px 18px;border-left:4px solid #059669">
        <div style="font-size:12px;color:var(--slate-500);font-weight:700;text-transform:uppercase">STATUS PEMBAHASAN AUDITI</div>
        <div style="display:flex;gap:6px;align-items:center;margin-top:8px;flex-wrap:wrap">
          <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700"><?= $countFinal ?> Final LHP</span>
          <span class="badge" style="background:#e0e7ff;color:#4338ca;font-weight:700"><?= $countDibahas ?> Dibahas</span>
          <span class="badge" style="background:#f1f5f9;color:#475569;font-weight:700"><?= $countDraft ?> Draft</span>
        </div>
      </div>
    </div>

    <!-- Tabel Daftar Temuan -->
    <div class="card" style="padding:0">
      <div class="table-wrap" style="border:0">
        <table class="table">
          <thead>
            <tr>
              <th style="width:36px">No</th>
              <th style="width:90px">Kode KTP</th>
              <th>Kepenghuluan &amp; Bidang</th>
              <th>Pokok Temuan / Judul</th>
              <th class="num">Nilai Temuan</th>
              <th>Status</th>
              <th style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarTemuan)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--slate-500)">
                  <i class="fa-solid fa-clipboard-check" style="font-size:36px;color:#cbd5e1;margin-bottom:10px;display:block"></i>
                  Belum ada temuan pemeriksaan untuk filter ini.<br>
                  <span style="font-size:12px">Anda dapat menambahkan temuan baru melalui tombol di atas atau langsung dari baris rincian belanja pada Kertas Kerja Audit (KKA).</span>
                </td>
              </tr>
            <?php else: $no=1; foreach ($daftarTemuan as $t): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><span class="badge" style="background:#fef3c7;color:#b45309;font-weight:700"><?= e($t['nomor_temuan']) ?></span></td>
                <td>
                  <strong><?= e($t['desa_nama']) ?></strong>
                  <div style="font-size:11.5px;color:var(--slate-500)">Kec. <?= e($t['kecamatan_nama']) ?> &bull; TA <?= $t['tahun_anggaran'] ?></div>
                  <?php if ($t['bidang_nama']): ?>
                    <div style="font-size:11px;color:var(--emerald-700);font-weight:600"><i class="fa-solid fa-tag"></i> <?= e($t['bidang_nama']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="color:var(--slate-800)"><?= e($t['judul']) ?></strong>
                  <div style="font-size:12px;color:var(--slate-600);margin-top:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                    <b>Kondisi:</b> <?= e($t['kondisi']) ?>
                  </div>
                </td>
                <td class="num" style="color:#dc2626;font-weight:700">
                  <?= (float)$t['nominal'] > 0 ? rupiah($t['nominal']) : '<span style="color:#64748b;font-weight:400">-</span>' ?>
                </td>
                <td>
                  <?php if ($t['status'] === 'FINAL_LHP'): ?>
                    <span class="badge" style="background:#dcfce7;color:#15803d;font-weight:700"><i class="fa-solid fa-check"></i> Final LHP</span>
                  <?php elseif ($t['status'] === 'DIBAHAS'): ?>
                    <span class="badge" style="background:#e0e7ff;color:#4338ca;font-weight:600">Dibahas Auditi</span>
                  <?php else: ?>
                    <span class="badge" style="background:#f1f5f9;color:#475569">Draft KTP</span>
                  <?php endif; ?>
                </td>
                <td style="white-space:nowrap">
                  <a href="<?= url('temuan/edit?id=' . $t['id']) ?>" class="btn btn-ghost btn-sm" style="color:var(--emerald-700);padding:5px 8px" title="Edit 5 Unsur"><i class="fa-solid fa-pen-to-square"></i></a>
                  <form method="post" action="<?= url('temuan/delete') ?>" onsubmit="return confirm('Hapus temuan <?= e($t['nomor_temuan']) ?> ini?')" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button class="btn btn-ghost btn-sm" style="color:#dc2626;padding:5px 8px" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<?php partial('foot'); ?>
