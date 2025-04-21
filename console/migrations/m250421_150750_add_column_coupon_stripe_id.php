<?php

use yii\db\Migration;

class m250421_150750_add_column_coupon_stripe_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%coupon}}', 'stripe_coupon_id', $this->string(255)->null()->comment('ID del cupón en Stripe'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250421_150750_add_column_coupon_stripe_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250421_150750_add_column_coupon_stripe_id cannot be reverted.\n";

        return false;
    }
    */
}
