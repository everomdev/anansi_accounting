<?php

use yii\db\Migration;

/**
 * Class m231008_113123_add_intro_to_plan_table
 */
class m231008_113123_add_intro_to_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('plan', 'intro', $this->text());
        $this->alterColumn('plan', 'description', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('plan', 'intro');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231008_113123_add_intro_to_plan_table cannot be reverted.\n";

        return false;
    }
    */
}
