<?php

use yii\db\Migration;

class m250826_160616_add_type_to_unit_of_measurement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar columna type para distinguir entre unidades de compra y cocina
        $this->addColumn('unit_of_measurement', 'type', $this->string(20)->notNull()->defaultValue('kitchen')->comment('Tipo de unidad: kitchen (cocina) o purchase (compra)'));
        
        // Crear índice para mejorar performance en consultas por tipo
        $this->createIndex('idx_unit_of_measurement_type', 'unit_of_measurement', 'type');
        
        // Actualizar unidades existentes - por defecto serán de cocina
        // Las unidades comunes de compra se pueden identificar y actualizar manualmente después
        $this->execute("UPDATE unit_of_measurement SET type = 'kitchen' WHERE type IS NULL OR type = ''");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar índice
        $this->dropIndex('idx_unit_of_measurement_type', 'unit_of_measurement');
        
        // Eliminar columna
        $this->dropColumn('unit_of_measurement', 'type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250826_160616_add_type_to_unit_of_measurement cannot be reverted.\n";

        return false;
    }
    */
}
