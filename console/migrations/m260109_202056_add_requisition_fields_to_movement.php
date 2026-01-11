<?php

use yii\db\Migration;

class m260109_202056_add_requisition_fields_to_movement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Campos para requisiciones
        $this->addColumn('{{%movement}}', 'requisition_number', $this->string(50)->null()->comment('Número único de requisición'));
        $this->addColumn('{{%movement}}', 'required_date', $this->dateTime()->null()->comment('Fecha requerida de entrega'));
        $this->addColumn('{{%movement}}', 'status', $this->string(20)->defaultValue('pending')->comment('Estado: pending, partially_fulfilled, fulfilled, cancelled'));
        $this->addColumn('{{%movement}}', 'requested_by_user_id', $this->integer()->null()->comment('Usuario que solicitó'));
        $this->addColumn('{{%movement}}', 'fulfilled_by_user_id', $this->integer()->null()->comment('Usuario que surtió'));
        $this->addColumn('{{%movement}}', 'parent_requisition_id', $this->integer()->null()->comment('ID de requisición padre'));
        $this->addColumn('{{%movement}}', 'is_without_requisition', $this->boolean()->defaultValue(false)->comment('Salida sin requisición'));
        $this->addColumn('{{%movement}}', 'client_timezone', $this->string(50)->null()->comment('Zona horaria del cliente'));
        
        // Índices
        $this->createIndex('idx-movement-requisition_number', '{{%movement}}', 'requisition_number');
        $this->createIndex('idx-movement-status', '{{%movement}}', 'status');
        $this->createIndex('idx-movement-required_date', '{{%movement}}', 'required_date');
        $this->createIndex('idx-movement-parent_requisition_id', '{{%movement}}', 'parent_requisition_id');
        
        // Foreign keys
        $this->addForeignKey(
            'fk-movement-requested_by_user_id',
            '{{%movement}}',
            'requested_by_user_id',
            '{{%user}}',
            'id',
            'SET NULL'
        );
        
        $this->addForeignKey(
            'fk-movement-fulfilled_by_user_id',
            '{{%movement}}',
            'fulfilled_by_user_id',
            '{{%user}}',
            'id',
            'SET NULL'
        );
        
        $this->addForeignKey(
            'fk-movement-parent_requisition_id',
            '{{%movement}}',
            'parent_requisition_id',
            '{{%movement}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-movement-parent_requisition_id', '{{%movement}}');
        $this->dropForeignKey('fk-movement-fulfilled_by_user_id', '{{%movement}}');
        $this->dropForeignKey('fk-movement-requested_by_user_id', '{{%movement}}');
        
        $this->dropIndex('idx-movement-parent_requisition_id', '{{%movement}}');
        $this->dropIndex('idx-movement-required_date', '{{%movement}}');
        $this->dropIndex('idx-movement-status', '{{%movement}}');
        $this->dropIndex('idx-movement-requisition_number', '{{%movement}}');
        
        $this->dropColumn('{{%movement}}', 'client_timezone');
        $this->dropColumn('{{%movement}}', 'is_without_requisition');
        $this->dropColumn('{{%movement}}', 'parent_requisition_id');
        $this->dropColumn('{{%movement}}', 'fulfilled_by_user_id');
        $this->dropColumn('{{%movement}}', 'requested_by_user_id');
        $this->dropColumn('{{%movement}}', 'status');
        $this->dropColumn('{{%movement}}', 'required_date');
        $this->dropColumn('{{%movement}}', 'requisition_number');
        
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260109_202056_add_requisition_fields_to_movement cannot be reverted.\n";

        return false;
    }
    */
}
