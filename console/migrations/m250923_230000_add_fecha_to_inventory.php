<?php
use yii\db\Migration;

/**
 * Handles adding column `fecha` to table `inventory`.
 */
class m250923_230000_add_fecha_to_inventory extends Migration
{
    public function safeUp()
    {
        $this->addColumn('inventory', 'fecha', $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'));
    }

    public function safeDown()
    {
        $this->dropColumn('inventory', 'fecha');
    }
}
