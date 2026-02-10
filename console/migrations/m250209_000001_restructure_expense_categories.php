<?php

use yii\db\Migration;

/**
 * Reestructura el sistema de categorías de gastos para soportar categorías y subcategorías
 */
class m250209_000001_restructure_expense_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. Modificar tabla expense_categories para convertirla en categorías principales
        // Verificar si las columnas ya existen antes de agregarlas
        $categoryTableSchema = $this->db->getTableSchema('{{%expense_categories}}');
        
        if (!isset($categoryTableSchema->columns['is_main_category'])) {
            $this->addColumn('{{%expense_categories}}', 'is_main_category', $this->boolean()->defaultValue(true)->after('description'));
            echo "✅ Columna 'is_main_category' agregada.\n";
        } else {
            echo "⚠️  Columna 'is_main_category' ya existe, omitiendo...\n";
        }
        
        if (!isset($categoryTableSchema->columns['sort_order'])) {
            $this->addColumn('{{%expense_categories}}', 'sort_order', $this->integer()->defaultValue(0)->after('is_main_category'));
            echo "✅ Columna 'sort_order' agregada.\n";
        } else {
            echo "⚠️  Columna 'sort_order' ya existe, omitiendo...\n";
        }
        
        // 2. Crear tabla de subcategorías
        $subcategoryTableSchema = $this->db->getTableSchema('{{%expense_subcategories}}');
        
        if ($subcategoryTableSchema === null) {
            $this->createTable('{{%expense_subcategories}}', [
                'id' => $this->primaryKey(),
                'category_id' => $this->integer()->notNull()->comment('Categoría principal'),
                'name' => $this->string(255)->notNull(),
                'description' => $this->text()->null(),
                'is_inventoriable' => $this->boolean()->defaultValue(false)->comment('Si requiere control de inventario'),
                'sort_order' => $this->integer()->defaultValue(0),
                'business_id' => $this->integer()->notNull(),
                'created_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ]);

            // Crear índices
            $this->createIndex(
                'idx-expense_subcategories-category_id',
                '{{%expense_subcategories}}',
                'category_id'
            );

            $this->createIndex(
                'idx-expense_subcategories-business_id',
                '{{%expense_subcategories}}',
                'business_id'
            );

            $this->createIndex(
                'idx-expense_subcategories-name-business_id',
                '{{%expense_subcategories}}',
                ['name', 'business_id', 'category_id'],
                true
            );

            // Crear llaves foráneas
            $this->addForeignKey(
                'fk-expense_subcategories-category_id',
                '{{%expense_subcategories}}',
                'category_id',
                '{{%expense_categories}}',
                'id',
                'CASCADE'
            );

            $this->addForeignKey(
                'fk-expense_subcategories-business_id',
                '{{%expense_subcategories}}',
                'business_id',
                '{{%business}}',
                'id',
                'CASCADE'
            );
            
            echo "✅ Tabla 'expense_subcategories' creada.\n";
        } else {
            echo "⚠️  Tabla 'expense_subcategories' ya existe, omitiendo...\n";
        }

        // 3. Agregar columna subcategory_id a expenses y mantener category_id temporal
        $expenseTableSchema = $this->db->getTableSchema('{{%expenses}}');
        
        if (!isset($expenseTableSchema->columns['subcategory_id'])) {
            $this->addColumn('{{%expenses}}', 'subcategory_id', $this->integer()->null()->after('category_id'));
            
            $this->createIndex(
                'idx-expenses-subcategory_id',
                '{{%expenses}}',
                'subcategory_id'
            );

            $this->addForeignKey(
                'fk-expenses-subcategory_id',
                '{{%expenses}}',
                'subcategory_id',
                '{{%expense_subcategories}}',
                'id',
                'SET NULL'
            );
            
            echo "✅ Columna 'subcategory_id' agregada a expenses.\n";
        } else {
            echo "⚠️  Columna 'subcategory_id' ya existe en expenses, omitiendo...\n";
        }

        // 4. Poblar categorías principales estándar para todos los negocios
        $this->populateMainCategories();
        
        // 5. Poblar subcategorías estándar para todos los negocios
        $this->populateSubcategories();

        echo "✅ Estructura de categorías y subcategorías creada exitosamente.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar foreign key y columna de subcategory_id en expenses
        $this->dropForeignKey('fk-expenses-subcategory_id', '{{%expenses}}');
        $this->dropIndex('idx-expenses-subcategory_id', '{{%expenses}}');
        $this->dropColumn('{{%expenses}}', 'subcategory_id');

        // Eliminar tabla de subcategorías
        $this->dropForeignKey('fk-expense_subcategories-business_id', '{{%expense_subcategories}}');
        $this->dropForeignKey('fk-expense_subcategories-category_id', '{{%expense_subcategories}}');
        $this->dropIndex('idx-expense_subcategories-name-business_id', '{{%expense_subcategories}}');
        $this->dropIndex('idx-expense_subcategories-business_id', '{{%expense_subcategories}}');
        $this->dropIndex('idx-expense_subcategories-category_id', '{{%expense_subcategories}}');
        $this->dropTable('{{%expense_subcategories}}');

        // Eliminar columnas agregadas a expense_categories
        $this->dropColumn('{{%expense_categories}}', 'sort_order');
        $this->dropColumn('{{%expense_categories}}', 'is_main_category');

        echo "✅ Estructura de categorías y subcategorías eliminada.\n";
    }

    /**
     * Poblar categorías principales estándar
     */
    private function populateMainCategories()
    {
        $businesses = $this->db->createCommand('SELECT id FROM {{%business}}')->queryAll();

        $mainCategories = [
            ['name' => 'Nómina / Costo de Personal', 'description' => 'Gastos relacionados con sueldos, prestaciones y personal', 'sort_order' => 1],
            ['name' => 'Gastos Variables Operativos', 'description' => 'Suministros y materiales operativos variables', 'sort_order' => 2],
            ['name' => 'Energía y Servicios', 'description' => 'Electricidad, agua, gas y servicios básicos', 'sort_order' => 3],
            ['name' => 'Marketing y Ventas', 'description' => 'Publicidad, promociones y comisiones', 'sort_order' => 4],
            ['name' => 'Gastos Administrativos', 'description' => 'Honorarios, papelería y servicios administrativos', 'sort_order' => 5],
            ['name' => 'Gastos Fijos', 'description' => 'Renta, seguros, licencias y suscripciones', 'sort_order' => 6],
            ['name' => 'Gastos de Dirección', 'description' => 'Consultoría, asesoría y gastos ejecutivos', 'sort_order' => 7],
            ['name' => 'Gastos Financieros', 'description' => 'Comisiones bancarias, intereses y costos financieros', 'sort_order' => 8],
        ];

        $categoriesCreated = 0;
        $categoriesSkipped = 0;

        foreach ($businesses as $business) {
            foreach ($mainCategories as $category) {
                // Verificar si la categoría ya existe para este negocio
                $exists = $this->db->createCommand(
                    'SELECT COUNT(*) FROM {{%expense_categories}} WHERE name = :name AND business_id = :business_id'
                )->bindValue(':name', $category['name'])
                  ->bindValue(':business_id', $business['id'])
                  ->queryScalar();

                if ($exists > 0) {
                    echo "⚠️  Categoría '{$category['name']}' ya existe para negocio {$business['id']}, omitiendo...\n";
                    $categoriesSkipped++;
                    continue;
                }

                // Insertar solo si no existe
                $this->insert('{{%expense_categories}}', [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'business_id' => $business['id'],
                    'is_main_category' => true,
                    'sort_order' => $category['sort_order'],
                ]);
                $categoriesCreated++;
            }
        }

        echo "✅ Categorías principales: {$categoriesCreated} creadas, {$categoriesSkipped} omitidas.\n";
    }

    /**
     * Poblar subcategorías estándar
     */
    private function populateSubcategories()
    {
        $businesses = $this->db->createCommand('SELECT id FROM {{%business}}')->queryAll();

        foreach ($businesses as $business) {
            $businessId = $business['id'];

            // Obtener IDs de las categorías principales para este negocio
            $categories = $this->db->createCommand(
                'SELECT id, name FROM {{%expense_categories}} WHERE business_id = :business_id AND is_main_category = 1'
            )->bindValue(':business_id', $businessId)->queryAll();

            $categoryMap = [];
            foreach ($categories as $cat) {
                $categoryMap[$cat['name']] = $cat['id'];
            }

            // Subcategorías organizadas por categoría principal
            $subcategories = [
                'Nómina / Costo de Personal' => [
                    ['name' => 'Sueldos operativos', 'inventoriable' => false, 'order' => 1],
                    ['name' => 'Sueldos administrativos', 'inventoriable' => false, 'order' => 2],
                    ['name' => 'Impuestos de nómina', 'inventoriable' => false, 'order' => 3],
                    ['name' => 'Prestaciones', 'inventoriable' => false, 'order' => 4],
                    ['name' => 'Bonos y comisiones', 'inventoriable' => false, 'order' => 5],
                    ['name' => 'Uniformes', 'inventoriable' => false, 'order' => 6],
                    ['name' => 'Capacitación operativa', 'inventoriable' => false, 'order' => 7],
                    ['name' => 'Capacitación administrativa', 'inventoriable' => false, 'order' => 8],
                    ['name' => 'Equipos de protección personal', 'inventoriable' => false, 'order' => 9],
                ],
                'Gastos Variables Operativos' => [
                    ['name' => 'Suministros de limpieza', 'inventoriable' => true, 'order' => 1],
                    ['name' => 'Suministros de baño', 'inventoriable' => true, 'order' => 2],
                    ['name' => 'Artículos de cocina', 'inventoriable' => true, 'order' => 3],
                    ['name' => 'Artículos de servicio', 'inventoriable' => true, 'order' => 4],
                    ['name' => 'Papelería operativa', 'inventoriable' => false, 'order' => 5],
                    ['name' => 'Empaques internos (no vendidos)', 'inventoriable' => false, 'order' => 6],
                    ['name' => 'Combustibles operativos (si aplica)', 'inventoriable' => false, 'order' => 7],
                ],
                'Energía y Servicios' => [
                    ['name' => 'Electricidad', 'inventoriable' => false, 'order' => 1],
                    ['name' => 'Agua', 'inventoriable' => false, 'order' => 2],
                    ['name' => 'Gas', 'inventoriable' => false, 'order' => 3],
                    ['name' => 'Recolección de basura', 'inventoriable' => false, 'order' => 4],
                    ['name' => 'Drenaje / servicios municipales', 'inventoriable' => false, 'order' => 5],
                    ['name' => 'Servicios externos recurrentes', 'inventoriable' => false, 'order' => 6],
                ],
                'Marketing y Ventas' => [
                    ['name' => 'Publicidad digital', 'inventoriable' => false, 'order' => 1],
                    ['name' => 'Promociones', 'inventoriable' => false, 'order' => 2],
                    ['name' => 'Diseño gráfico', 'inventoriable' => false, 'order' => 3],
                    ['name' => 'Fotografía / video', 'inventoriable' => false, 'order' => 4],
                    ['name' => 'Influencers', 'inventoriable' => false, 'order' => 5],
                    ['name' => 'Comisiones de plataformas de delivery', 'inventoriable' => false, 'order' => 6],
                    ['name' => 'Plataformas de reservaciones', 'inventoriable' => false, 'order' => 7],
                ],
                'Gastos Administrativos' => [
                    ['name' => 'Honorarios contables', 'inventoriable' => false, 'order' => 1],
                    ['name' => 'Honorarios legales', 'inventoriable' => false, 'order' => 2],
                    ['name' => 'Servicios administrativos externos', 'inventoriable' => false, 'order' => 3],
                    ['name' => 'Papelería administrativa', 'inventoriable' => false, 'order' => 4],
                    ['name' => 'Mensajería y paquetería', 'inventoriable' => false, 'order' => 5],
                    ['name' => 'Capacitación administrativa', 'inventoriable' => false, 'order' => 6],
                ],
                'Gastos Fijos' => [
                    ['name' => 'Renta', 'inventoriable' => false, 'order' => 1],
                    ['name' => 'Mantenimiento preventivo', 'inventoriable' => false, 'order' => 2],
                    ['name' => 'Seguros', 'inventoriable' => false, 'order' => 3],
                    ['name' => 'Licencias y permisos', 'inventoriable' => false, 'order' => 4],
                    ['name' => 'Suscripciones fijas (software, POS, cámaras)', 'inventoriable' => false, 'order' => 5],
                    ['name' => 'Internet', 'inventoriable' => false, 'order' => 6],
                    ['name' => 'Teléfono', 'inventoriable' => false, 'order' => 7],
                ],
                'Gastos de Dirección' => [
                    ['name' => 'Consultoría', 'inventoriable' => false, 'order' => 1],
                    ['name' => 'Asesoría estratégica', 'inventoriable' => false, 'order' => 2],
                    ['name' => 'Coaching', 'inventoriable' => false, 'order' => 3],
                    ['name' => 'Viajes de dirección', 'inventoriable' => false, 'order' => 4],
                    ['name' => 'Representación', 'inventoriable' => false, 'order' => 5],
                    ['name' => 'Comidas ejecutivas', 'inventoriable' => false, 'order' => 6],
                ],
                'Gastos Financieros' => [
                    ['name' => 'Comisiones bancarias', 'inventoriable' => false, 'order' => 1],
                    ['name' => 'Comisiones TPV', 'inventoriable' => false, 'order' => 2],
                    ['name' => 'Intereses', 'inventoriable' => false, 'order' => 3],
                    ['name' => 'Penalizaciones', 'inventoriable' => false, 'order' => 4],
                    ['name' => 'Costos de financiamiento', 'inventoriable' => false, 'order' => 5],
                ],
            ];

            // Insertar subcategorías
            $subcategoriesCreated = 0;
            $subcategoriesSkipped = 0;
            
            foreach ($subcategories as $categoryName => $subs) {
                if (!isset($categoryMap[$categoryName])) {
                    echo "⚠️  Categoría principal '{$categoryName}' no encontrada para negocio {$businessId}, omitiendo subcategorías...\n";
                    continue;
                }

                $categoryId = $categoryMap[$categoryName];
                
                foreach ($subs as $sub) {
                    // Verificar si la subcategoría ya existe
                    $exists = $this->db->createCommand(
                        'SELECT COUNT(*) FROM {{%expense_subcategories}} 
                         WHERE name = :name AND business_id = :business_id AND category_id = :category_id'
                    )->bindValue(':name', $sub['name'])
                      ->bindValue(':business_id', $businessId)
                      ->bindValue(':category_id', $categoryId)
                      ->queryScalar();

                    if ($exists > 0) {
                        $subcategoriesSkipped++;
                        continue;
                    }

                    // Insertar solo si no existe
                    $this->insert('{{%expense_subcategories}}', [
                        'category_id' => $categoryId,
                        'name' => $sub['name'],
                        'description' => null,
                        'is_inventoriable' => $sub['inventoriable'],
                        'sort_order' => $sub['order'],
                        'business_id' => $businessId,
                    ]);
                    $subcategoriesCreated++;
                }
            }
        }

        echo "✅ Subcategorías: {$subcategoriesCreated} creadas, {$subcategoriesSkipped} omitidas.\n";
    }
}
