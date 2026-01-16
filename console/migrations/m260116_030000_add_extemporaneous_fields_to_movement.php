<?php

use yii\db\Migration;

/**
 * Handles adding extemporaneous fields to table `{{%movement}}`.
 */
class m260116_030000_add_extemporaneous_fields_to_movement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar campos para requisiciones extemporáneas
        $this->addColumn('{{%movement}}', 'is_extemporaneous', $this->boolean()->defaultValue(false)->comment('Indica si es una requisición extemporánea'));
        $this->addColumn('{{%movement}}', 'extemporaneous_reason', $this->text()->null()->comment('Motivo de la requisición extemporánea'));
        
        // Índice para consultas rápidas de requisiciones extemporáneas
        $this->createIndex(
            'idx-movement-is_extemporaneous',
            '{{%movement}}',
            'is_extemporaneous'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-movement-is_extemporaneous', '{{%movement}}');
        $this->dropColumn('{{%movement}}', 'extemporaneous_reason');
        $this->dropColumn('{{%movement}}', 'is_extemporaneous');
    }
}
