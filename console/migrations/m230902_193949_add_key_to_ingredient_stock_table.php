<?php

use yii\db\Migration;

/**
 * Class m230902_193949_add_key_to_ingredient_stock_table
 */
class m230902_193949_add_key_to_ingredient_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ingredient_stock', 'key', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('ingredient_stock', 'key');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230902_193949_add_key_to_ingredient_stock_table cannot be reverted.\n";

        return false;
    }
    */
}
