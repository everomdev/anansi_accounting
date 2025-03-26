<?php

use yii\db\Migration;

class m250325_222555_add_column_to_ingredient extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ingredient_standard_recipe', 'exclude_from_cost', $this->boolean()->defaultValue(false)->after('quantity'));
        $this->addColumn('ingredient_standard_recipe', 'cost_percentage', $this->integer()->defaultValue(0)->after('exclude_from_cost'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250325_222555_add_column_to_ingredient cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250325_222555_add_column_to_ingredient cannot be reverted.\n";

        return false;
    }
    */
}
