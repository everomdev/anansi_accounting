<?php

use yii\db\Migration;

class m260304_015744_add_quantity_fields_to_expense_movements extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar campo para cantidad
        $this->addColumn('{{%expense_movements}}', 'quantity', $this->decimal(10, 2)->null()->after('amount')->comment('Cantidad de unidades (para gastos inventariables)'));
        
        // Agregar campo para monto unitario
        $this->addColumn('{{%expense_movements}}', 'unit_amount', $this->decimal(10, 2)->null()->after('quantity')->comment('Monto por unidad (para gastos inventariables)'));
        
        // Crear índice para quantity
        $this->createIndex(
            'idx-expense_movements-quantity',
            '{{%expense_movements}}',
            'quantity'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar índice
        $this->dropIndex('idx-expense_movements-quantity', '{{%expense_movements}}');
        
        // Eliminar columnas
        $this->dropColumn('{{%expense_movements}}', 'unit_amount');
        $this->dropColumn('{{%expense_movements}}', 'quantity');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260304_015744_add_quantity_fields_to_expense_movements cannot be reverted.\n";

        return false;
    }
    */
}
