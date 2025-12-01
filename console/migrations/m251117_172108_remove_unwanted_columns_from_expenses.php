<?php

use yii\db\Migration;

class m251117_172108_remove_unwanted_columns_from_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->db->getTableSchema('{{%expenses}}');
        
        // Eliminar columnas que no deberían estar en gastos
        $columnsToRemove = [
            'um', 'quantity', 'yield', 'portion_um', 'portions_per_unit', 
            'min_stock', 'max_stock', 'final_quantity'
        ];
        
        foreach ($columnsToRemove as $column) {
            if (isset($tableSchema->columns[$column])) {
                try {
                    $this->dropColumn('{{%expenses}}', $column);
                    echo "Eliminando columna: $column\n";
                } catch (\Exception $e) {
                    echo "No se pudo eliminar la columna $column: " . $e->getMessage() . "\n";
                }
            }
        }
        
        // Verificar si category_id aún existe y eliminarla
        if (isset($tableSchema->columns['category_id'])) {
            try {
                // Primero eliminar cualquier clave foránea si existe
                $this->dropForeignKey('fk-expenses-category_id', '{{%expenses}}');
            } catch (\Exception $e) {
                // La clave foránea ya no existe
            }
            
            try {
                $this->dropColumn('{{%expenses}}', 'category_id');
                echo "Eliminando columna: category_id\n";
            } catch (\Exception $e) {
                echo "No se pudo eliminar la columna category_id: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // No revertir, ya que estas columnas no deberían existir en gastos
        echo "Esta migración no se puede revertir ya que las columnas eliminadas no son apropiadas para gastos.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251117_172108_remove_unwanted_columns_from_expenses cannot be reverted.\n";

        return false;
    }
    */
}
