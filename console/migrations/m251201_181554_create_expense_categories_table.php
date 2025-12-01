<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%expense_categories}}`.
 */
class m251201_181554_create_expense_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Crear tabla de categorías de gastos
        $this->createTable('{{%expense_categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'business_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Crear índices
        $this->createIndex(
            'idx-expense_categories-business_id',
            '{{%expense_categories}}',
            'business_id'
        );

        $this->createIndex(
            'idx-expense_categories-name-business_id',
            '{{%expense_categories}}',
            ['name', 'business_id'],
            true
        );

        // Crear llave foránea
        $this->addForeignKey(
            'fk-expense_categories-business_id',
            '{{%expense_categories}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE'
        );

        // Insertar categorías por defecto para cada negocio
        $businesses = $this->db->createCommand('SELECT id FROM {{%business}}')->queryAll();
        
        $defaultCategories = [
            ['name' => 'GASTOS VARIABLES (Gastos Operativos Flexibles)', 'description' => 'Gastos que varían según la operación del negocio'],
            ['name' => 'ENERGÍA Y SERVICIOS', 'description' => 'Gastos de electricidad, agua, gas y otros servicios'],
            ['name' => 'GASTOS ADMINISTRATIVOS', 'description' => 'Gastos relacionados con la administración del negocio'],
            ['name' => 'Gastos Fijos', 'description' => 'Gastos que se mantienen constantes cada período'],
        ];

        foreach ($businesses as $business) {
            foreach ($defaultCategories as $category) {
                $this->insert('{{%expense_categories}}', [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'business_id' => $business['id'],
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar llave foránea
        $this->dropForeignKey('fk-expense_categories-business_id', '{{%expense_categories}}');

        // Eliminar índices
        $this->dropIndex('idx-expense_categories-name-business_id', '{{%expense_categories}}');
        $this->dropIndex('idx-expense_categories-business_id', '{{%expense_categories}}');

        // Eliminar tabla
        $this->dropTable('{{%expense_categories}}');
    }
}
