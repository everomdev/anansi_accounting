<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%menu}}`.
 */
class m230719_134843_add_prices_columns_to_menu_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('menu', 'total_cost_last_price', $this->float(2));
        $this->addColumn('menu', 'total_cost_avg_price', $this->float(2));
        $this->addColumn('menu', 'total_cost_higher_price', $this->float(2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('menu', 'total_cost_last_price');
        $this->dropColumn('menu', 'total_cost_avg_price');
        $this->dropColumn('menu', 'total_cost_higher_price');
    }
}
