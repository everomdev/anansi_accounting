<?php

use yii\db\Migration;

class m260603_175216_update_datos_documentos extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         // Agregar nuevos tipos de documentos al ENUM
        $this->execute("ALTER TABLE `documentos_empleado` MODIFY `tipo_documento` ENUM(
            'identificacion',
            'numero_seguro_social',
            'contrato',
            'curp',
            'constancia_situacion_fiscal',
            'acta_nacimiento',
            'clabe',
            'cv',
            'curso_higiene',
            'induccion',
            'cartas_recomendacion',
            'comprobante_domicilio',
            'estudios',
            'certificado_medico',
            'comprobante_cursos',
            'test_personalidad',
            'test_psicometrico',
            'otros'
        ) NOT NULL");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260603_175216_update_datos_documentos cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260603_175216_update_datos_documentos cannot be reverted.\n";

        return false;
    }
    */
}
