<?php $title = 'Matriks PKA - KKA Digital'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-pka-show">
  <div class="topbar">
    <div class="crumb">
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">☰</button>
      <i class="fa-solid fa-list-check"></i> <b>Matriks Program Kerja Audit (PKA)</b>
    </div>
    <div class="topbar-right">
      <a href="<?= url('penugasan/pka') ?>" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar PKA
      </a>
      <a href="<?= url('print/pka?id=' . $pka['id']) ?>" target="_blank" class="btn btn-outline btn-sm" data-testid="btn-print-pka">
        <i class="fa-solid fa-print"></i> Cetak PKA Resmi
      </a>
    </div>
  </div>

  <div class="content">
    <?php partial('flash'); ?>

    <!-- HEADER PENUGASAN -->
    <div class="card" style="margin-bottom:16px;background:linear-gradient(to right, #f8fafc, #f1f5f9)">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px">
        <div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
            <span class="badge badge-primary"><?= e($pka['no_pka']) ?></span>
            <span style="font-size:13px;color:#64748b">Tanggal: <?= tgl_id($pka['tgl_pka']) ?></span>
          </div>
          <h3 style="margin:0 0 4px;font-size:18px;font-weight:800;color:#0f172a">
            Pemeriksaan Kepenghuluan <?= e($pka['desa_nama']) ?> (Kec. <?= e($pka['kecamatan_nama']) ?>)
          </h3>
          <div style="font-size:12.5px;color:#475569">
            <b>Dasar SPT:</b> <?= e($pka['no_spt']) ?> (<?= tgl_id($pka['tgl_mulai']) ?> s.d <?= tgl_id($pka['tgl_selesai']) ?> &bull; <?= (int)$pka['lama_hari'] ?> Hari Kerja)
          </div>
        </div>

        <div style="display:flex;gap:12px;font-size:12.5px">
          <div style="background:#fff;padding:8px 14px;border-radius:8px;border:1px solid #e2e8f0">
            <div style="color:#64748b;font-size:11px">Pengendali Teknis (Dalnis)</div>
            <div style="font-weight:700;color:#0f172a"><?= e($pka['dalnis_nama']) ?></div>
          </div>
          <div style="background:#fff;padding:8px 14px;border-radius:8px;border:1px solid #e2e8f0">
            <div style="color:#64748b;font-size:11px">Ketua Tim Pemeriksa</div>
            <div style="font-weight:700;color:#0369a1"><?= e($pka['ketua_tim_nama']) ?></div>
          </div>
        </div>
      </div>

      <!-- DAFTAR ANGGOTA TIM -->
      <div style="margin-top:14px;padding-top:10px;border-top:1px dashed #cbd5e1;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <span style="font-size:12px;font-weight:700;color:#334155"><i class="fa-solid fa-users"></i> Anggota Tim Tersedia:</span>
        <?php $idx = 1; foreach ($anggotaList as $ag): ?>
          <span class="badge" style="background:#e0f2fe;color:#0369a1;padding:4px 10px;font-size:11.5px">
            <b>Anggota <?= $idx++ ?>:</b> <?= e($ag['nama']) ?> (<?= e($ag['jabatan']) ?>)
          </span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- KARTU APPROVAL DALNIS (JIKA DALNIS LOGIN & STATUS REVIEW_DALNIS) -->
    <?php if ($isDalnis && $pka['status'] === 'REVIEW_DALNIS'): ?>
      <div class="card" style="border:2px solid #f59e0b;background:#fffdfa;margin-bottom:16px">
        <h3 style="margin:0 0 10px;font-size:15px;font-weight:700;color:#92400e;display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-file-signature" style="color:#f59e0b"></i> Lembar Persetujuan Pengendali Teknis (Dalnis)
        </h3>
        <p style="font-size:12.5px;color:#78350f;margin:0 0 12px">
          Ketua Tim telah menyusun alokasi tugas langkah kerja audit. Silakan reviu dan berikan persetujuan.
        </p>
        <form method="POST" action="<?= url('penugasan/pka/approve') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $pka['id'] ?>">
          <div style="margin-bottom:12px">
            <label class="form-label" style="font-weight:600;font-size:12px">Catatan / Arahan Dalnis:</label>
            <textarea name="catatan_dalnis" class="input" rows="2" style="font-size:12.5px;width:100%">Disetujui. Laksanakan pengujian secara seksama dan laporkan progres secara berkala.</textarea>
          </div>
          <button type="submit" class="btn btn-success">
            <i class="fa-solid fa-circle-check"></i> Setujui Program Kerja Audit (PKA)
          </button>
        </form>
      </div>
    <?php endif; ?>

    <!-- FORM MATRIKS LANGKAH KERJA AUDIT -->
    <form method="POST" action="<?= url('penugasan/pka/update') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $pka['id'] ?>">

      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;flex-wrap:wrap;gap:10px">
          <div>
            <h3 style="margin:0;font-size:15px;font-weight:700;color:#0f172a">
              <i class="fa-solid fa-table-list" style="color:#0284c7"></i> Matriks Alokasi Prosedur Audit ke Anggota Tim
            </h3>
            <p style="margin:2px 0 0;font-size:12px;color:#64748b">
              Ketua Tim menentukan pembagian tugas (Anggota 1, Anggota 2, atau Bersama) pada setiap bidang pengujian.
            </p>
          </div>
          <div style="display:flex;gap:8px">
            <button type="submit" name="action" value="save" class="btn btn-outline" style="font-size:12.5px">
              <i class="fa-solid fa-floppy-disk"></i> Simpan Pembagian Tugas
            </button>
            <?php if ($pka['status'] !== 'DISETUJUI'): ?>
              <button type="submit" name="action" value="ajukan" class="btn btn-primary" style="font-size:12.5px">
                <i class="fa-solid fa-paper-plane"></i> Ajukan ke Dalnis
              </button>
            <?php endif; ?>
          </div>
        </div>

        <div class="table-wrap">
          <table class="table" style="font-size:12.5px;vertical-align:middle">
            <thead>
              <tr style="background:#f8fafc">
                <th style="width:35px;text-align:center">No</th>
                <th style="width:22%">Ruang Lingkup &amp; Bidang Belanja</th>
                <th style="width:28%">Uraian Prosedur / Langkah Pengujian</th>
                <th style="width:20%">Pelaksana Audit (Anggota Tim)</th>
                <th style="width:10%;text-align:center">Waktu (Hari)</th>
                <th style="width:10%">Ref. Indeks KKA</th>
                <th style="width:10%">Status Uji</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($langkah as $l): ?>
                <tr>
                  <td style="text-align:center;font-weight:700"><?= $no++ ?></td>
                  <td>
                    <div style="font-weight:700;color:#0f172a"><?= e($l['bidang_nama']) ?></div>
                    <span class="badge" style="background:#f1f5f9;color:#475569;font-size:10.5px;margin-top:2px"><?= e($l['bidang_kode']) ?></span>
                  </td>
                  <td>
                    <div style="font-size:12px;line-height:1.4"><?= e($l['uraian_prosedur']) ?></div>
                    <?php if (!empty($l['tujuan_pengujian'])): ?>
                      <div style="font-size:11px;color:#0369a1;margin-top:3px"><b>Tujuan:</b> <?= e($l['tujuan_pengujian']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <!-- DROPDOWN PILIH PELAKSANA ANGGOTA 1 / ANGGOTA 2 / BERSAMA -->
                    <select name="langkah[<?= $l['id'] ?>][pelaksana_user_id]" class="input" style="font-size:12px;padding:6px 8px;font-weight:600;width:100%">
                      <option value="">-- Pilih Pelaksana --</option>
                      <?php $agNo = 1; foreach ($anggotaList as $ag): 
                        $selected = ((int)$l['pelaksana_user_id'] === (int)$ag['id']) ? 'selected' : '';
                      ?>
                        <option value="<?= $ag['id'] ?>" <?= $selected ?>>
                          Anggota <?= $agNo++ ?>: <?= e($ag['nama']) ?>
                        </option>
                      <?php endforeach; ?>
                      <option value="0" <?= ($l['pelaksana_user_id'] === null || $l['pelaksana_user_id'] == 0) ? 'selected' : '' ?>>
                        Ketua Tim &amp; Anggota (Bersama)
                      </option>
                    </select>
                  </td>
                  <td style="text-align:center">
                    <div style="display:flex;align-items:center;justify-content:center;gap:4px">
                      <input type="number" name="langkah[<?= $l['id'] ?>][waktu_rencana_hari]" class="input" value="<?= (int)($l['waktu_rencana_hari'] ?: 1) ?>" min="1" max="30" style="width:50px;font-size:12px;padding:6px 4px;text-align:center">
                      <span style="font-size:11px;color:#64748b">hari</span>
                    </div>
                  </td>
                  <td>
                    <input type="text" name="langkah[<?= $l['id'] ?>][ref_kka_nomor]" class="input" value="<?= e($l['ref_kka_nomor'] ?: '') ?>" placeholder="KKA-B.X.X" style="font-size:12px;padding:6px 8px;width:100%">
                  </td>
                  <td>
                    <select name="langkah[<?= $l['id'] ?>][status_pelaksanaan]" class="input" style="font-size:11.5px;padding:5px 6px;width:100%">
                      <option value="BELUM" <?= $l['status_pelaksanaan']==='BELUM'?'selected':'' ?>>Belum Selesai</option>
                      <option value="SEDANG" <?= $l['status_pelaksanaan']==='SEDANG'?'selected':'' ?>>Sedang Berjalan</option>
                      <option value="SELESAI" <?= $l['status_pelaksanaan']==='SELESAI'?'selected':'' ?>>Selesai Teruji</option>
                    </select>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <?php 
              $totRencanaHari = 0;
              foreach ($langkah as $l) {
                $totRencanaHari += (int)($l['waktu_rencana_hari'] ?: 1);
              }
            ?>
            <tfoot>
              <tr style="background:#f8fafc;font-weight:700;border-top:2px solid #cbd5e1">
                <td colspan="4" style="text-align:right;padding:10px 12px;color:#1e293b;font-size:13px">
                  TOTAL ALOKASI WAKTU PENUGASAN :
                </td>
                <td style="text-align:center;color:#0284c7;font-size:13.5px;font-weight:800" id="totalWaktuHari">
                  <?= $totRencanaHari ?> hari
                </td>
                <td colspan="2" style="font-size:11.5px;color:#64748b;vertical-align:middle">
                  Target SPT: <b><?= (int)$pka['lama_hari'] ?> hari kerja</b>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;border-top:1px solid #e2e8f0;padding-top:12px">
          <button type="submit" name="action" value="save" class="btn btn-outline">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Pembagian Tugas
          </button>
          <?php if ($pka['status'] !== 'DISETUJUI'): ?>
            <button type="submit" name="action" value="ajukan" class="btn btn-primary">
              <i class="fa-solid fa-paper-plane"></i> Ajukan ke Dalnis untuk Disetujui
            </button>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</main>

<script>
function hitungTotalWaktu() {
  let total = 0;
  document.querySelectorAll('input[name*="[waktu_rencana_hari]"]').forEach(inp => {
    let val = parseInt(inp.value, 10);
    if (!isNaN(val) && val > 0) total += val;
  });
  const el = document.getElementById('totalWaktuHari');
  if (el) el.textContent = total + ' hari';
}
document.querySelectorAll('input[name*="[waktu_rencana_hari]"]').forEach(inp => {
  inp.addEventListener('input', hitungTotalWaktu);
});
</script>

<?php partial('foot'); ?>
