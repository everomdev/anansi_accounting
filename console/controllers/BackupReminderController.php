<?php
// filepath: f:\Proyectos\anansi_accounting\anansi_accounting\console\controllers\BackupReminderController.php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\User;

class BackupReminderController extends Controller
{
    /**
     * Marca a todos los usuarios para mostrar el recordatorio de copia de seguridad
     */
    public function actionScheduleReminder()
    {
        $this->stdout("Iniciando programación de recordatorios de copia de seguridad...\n");
        
        // Obtener todos los usuarios activos
        $users = User::find()->all();
        $count = 0;
        
        foreach ($users as $user) {
            $status = Yii::$app->db->createCommand("
                SELECT * FROM user_notification_status WHERE user_id = :userId
            ", [':userId' => $user->id])->queryOne();
            
            if (!$status) {
                // Si no existe, crear un nuevo registro
                Yii::$app->db->createCommand()->insert('user_notification_status', [
                    'user_id' => $user->id,
                    'last_backup_reminder' => null,
                    'show_backup_reminder' => 1
                ])->execute();
                $count++;
            } else {
                // Verificar si han pasado 15 días desde el último recordatorio
                $shouldShow = false;
                
                if ($status['last_backup_reminder'] === null) {
                    $shouldShow = true;
                } else {
                    $lastDate = new \DateTime($status['last_backup_reminder']);
                    $now = new \DateTime();
                    $diff = $lastDate->diff($now);
                    $shouldShow = $diff->days >= 15;
                }
                
                if ($shouldShow) {
                    Yii::$app->db->createCommand()->update('user_notification_status', [
                        'show_backup_reminder' => 1
                    ], ['user_id' => $user->id])->execute();
                    $count++;
                }
            }
        }
        
        $this->stdout("Completado: $count usuarios recibirán recordatorios en su próxima actividad.\n");
    }
}