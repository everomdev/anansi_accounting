<?php

use yii\db\Migration;

/**
 * Class m230919_170635_add_sales_to_standard_recipe
 */
class m230919_170635_add_sales_to_standard_recipe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'sales', $this->float()->defaultValue(0));
        $this->addColumn('standard_recipe', 'in_menu', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'sales');
        $this->dropColumn('standard_recipe', 'in_menu');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230919_170635_add_sales_to_standard_recipe cannot be reverted.\n";

        return false;
    }
    */
}
