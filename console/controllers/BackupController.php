<?php

namespace console\controllers;

use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Comando para activar recordatorios de backup cada 15 días
 * El BackupReminderBehavior se encarga de mostrar el modal automáticamente
 */
class BackupController extends Controller
{
    /**
     * Activa el recordatorio de backup para usuarios que lo necesiten
     * El BackupReminderBehavior ya existente se encargará del resto
     */
    public function actionActivateReminder()
    {
        echo "Activando recordatorios de backup cada 15 días...\n";
        
        try {
            // Activar show_backup_reminder = 1 para usuarios que:
            // 1. Nunca han recibido recordatorio (last_backup_reminder IS NULL)
            // 2. Han pasado 15 días desde el último recordatorio
            $result = \Yii::$app->db->createCommand("
                UPDATE user_notification_status 
                SET show_backup_reminder = 1 
                WHERE show_backup_reminder = 0 
                AND (
                    last_backup_reminder IS NULL 
                    OR last_backup_reminder < DATE_SUB(NOW(), INTERVAL 15 DAY)
                )
            ")->execute();
            
            echo "✅ Recordatorios activados para {$result} usuarios.\n";
            echo "ℹ️  El BackupReminderBehavior mostrará el modal automáticamente cuando accedan al sistema.\n";
            echo "📅 Fecha: " . date('Y-m-d H:i:s') . "\n";
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Forzar activación inmediata para testing
     */
    public function actionForceAll()
    {
        echo "Forzando recordatorio para TODOS los usuarios (solo para testing)...\n";
        
        try {
            $result = \Yii::$app->db->createCommand("
                UPDATE user_notification_status 
                SET show_backup_reminder = 1
            ")->execute();
            
            echo "✅ Recordatorio forzado para {$result} usuarios.\n";
            echo "ℹ️  Accede al sistema web para ver el modal.\n";
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
