<?php

use yii\db\Migration;

/**
 * Adds `is_food` column to `recipe_category` table.
 * This column defines whether the category (family) belongs to ALIMENTOS or BEBIDAS.
 * Only applies to TYPE_MAIN categories (for recipes, not sub-recipes).
 */
class m260426_000001_add_is_food_to_recipe_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('recipe_category', 'is_food', $this->boolean()->null()->defaultValue(null)->after('type'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('recipe_category', 'is_food');
    }
}
