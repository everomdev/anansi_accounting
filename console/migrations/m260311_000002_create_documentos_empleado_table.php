<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%documentos_empleado}}`.
 */
class m260311_000002_create_documentos_empleado_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%documentos_empleado}}', [
            'id' => $this->primaryKey(),
            'empleado_id' => $this->integer()->notNull(),
            'tipo_documento' => "ENUM(
                'comprobante_domicilio',
                'estudios',
                'identificacion',
                'cartas_recomendacion',
                'acta_nacimiento',
                'identificacion_fotografia',
                'numero_seguro_social',
                'contrato',
                'comprobante_cursos',
                'test_personalidad',
                'test_psicometrico',
                'curso_higiene',
                'induccion'
            ) NOT NULL",
            'archivo' => $this->string(255)->notNull(),
            'nombre_original' => $this->string(255)->notNull(),
            'fecha_carga' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'fecha_vencimiento' => $this->date()->null()->comment('Para documentos que expiran'),
            'observaciones' => $this->text()->null(),
            'uploaded_by' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Índices
        $this->createIndex(
            'idx-documentos_empleado-empleado_id',
            '{{%documentos_empleado}}',
            'empleado_id'
        );

        $this->createIndex(
            'idx-documentos_empleado-tipo_documento',
            '{{%documentos_empleado}}',
            'tipo_documento'
        );

        // Foreign keys
        $this->addForeignKey(
            'fk-documentos_empleado-empleado_id',
            '{{%documentos_empleado}}',
            'empleado_id',
            '{{%empleados}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-documentos_empleado-uploaded_by',
            '{{%documentos_empleado}}',
            'uploaded_by',
            '{{%user}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-documentos_empleado-uploaded_by', '{{%documentos_empleado}}');
        $this->dropForeignKey('fk-documentos_empleado-empleado_id', '{{%documentos_empleado}}');
        $this->dropTable('{{%documentos_empleado}}');
    }
}
