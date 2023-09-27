<?php

use yii\db\Migration;

/**
 * Class m230908_113934_add_monthly_plate_sales_to_business
 */
class m230908_113934_add_monthly_plate_sales_to_business extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('business', 'monthly_plate_sales', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('business', 'monthly_plate_sales');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230908_113934_add_monthly_plate_sales_to_business cannot be reverted.\n";

        return false;
    }
    */
}
