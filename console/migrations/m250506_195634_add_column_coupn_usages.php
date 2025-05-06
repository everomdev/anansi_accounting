<?php

use yii\db\Migration;

class m250506_195634_add_column_coupn_usages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%coupon}}', 'usages', $this->integer()->defaultValue(0)->after('stripe_coupon_id'));
       
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250506_195634_add_column_coupn_usages cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250506_195634_add_column_coupn_usages cannot be reverted.\n";

        return false;
    }
    */
}
