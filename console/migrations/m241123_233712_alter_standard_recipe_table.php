<?php

use yii\db\Migration;

/**
 * Class m241123_233712_alter_standard_recipe_table
 */
class m241123_233712_alter_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'is_food', $this->boolean()->defaultValue(true));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'is_food');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241123_233712_alter_standard_recipe_table cannot be reverted.\n";

        return false;
    }
    */
}
