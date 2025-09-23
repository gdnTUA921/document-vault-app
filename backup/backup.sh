#!/bin/sh
set -e

DATE=$(date +%F_%H-%M-%S)
BACKUP_DIR="/backups/$DATE"
mkdir -p "$BACKUP_DIR"

echo "[*] Dumping MySQL database..."
mysqldump \
  -h "$DB_HOST" \
  -P "$DB_PORT" \
  -u "$DB_USERNAME" \
  -p"$DB_PASSWORD" \
  "$DB_DATABASE" > "$BACKUP_DIR/db.sql"

echo "[*] Copying uploaded files..."
cp -r /var/www/html/storage/app "$BACKUP_DIR/files"

echo "[*] Backup complete at $BACKUP_DIR"
