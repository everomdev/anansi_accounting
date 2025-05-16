<?php

namespace backend\components;

use Yii;
use yii\base\Behavior;
use yii\web\Controller;

class BackupReminderBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'checkBackupReminder',
        ];
    }

    /**
     * Verifica si se debe mostrar el recordatorio de copia de seguridad
     */
    public function checkBackupReminder()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }
        
        $userId = Yii::$app->user->id;
        
        // Verificar si el usuario debe ver el recordatorio
        $status = \Yii::$app->db->createCommand("
            SELECT * FROM user_notification_status WHERE user_id = :userId AND show_backup_reminder = 1
        ", [':userId' => $userId])->queryOne();
        
        if ($status) {
            // Marcar como mostrado para que no vuelva a aparecer
            \Yii::$app->db->createCommand()->update('user_notification_status', [
                'show_backup_reminder' => 0,
                'last_backup_reminder' => date('Y-m-d H:i:s')
            ], ['user_id' => $userId])->execute();
            
            // Preparar el mensaje para mostrar con fondo de color
            $message = '
            <div style="background-color: #f8f9fa; border-left: 4px solid #4e73df; padding: 15px; margin-bottom: 20px; color: #333;">
                <h4><i class="fas fa-shield-alt"></i> Recordatorio amistoso de seguridad</h4>
                <p>Querid@ restauranter@,</p>
                <p>Para mantener tu información siempre protegida, te recomendamos descargar una copia actualizada de tus insumos, subrecetas y recetas y guardarla en un lugar seguro (como tu computadora o la nube).</p>
                <p>Esto es especialmente útil si algún día no tienes conexión a internet o si surge alguna falla técnica.</p>
                <p><strong>Recuerda:</strong> tu esfuerzo vale oro y cuidarlo es parte de hacerlo crecer. 💡</p>
                <p><i class="fas fa-thumbtack"></i> Te estaremos recordando esto cada 15 días para que siempre tengas el respaldo de tu información actualizada.</p>
                <p>¡Gracias por seguir usando nuestro sistema!</p>
            </div>';
            
            // Guardar el mensaje en la sesión para mostrarlo en la vista
            Yii::$app->session->setFlash('backup-reminder-modal', $message);
        }
    }
}