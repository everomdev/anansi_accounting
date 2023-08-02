<?php

use yii\db\Migration;

/**
 * Class m230617_163747_add_final_quantity_to_ingredient_stock_table
 */
class m230617_163747_add_final_quantity_to_ingredient_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ingredient_stock', 'final_quantity', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('ingredient_stock', 'final_quantity');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230617_163747_add_final_quantity_to_ingredient_stock_table cannot be reverted.\n";

        return false;
    }
    */
}
