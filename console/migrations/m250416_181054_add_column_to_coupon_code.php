<?php

use yii\db\Migration;

class m250416_181054_add_column_to_coupon_code extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('coupon', 'expiration_date', $this->integer()->notNull()->defaultValue(0)->comment('Expiration Date in Unix timestamp format for Stripe compatibility')->after('type'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250416_181054_add_column_to_coupon_code cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250416_181054_add_column_to_coupon_code cannot be reverted.\n";

        return false;
    }
    */
}
