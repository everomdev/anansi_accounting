<?php

use yii\db\Migration;

class m250518_200025_add_sales_month_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar columna sales_month a la tabla standard_recipe
        $this->addColumn('standard_recipe', 'sales_month', $this->integer()->defaultValue(null));

        // Agregar columna sales_month a la tabla menu
        $this->addColumn('menu', 'sales_month', $this->integer()->defaultValue(null));

        // Inicializar los valores existentes al mes actual
        $currentMonth = date('n'); // Obtiene el mes actual (1-12)
        $this->update('standard_recipe', ['sales_month' => $currentMonth]);
        $this->update('menu', ['sales_month' => $currentMonth]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar columna de standard_recipe
        $this->dropColumn('standard_recipe', 'sales_month');

        // Eliminar columna de menu
        $this->dropColumn('menu', 'sales_month');

        return true;
    }
}
