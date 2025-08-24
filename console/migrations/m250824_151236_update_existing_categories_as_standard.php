<?php

use yii\db\Migration;

class m250824_151236_update_existing_categories_as_standard extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Mark all existing categories as standard (custom = 0)
        $this->update('{{%recipe_category}}', ['custom' => 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // No need to revert this change
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250824_151236_update_existing_categories_as_standard cannot be reverted.\n";

        return false;
    }
    */
}
