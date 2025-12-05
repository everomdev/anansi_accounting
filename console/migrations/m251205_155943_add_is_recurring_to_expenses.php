<?php

use yii\db\Migration;

class m251205_155943_add_is_recurring_to_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar columna is_recurring para indicar si el gasto es frecuente/recurrente
        $this->addColumn('{{%expenses}}', 'is_recurring', $this->tinyInteger(1)->defaultValue(0)->after('is_active'));
        
        // Crear índice para mejor rendimiento en consultas de gastos recurrentes
        $this->createIndex('idx-expenses-is_recurring', '{{%expenses}}', 'is_recurring');
        
        // Hacer que los campos amount, frequency y expense_date puedan ser NULL 
        // ya que los gastos no recurrentes no necesitarán estos campos
        $this->alterColumn('{{%expenses}}', 'amount', $this->decimal(10, 2)->null());
        $this->alterColumn('{{%expenses}}', 'frequency', $this->string(50)->null());
        $this->alterColumn('{{%expenses}}', 'expense_date', $this->date()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Primero, establecer valores por defecto para los campos que volverán a ser NOT NULL
        $this->update('{{%expenses}}', ['amount' => 0], ['amount' => null]);
        $this->update('{{%expenses}}', ['frequency' => 'unico'], ['frequency' => null]);
        $this->update('{{%expenses}}', ['expense_date' => date('Y-m-d')], ['expense_date' => null]);
        
        // Revertir los cambios en las columnas
        $this->alterColumn('{{%expenses}}', 'amount', $this->decimal(10, 2)->notNull());
        $this->alterColumn('{{%expenses}}', 'frequency', $this->string(50)->notNull()->defaultValue('unico'));
        $this->alterColumn('{{%expenses}}', 'expense_date', $this->date()->notNull());
        
        // Eliminar índice
        $this->dropIndex('idx-expenses-is_recurring', '{{%expenses}}');
        
        // Eliminar columna
        $this->dropColumn('{{%expenses}}', 'is_recurring');
    }
}
