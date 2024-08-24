<?php

use yii\db\Migration;

/**
 * Class m240824_155958_add_adjusted_price_to_stock_price_table
 */
class m240824_155958_add_adjusted_price_to_stock_price_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('stock_price', 'adjusted_price', $this->decimal(10, 2)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('stock_price', 'adjusted_price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240824_155958_add_adjusted_price_to_stock_price_table cannot be reverted.\n";

        return false;
    }
    */
}
