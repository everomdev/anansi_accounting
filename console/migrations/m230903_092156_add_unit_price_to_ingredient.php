<?php

use yii\db\Migration;

/**
 * Class m230903_092156_add_unit_price_to_ingredient
 */
class m230903_092156_add_unit_price_to_ingredient extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ingredient', 'unit_price', $this->float(2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('ingredient', 'unit_price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230903_092156_add_unit_price_to_ingredient cannot be reverted.\n";

        return false;
    }
    */
}
