<?php

use yii\db\Migration;

class m251117_170640_fix_expenses_table_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar los campos necesarios para gastos si no existen
        $tableSchema = $this->db->getTableSchema('{{%expenses}}');
        
        if (!isset($tableSchema->columns['description'])) {
            $this->addColumn('{{%expenses}}', 'description', $this->text()->null()->comment('Descripción del gasto'));
        }
        
        if (!isset($tableSchema->columns['provider_id'])) {
            $this->addColumn('{{%expenses}}', 'provider_id', $this->integer()->null()->comment('ID del proveedor (opcional)'));
        }
        
        if (!isset($tableSchema->columns['amount'])) {
            $this->addColumn('{{%expenses}}', 'amount', $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('Monto del gasto'));
        }
        
        if (!isset($tableSchema->columns['frequency'])) {
            $this->addColumn('{{%expenses}}', 'frequency', $this->string(50)->notNull()->defaultValue('unico')->comment('Frecuencia: unico, diario, semanal, quincenal, mensual, bimestral, trimestral, semestral, anual'));
        }
        
        if (!isset($tableSchema->columns['expense_date'])) {
            $this->addColumn('{{%expenses}}', 'expense_date', $this->date()->notNull()->defaultValue('2024-01-01')->comment('Fecha del gasto'));
        }
        
        if (!isset($tableSchema->columns['is_active'])) {
            $this->addColumn('{{%expenses}}', 'is_active', $this->boolean()->defaultValue(true)->comment('Si el gasto está activo'));
        }

        // Agregar índices si no existen
        try {
            $this->createIndex('idx-expenses-provider_id', '{{%expenses}}', 'provider_id');
        } catch (\Exception $e) {
            // El índice ya existe
        }
        
        try {
            $this->createIndex('idx-expenses-expense_date', '{{%expenses}}', 'expense_date');
        } catch (\Exception $e) {
            // El índice ya existe
        }
        
        try {
            $this->createIndex('idx-expenses-frequency', '{{%expenses}}', 'frequency');
        } catch (\Exception $e) {
            // El índice ya existe
        }

        // Agregar llave foránea para proveedor si no existe
        try {
            $this->addForeignKey(
                'fk-expenses-provider_id',
                '{{%expenses}}',
                'provider_id',
                '{{%provider}}',
                'id',
                'SET NULL'
            );
        } catch (\Exception $e) {
            // La llave foránea ya existe o hay otro problema
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar llave foránea si existe
        try {
            $this->dropForeignKey('fk-expenses-provider_id', '{{%expenses}}');
        } catch (\Exception $e) {
            // La llave foránea no existe
        }
        
        // Eliminar índices si existen
        try {
            $this->dropIndex('idx-expenses-frequency', '{{%expenses}}');
        } catch (\Exception $e) {
            // El índice no existe
        }
        
        try {
            $this->dropIndex('idx-expenses-expense_date', '{{%expenses}}');
        } catch (\Exception $e) {
            // El índice no existe
        }
        
        try {
            $this->dropIndex('idx-expenses-provider_id', '{{%expenses}}');
        } catch (\Exception $e) {
            // El índice no existe
        }

        // Eliminar columnas si existen
        $tableSchema = $this->db->getTableSchema('{{%expenses}}');
        
        if (isset($tableSchema->columns['is_active'])) {
            $this->dropColumn('{{%expenses}}', 'is_active');
        }
        
        if (isset($tableSchema->columns['expense_date'])) {
            $this->dropColumn('{{%expenses}}', 'expense_date');
        }
        
        if (isset($tableSchema->columns['frequency'])) {
            $this->dropColumn('{{%expenses}}', 'frequency');
        }
        
        if (isset($tableSchema->columns['amount'])) {
            $this->dropColumn('{{%expenses}}', 'amount');
        }
        
        if (isset($tableSchema->columns['provider_id'])) {
            $this->dropColumn('{{%expenses}}', 'provider_id');
        }
        
        if (isset($tableSchema->columns['description'])) {
            $this->dropColumn('{{%expenses}}', 'description');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251117_170640_fix_expenses_table_structure cannot be reverted.\n";

        return false;
    }
    */
}
