<?php

use yii\db\Migration;

/**
 * Class m230916_111605_alter_standard_recipe_table
 */
class m230916_111605_alter_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe','other_specs', $this->text());

        $this->addColumn('recipe_step', 'type', $this->string()->defaultValue('procedure'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'other_specs');
        $this->dropColumn('recipe_step', 'type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230916_111605_alter_standard_recipe_table cannot be reverted.\n";

        return false;
    }
    */
}
