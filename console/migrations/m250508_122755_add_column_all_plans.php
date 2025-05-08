<?php

use yii\db\Migration;

class m250508_122755_add_column_all_plans extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Añadir columna all_plans con valor por defecto false
        $this->addColumn('coupon', 'all_plans', $this->boolean()->notNull()->defaultValue(false));

        // Actualizar registros existentes donde plan_id es NULL
        $this->update('coupon', ['all_plans' => false], 'plan_id IS NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('coupon', 'all_plans');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250508_122755_add_column_all_plans cannot be reverted.\n";

        return false;
    }
    */
}
