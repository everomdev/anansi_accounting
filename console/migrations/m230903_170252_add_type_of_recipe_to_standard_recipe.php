<?php

use yii\db\Migration;

/**
 * Class m230903_170252_add_type_of_recipe_to_standard_recipe
 */
class m230903_170252_add_type_of_recipe_to_standard_recipe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'type_of_recipe', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'type_of_recipe');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230903_170252_add_type_of_recipe_to_standard_recipe cannot be reverted.\n";

        return false;
    }
    */
}
