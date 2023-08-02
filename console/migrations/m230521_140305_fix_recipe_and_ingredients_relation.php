<?php

use yii\db\Migration;

/**
 * Class m230521_140305_fix_recipe_and_ingredients_relation
 */
class m230521_140305_fix_recipe_and_ingredients_relation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-ingredient_id-ingredient_standard_recipe', 'ingredient_standard_recipe');
        $this->addForeignKey(
            'fk-ingredient_id-ingredient_standard_recipe',
            'ingredient_standard_recipe',
            'ingredient_id',
            'ingredient_stock',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230521_140305_fix_recipe_and_ingredients_relation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230521_140305_fix_recipe_and_ingredients_relation cannot be reverted.\n";

        return false;
    }
    */
}
