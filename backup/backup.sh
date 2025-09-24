#!/bin/sh
set -e

DATE=$(date +%F_%H-%M-%S)
BACKUP_DIR="/backups/$DATE"
mkdir -p "$BACKUP_DIR"

echo "[*] Dumping MySQL database..."
mysqldump --skip-ssl -h db -u root -proot document_vault_app > "$BACKUP_DIR/db.sql"

echo "[*] Copying uploaded files..."
cp -r /var/www/html/storage/app "$BACKUP_DIR/files"

echo "[*] Backup complete at $BACKUP_DIR"
