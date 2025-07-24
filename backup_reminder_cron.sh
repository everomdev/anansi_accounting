#!/bin/bash

# Script para activar recordatorios de backup cada 15 días
# El BackupReminderBehavior ya existente mostrará automáticamente el modal

# Cambiar al directorio del proyecto (CAMBIAR ESTA RUTA)
cd /opt/docker/projects/anansi_accounting/


# Ejecutar el comando que activa los recordatorios
#php yii backup/activate-reminder
docker compose -f docker-compose.prod.yml exec php yii backup/activate-reminder

# Log opcional
echo "$(date): Backup reminder cron ejecutado" >> logs/backup_reminder.log
