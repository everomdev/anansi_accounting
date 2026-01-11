<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%requisition_item}}`.
 */
class m260109_211241_create_requisition_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%requisition_item}}', [
            'id' => $this->primaryKey(),
            'requisition_id' => $this->integer()->notNull()->comment('ID de la requisición (movement)'),
            'ingredient_id' => $this->integer()->notNull()->comment('ID del ingrediente solicitado'),
            'quantity_requested' => $this->decimal(10, 2)->notNull()->comment('Cantidad solicitada'),
            'quantity_fulfilled' => $this->decimal(10, 2)->defaultValue(0)->comment('Cantidad surtida'),
            'availability_status' => $this->string(20)->defaultValue('pending')->comment('available, warning, insufficient'),
            'availability_percentage' => $this->decimal(5, 2)->comment('Porcentaje de disponibilidad'),
            'cost_at_request' => $this->decimal(10, 2)->comment('Costo unitario al momento de la solicitud'),
            'observations' => $this->text()->comment('Observaciones del item'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Índices
        $this->createIndex('idx-requisition_item-requisition_id', '{{%requisition_item}}', 'requisition_id');
        $this->createIndex('idx-requisition_item-ingredient_id', '{{%requisition_item}}', 'ingredient_id');
        $this->createIndex('idx-requisition_item-availability_status', '{{%requisition_item}}', 'availability_status');

        // Foreign keys
        $this->addForeignKey(
            'fk-requisition_item-requisition_id',
            '{{%requisition_item}}',
            'requisition_id',
            '{{%movement}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-requisition_item-ingredient_id',
            '{{%requisition_item}}',
            'ingredient_id',
            '{{%ingredient_stock}}',
            'id',
            'CASCADE',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%requisition_item}}');
    }
}
