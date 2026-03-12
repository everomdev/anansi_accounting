<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%area_trabajo}}`.
 */
class m260311_000003_create_area_trabajo_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%area_trabajo}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'nombre' => $this->string(100)->notNull()->comment('Nombre del área (Cocina, Servicio, etc.)'),
            'descripcion' => $this->text()->null()->comment('Descripción del área'),
            'orden' => $this->integer()->defaultValue(0)->comment('Orden de visualización'),
            'estado' => $this->string(20)->notNull()->defaultValue('activo')->comment('activo, inactivo'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Índices
        $this->createIndex(
            'idx-area_trabajo-business_id',
            '{{%area_trabajo}}',
            'business_id'
        );

        $this->createIndex(
            'idx-area_trabajo-estado',
            '{{%area_trabajo}}',
            'estado'
        );

        // Foreign key
        $this->addForeignKey(
            'fk-area_trabajo-business_id',
            '{{%area_trabajo}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-area_trabajo-business_id', '{{%area_trabajo}}');
        $this->dropTable('{{%area_trabajo}}');
    }
}
