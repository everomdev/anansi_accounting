<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%balance}}`.
 */
class m230920_184827_create_balance_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%balance}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'current_balance' => $this->float()->notNull(),
            'date' => $this->date()->notNull(),
            'expense' => $this->float()->defaultValue(0),
            'created_by' => $this->integer()
        ]);

        $this->createIndex('idx-business_id-balance', 'balance', 'business_id');
        $this->addForeignKey('fk-business_id-balance', 'balance', 'business_id', 'business', 'id', 'CASCADE');

        $this->createIndex('idx-created_by-balance', 'balance', 'created_by');
        $this->addForeignKey('fk-created_by-balance', 'balance', 'created_by', 'user', 'id', 'SET NULL');


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-business_id-balance', 'balance');
        $this->dropForeignKey('fk-business_id-balance', 'balance');
        $this->dropIndex('idx-created_by-balance', 'balance');
        $this->dropForeignKey('fk-created_by-balance', 'balance');
        $this->dropTable('{{%balance}}');
    }
}
