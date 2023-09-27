<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%consumption_center}}`.
 */
class m230907_065644_create_consumption_center_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%consumption_center}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'business_id' => $this->integer()->notNull()
        ]);

        $this->createIndex('idx-business_id-consumption_center', 'consumption_center', 'business_id');
        $this->addForeignKey('fk-business_id-consumption_center', 'consumption_center', 'business_id', 'business', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-business_id-consumption_center', 'consumption_center');
        $this->dropIndex('idx-business_id-consumption_center', 'consumption_center');
        $this->dropTable('{{%consumption_center}}');
    }
}
