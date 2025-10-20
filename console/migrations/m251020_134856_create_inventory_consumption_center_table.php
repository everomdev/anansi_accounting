<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%inventory_consumption_center}}`.
 */
class m251020_134856_create_inventory_consumption_center_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%inventory_consumption_center}}', [
            'id' => $this->primaryKey(),
            'inventory_id' => $this->integer()->notNull(),
            'consumption_center_id' => $this->integer()->notNull(),
            'quantity' => $this->decimal(10, 3)->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk_inventory_consumption_center_inventory',
            '{{%inventory_consumption_center}}',
            'inventory_id',
            '{{%inventory}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_inventory_consumption_center_consumption_center',
            '{{%inventory_consumption_center}}',
            'consumption_center_id',
            '{{%consumption_center}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Unique constraint to prevent duplicates
        $this->createIndex(
            'idx_inventory_consumption_center_unique',
            '{{%inventory_consumption_center}}',
            ['inventory_id', 'consumption_center_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%inventory_consumption_center}}');
    }
}
