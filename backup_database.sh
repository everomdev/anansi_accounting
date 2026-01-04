#!/bin/bash

# Configuración
PROJECT_DIR="/opt/docker/projects/anansi_accounting"
BACKUP_DIR="$PROJECT_DIR/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="backup_$DATE.sql"

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Realizar backup usando Docker
docker compose -f $PROJECT_DIR/docker-compose.prod.yml exec -T mysql mysqldump \
    -uroot -pverysecret \
    costeo \
    > "$BACKUP_DIR/$BACKUP_FILE"

# Comprimir backup
gzip "$BACKUP_DIR/$BACKUP_FILE"

# Eliminar backups antiguos (mantener últimos 30 días)
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

# Log
echo "$(date): Backup completado - $BACKUP_FILE.gz" >> $PROJECT_DIR/logs/database_backup.log