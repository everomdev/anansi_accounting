<?php

use yii\db\Migration;

/**
 * Class m230523_190323_add_unit_price_to_purchase_table
 */
class m230523_190323_add_unit_price_to_purchase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('purchase', 'unit_price', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('purchase', 'unit_price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230523_190323_add_unit_price_to_purchase_table cannot be reverted.\n";

        return false;
    }
    */
}
