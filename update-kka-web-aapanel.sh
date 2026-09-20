#!/bin/bash
# =====================================================================
# UPDATE KKA WEB INSPEKTORAT ROKAN HILIR
# Pembatasan Hak Akses Role Operator SPT & Update Tampilan
# =====================================================================

set -e

echo "=========================================================="
echo " [1/4] Mendeteksi lokasi instalasi KKA Web di server..."
echo "=========================================================="

KKA_DIR=""
if [ -d "/www/wwwroot/kka.arsipdigital-inspektorat.com" ] && [ -f "/www/wwwroot/kka.arsipdigital-inspektorat.com/src/lib/Helpers.php" ]; then
    KKA_DIR="/www/wwwroot/kka.arsipdigital-inspektorat.com"
elif [ -d "/www/wwwroot/kka" ] && [ -f "/www/wwwroot/kka/src/lib/Helpers.php" ]; then
    KKA_DIR="/www/wwwroot/kka"
else
    KKA_DIR=$(find /www/wwwroot -maxdepth 4 -name "Helpers.php" -path "*/src/lib/Helpers.php" 2>/dev/null | head -1 | sed 's|/src/lib/Helpers.php||')
fi

if [ -z "$KKA_DIR" ] || [ ! -d "$KKA_DIR" ]; then
    echo "❌ Folder KKA Web tidak ditemukan di /www/wwwroot."
    exit 1
fi

echo "✔ Folder KKA terdeteksi di: $KKA_DIR"

echo "=========================================================="
echo " [2/4] Melakukan backup file sebelum update..."
echo "=========================================================="
STAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/www/backup-kka/update-$STAMP"
mkdir -p "$BACKUP_DIR"

if [ -d "$KKA_DIR/src" ]; then
    cp -rf "$KKA_DIR/src" "$BACKUP_DIR/" 2>/dev/null || true
    echo "✔ Backup folder src disimpan di: $BACKUP_DIR/src"
fi

echo "=========================================================="
echo " [3/4] Menerapkan pembaruan dari GitHub..."
echo "=========================================================="
cd "$KKA_DIR"

if [ -d "$KKA_DIR/.git" ]; then
    echo "Menarik via Git..."
    git remote set-url origin https://github.com/zhiezack17/KKA-Web-Inspektorat.git 2>/dev/null || true
    git fetch origin main
    git reset --hard origin/main
else
    echo "Mengunduh file pembaruan..."
    TMP_DIR="/tmp/kka-update-$STAMP"
    mkdir -p "$TMP_DIR"
    curl -sSL "https://github.com/zhiezack17/KKA-Web-Inspektorat/archive/refs/heads/main.tar.gz" | tar -xz -C "$TMP_DIR" --strip-components=1
    
    # Salin src dan public, jangan timpa .env dan uploads
    cp -rf "$TMP_DIR/src" "$KKA_DIR/"
    cp -rf "$TMP_DIR/public/index.php" "$KKA_DIR/public/" 2>/dev/null || true
    cp -rf "$TMP_DIR/public/assets" "$KKA_DIR/public/" 2>/dev/null || true
    rm -rf "$TMP_DIR"
fi

echo "✔ Kode pembaruan berhasil diterapkan."

echo "=========================================================="
echo " [4/4] Memastikan hak akses folder uploads..."
echo "=========================================================="
mkdir -p "$KKA_DIR/public/uploads"
chmod -R 775 "$KKA_DIR/public/uploads" 2>/dev/null || true
chown -R www:www "$KKA_DIR" 2>/dev/null || true

echo ""
echo "=========================================================="
echo "🎉 UPDATE KKA DIGITAL SELESAI!"
echo " Hak akses Operator SPT telah diperketat:"
echo " 1. Modul Pelaksanaan Audit (KKA, Opname Kas, Rekap Belanja, Master KKA) disembunyikan."
echo " 2. Modul Hasil Pengawasan (KTP, LHP) disembunyikan."
echo " 3. Operator SPT hanya fokus ke Pra-Audit & Penugasan (Nota Dinas & SPT)."
echo "=========================================================="
