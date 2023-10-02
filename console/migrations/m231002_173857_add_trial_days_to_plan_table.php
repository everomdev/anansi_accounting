<?php

use yii\db\Migration;

/**
 * Class m231002_173857_add_trial_days_to_plan_table
 */
class m231002_173857_add_trial_days_to_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('plan', 'trial_days', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('plan', 'trial_days');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231002_173857_add_trial_days_to_plan_table cannot be reverted.\n";

        return false;
    }
    */
}
