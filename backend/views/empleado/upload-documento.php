<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Empleado;

/* @var $this yii\web\View */
/* @var $model common\models\DocumentoEmpleado */
/* @var $empleado common\models\Empleado */

$this->title = 'Subir Documento: ' . $empleado->getNombreCompleto();
$this->params['breadcrumbs'][] = ['label' => 'Empleados', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $empleado->getNombreCompleto(), 'url' => ['view', 'id' => $empleado->id]];
$this->params['breadcrumbs'][] = 'Subir Documento';
?>
<div class="documento-empleado-form">

    <div class="card">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <?= $form->field($model, 'tipo_documento')->dropDownList(
                Empleado::getTiposDocumentosLabels(),
                ['prompt' => 'Seleccione un tipo de documento']
            )->hint('Seleccione el tipo de documento que desea cargar') ?>

            <?= $form->field($model, 'archivoFile')->fileInput()->label('Archivo')->hint('Formatos permitidos: PDF, JPG, PNG, DOC, DOCX') ?>

            <?= $form->field($model, 'fecha_vencimiento')->input('date')->hint('Opcional. Solo para documentos que expiran (ej: cursos)') ?>

            <?= $form->field($model, 'observaciones')->textarea(['rows' => 4])->hint('Opcional. Cualquier nota adicional sobre este documento') ?>

            <div class="alert alert-info">
                <h6><strong>Clasificación de Documentos:</strong></h6>
                <ul class="mb-0">
                    <li><strong class="text-danger">Críticos Legales:</strong> Identificación, NSS, Contrato</li>
                    <li><strong class="text-danger">Críticos Operativos:</strong> Curso de Higiene, Inducción</li>
                    <li><strong class="text-muted">Deseables:</strong> Comprobantes, Cartas de Recomendación, Tests, etc.</li>
                </ul>
            </div>

            <div class="form-group mt-3">
                <?= Html::submitButton('<i class="bx bx-upload"></i> Subir Documento', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="bx bx-x"></i> Cancelar', ['view', 'id' => $empleado->id], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
