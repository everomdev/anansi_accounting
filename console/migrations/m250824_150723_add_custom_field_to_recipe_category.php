<?php

use yii\db\Migration;

class m250824_150723_add_custom_field_to_recipe_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%recipe_category}}', 'custom', $this->integer()->notNull()->defaultValue(0)->comment('Flag to indicate if this is a custom category (1) or standard category (0)'));
        $this->createIndex('idx-recipe_category-custom', '{{%recipe_category}}', 'custom');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-recipe_category-custom', '{{%recipe_category}}');
        $this->dropColumn('{{%recipe_category}}', 'custom');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250824_150723_add_custom_field_to_recipe_category cannot be reverted.\n";

        return false;
    }
    */
}
