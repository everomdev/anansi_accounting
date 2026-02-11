<?php

use yii\db\Migration;

class m260211_015604_fix_subcategory_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. Agregar descripción "Del local" a todas las subcategorías de "Energía y servicios"
        $energiaCategories = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Energía y servicios')->queryAll();
        
        foreach ($energiaCategories as $category) {
            $categoryId = $category['id'];
            $businessId = $category['business_id'];
            
            echo "Actualizando descripciones de Energía y servicios para negocio {$businessId}...\n";
            
            $this->update('{{%expense_subcategories}}', [
                'description' => 'Del local'
            ], [
                'category_id' => $categoryId,
                'business_id' => $businessId
            ]);
        }
        
        echo "✅ Descripciones de Energía y servicios actualizadas.\n";
        
        // 2. Eliminar la categoría "Gastos Variables" o "Gastos operativos flexibles" duplicada
        $gastosVariablesDuplicados = $this->db->createCommand(
            'SELECT id, business_id, name FROM {{%expense_categories}} 
             WHERE name IN (:name1)'
        )->bindValue(':name1', 'GASTOS VARIABLES (Gastos Operativos Flexibles)')
          ->queryAll();
        
        foreach ($gastosVariablesDuplicados as $catDuplicada) {
            echo "Eliminando categoría duplicada '{$catDuplicada['name']}' del negocio {$catDuplicada['business_id']}...\n";
            
            // Primero eliminar subcategorías asociadas
            $this->delete('{{%expense_subcategories}}', [
                'category_id' => $catDuplicada['id'],
                'business_id' => $catDuplicada['business_id']
            ]);
            
            // Luego eliminar la categoría
            $this->delete('{{%expense_categories}}', [
                'id' => $catDuplicada['id']
            ]);
        }
        
        echo "✅ Categorías duplicadas 'Gastos Variables' eliminadas.\n";
        
        // 3. Eliminar subcategorías duplicadas en Nómina (las nuevas con nombres cortos)
        $nominaCategories = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Nómina / Costo de Personal')->queryAll();
        
        $subcategoriasDuplicadasNomina = ['Sueldos', 'Impuestos', 'Bonos', 'Capacitación', 'EPP'];
        
        foreach ($nominaCategories as $category) {
            $categoryId = $category['id'];
            $businessId = $category['business_id'];
            
            echo "Eliminando subcategorías duplicadas de Nómina para negocio {$businessId}...\n";
            
            foreach ($subcategoriasDuplicadasNomina as $nombreDuplicado) {
                $deleted = $this->delete('{{%expense_subcategories}}', [
                    'category_id' => $categoryId,
                    'business_id' => $businessId,
                    'name' => $nombreDuplicado
                ]);
                
                if ($deleted > 0) {
                    echo "  ✅ Eliminada subcategoría '{$nombreDuplicado}'.\n";
                }
            }
        }
        
        // 4. Eliminar subcategorías duplicadas en Marketing (las nuevas con nombres diferentes)
        $marketingCategories = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Marketing y Ventas')->queryAll();
        
        $subcategoriasDuplicadasMarketing = [
            'Publicidad',
            'Comisiones',
            'Producción de contenido',
            'Influencers / RP'
        ];
        
        foreach ($marketingCategories as $category) {
            $categoryId = $category['id'];
            $businessId = $category['business_id'];
            
            echo "Revisando subcategorías de Marketing para negocio {$businessId}...\n";
            
            // Verificar si existen las originales antes de borrar
            $originales = [
                'Publicidad digital',
                'Diseño gráfico',
                'Promociones',
                'Fotografía / video',
                'Influencers',
                'Comisiones de plataformas de delivery',
                'Plataformas de reservaciones'
            ];
            
            $existenOriginales = $this->db->createCommand(
                'SELECT COUNT(*) FROM {{%expense_subcategories}} 
                 WHERE category_id = :category_id AND business_id = :business_id 
                 AND name IN (:n1, :n2, :n3, :n4, :n5, :n6, :n7)'
            )->bindValue(':category_id', $categoryId)
              ->bindValue(':business_id', $businessId)
              ->bindValue(':n1', $originales[0])
              ->bindValue(':n2', $originales[1])
              ->bindValue(':n3', $originales[2])
              ->bindValue(':n4', $originales[3])
              ->bindValue(':n5', $originales[4])
              ->bindValue(':n6', $originales[5])
              ->bindValue(':n7', $originales[6])
              ->queryScalar();
            
            // Solo eliminar las nuevas si existen las originales
            if ($existenOriginales > 0) {
                foreach ($subcategoriasDuplicadasMarketing as $nombreDuplicado) {
                    $deleted = $this->delete('{{%expense_subcategories}}', [
                        'category_id' => $categoryId,
                        'business_id' => $businessId,
                        'name' => $nombreDuplicado
                    ]);
                    
                    if ($deleted > 0) {
                        echo "  ✅ Eliminada subcategoría duplicada '{$nombreDuplicado}'.\n";
                    }
                }
            } else {
                echo "  ⚠️  No se encontraron subcategorías originales, conservando las nuevas.\n";
            }
        }
        
        echo "✅ Limpieza de duplicados completada.\n";
        
        // 5. Corregir sort_order de categorías para mantener el orden correcto
        echo "Actualizando sort_order de categorías...\n";
        
        $categoryOrder = [
            'Nómina / Costo de Personal' => 1,
            'Gastos Variables Operativos' => 2,
            'Energía y servicios' => 3,
            'Gastos Administrativos' => 4,
            'Marketing y Ventas' => 5,
            'Gastos Fijos' => 6,
            'Gastos de Dirección' => 7,
            'Gastos Financieros' => 8,
        ];
        
        foreach ($categoryOrder as $categoryName => $order) {
            $this->update('{{%expense_categories}}', [
                'sort_order' => $order
            ], [
                'name' => $categoryName
            ]);
        }
        
        echo "✅ sort_order de categorías actualizado.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260211_015604_fix_subcategory_expenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260211_015604_fix_subcategory_expenses cannot be reverted.\n";

        return false;
    }
    */
}
