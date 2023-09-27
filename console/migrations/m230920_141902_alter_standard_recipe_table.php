<?php

use yii\db\Migration;

/**
 * Class m230920_141902_alter_standard_recipe_table
 */
class m230920_141902_alter_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'custom_cost', $this->float()->defaultValue(0));
        $this->addColumn('standard_recipe', 'custom_price', $this->float()->defaultValue(0));
        $this->addColumn('menu', 'custom_cost', $this->float()->defaultValue(0));
        $this->addColumn('menu', 'custom_price', $this->float()->defaultValue(0));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'custom_cost');
        $this->dropColumn('standard_recipe', 'custom_price');
        $this->dropColumn('menu', 'custom_cost');
        $this->dropColumn('menu', 'custom_price');
    }

}
