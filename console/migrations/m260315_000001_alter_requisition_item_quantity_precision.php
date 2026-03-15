<?php

use yii\db\Migration;

/**
 * Cambiar la precisión de los campos de cantidad en requisition_item de DECIMAL(10,2) a DECIMAL(10,3)
 * para permitir 3 decimales (ej: 0.075)
 */
class m260315_000001_alter_requisition_item_quantity_precision extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Cambiar quantity_requested de DECIMAL(10,2) a DECIMAL(10,3)
        $this->alterColumn('{{%requisition_item}}', 'quantity_requested', $this->decimal(10, 3)->notNull()->comment('Cantidad solicitada'));
        
        // Cambiar quantity_fulfilled de DECIMAL(10,2) a DECIMAL(10,3)
        $this->alterColumn('{{%requisition_item}}', 'quantity_fulfilled', $this->decimal(10, 3)->defaultValue(0)->comment('Cantidad surtida'));
        
        echo "Campos de cantidad actualizados a DECIMAL(10,3) para permitir 3 decimales.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revertir a DECIMAL(10,2)
        $this->alterColumn('{{%requisition_item}}', 'quantity_requested', $this->decimal(10, 2)->notNull()->comment('Cantidad solicitada'));
        $this->alterColumn('{{%requisition_item}}', 'quantity_fulfilled', $this->decimal(10, 2)->defaultValue(0)->comment('Cantidad surtida'));
        
        echo "Campos de cantidad revertidos a DECIMAL(10,2).\n";
    }
}
