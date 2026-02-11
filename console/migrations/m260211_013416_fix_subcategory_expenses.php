<?php

use yii\db\Migration;

/**
 * Corrige la categoría "Energía y Servicios" y sus subcategorías
 */
class m260211_013416_fix_subcategory_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. Cambiar el nombre de la categoría a mayúsculas y minúsculas correctas
        $this->update('{{%expense_categories}}', [
            'name' => 'Energía y servicios',
            'description' => 'Gastos de electricidad, agua, gas y otros servicios del local'
        ], [
            'name' => 'Energía y Servicios'
        ]);
        
        echo "✅ Categoría 'Energía y Servicios' actualizada a 'Energía y servicios'.\n";
        
        // 2. Obtener todas las categorías "Energía y servicios" para todos los negocios
        $categories = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Energía y servicios')->queryAll();
        
        if (empty($categories)) {
            echo "⚠️  No se encontraron categorías 'Energía y servicios'.\n";
        } else {
            // 3. Para cada categoría, actualizar/agregar subcategorías
            foreach ($categories as $category) {
                $categoryId = $category['id'];
                $businessId = $category['business_id'];
                
                echo "Procesando Energía y servicios para negocio {$businessId}...\n";
                
                // Obtener ID de la categoría "Gastos Fijos" para este negocio (para mover Internet y Teléfono)
                $gastosFijosId = $this->db->createCommand(
                    'SELECT id FROM {{%expense_categories}} WHERE name = :name AND business_id = :business_id'
                )->bindValue(':name', 'Gastos Fijos')
                  ->bindValue(':business_id', $businessId)
                  ->queryScalar();
                
                // Subcategorías que deben existir en "Energía y servicios"
                $subcategoriasDeseadas = [
                    ['name' => 'Agua', 'order' => 1],
                    ['name' => 'Gas', 'order' => 2],
                    ['name' => 'Electricidad', 'order' => 3],
                    ['name' => 'Internet', 'order' => 4],
                    ['name' => 'Teléfono', 'order' => 5],
                    ['name' => 'TV de paga', 'order' => 6],
                    ['name' => 'Recolección de basura', 'order' => 7],
                    ['name' => 'Drenaje / servicios municipales', 'order' => 8],
                    ['name' => 'Servicios externos recurrentes', 'order' => 9],
                ];
                
                // 4. Mover Internet y Teléfono desde "Gastos Fijos" a "Energía y servicios"
                if ($gastosFijosId) {
                    $this->update('{{%expense_subcategories}}', [
                        'category_id' => $categoryId,
                        'sort_order' => 4 // Internet
                    ], [
                        'name' => 'Internet',
                        'business_id' => $businessId,
                        'category_id' => $gastosFijosId
                    ]);
                    
                    $this->update('{{%expense_subcategories}}', [
                        'category_id' => $categoryId,
                        'sort_order' => 5 // Teléfono
                    ], [
                        'name' => 'Teléfono',
                        'business_id' => $businessId,
                        'category_id' => $gastosFijosId
                    ]);
                    
                    echo "  ✅ Internet y Teléfono movidos desde Gastos Fijos.\n";
                }
                
                // 5. Insertar o actualizar subcategorías
                foreach ($subcategoriasDeseadas as $sub) {
                    // Verificar si ya existe
                    $existingId = $this->db->createCommand(
                        'SELECT id FROM {{%expense_subcategories}} 
                         WHERE name = :name AND business_id = :business_id AND category_id = :category_id'
                    )->bindValue(':name', $sub['name'])
                      ->bindValue(':business_id', $businessId)
                      ->bindValue(':category_id', $categoryId)
                      ->queryScalar();
                    
                    if ($existingId) {
                        // Actualizar el sort_order
                        $this->update('{{%expense_subcategories}}', [
                            'sort_order' => $sub['order']
                        ], [
                            'id' => $existingId
                        ]);
                        echo "  ⚠️  Subcategoría '{$sub['name']}' actualizada.\n";
                    } else {
                        // Insertar nueva
                        $this->insert('{{%expense_subcategories}}', [
                            'category_id' => $categoryId,
                            'name' => $sub['name'],
                            'description' => null,
                            'is_inventoriable' => false,
                            'sort_order' => $sub['order'],
                            'business_id' => $businessId,
                        ]);
                        echo "  ✅ Subcategoría '{$sub['name']}' creada.\n";
                    }
                }
            }
        }
        
        // 6. Corregir "Gastos Administrativos"
        $gastosAdministrativos = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Gastos Administrativos')->queryAll();
        
        if (!empty($gastosAdministrativos)) {
            foreach ($gastosAdministrativos as $category) {
                $categoryId = $category['id'];
                $businessId = $category['business_id'];
                
                echo "Procesando Gastos Administrativos para negocio {$businessId}...\n";
                
                // Subcategorías deseadas para Gastos Administrativos
                $subcategoriasAdministrativas = [
                    ['name' => 'Contabilidad', 'order' => 1],
                    ['name' => 'Software / sistemas', 'order' => 2],
                    ['name' => 'Papelería administrativa', 'order' => 3],
                    ['name' => 'Honorarios', 'order' => 4],
                    ['name' => 'Servicios de oficina', 'order' => 5],
                    ['name' => 'Servicios administrativos varios', 'order' => 6],
                ];
                
                foreach ($subcategoriasAdministrativas as $sub) {
                    // Verificar si ya existe
                    $existingId = $this->db->createCommand(
                        'SELECT id FROM {{%expense_subcategories}} 
                         WHERE name = :name AND business_id = :business_id AND category_id = :category_id'
                    )->bindValue(':name', $sub['name'])
                      ->bindValue(':business_id', $businessId)
                      ->bindValue(':category_id', $categoryId)
                      ->queryScalar();
                    
                    if ($existingId) {
                        // Actualizar el sort_order
                        $this->update('{{%expense_subcategories}}', [
                            'sort_order' => $sub['order']
                        ], [
                            'id' => $existingId
                        ]);
                        echo "  ⚠️  Subcategoría '{$sub['name']}' actualizada.\n";
                    } else {
                        // Insertar nueva
                        $this->insert('{{%expense_subcategories}}', [
                            'category_id' => $categoryId,
                            'name' => $sub['name'],
                            'description' => null,
                            'is_inventoriable' => false,
                            'sort_order' => $sub['order'],
                            'business_id' => $businessId,
                        ]);
                        echo "  ✅ Subcategoría '{$sub['name']}' creada.\n";
                    }
                }
            }
        }
        
        // 7. Corregir "Nómina / Costo de Personal"
        $nomina = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Nómina / Costo de Personal')->queryAll();
        
        if (!empty($nomina)) {
            foreach ($nomina as $category) {
                $categoryId = $category['id'];
                $businessId = $category['business_id'];
                
                echo "Procesando Nómina para negocio {$businessId}...\n";
                
                // Subcategorías deseadas para Nómina
                $subcategoriasNomina = [
                    ['name' => 'Sueldos', 'order' => 1],
                    ['name' => 'Impuestos', 'order' => 2],
                    ['name' => 'Prestaciones', 'order' => 3],
                    ['name' => 'Bonos', 'order' => 4],
                    ['name' => 'Uniformes', 'order' => 5],
                    ['name' => 'Capacitación', 'order' => 6],
                    ['name' => 'EPP', 'order' => 7],
                ];
                
                foreach ($subcategoriasNomina as $sub) {
                    $existingId = $this->db->createCommand(
                        'SELECT id FROM {{%expense_subcategories}} 
                         WHERE name = :name AND business_id = :business_id AND category_id = :category_id'
                    )->bindValue(':name', $sub['name'])
                      ->bindValue(':business_id', $businessId)
                      ->bindValue(':category_id', $categoryId)
                      ->queryScalar();
                    
                    if ($existingId) {
                        $this->update('{{%expense_subcategories}}', [
                            'sort_order' => $sub['order']
                        ], [
                            'id' => $existingId
                        ]);
                        echo "  ⚠️  Subcategoría '{$sub['name']}' actualizada.\n";
                    } else {
                        $this->insert('{{%expense_subcategories}}', [
                            'category_id' => $categoryId,
                            'name' => $sub['name'],
                            'description' => null,
                            'is_inventoriable' => false,
                            'sort_order' => $sub['order'],
                            'business_id' => $businessId,
                        ]);
                        echo "  ✅ Subcategoría '{$sub['name']}' creada.\n";
                    }
                }
            }
        }
        
        // 8. Corregir "Marketing y Ventas"
        $marketing = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Marketing y Ventas')->queryAll();
        
        if (!empty($marketing)) {
            foreach ($marketing as $category) {
                $categoryId = $category['id'];
                $businessId = $category['business_id'];
                
                echo "Procesando Marketing y Ventas para negocio {$businessId}...\n";
                
                // Subcategorías deseadas para Marketing
                $subcategoriasMarketing = [
                    ['name' => 'Publicidad', 'order' => 1],
                    ['name' => 'Promociones', 'order' => 2],
                    ['name' => 'Comisiones', 'order' => 3],
                    ['name' => 'Producción de contenido', 'order' => 4],
                    ['name' => 'Influencers / RP', 'order' => 5],
                ];
                
                foreach ($subcategoriasMarketing as $sub) {
                    $existingId = $this->db->createCommand(
                        'SELECT id FROM {{%expense_subcategories}} 
                         WHERE name = :name AND business_id = :business_id AND category_id = :category_id'
                    )->bindValue(':name', $sub['name'])
                      ->bindValue(':business_id', $businessId)
                      ->bindValue(':category_id', $categoryId)
                      ->queryScalar();
                    
                    if ($existingId) {
                        $this->update('{{%expense_subcategories}}', [
                            'sort_order' => $sub['order']
                        ], [
                            'id' => $existingId
                        ]);
                        echo "  ⚠️  Subcategoría '{$sub['name']}' actualizada.\n";
                    } else {
                        $this->insert('{{%expense_subcategories}}', [
                            'category_id' => $categoryId,
                            'name' => $sub['name'],
                            'description' => null,
                            'is_inventoriable' => false,
                            'sort_order' => $sub['order'],
                            'business_id' => $businessId,
                        ]);
                        echo "  ✅ Subcategoría '{$sub['name']}' creada.\n";
                    }
                }
            }
        }
        
        // 9. Corregir "Gastos Variables Operativos"
        $gastosVariables = $this->db->createCommand(
            'SELECT id, business_id FROM {{%expense_categories}} WHERE name = :name'
        )->bindValue(':name', 'Gastos Variables Operativos')->queryAll();
        
        if (!empty($gastosVariables)) {
            foreach ($gastosVariables as $category) {
                $categoryId = $category['id'];
                $businessId = $category['business_id'];
                
                echo "Procesando Gastos Variables Operativos para negocio {$businessId}...\n";
                
                // Renombrar "Empaques internos (no vendidos)" a "Artículos de empaque"
                $this->update('{{%expense_subcategories}}', [
                    'name' => 'Artículos de empaque',
                    'is_inventoriable' => true
                ], [
                    'business_id' => $businessId,
                    'category_id' => $categoryId,
                    'name' => 'Empaques internos (no vendidos)'
                ]);
                
                // Actualizar Papelería operativa para que sea inventariable
                $this->update('{{%expense_subcategories}}', [
                    'is_inventoriable' => true
                ], [
                    'business_id' => $businessId,
                    'category_id' => $categoryId,
                    'name' => 'Papelería operativa'
                ]);
                
                // Renombrar "Combustibles operativos (si aplica)" a "Combustibles operativos"
                $this->update('{{%expense_subcategories}}', [
                    'name' => 'Combustibles operativos'
                ], [
                    'business_id' => $businessId,
                    'category_id' => $categoryId,
                    'name' => 'Combustibles operativos (si aplica)'
                ]);
                
                // Subcategorías con orden específico
                $subcategoriasVariables = [
                    ['name' => 'Suministros de limpieza', 'order' => 1, 'inventoriable' => true],
                    ['name' => 'Suministros de baño', 'order' => 2, 'inventoriable' => true],
                    ['name' => 'Artículos de cocina', 'order' => 3, 'inventoriable' => true],
                    ['name' => 'Artículos de servicio', 'order' => 4, 'inventoriable' => true],
                    ['name' => 'Artículos de empaque', 'order' => 5, 'inventoriable' => true],
                    ['name' => 'Papelería operativa', 'order' => 6, 'inventoriable' => true],
                    ['name' => 'Combustibles operativos', 'order' => 7, 'inventoriable' => false],
                    ['name' => 'Mantenimiento correctivo', 'order' => 8, 'inventoriable' => false],
                ];
                
                foreach ($subcategoriasVariables as $sub) {
                    $existingId = $this->db->createCommand(
                        'SELECT id FROM {{%expense_subcategories}} 
                         WHERE name = :name AND business_id = :business_id AND category_id = :category_id'
                    )->bindValue(':name', $sub['name'])
                      ->bindValue(':business_id', $businessId)
                      ->bindValue(':category_id', $categoryId)
                      ->queryScalar();
                    
                    if ($existingId) {
                        $this->update('{{%expense_subcategories}}', [
                            'sort_order' => $sub['order'],
                            'is_inventoriable' => $sub['inventoriable']
                        ], [
                            'id' => $existingId
                        ]);
                        echo "  ⚠️  Subcategoría '{$sub['name']}' actualizada.\n";
                    } else {
                        $this->insert('{{%expense_subcategories}}', [
                            'category_id' => $categoryId,
                            'name' => $sub['name'],
                            'description' => null,
                            'is_inventoriable' => $sub['inventoriable'],
                            'sort_order' => $sub['order'],
                            'business_id' => $businessId,
                        ]);
                        echo "  ✅ Subcategoría '{$sub['name']}' creada.\n";
                    }
                }
            }
        }
        
        echo "✅ Correcciones aplicadas exitosamente.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "⚠️  Esta migración no se puede revertir automáticamente.\n";
        echo "   Las subcategorías han sido reorganizadas y no se puede determinar su estado original.\n";
        
        return false;
    }
}
