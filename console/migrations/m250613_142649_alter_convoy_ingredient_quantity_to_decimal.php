<?php

use yii\db\Migration;

class m250613_142649_alter_convoy_ingredient_quantity_to_decimal extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Cambiar el tipo de datos de la columna quantity de integer a decimal
        $this->alterColumn('convoy_ingredient', 'quantity', $this->decimal(10, 3)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revertir el cambio: volver a integer
        $this->alterColumn('convoy_ingredient', 'quantity', $this->integer()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250613_142649_alter_convoy_ingredient_quantity_to_decimal cannot be reverted.\n";

        return false;
    }
    */
}
