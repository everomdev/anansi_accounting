<?php

use yii\db\Migration;

class m251001_154231_create_table_logs_inventario extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%logs_inventario}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('ID del usuario que ejecutó el ajuste'),
            'ingredient_stock_id' => $this->integer()->notNull()->comment('ID del insumo ajustado'),
            'business_id' => $this->integer()->notNull()->comment('ID del negocio'),
            'fecha_ajuste' => $this->datetime()->notNull()->comment('Fecha y hora del ajuste'),
            'existencia_anterior' => $this->decimal(10, 2)->defaultValue(0)->comment('Existencia anterior'),
            'existencia_nueva' => $this->decimal(10, 2)->defaultValue(0)->comment('Existencia nueva (ajustada)'),
            'motivo' => $this->text()->comment('Motivo del ajuste (opcional)'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Agregar índices
        $this->createIndex(
            'idx-logs_inventario-user_id',
            '{{%logs_inventario}}',
            'user_id'
        );

        $this->createIndex(
            'idx-logs_inventario-ingredient_stock_id',
            '{{%logs_inventario}}',
            'ingredient_stock_id'
        );

        $this->createIndex(
            'idx-logs_inventario-business_id',
            '{{%logs_inventario}}',
            'business_id'
        );

        $this->createIndex(
            'idx-logs_inventario-fecha_ajuste',
            '{{%logs_inventario}}',
            'fecha_ajuste'
        );

        // Agregar foreign keys
        $this->addForeignKey(
            'fk-logs_inventario-user_id',
            '{{%logs_inventario}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-logs_inventario-ingredient_stock_id',
            '{{%logs_inventario}}',
            'ingredient_stock_id',
            '{{%ingredient_stock}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-logs_inventario-business_id',
            '{{%logs_inventario}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar foreign keys
        $this->dropForeignKey(
            'fk-logs_inventario-user_id',
            '{{%logs_inventario}}'
        );

        $this->dropForeignKey(
            'fk-logs_inventario-ingredient_stock_id',
            '{{%logs_inventario}}'
        );

        $this->dropForeignKey(
            'fk-logs_inventario-business_id',
            '{{%logs_inventario}}'
        );

        // Eliminar índices
        $this->dropIndex(
            'idx-logs_inventario-user_id',
            '{{%logs_inventario}}'
        );

        $this->dropIndex(
            'idx-logs_inventario-ingredient_stock_id',
            '{{%logs_inventario}}'
        );

        $this->dropIndex(
            'idx-logs_inventario-business_id',
            '{{%logs_inventario}}'
        );

        $this->dropIndex(
            'idx-logs_inventario-fecha_ajuste',
            '{{%logs_inventario}}'
        );

        // Eliminar tabla
        $this->dropTable('{{%logs_inventario}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251001_154231_create_table_logs_inventario cannot be reverted.\n";

        return false;
    }
    */
}
