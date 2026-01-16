<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%consumption_center_requisition_rules}}`.
 */
class m260116_040000_create_consumption_center_requisition_rules_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%consumption_center_requisition_rules}}', [
            'id' => $this->primaryKey(),
            'consumption_center_id' => $this->integer()->notNull()->comment('Centro de consumo'),
            'business_id' => $this->integer()->notNull()->comment('Business ID para consultas rápidas'),
            'requisition_allowed_days' => $this->json()->comment('Array de días permitidos (0=Dom, 1=Lun, ..., 6=Sáb)'),
            'requisition_start_time' => $this->string(5)->comment('Hora de inicio (HH:MM)'),
            'requisition_end_time' => $this->string(5)->comment('Hora de fin (HH:MM)'),
            'allow_extemporaneous_requisitions' => $this->boolean()->defaultValue(true)->comment('Permitir requisiciones fuera de horario/días'),
            'require_extemporaneous_reason' => $this->boolean()->defaultValue(true)->comment('Requiere motivo para requisiciones extemporáneas'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Índices
        $this->createIndex(
            'idx-ccr_rules-consumption_center_id',
            '{{%consumption_center_requisition_rules}}',
            'consumption_center_id',
            true // UNIQUE - solo una configuración por centro
        );

        $this->createIndex(
            'idx-ccr_rules-business_id',
            '{{%consumption_center_requisition_rules}}',
            'business_id'
        );

        // Foreign keys
        $this->addForeignKey(
            'fk-ccr_rules-consumption_center_id',
            '{{%consumption_center_requisition_rules}}',
            'consumption_center_id',
            '{{%consumption_center}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-ccr_rules-business_id',
            '{{%consumption_center_requisition_rules}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ccr_rules-business_id', '{{%consumption_center_requisition_rules}}');
        $this->dropForeignKey('fk-ccr_rules-consumption_center_id', '{{%consumption_center_requisition_rules}}');
        $this->dropIndex('idx-ccr_rules-business_id', '{{%consumption_center_requisition_rules}}');
        $this->dropIndex('idx-ccr_rules-consumption_center_id', '{{%consumption_center_requisition_rules}}');
        $this->dropTable('{{%consumption_center_requisition_rules}}');
    }
}
