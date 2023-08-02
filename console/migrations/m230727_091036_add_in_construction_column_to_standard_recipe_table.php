<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%standard_recipe}}`.
 */
class m230727_091036_add_in_construction_column_to_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'in_construction', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'in_construction');
    }
}
