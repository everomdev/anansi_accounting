<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%convoy}}`.
 */
class m230908_081058_create_convoy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%convoy}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'quantity' => $this->string()->notNull(),
            'um' => $this->string(),
            'entity' => $this->string()->notNull(),
            'entity_id' => $this->integer()->notNull()
        ]);

        $this->createIndex('idx-business_id-convoy', 'convoy', 'business_id');
        $this->addForeignKey('fk-business_id-convoy', 'convoy', 'business_id', 'business', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('idx-business_id-convoy', 'convoy');
        $this->dropIndex('fk-business_id-convoy', 'convoy');
        $this->dropTable('{{%convoy}}');
    }
}
