<?php

use yii\db\Migration;

class m260212_144121_fix_gastos_administrativos extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Corregir el nombre de la categoría "Gastos Administrativos" si está en mayúsculas
        echo "Corrigiendo nombre de categoría Gastos Administrativos...\n";
        
        $this->update('{{%expense_categories}}', [
            'name' => 'Gastos Administrativos'
        ], [
            'name' => 'GASTOS ADMINISTRATIVOS'
        ]);
        
        // También verificar variantes
        $this->update('{{%expense_categories}}', [
            'name' => 'Gastos Administrativos'
        ], [
            'name' => 'Gastos administrativos'
        ]);
        
        echo "✅ Categoría 'Gastos Administrativos' corregida.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260212_144121_fix_gastos_administrativos cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260212_144121_fix_gastos_administrativos cannot be reverted.\n";

        return false;
    }
    */
}
