<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plantilla_puesto}}`.
 */
class m260311_000004_create_plantilla_puesto_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plantilla_puesto}}', [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull(),
            'area_trabajo_id' => $this->integer()->notNull()->comment('FK a area_trabajo'),
            'nombre_puesto' => $this->string(100)->notNull()->comment('Nombre del puesto (Cocinero B, Mesero, etc.)'),
            'descripcion' => $this->text()->null()->comment('Descripción y responsabilidades (Sartenes, Caldos, etc.)'),
            'cantidad_minima' => $this->integer()->notNull()->defaultValue(1)->comment('Cantidad mínima requerida'),
            'cantidad_ideal' => $this->integer()->notNull()->defaultValue(1)->comment('Cantidad ideal de personas'),
            'salario_estimado' => $this->decimal(10, 2)->null()->comment('Salario estimado para el puesto'),
            'estado' => $this->string(20)->notNull()->defaultValue('activo')->comment('activo, inactivo'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Índices
        $this->createIndex(
            'idx-plantilla_puesto-business_id',
            '{{%plantilla_puesto}}',
            'business_id'
        );

        $this->createIndex(
            'idx-plantilla_puesto-area_trabajo_id',
            '{{%plantilla_puesto}}',
            'area_trabajo_id'
        );

        $this->createIndex(
            'idx-plantilla_puesto-estado',
            '{{%plantilla_puesto}}',
            'estado'
        );

        // Foreign keys
        $this->addForeignKey(
            'fk-plantilla_puesto-business_id',
            '{{%plantilla_puesto}}',
            'business_id',
            '{{%business}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-plantilla_puesto-area_trabajo_id',
            '{{%plantilla_puesto}}',
            'area_trabajo_id',
            '{{%area_trabajo}}',
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
        $this->dropForeignKey('fk-plantilla_puesto-area_trabajo_id', '{{%plantilla_puesto}}');
        $this->dropForeignKey('fk-plantilla_puesto-business_id', '{{%plantilla_puesto}}');
        $this->dropTable('{{%plantilla_puesto}}');
    }
}
