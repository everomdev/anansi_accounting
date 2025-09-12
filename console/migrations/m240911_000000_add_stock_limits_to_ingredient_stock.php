<?php

use yii\db\Migration;

/**
 * Class m240911_000000_add_stock_limits_to_ingredient_stock
 */
class m240911_000000_add_stock_limits_to_ingredient_stock extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ingredient_stock}}', 'min_stock', $this->decimal(10, 2)->null()->comment('Stock mínimo permitido'));
        $this->addColumn('{{%ingredient_stock}}', 'max_stock', $this->decimal(10, 2)->null()->comment('Stock máximo permitido'));
        
        // Agregar índice para consultas de stock bajo/alto
        $this->createIndex(
            'idx-ingredient_stock-min_max_stock',
            '{{%ingredient_stock}}',
            ['min_stock', 'max_stock', 'quantity']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-ingredient_stock-min_max_stock', '{{%ingredient_stock}}');
        $this->dropColumn('{{%ingredient_stock}}', 'max_stock');
        $this->dropColumn('{{%ingredient_stock}}', 'min_stock');
    }
}
