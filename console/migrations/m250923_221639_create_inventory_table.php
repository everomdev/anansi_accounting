<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%inventory}}`.
 */
class m250923_221639_create_inventory_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%inventory}}', [
            'id' => $this->primaryKey(),
            'ingredient_stock_id' => $this->integer()->notNull(),
            'business_id' => $this->integer()->notNull(),
            'inventario_almacen' => $this->decimal(12,3)->defaultValue(0),
            'inventario_cocina' => $this->decimal(12,3)->defaultValue(0),
            'inventario_barra' => $this->decimal(12,3)->defaultValue(0),
            'inventario_servicio' => $this->decimal(12,3)->defaultValue(0),
            'inventario_otro' => $this->decimal(12,3)->defaultValue(0),
        ]);

        $this->addForeignKey(
            'fk-inventory-ingredient_stock_id',
            '{{%inventory}}',
            'ingredient_stock_id',
            '{{%ingredient_stock}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-inventory-business_id',
            '{{%inventory}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%inventory}}');
    }
}
