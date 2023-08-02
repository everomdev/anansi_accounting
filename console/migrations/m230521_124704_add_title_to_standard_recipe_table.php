<?php

use yii\db\Migration;

/**
 * Class m230521_124704_add_title_to_standard_recipe_table
 */
class m230521_124704_add_title_to_standard_recipe_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('standard_recipe', 'title', $this->string()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('standard_recipe', 'title');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230521_124704_add_title_to_standard_recipe_table cannot be reverted.\n";

        return false;
    }
    */
}
