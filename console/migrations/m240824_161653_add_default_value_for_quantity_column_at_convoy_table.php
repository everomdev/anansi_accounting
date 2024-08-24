<?php

use yii\db\Migration;

/**
 * Class m240824_161653_add_default_value_for_quantity_column_at_convoy_table
 */
class m240824_161653_add_default_value_for_quantity_column_at_convoy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('convoy', 'quantity', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('convoy', 'quantity', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240824_161653_add_default_value_for_quantity_column_at_convoy_table cannot be reverted.\n";

        return false;
    }
    */
}
