<?php

use yii\db\Migration;

/**
 * Class m251022_000000_add_indexes_for_theoretical_yield_performance
 */
class m251022_000000_add_indexes_for_theoretical_yield_performance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Índice compuesto para standard_recipe en getTheoreticalYield
        // WHERE business_id = ? AND in_construction = 0 AND type = ? AND in_menu = 1 AND type_of_recipe = ?
        $this->createIndex(
            'idx-standard_recipe-theoretical_yield',
            'standard_recipe',
            ['business_id', 'in_construction', 'type', 'in_menu', 'type_of_recipe']
        );

        // Índice compuesto para menu en getTheoreticalYield
        // WHERE business_id = ? AND in_menu = 1
        $this->createIndex(
            'idx-menu-theoretical_yield',
            'menu',
            ['business_id', 'in_menu']
        );

        // Índice para recipe_category si no existe
        // WHERE business_id = ? OR business_id IS NULL
        // Nota: El índice en business_id ya debería existir, pero podemos optimizar con un índice parcial
        // Para MySQL, el índice existente en business_id debería ser suficiente para OR business_id IS NULL
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-standard_recipe-theoretical_yield', 'standard_recipe');
        $this->dropIndex('idx-menu-theoretical_yield', 'menu');
    }
}
