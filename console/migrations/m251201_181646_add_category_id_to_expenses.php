<?php

use yii\db\Migration;

class m251201_181646_add_category_id_to_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar columna category_id
        $this->addColumn('{{%expenses}}', 'category_id', $this->integer()->null()->after('unit_measurement_id'));

        // Crear índice
        $this->createIndex(
            'idx-expenses-category_id',
            '{{%expenses}}',
            'category_id'
        );

        // Crear llave foránea
        $this->addForeignKey(
            'fk-expenses-category_id',
            '{{%expenses}}',
            'category_id',
            '{{%expense_categories}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar llave foránea
        $this->dropForeignKey('fk-expenses-category_id', '{{%expenses}}');

        // Eliminar índice
        $this->dropIndex('idx-expenses-category_id', '{{%expenses}}');

        // Eliminar columna
        $this->dropColumn('{{%expenses}}', 'category_id');
    }
}
