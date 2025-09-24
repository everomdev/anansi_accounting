<?php

use yii\db\Migration;

class m250924_001057_add_index_to_inventory_fecha extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-inventory-fecha',
            'inventory',
            'fecha'
        );
    }

    public function safeDown()
    {
        $this->dropIndex('idx-inventory-fecha', 'inventory');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250924_001057_add_index_to_inventory_fecha cannot be reverted.\n";

        return false;
    }
    */
}
