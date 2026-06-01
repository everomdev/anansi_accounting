<?php

use yii\db\Migration;

class m260601_084007_add_valores_documentos_empleados extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar nuevos tipos de documentos al ENUM
        $this->execute("ALTER TABLE `documentos_empleado` MODIFY `tipo_documento` ENUM(
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
            'induccion',
            'clabe',
            'constancia_situacion_fiscal',
            'curp'
        ) NOT NULL");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260601_084007_add_valores_documentos_empleados cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260601_084007_add_valores_documentos_empleados cannot be reverted.\n";

        return false;
    }
    */
}
