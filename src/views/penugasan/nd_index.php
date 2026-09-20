<?php $title = 'Nota Dinas Penugasan - KKA Digital'; ?>
<?php partial('head', compact('title')); ?>
<?php partial('sidebar'); ?>

<main class="main" data-testid="page-nota-dinas">
  <?php partial('topbar', ['title' => 'Nota Dinas Pengajuan Audit', 'icon' => 'fa-solid fa-envelope-open-text']); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2>Nota Dinas Pengajuan Audit</h2>
        <p>Permohonan penugasan audit dari Inspektur Pembantu (Irban) kepada Inspektur Daerah.</p>
      </div>
      <?php if ($isIrban || $auth->isAdmin()): ?>
        <a href="<?= url('penugasan/nota-dinas/create') ?>" class="btn btn-primary" data-testid="btn-create-nd-top">
          <i class="fa-solid fa-plus"></i> Buat Nota Dinas
        </a>
      <?php endif; ?>
    </div>

    <!-- BANNER NOTIFIKASI DISPOSISI INSPEKTUR -->
    <?php if ($isInspektur && $countMenungguDisposisi > 0): ?>
      <div style="background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%);color:#fff;border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:14px">
          <div style="width:42px;height:42px;border-radius:10px;background:#ef4444;display:flex;align-items:center;justify-content:center;font-size:18px">
            <i class="fa-solid fa-bell"></i>
          </div>
          <div>
            <div style="font-weight:700;font-size:15px">Perhatian: Ada <?= $countMenungguDisposisi ?> Nota Dinas Menunggu Disposisi Anda</div>
            <div style="font-size:12.5px;color:#94a3b8">Segera periksa susunan tim dan berikan arahan/disposisi agar Bagian SPT dapat memproses Surat Tugas.</div>
          </div>
        </div>
        <a href="<?= url('penugasan/nota-dinas?status=DIAJUKAN_INSPEKTUR') ?>" class="btn" style="background:#ef4444;color:#fff;border:none;padding:8px 16px;font-size:13px;border-radius:8px">
          Lihat Antrean Disposisi
        </a>
      </div>
    <?php endif; ?>

    <!-- FILTER TAHUN & STATUS -->
    <div class="card" style="margin-bottom:16px;padding:14px 20px">
      <form method="GET" action="<?= url('penugasan/nota-dinas') ?>" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div style="min-width:180px">
          <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">STATUS PENGAJUAN</label>
          <select name="status" class="input" onchange="this.form.submit()" style="padding:7px 12px;font-size:13px;width:100%">
            <option value="">Semua Status</option>
            <option value="DRAFT" <?= $status==='DRAFT'?'selected':'' ?>>Draft (Irban)</option>
            <option value="DIAJUKAN_INSPEKTUR" <?= $status==='DIAJUKAN_INSPEKTUR'?'selected':'' ?>>Menunggu Disposisi Inspektur</option>
            <option value="DISETUJUI" <?= $status==='DISETUJUI'?'selected':'' ?>>Disetujui (Siap SPT)</option>
            <option value="DITOLAK" <?= $status==='DITOLAK'?'selected':'' ?>>Ditolak / Dikembalikan</option>
          </select>
        </div>
        <div style="min-width:140px">
          <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">TAHUN ANGGARAN</label>
          <select name="tahun" class="input" onchange="this.form.submit()" style="padding:7px 12px;font-size:13px;width:100%">
            <option value="">Semua Tahun</option>
            <?php for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--): ?>
              <option value="<?= $y ?>" <?= $tahun===$y?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div style="margin-top:18px">
          <a href="<?= url('penugasan/nota-dinas') ?>" class="btn btn-outline" style="padding:7px 14px;font-size:13px">Reset</a>
        </div>
      </form>
    </div>

    <!-- TABEL DAFTAR NOTA DINAS -->
    <div class="card">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:40px">No</th>
              <th>Nomor &amp; Tanggal ND</th>
              <th>Wilayah / Objek Audit</th>
              <th>Susunan Tim Pemeriksa</th>
              <th>Jadwal Penugasan</th>
              <th>Status</th>
              <th style="text-align:right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($list)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8">
                  <i class="fa-solid fa-inbox" style="font-size:32px;display:block;margin-bottom:8px"></i>
                  Belum ada Nota Dinas penugasan.
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1; foreach ($list as $row): 
                $anggotaList = json_decode($row['anggota_data'] ?? '[]', true) ?: [];
              ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td>
                    <div style="font-weight:700;font-size:13.5px;color:#1e293b"><?= e($row['no_nd']) ?></div>
                    <div style="font-size:11.5px;color:#64748b">Tgl: <?= tgl_id($row['tgl_nd']) ?></div>
                    <div style="font-size:11.5px;color:#0369a1;margin-top:2px"><i class="fa-solid fa-user-shield"></i> <?= e($row['irban_nama']) ?></div>
                  </td>
                  <td>
                    <div style="font-weight:700;font-size:13.5px"><?= e($row['desa_nama']) ?></div>
                    <div style="font-size:12px;color:#64748b">Kec. <?= e($row['kecamatan_nama']) ?></div>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;margin-top:4px">TA <?= (int)$row['tahun_anggaran'] ?></span>
                  </td>
                  <td>
                    <div style="font-size:12px"><b>Dalnis:</b> <?= e($row['dalnis_nama']) ?></div>
                    <div style="font-size:12px"><b>Ketua Tim:</b> <?= e($row['ketua_tim_nama']) ?></div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">
                      <b>Anggota (<?= count($anggotaList) ?>):</b> 
                      <?= e(implode(', ', array_column($anggotaList, 'nama'))) ?>
                    </div>
                  </td>
                  <td>
                    <div style="font-size:12px;font-weight:600"><?= tgl_id($row['tgl_mulai']) ?> s.d <?= tgl_id($row['tgl_selesai']) ?></div>
                    <div style="font-size:11px;color:#64748b"><?= (int)$row['lama_hari'] ?> Hari Kerja</div>
                  </td>
                  <td>
                    <?php if ($row['status'] === 'DRAFT'): ?>
                      <span class="badge badge-secondary">Draft Irban</span>
                    <?php elseif ($row['status'] === 'DIAJUKAN_INSPEKTUR'): ?>
                      <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a">
                        <i class="fa-solid fa-clock"></i> Disposisi Inspektur
                      </span>
                    <?php elseif ($row['status'] === 'DISETUJUI'): ?>
                      <span class="badge badge-success"><i class="fa-solid fa-check-circle"></i> Disetujui</span>
                      <?php if (!empty($row['spt_no'])): ?>
                        <div style="font-size:11px;color:#047857;margin-top:4px"><b>SPT:</b> <?= e($row['spt_no']) ?></div>
                      <?php else: ?>
                        <div style="font-size:11px;color:#0284c7;margin-top:2px">Antrean SPT</div>
                      <?php endif; ?>
                    <?php elseif ($row['status'] === 'DITOLAK'): ?>
                      <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Perlu Revisi</span>
                    <?php endif; ?>

                    <?php if (!empty($row['catatan_inspektur'])): ?>
                      <div style="font-size:11px;background:#f1f5f9;padding:4px 6px;border-radius:4px;margin-top:4px;color:#334155" title="<?= e($row['catatan_inspektur']) ?>">
                        <i class="fa-solid fa-comment-dots"></i> <?= e(mb_substr($row['catatan_inspektur'], 0, 30)) ?>...
                      </div>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:right">
                    <div style="display:inline-flex;gap:6px">
                      <!-- CETAK NOTA DINAS -->
                      <a href="<?= url('print/nota-dinas?id=' . $row['id']) ?>" target="_blank" class="btn btn-sm btn-outline" title="Cetak Nota Dinas">
                        <i class="fa-solid fa-print"></i> Cetak
                      </a>

                      <!-- AKSI DISPOSISI (KHUSUS INSPEKTUR / ADMIN) -->
                      <?php if (($isInspektur || $auth->isAdmin()) && $row['status'] === 'DIAJUKAN_INSPEKTUR'): ?>
                        <button type="button" class="btn btn-sm btn-primary" onclick="bukaModalDisposisi(<?= $row['id'] ?>, '<?= e($row['no_nd']) ?>', '<?= e($row['desa_nama']) ?>')">
                          <i class="fa-solid fa-file-pen"></i> Disposisi
                        </button>
                      <?php endif; ?>

                      <!-- OPERATOR SPT: BUAT SPT JIKA SUDAH DISETUJUI -->
                      <?php if (($auth->isOperatorSpt() || $auth->isAdmin()) && $row['status'] === 'DISETUJUI' && empty($row['spt_id'])): ?>
                        <a href="<?= url('penugasan/spt/create?nd_id=' . $row['id']) ?>" class="btn btn-sm btn-success" title="Terbitkan Surat Perintah Tugas">
                          <i class="fa-solid fa-stamp"></i> Terbitkan SPT
                        </a>
                      <?php endif; ?>

                      <!-- EDIT NOTA DINAS -->
                      <?php if (empty($row['spt_id']) && ($auth->isAdmin() || $isIrban || $auth->id() === (int)$row['created_by'])): ?>
                        <a href="<?= url('penugasan/nota-dinas/edit?id=' . $row['id']) ?>" class="btn btn-sm btn-outline" title="Edit / Koreksi Nota Dinas" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                          <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>
                      <?php endif; ?>

                      <!-- HAPUS NOTA DINAS -->
                      <?php if (empty($row['spt_id']) && ($auth->isAdmin() || ($isIrban && in_array($row['status'], ['DRAFT', 'DIAJUKAN_INSPEKTUR', 'DITOLAK'])))): ?>
                        <form method="POST" action="<?= url('penugasan/nota-dinas/delete') ?>" style="display:inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Nota Dinas <?= e($row['no_nd']) ?>? Data yang dihapus tidak dapat dikembalikan.');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="id" value="<?= $row['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline" title="Hapus Nota Dinas" style="color:#dc2626;border-color:#fecaca;background:#fef2f2">
                            <i class="fa-solid fa-trash"></i> Hapus
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- MODAL DISPOSISI INSPEKTUR -->
<div id="modalDisposisi" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;width:95%;max-width:520px;padding:24px;box-shadow:0 10px 25px rgba(0,0,0,0.2)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:10px">
      <h3 style="margin:0;font-size:16px;font-weight:700"><i class="fa-solid fa-pen-fancy"></i> Lembar Disposisi Inspektur Daerah</h3>
      <button type="button" onclick="tutupModalDisposisi()" style="background:none;border:none;font-size:18px;cursor:pointer">&times;</button>
    </div>
    <form method="POST" action="<?= url('penugasan/nota-dinas/disposisi') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="dispNdId">
      
      <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:14px;border:1px solid #e2e8f0">
        <div style="font-size:12px;color:#64748b">Nota Dinas:</div>
        <div id="dispNoNd" style="font-weight:700;color:#0f172a;font-size:13.5px"></div>
        <div id="dispDesa" style="font-size:12.5px;color:#0369a1;margin-top:2px"></div>
      </div>

      <div style="margin-bottom:14px">
        <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:6px">Keputusan / Disposisi:</label>
        <div style="display:flex;gap:12px">
          <label style="flex:1;display:flex;align-items:center;gap:8px;border:1px solid #cbd5e1;padding:10px;border-radius:8px;cursor:pointer">
            <input type="radio" name="aksi" value="setuju" checked>
            <div>
              <b style="color:#059669">SETUJUI &amp; PROSES SPT</b>
              <div style="font-size:11px;color:#64748b">Diteruskan ke Bagian SPT</div>
            </div>
          </label>
          <label style="flex:1;display:flex;align-items:center;gap:8px;border:1px solid #cbd5e1;padding:10px;border-radius:8px;cursor:pointer">
            <input type="radio" name="aksi" value="tolak">
            <div>
              <b style="color:#dc2626">KEMBALIKAN KE IRBAN</b>
              <div style="font-size:11px;color:#64748b">Perlu perbaikan tim/jadwal</div>
            </div>
          </label>
        </div>
      </div>

      <div style="margin-bottom:18px">
        <label style="display:block;font-size:12.5px;font-weight:600;margin-bottom:6px">Catatan / Arahan Inspektur:</label>
        <textarea name="catatan_inspektur" class="input" rows="3" placeholder="Contoh: Disetujui. Laksanakan audit dengan mempedomani PKPT dan selesaikan tepat waktu." style="width:100%;font-size:13px"></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px">
        <button type="button" class="btn btn-outline" onclick="tutupModalDisposisi()">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim Disposisi</button>
      </div>
    </form>
  </div>
</div>

<script>
function bukaModalDisposisi(id, noNd, desa) {
  document.getElementById('dispNdId').value = id;
  document.getElementById('dispNoNd').innerText = noNd;
  document.getElementById('dispDesa').innerText = 'Objek: Kepenghuluan ' + desa;
  var m = document.getElementById('modalDisposisi');
  m.style.display = 'flex';
}
function tutupModalDisposisi() {
  document.getElementById('modalDisposisi').style.display = 'none';
}
</script>

<?php partial('foot'); ?>
