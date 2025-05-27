<?php

use yii\db\Migration;

/**
 * Class m250518_201025_create_monthly_sales
 */
class m250518_201025_create_monthly_sales extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%monthly_sales}}', [
            'id' => $this->primaryKey(),
            'model_type' => $this->string()->notNull()->comment('standard_recipe o menu'),
            'model_id' => $this->integer()->notNull(),
            'month' => $this->integer()->notNull(),
            'year' => $this->integer()->notNull(),
            'sales' => $this->decimal(10, 2)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Índice compuesto para búsquedas eficientes
        $this->createIndex(
            'idx-monthly_sales-model',
            '{{%monthly_sales}}',
            ['model_type', 'model_id', 'month', 'year'],
            true // unique - una entrada por modelo/mes/año
        );

        // Migrar datos existentes
        $this->execute("
            INSERT INTO monthly_sales (model_type, model_id, month, year, sales)
            SELECT 'standard_recipe', id, sales_month, YEAR(CURRENT_DATE), sales
            FROM standard_recipe 
            WHERE sales_month IS NOT NULL AND sales > 0
        ");

        $this->execute("
            INSERT INTO monthly_sales (model_type, model_id, month, year, sales)
            SELECT 'menu', id, sales_month, YEAR(CURRENT_DATE), sales
            FROM menu 
            WHERE sales_month IS NOT NULL AND sales > 0
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%monthly_sales}}');
    }
}
