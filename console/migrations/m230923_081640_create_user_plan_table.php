<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_plan}}`.
 */
class m230923_081640_create_user_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_plan}}', [
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer(),
            'user_id' => $this->integer()->notNull(),
            'stripe_subscription_id' => $this->string(),
            'stripe_subscription_status' => $this->string(),
            'stripe_customer_id' => $this->string()
        ]);

        $this->createIndex('idx-plan_id-user_plan', 'user_plan', 'plan_id');
        $this->addForeignKey('fk-plan_id-user_plan', 'user_plan', 'plan_id', 'plan', 'id', 'SET NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-plan_id-user_plan', 'user_plan');
        $this->dropIndex('idx-plan_id-user_plan', 'user_plan');
        $this->dropTable('{{%user_plan}}');
    }
}
