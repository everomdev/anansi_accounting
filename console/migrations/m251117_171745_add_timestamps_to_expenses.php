<?php

use yii\db\Migration;

class m251117_171745_add_timestamps_to_expenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Verificar si las columnas ya existen antes de agregarlas
        $tableSchema = $this->db->getTableSchema('{{%expenses}}');
        
        if (!isset($tableSchema->columns['created_at'])) {
            $this->addColumn('{{%expenses}}', 'created_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Fecha de creación'));
        }
        
        if (!isset($tableSchema->columns['updated_at'])) {
            $this->addColumn('{{%expenses}}', 'updated_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Fecha de actualización'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar las columnas si existen
        $tableSchema = $this->db->getTableSchema('{{%expenses}}');
        
        if (isset($tableSchema->columns['updated_at'])) {
            $this->dropColumn('{{%expenses}}', 'updated_at');
        }
        
        if (isset($tableSchema->columns['created_at'])) {
            $this->dropColumn('{{%expenses}}', 'created_at');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251117_171745_add_timestamps_to_expenses cannot be reverted.\n";

        return false;
    }
    */
}
