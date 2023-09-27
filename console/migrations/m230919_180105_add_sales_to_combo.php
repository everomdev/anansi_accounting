<?php

use yii\db\Migration;

/**
 * Class m230919_180105_add_sales_to_combo
 */
class m230919_180105_add_sales_to_combo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('menu', 'sales', $this->float()->defaultValue(0));
        $this->addColumn('menu', 'in_menu', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('menu', 'sales');
        $this->dropColumn('menu', 'in_menu');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230919_180105_add_sales_to_combo cannot be reverted.\n";

        return false;
    }
    */
}
