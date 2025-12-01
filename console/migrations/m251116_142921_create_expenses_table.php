<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%expenses}}`.
 */
class m251116_142921_create_expenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%expenses}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Nombre del gasto'),
            'description' => $this->text()->null()->comment('Descripción del gasto'),
            'business_id' => $this->integer()->notNull()->comment('ID del negocio'),
            'provider_id' => $this->integer()->null()->comment('ID del proveedor (opcional)'),
            'amount' => $this->decimal(10, 2)->notNull()->comment('Monto del gasto'),
            'frequency' => $this->string(50)->notNull()->defaultValue('unico')->comment('Frecuencia: unico, diario, semanal, quincenal, mensual, bimestral, trimestral, semestral, anual'),
            'expense_date' => $this->date()->notNull()->comment('Fecha del gasto'),
            'observations' => $this->text()->null()->comment('Observaciones'),
            'key' => $this->string(255)->notNull()->comment('Clave única'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Si el gasto está activo'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Fecha de creación'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Fecha de actualización'),
        ]);

        // Agregar índices
        $this->createIndex(
            'idx-expenses-business_id',
            '{{%expenses}}',
            'business_id'
        );

        $this->createIndex(
            'idx-expenses-provider_id',
            '{{%expenses}}',
            'provider_id'
        );

        $this->createIndex(
            'idx-expenses-key-business_id',
            '{{%expenses}}',
            ['key', 'business_id'],
            true // unique
        );

        $this->createIndex(
            'idx-expenses-name',
            '{{%expenses}}',
            'name'
        );

        $this->createIndex(
            'idx-expenses-expense_date',
            '{{%expenses}}',
            'expense_date'
        );

        // Agregar llaves foráneas
        $this->addForeignKey(
            'fk-expenses-business_id',
            '{{%expenses}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE'
        );

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
        // Eliminar llaves foráneas
        $this->dropForeignKey(
            'fk-expenses-provider_id',
            '{{%expenses}}'
        );

        $this->dropForeignKey(
            'fk-expenses-business_id',
            '{{%expenses}}'
        );

        // Eliminar índices
        $this->dropIndex(
            'idx-expenses-expense_date',
            '{{%expenses}}'
        );

        $this->dropIndex(
            'idx-expenses-name',
            '{{%expenses}}'
        );

        $this->dropIndex(
            'idx-expenses-key-business_id',
            '{{%expenses}}'
        );

        $this->dropIndex(
            'idx-expenses-provider_id',
            '{{%expenses}}'
        );

        $this->dropIndex(
            'idx-expenses-business_id',
            '{{%expenses}}'
        );

        // Eliminar tabla
        $this->dropTable('{{%expenses}}');
    }
}
