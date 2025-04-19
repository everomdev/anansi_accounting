<?php

use yii\db\Migration;

class m250419_153523_update_column_price extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('standard_recipe', 'price', $this->decimal(10, 2)->null());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250419_153523_update_column_price cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250419_153523_update_column_price cannot be reverted.\n";

        return false;
    }
    */
}
