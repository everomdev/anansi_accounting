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
            'brand' => $this->string(255)->null()->comment('Proveedor/Empresa'),
            'presentation' => $this->string(255)->null()->comment('Frecuencia/Tipo'),
            'business_id' => $this->integer()->notNull()->comment('ID del negocio'),
            'quantity' => $this->decimal(10, 3)->null()->comment('Cantidad'),
            'um' => $this->string(255)->notNull()->comment('Unidad de medida'),
            'yield' => $this->decimal(5, 3)->null()->comment('Rendimiento'),
            'portions_per_unit' => $this->decimal(10, 3)->null()->comment('Porciones por unidad'),
            'portion_um' => $this->string(255)->null()->comment('Unidad de porción'),
            'observations' => $this->text()->null()->comment('Observaciones'),
            'key' => $this->string(255)->notNull()->comment('Clave única'),
            'final_quantity' => $this->decimal(10, 3)->defaultValue(0)->comment('Cantidad final'),
            'category_id' => $this->integer()->null()->comment('ID de categoría'),
            'min_stock' => $this->decimal(10, 3)->null()->comment('Stock mínimo'),
            'max_stock' => $this->decimal(10, 3)->null()->comment('Stock máximo'),
            'created_at' => $this->timestamp()->null()->comment('Fecha de creación'),
            'updated_at' => $this->timestamp()->null()->comment('Fecha de actualización'),
        ]);

        // Agregar índices
        $this->createIndex(
            'idx-expenses-business_id',
            '{{%expenses}}',
            'business_id'
        );

        $this->createIndex(
            'idx-expenses-category_id',
            '{{%expenses}}',
            'category_id'
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
            'fk-expenses-category_id',
            '{{%expenses}}',
            'category_id',
            '{{%category}}',
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
            'fk-expenses-category_id',
            '{{%expenses}}'
        );

        $this->dropForeignKey(
            'fk-expenses-business_id',
            '{{%expenses}}'
        );

        // Eliminar índices
        $this->dropIndex(
            'idx-expenses-name',
            '{{%expenses}}'
        );

        $this->dropIndex(
            'idx-expenses-key-business_id',
            '{{%expenses}}'
        );

        $this->dropIndex(
            'idx-expenses-category_id',
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
