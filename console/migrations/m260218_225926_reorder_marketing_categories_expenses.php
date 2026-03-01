<?php

use yii\db\Migration;

class m260218_225926_reorder_marketing_categories_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Paso 1: Renombrar la categoría "Marketing y Ventas" a "Gastos Comerciales"
        echo "Renombrando categoría 'Marketing y Ventas' a 'Gastos Comerciales'...\n";
        $this->update('{{%expense_categories}}', [
            'name' => 'Gastos Comerciales'
        ], [
            'name' => 'Marketing y Ventas'
        ]);
        echo "✅ Categoría renombrada.\n\n";
        
        // Paso 2: Reordenar subcategorías de "Gastos Comerciales"
        $gastosComerciales = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Gastos Comerciales')->queryAll();
        
        if (!empty($gastosComerciales)) {
            foreach ($gastosComerciales as $category) {
                $categoryId = $category['id'];
                $businessId = $category['business_id'];
                
                echo "Reordenando subcategorías de Gastos Comerciales para negocio {$businessId}...\n";
                
                // Nuevo orden deseado según especificaciones
                $ordenDeseado = [
                    'Publicidad digital' => 1,
                    'Promociones' => 2,
                    'Diseño gráfico' => 3,
                    'Fotografía / video' => 4,
                    'Influencers' => 5,
                    'Comisiones de plataformas de delivery' => 6,
                    'Plataformas de reservaciones' => 7,
                ];
                
                foreach ($ordenDeseado as $nombre => $orden) {
                    $updated = $this->update('{{%expense_subcategories}}', [
                        'sort_order' => $orden
                    ], [
                        'business_id' => $businessId,
                        'category_id' => $categoryId,
                        'name' => $nombre
                    ]);
                    
                    if ($updated > 0) {
                        echo "  ✅ '{$nombre}' actualizada a orden {$orden}\n";
                    } else {
                        echo "  ⚠️  '{$nombre}' no encontrada\n";
                    }
                }
            }
        }
        
        echo "\n✅ Renombramiento y reordenamiento completado.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "Revirtiendo cambios...\n";
        
        // Revertir el nombre de la categoría
        $this->update('{{%expense_categories}}', [
            'name' => 'Marketing y Ventas'
        ], [
            'name' => 'Gastos Comerciales'
        ]);
        
        echo "✅ Categoría revertida a 'Marketing y Ventas'.\n";
        
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260218_225926_reorder_marketing_categories_expenses cannot be reverted.\n";

        return false;
    }
    */
}
