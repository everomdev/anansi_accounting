<?php

use yii\db\Migration;

/**
 * Class m230904_084417_add_price_to_standard_recipe
 */
class m230904_084417_add_price_to_standard_recipe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'price', $this->float(2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230904_084417_add_price_to_standard_recipe cannot be reverted.\n";

        return false;
    }
    */
}
