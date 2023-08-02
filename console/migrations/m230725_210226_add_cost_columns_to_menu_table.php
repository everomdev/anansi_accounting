<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%menu}}`.
 */
class m230725_210226_add_cost_columns_to_menu_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('menu', 'cost_percent_last_price', $this->float());
        $this->addColumn('menu', 'cost_percent_avg_price', $this->float());
        $this->addColumn('menu', 'cost_percent_higher_price', $this->float());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('menu', 'cost_percent_last_price');
        $this->dropColumn('menu', 'cost_percent_avg_price');
        $this->dropColumn('menu', 'cost_percent_higher_price');
    }
}
