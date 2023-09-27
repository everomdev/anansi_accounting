<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_permission}}`.
 */
class m230923_141444_create_plan_permission_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_permission}}', [
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer()->notNull(),
            'item_name' => $this->string()->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%plan_permission}}');
    }
}
