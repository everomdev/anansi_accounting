<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%requisition_config}}`.
 */
class m260109_201953_create_requisition_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%requisition_config}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull()->unique()->comment('Negocio'),
            'max_future_days' => $this->integer()->defaultValue(7)->comment('Días máximos a futuro permitidos'),
            'availability_green_threshold' => $this->decimal(5, 2)->defaultValue(70.00)->comment('% para indicador verde'),
            'availability_yellow_threshold' => $this->decimal(5, 2)->defaultValue(100.00)->comment('% para indicador amarillo'),
            'enable_frequent_combos' => $this->boolean()->defaultValue(true)->comment('Habilitar combos frecuentes'),
            'enable_auto_suggestions' => $this->boolean()->defaultValue(true)->comment('Habilitar sugerencias automáticas'),
            'require_observations_without_requisition' => $this->boolean()->defaultValue(true)->comment('Observaciones obligatorias en salidas sin requisición'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        
        // Foreign key
        $this->addForeignKey(
            'fk-requisition_config-business_id',
            '{{%requisition_config}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE'
        );
        
        // Crear configuración por defecto para negocios existentes
        $this->execute("
            INSERT INTO requisition_config (business_id, max_future_days, availability_green_threshold, availability_yellow_threshold, enable_frequent_combos, enable_auto_suggestions, require_observations_without_requisition, created_at, updated_at)
            SELECT id, 7, 70.00, 100.00, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
            FROM business
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-requisition_config-business_id', '{{%requisition_config}}');
        $this->dropTable('{{%requisition_config}}');
    }
}
