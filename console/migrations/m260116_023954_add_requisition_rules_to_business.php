<?php

use yii\db\Migration;

/**
 * Agregar campos de configuración de reglas de requisición a la tabla business
 * Estos campos permiten configurar:
 * - Días permitidos para requisiciones
 * - Ventana horaria permitida
 * - Si permite requisiciones extemporáneas
 * - Si requiere motivo para requisiciones extemporáneas
 */
class m260116_023954_add_requisition_rules_to_business extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Días permitidos para requisiciones (JSON array de días: 0=Domingo, 1=Lunes, ..., 6=Sábado)
        // Ejemplo: [1,2,3,4,5] = Lunes a Viernes
        $this->addColumn('{{%business}}', 'requisition_allowed_days', $this->json()->comment('Días permitidos para requisiciones (JSON array: 0=Dom, 1=Lun, ..., 6=Sáb)'));
        
        // Hora de inicio de la ventana horaria permitida (formato HH:MM)
        // Ejemplo: "08:00"
        $this->addColumn('{{%business}}', 'requisition_start_time', $this->string(5)->defaultValue('00:00')->comment('Hora de inicio para requisiciones (HH:MM)'));
        
        // Hora de fin de la ventana horaria permitida (formato HH:MM)
        // Ejemplo: "18:00"
        $this->addColumn('{{%business}}', 'requisition_end_time', $this->string(5)->defaultValue('23:59')->comment('Hora de fin para requisiciones (HH:MM)'));
        
        // ¿Permite requisiciones extemporáneas? (fuera de días/horarios permitidos)
        $this->addColumn('{{%business}}', 'allow_extemporaneous_requisitions', $this->boolean()->defaultValue(true)->comment('Permitir requisiciones fuera de horario/días establecidos'));
        
        // ¿Requiere motivo para requisiciones extemporáneas?
        $this->addColumn('{{%business}}', 'require_extemporaneous_reason', $this->boolean()->defaultValue(true)->comment('Requiere motivo para requisiciones extemporáneas'));
        
        // Agregar índice para mejorar rendimiento en consultas
        $this->createIndex(
            'idx-business-requisition_rules',
            '{{%business}}',
            ['allow_extemporaneous_requisitions', 'requisition_start_time', 'requisition_end_time']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar índice
        $this->dropIndex('idx-business-requisition_rules', '{{%business}}');
        
        // Eliminar columnas en orden inverso
        $this->dropColumn('{{%business}}', 'require_extemporaneous_reason');
        $this->dropColumn('{{%business}}', 'allow_extemporaneous_requisitions');
        $this->dropColumn('{{%business}}', 'requisition_end_time');
        $this->dropColumn('{{%business}}', 'requisition_start_time');
        $this->dropColumn('{{%business}}', 'requisition_allowed_days');
    }
}
