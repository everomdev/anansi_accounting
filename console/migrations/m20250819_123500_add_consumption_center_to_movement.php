<?php

use yii\db\Migration;

/**
 * Handles adding consumption_center_id to table `{{%movement}}`.
 */
class m20250819_123500_add_consumption_center_to_movement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar columna para el centro de consumo
        $this->addColumn('{{%movement}}', 'consumption_center_id', $this->integer()->null());
        
        // Crear índice para mejor rendimiento
        $this->createIndex("idx-consumption_center_id-movement", "movement", "consumption_center_id");
        
        // Agregar foreign key si existe la tabla consumption_center
        $this->addForeignKey(
            "fk-consumption_center_id-movement",
            "movement",
            "consumption_center_id",
            "consumption_center",
            "id",
            "SET NULL",
            "CASCADE"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar foreign key
        $this->dropForeignKey("fk-consumption_center_id-movement", "movement");
        
        // Eliminar índice
        $this->dropIndex("idx-consumption_center_id-movement", "movement");
        
        // Eliminar columna
        $this->dropColumn('{{%movement}}', 'consumption_center_id');
    }
}
