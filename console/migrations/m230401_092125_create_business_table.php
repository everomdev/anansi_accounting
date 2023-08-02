<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%business}}`.
 */
class m230401_092125_create_business_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%business}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'user_id' => $this->integer()->notNull()
        ]);

        $this->createIndex(
            'idx-user_id-business',
            'business',
            'user_id',
        );

        $this->addForeignKey(
            'fk-user_id-business',
            'business',
            'user_id',
            'user',
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
        $this->dropForeignKey('fk-user_id-business', 'business');
        $this->dropIndex('idx-user_id-business', 'business');
        $this->dropTable('{{%business}}');
    }
}
