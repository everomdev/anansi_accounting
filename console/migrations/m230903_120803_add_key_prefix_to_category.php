<?php

use yii\db\Migration;

/**
 * Class m230903_120803_add_key_prefix_to_category
 */
class m230903_120803_add_key_prefix_to_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'key_prefix', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('category', 'key_prefix');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230903_120803_add_key_prefix_to_category cannot be reverted.\n";

        return false;
    }
    */
}
