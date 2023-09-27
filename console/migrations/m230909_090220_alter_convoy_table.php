<?php

use yii\db\Migration;

/**
 * Class m230909_090220_alter_convoy_table
 */
class m230909_090220_alter_convoy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('convoy', 'plates', $this->integer());
        $this->addColumn('convoy', 'type', $this->string());
        $this->addColumn('convoy', 'name', $this->string());

        $this->dropColumn('convoy', 'entity');
        $this->dropColumn('convoy', 'entity_id');

        $this->createTable('convoy_ingredient', [
            'id' => $this->primaryKey(),
            'convoy_id' => $this->integer()->notNull(),
            'entity_class' => $this->string()->notNull(),
            'entity_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()
        ]);

        $this->createIndex('idx-convoy_id-convoy_ingredient', 'convoy_ingredient', 'convoy_id');
        $this->addForeignKey('fk-convoy_id-convoy_ingredient', 'convoy_ingredient', 'convoy_id', 'convoy', 'id', 'CASCADE');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-convoy_id-convoy_ingredient', 'convoy_ingredient');
        $this->dropIndex('idx-convoy_id-convoy_ingredient', 'convoy_ingredient');
        $this->dropTable('convoy_ingredient');

        $this->addColumn('convoy', 'entity', $this->string());
        $this->addColumn('convoy', 'entity_id', $this->integer());

        $this->dropColumn('convoy', 'plates');
        $this->dropColumn('convoy', 'type');
        $this->dropColumn('convoy', 'name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230909_090220_alter_convoy_table cannot be reverted.\n";

        return false;
    }
    */
}
