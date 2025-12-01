<?php

use yii\db\Migration;

class m251117_165350_modify_expenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Primero eliminar las claves foráneas existentes si existen
        try {
            $this->dropForeignKey('fk-expenses-category_id', '{{%expenses}}');
        } catch (\Exception $e) {
            // La clave foránea podría no existir
        }
        
        // Eliminar columnas que no aplican para gastos
        $this->dropColumn('{{%expenses}}', 'brand');
        $this->dropColumn('{{%expenses}}', 'presentation');
        $this->dropColumn('{{%expenses}}', 'category_id');
        $this->dropColumn('{{%expenses}}', 'um');
        $this->dropColumn('{{%expenses}}', 'quantity');
        $this->dropColumn('{{%expenses}}', 'yield');
        $this->dropColumn('{{%expenses}}', 'portion_um');
        $this->dropColumn('{{%expenses}}', 'portions_per_unit');
        $this->dropColumn('{{%expenses}}', 'min_stock');
        $this->dropColumn('{{%expenses}}', 'max_stock');

        // Agregar los campos necesarios para gastos
        $this->addColumn('{{%expenses}}', 'description', $this->text()->null()->comment('Descripción del gasto'));
        $this->addColumn('{{%expenses}}', 'provider_id', $this->integer()->null()->comment('ID del proveedor (opcional)'));
        $this->addColumn('{{%expenses}}', 'amount', $this->decimal(10, 2)->notNull()->comment('Monto del gasto'));
        $this->addColumn('{{%expenses}}', 'frequency', $this->string(50)->notNull()->defaultValue('unico')->comment('Frecuencia: unico, diario, semanal, quincenal, mensual, bimestral, trimestral, semestral, anual'));
        $this->addColumn('{{%expenses}}', 'expense_date', $this->date()->notNull()->comment('Fecha del gasto'));
        $this->addColumn('{{%expenses}}', 'is_active', $this->boolean()->defaultValue(true)->comment('Si el gasto está activo'));
        $this->addColumn('{{%expenses}}', 'created_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Fecha de creación'));
        $this->addColumn('{{%expenses}}', 'updated_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Fecha de actualización'));

        // Agregar índices
        $this->createIndex('idx-expenses-provider_id', '{{%expenses}}', 'provider_id');
        $this->createIndex('idx-expenses-expense_date', '{{%expenses}}', 'expense_date');
        $this->createIndex('idx-expenses-frequency', '{{%expenses}}', 'frequency');

        // Agregar llave foránea para proveedor
        $this->addForeignKey(
            'fk-expenses-provider_id',
            '{{%expenses}}',
            'provider_id',
            '{{%provider}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar llave foránea
        $this->dropForeignKey('fk-expenses-provider_id', '{{%expenses}}');
        
        // Eliminar índices
        $this->dropIndex('idx-expenses-frequency', '{{%expenses}}');
        $this->dropIndex('idx-expenses-expense_date', '{{%expenses}}');
        $this->dropIndex('idx-expenses-provider_id', '{{%expenses}}');

        // Eliminar columnas nuevas
        $this->dropColumn('{{%expenses}}', 'updated_at');
        $this->dropColumn('{{%expenses}}', 'created_at');
        $this->dropColumn('{{%expenses}}', 'is_active');
        $this->dropColumn('{{%expenses}}', 'expense_date');
        $this->dropColumn('{{%expenses}}', 'frequency');
        $this->dropColumn('{{%expenses}}', 'amount');
        $this->dropColumn('{{%expenses}}', 'provider_id');
        $this->dropColumn('{{%expenses}}', 'description');

        // Restaurar columnas originales
        $this->addColumn('{{%expenses}}', 'brand', $this->string(255)->null());
        $this->addColumn('{{%expenses}}', 'presentation', $this->string(255)->null());
        $this->addColumn('{{%expenses}}', 'category_id', $this->integer()->null());
        $this->addColumn('{{%expenses}}', 'um', $this->string(50)->null());
        $this->addColumn('{{%expenses}}', 'quantity', $this->decimal(10, 3)->null());
        $this->addColumn('{{%expenses}}', 'yield', $this->decimal(5, 2)->null());
        $this->addColumn('{{%expenses}}', 'portion_um', $this->string(50)->null());
        $this->addColumn('{{%expenses}}', 'portions_per_unit', $this->decimal(10, 3)->null());
        $this->addColumn('{{%expenses}}', 'min_stock', $this->decimal(10, 3)->null());
        $this->addColumn('{{%expenses}}', 'max_stock', $this->decimal(10, 3)->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251117_165350_modify_expenses_table cannot be reverted.\n";

        return false;
    }
    */
}
