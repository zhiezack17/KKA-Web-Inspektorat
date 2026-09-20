<?php
partial('head', ['title' => 'Buat Berita Acara Pemeriksaan Kas - Inspektorat Rokan Hilir']);
partial('sidebar');
?>

<main class="main">
  <?php partial('topbar'); ?>

  <div class="content">
    <?php partial('flash'); ?>

    <div class="page-head">
      <div>
        <h2 style="display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-file-circle-plus" style="color:#059669"></i>
          Buat Berita Acara Pemeriksaan Kas (Opname Kas Desa)
        </h2>
        <p>Standar Formulir Kendali Mutu APIP &bull; Perhitungan Fisik Uang Tunai Brankas, Rekening Bank Riau Kepri &amp; BKU</p>
      </div>
      <div>
        <a href="<?= url('opname-kas?tahun=' . $tahun) ?>" class="btn btn-outline">
          <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
      </div>
    </div>

    <form method="POST" action="<?= url('opname-kas/store') ?>" id="formOpname">
      <?= csrf_field() ?>
      <input type="hidden" name="tahun_anggaran" value="<?= $tahun ?>">
      <input type="hidden" name="spt_id" value="<?= $spt['id'] ?? '' ?>">

      <!-- 1. DATA UMUM PEMERIKSAAN -->
      <div class="card" style="padding:22px;margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:14px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--slate-200);padding-bottom:8px">
          <i class="fa-solid fa-landmark" style="color:#059669"></i>
          I. DATA UMUM &amp; PARA PIHAK PEMERIKSAAN KAS
        </h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px">
          <div>
            <label class="form-label" style="font-weight:700">Kepenghuluan (Desa) <span style="color:#dc2626">*</span></label>
            <select name="desa_id" id="desa_id" class="form-control" required onchange="onDesaChange(this)">
              <option value="">-- Pilih Kepenghuluan --</option>
              <?php foreach ($desaList as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $desaId == $d['id'] ? 'selected' : '' ?>>
                  <?= e($d['nama']) ?> (Kec. <?= e($d['kecamatan_nama']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Nomor Berita Acara (BAP) <span style="color:#dc2626">*</span></label>
            <input type="text" name="no_bap" class="form-control" required value="<?= e($defaultNoBap) ?>" placeholder="700/BAP-KAS/INSP/2025/001">
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Tanggal Pemeriksaan Kas <span style="color:#dc2626">*</span></label>
            <input type="date" name="tgl_pemeriksaan" class="form-control" required value="<?= date('Y-m-d') ?>">
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Waktu &amp; Tempat Pemeriksaan</label>
            <div style="display:grid;grid-template-columns:120px 1fr;gap:8px">
              <input type="text" name="waktu_pemeriksaan" class="form-control" value="09.30 WIB">
              <input type="text" name="tempat_pemeriksaan" class="form-control" value="Kantor Kepenghuluan">
            </div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-top:16px">
          <div>
            <label class="form-label" style="font-weight:700">Nama Bendahara Kepenghuluan <span style="color:#dc2626">*</span></label>
            <input type="text" name="nama_bendahara" id="nama_bendahara" class="form-control" required placeholder="Nama lengkap bendahara pengeluaran desa">
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Nama Pj. Penghulu / Penghulu <span style="color:#dc2626">*</span></label>
            <input type="text" name="nama_kepala_desa" id="nama_kepala_desa" class="form-control" required value="" placeholder="Nama Penghulu / Pj. Penghulu">
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Nama Ketua Tim Pemeriksa APIP <span style="color:#dc2626">*</span></label>
            <input type="text" name="nama_ketua_tim" class="form-control" required value="<?= e($spt['ketua_tim_nama'] ?? $auth->user()['nama'] ?? '') ?>">
          </div>

          <div>
            <label class="form-label" style="font-weight:700">NIP Ketua Tim Pemeriksa</label>
            <input type="text" name="nip_ketua_tim" class="form-control" value="<?= e($spt['ketua_tim_nip'] ?? $auth->user()['nip'] ?? '') ?>" placeholder="19xxxxxxxx xxxxxx x xxx">
          </div>
        </div>
      </div>

      <!-- 2. KALKULATOR FISIK KAS BRANKAS (UANG KERTAS & LOGAM) -->
      <div class="card" style="padding:22px;margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:14px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--slate-200);padding-bottom:8px">
          <i class="fa-solid fa-calculator" style="color:#0284c7"></i>
          II. PENGUJIAN FISIK UANG TUNAI DALAM KAS/BRANKAS DESA
        </h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
          <!-- Kolom Uang Kertas -->
          <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0">
            <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:6px">
              <i class="fa-regular fa-money-bill-1" style="color:#16a34a"></i> UANG KERTAS
            </h4>

            <table style="width:100%;font-size:12.5px;border-collapse:collapse">
              <thead>
                <tr style="border-bottom:1.5px solid #cbd5e1;color:#475569">
                  <th style="text-align:left;padding:6px 4px">Pecahan</th>
                  <th style="text-align:center;padding:6px 4px;width:100px">Jumlah (Lembar)</th>
                  <th style="text-align:right;padding:6px 4px">Subtotal (Rp)</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $kertasPecahan = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
                foreach ($kertasPecahan as $p): 
                ?>
                  <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:6px 4px;font-weight:600">Rp <?= number_format($p, 0, ',', '.') ?></td>
                    <td style="padding:6px 4px;text-align:center">
                      <input type="number" min="0" name="kertas_<?= $p ?>" class="form-control count-kertas" 
                             data-nominal="<?= $p ?>" value="0" style="width:85px;padding:4px 6px;text-align:center;font-weight:700;margin:0 auto" oninput="hitungTotalKas()">
                    </td>
                    <td style="padding:6px 4px;text-align:right;font-weight:700" id="sub_kertas_<?= $p ?>">Rp 0</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="border-top:2px solid #cbd5e1;font-weight:800;background:#f1f5f9">
                  <td colspan="2" style="padding:8px 4px;color:#0f172a">SUBTOTAL UANG KERTAS</td>
                  <td style="padding:8px 4px;text-align:right;color:#15803d;font-size:13px" id="total_kertas_view">Rp 0</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Kolom Uang Logam -->
          <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0">
            <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:6px">
              <i class="fa-solid fa-coins" style="color:#d97706"></i> UANG LOGAM (KOIN)
            </h4>

            <table style="width:100%;font-size:12.5px;border-collapse:collapse">
              <thead>
                <tr style="border-bottom:1.5px solid #cbd5e1;color:#475569">
                  <th style="text-align:left;padding:6px 4px">Pecahan</th>
                  <th style="text-align:center;padding:6px 4px;width:100px">Jumlah (Keping)</th>
                  <th style="text-align:right;padding:6px 4px">Subtotal (Rp)</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $logamPecahan = [1000, 500, 200, 100];
                foreach ($logamPecahan as $p): 
                ?>
                  <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:6px 4px;font-weight:600">Rp <?= number_format($p, 0, ',', '.') ?></td>
                    <td style="padding:6px 4px;text-align:center">
                      <input type="number" min="0" name="logam_<?= $p ?>" class="form-control count-logam" 
                             data-nominal="<?= $p ?>" value="0" style="width:85px;padding:4px 6px;text-align:center;font-weight:700;margin:0 auto" oninput="hitungTotalKas()">
                    </td>
                    <td style="padding:6px 4px;text-align:right;font-weight:700" id="sub_logam_<?= $p ?>">Rp 0</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="border-top:2px solid #cbd5e1;font-weight:800;background:#f1f5f9">
                  <td colspan="2" style="padding:8px 4px;color:#0f172a">SUBTOTAL UANG LOGAM</td>
                  <td style="padding:8px 4px;text-align:right;color:#d97706;font-size:13px" id="total_logam_view">Rp 0</td>
                </tr>
              </tfoot>
            </table>

            <!-- Total Kas Fisik Box -->
            <div style="margin-top:20px;padding:14px;background:#e0f2fe;border:1.5px solid #7dd3fc;border-radius:8px">
              <div style="font-size:11.5px;font-weight:700;color:#0369a1;text-transform:uppercase">JUMLAH KAS FISIK DI BRANKAS (A)</div>
              <div style="font-size:20px;font-weight:800;color:#0284c7;margin-top:2px" id="total_kas_fisik_view">Rp 0</div>
              <div style="font-size:11px;color:#075985;margin-top:2px">Uang Kertas + Uang Logam</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. REKONSILIASI REKENING BANK & BKU -->
      <div class="card" style="padding:22px;margin-bottom:20px">
        <h3 style="margin:0 0 16px;font-size:14px;font-weight:700;color:var(--slate-800);display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--slate-200);padding-bottom:8px">
          <i class="fa-solid fa-scale-balanced" style="color:#7c3aed"></i>
          III. SALDO REKENING BANK &amp; REKONSILIASI BUKU KAS UMUM (BKU)
        </h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px">
          <div>
            <label class="form-label" style="font-weight:700">Nama Bank Rekening Kas Desa</label>
            <input type="text" name="nama_bank" class="form-control" value="Bank Riau Kepri Syariah">
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Nomor Rekening Giro Kas Desa</label>
            <input type="text" name="no_rekening_bank" class="form-control" placeholder="Contoh: 104-08-01234-5">
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Saldo Rekening Bank (B) <span style="color:#dc2626">*</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:10px;top:8px;font-weight:700;color:var(--slate-500);font-size:13px">Rp</span>
              <input type="text" name="saldo_bank" id="saldo_bank" class="form-control" style="padding-left:36px;font-weight:700;font-size:14px;color:#6d28d9" value="0" oninput="formatAndCalc(this)">
            </div>
            <small style="color:var(--slate-500)">Sesuai rekening koran / buku tabungan Bank Riau Kepri Syariah</small>
          </div>

          <div>
            <label class="form-label" style="font-weight:700">Saldo Menurut Buku Kas Umum / BKU (D) <span style="color:#dc2626">*</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:10px;top:8px;font-weight:700;color:var(--slate-500);font-size:13px">Rp</span>
              <input type="text" name="saldo_bku" id="saldo_bku" class="form-control" style="padding-left:36px;font-weight:700;font-size:14px;color:#0f172a" value="0" oninput="formatAndCalc(this)">
            </div>
            <small style="color:var(--slate-500)">Sesuai penutupan BKU Siskeudes pada tanggal pemeriksaan</small>
          </div>
        </div>

        <!-- HASIL REKONSILIASI KAS REAL-TIME -->
        <div style="margin-top:22px;padding:18px 20px;border-radius:8px;background:#f8fafc;border:1.5px solid #cbd5e1" id="boxRekonsiliasi">
          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;align-items:center">
            <div>
              <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase">TOTAL KAS RIIL (C = A + B)</div>
              <div style="font-size:18px;font-weight:800;color:#0369a1;margin-top:2px" id="total_kas_riil_view">Rp 0</div>
              <div style="font-size:11px;color:#64748b">Kas Fisik + Saldo Bank</div>
            </div>

            <div>
              <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase">SALDO MENURUT BKU (D)</div>
              <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px" id="saldo_bku_view">Rp 0</div>
              <div style="font-size:11px;color:#64748b">Posisi Pembukuan Desa</div>
            </div>

            <div>
              <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase">SELISIH KAS (C - D)</div>
              <div style="font-size:20px;font-weight:800;color:#16a34a;margin-top:2px" id="selisih_kas_view">Rp 0</div>
              <div style="font-size:11.5px;font-weight:700" id="status_selisih_badge">
                <span class="badge" style="background:#dcfce7;color:#15803d"><i class="fa-solid fa-check"></i> Kas Cocok</span>
              </div>
            </div>
          </div>
        </div>

        <div style="margin-top:16px">
          <label class="form-label" style="font-weight:700">Penjelasan / Alasan Selisih Kas (Wajib diisi jika terdapat selisih lebih/kurang)</label>
          <textarea name="penjelasan_selisih" class="form-control" rows="2" placeholder="Jelaskan penyebab selisih kas fisik vs BKU (misal: terdapat penarikan tunai belum dicatat, bukti pengeluaran belum dibukukan, dll)..."></textarea>
        </div>

        <div style="margin-top:14px">
          <label class="form-label" style="font-weight:700">Catatan &amp; Arahan Tim Pemeriksa APIP</label>
          <textarea name="catatan_pemeriksaan" class="form-control" rows="2" placeholder="Catatan integritas penyimpanan brankas, kelayakan tempat penyimpanan kas tunai di desa, dll..."></textarea>
        </div>
      </div>

      <!-- ACTION BUTTONS -->
      <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:40px">
        <a href="<?= url('opname-kas?tahun=' . $tahun) ?>" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary" style="background:#059669;border-color:#059669;padding:10px 24px;font-size:14px;font-weight:700">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Berita Acara Kas
        </button>
      </div>

    </form>
  </div>
</main>

<script>
function formatRupiahJs(angka) {
  let num = Math.round(angka);
  return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function parseMoneyJs(val) {
  if (!val) return 0;
  let clean = val.toString().replace(/[^0-9]/g, '');
  return parseFloat(clean) || 0;
}

function formatAndCalc(input) {
  let num = parseMoneyJs(input.value);
  input.value = num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  hitungTotalKas();
}

function hitungTotalKas() {
  let totalKertas = 0;
  document.querySelectorAll('.count-kertas').forEach(function(el) {
    let nominal = parseFloat(el.getAttribute('data-nominal')) || 0;
    let qty = parseInt(el.value) || 0;
    let sub = nominal * qty;
    totalKertas += sub;
    let subEl = document.getElementById('sub_kertas_' + nominal);
    if (subEl) subEl.textContent = formatRupiahJs(sub);
  });
  document.getElementById('total_kertas_view').textContent = formatRupiahJs(totalKertas);

  let totalLogam = 0;
  document.querySelectorAll('.count-logam').forEach(function(el) {
    let nominal = parseFloat(el.getAttribute('data-nominal')) || 0;
    let qty = parseInt(el.value) || 0;
    let sub = nominal * qty;
    totalLogam += sub;
    let subEl = document.getElementById('sub_logam_' + nominal);
    if (subEl) subEl.textContent = formatRupiahJs(sub);
  });
  document.getElementById('total_logam_view').textContent = formatRupiahJs(totalLogam);

  let totalKasFisik = totalKertas + totalLogam;
  document.getElementById('total_kas_fisik_view').textContent = formatRupiahJs(totalKasFisik);

  let saldoBank = parseMoneyJs(document.getElementById('saldo_bank').value);
  let saldoBku = parseMoneyJs(document.getElementById('saldo_bku').value);
  let totalKasRiil = totalKasFisik + saldoBank;
  let selisih = totalKasRiil - saldoBku;

  document.getElementById('total_kas_riil_view').textContent = formatRupiahJs(totalKasRiil);
  document.getElementById('saldo_bku_view').textContent = formatRupiahJs(saldoBku);
  
  let selisihEl = document.getElementById('selisih_kas_view');
  let badgeEl = document.getElementById('status_selisih_badge');
  let boxEl = document.getElementById('boxRekonsiliasi');

  selisihEl.textContent = formatRupiahJs(selisih);

  if (Math.abs(selisih) === 0) {
    selisihEl.style.color = '#16a34a';
    badgeEl.innerHTML = '<span class="badge" style="background:#dcfce7;color:#15803d"><i class="fa-solid fa-check"></i> Kas Cocok Sesuai BKU</span>';
    boxEl.style.borderColor = '#86efac';
    boxEl.style.background = '#f0fdf4';
  } else if (selisih < 0) {
    selisihEl.style.color = '#dc2626';
    badgeEl.innerHTML = '<span class="badge" style="background:#fee2e2;color:#b91c1c"><i class="fa-solid fa-triangle-exclamation"></i> Selisih Kurang (Kas Tekor)</span>';
    boxEl.style.borderColor = '#fca5a5';
    boxEl.style.background = '#fef2f2';
  } else {
    selisihEl.style.color = '#d97706';
    badgeEl.innerHTML = '<span class="badge" style="background:#fef3c7;color:#b45309"><i class="fa-solid fa-circle-exclamation"></i> Selisih Lebih</span>';
    boxEl.style.borderColor = '#fde68a';
    boxEl.style.background = '#fffbeb';
  }
}

function onDesaChange(select) {
  if (select.value) {
    window.location.href = '<?= url("opname-kas/create") ?>?tahun=<?= $tahun ?>&desa_id=' + select.value;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  hitungTotalKas();
});
</script>

<?php partial('foot'); ?>
