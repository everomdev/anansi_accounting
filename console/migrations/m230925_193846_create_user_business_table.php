<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_business}}`.
 */
class m230925_193846_create_user_business_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_business}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'business_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_business}}');
    }
}
