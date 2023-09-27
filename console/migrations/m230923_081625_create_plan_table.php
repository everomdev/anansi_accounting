<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan}}`.
 */
class m230923_081625_create_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'monthly_price' => $this->float()->notNull(),
            'yearly_price' => $this->float()->notNull(),
            'stripe_product_id' => $this->string(),
            'users' => $this->integer()->defaultValue(1),
            'description' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%plan}}');
    }
}
