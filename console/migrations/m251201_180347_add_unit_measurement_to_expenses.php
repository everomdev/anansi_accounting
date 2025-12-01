<?php

use yii\db\Migration;

class m251201_180347_add_unit_measurement_to_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%expenses}}', 'unit_measurement_id', $this->integer()->null()->after('amount'));

        // Crear índice
        $this->createIndex(
            'idx-expenses-unit_measurement_id',
            '{{%expenses}}',
            'unit_measurement_id'
        );

        // Crear llave foránea
        $this->addForeignKey(
            'fk-expenses-unit_measurement_id',
            '{{%expenses}}',
            'unit_measurement_id',
            '{{%expense_unit_measurements}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-expenses-unit_measurement_id', '{{%expenses}}');
        $this->dropIndex('idx-expenses-unit_measurement_id', '{{%expenses}}');
        $this->dropColumn('{{%expenses}}', 'unit_measurement_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251201_180347_add_unit_measurement_to_expenses cannot be reverted.\n";

        return false;
    }
    */
}
