<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\PlantillaPuesto;

/* @var $this yii\web\View */
/* @var $model common\models\PlantillaPuesto */
/* @var $area common\models\AreaTrabajo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="plantilla-puesto-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'nombre_puesto')->textInput(['maxlength' => true, 'placeholder' => 'Ej: Cocinero B, Mesero, Cajero']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'salario_estimado')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => '0.00']) ?>
        </div>
    </div>

    <?= $form->field($model, 'descripcion')->textarea(['rows' => 3, 'placeholder' => 'Ej: Sartenes, Caldos, Barra fría']) ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'cantidad_minima')->textInput(['type' => 'number', 'min' => 0]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'cantidad_ideal')->textInput(['type' => 'number', 'min' => 0]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'estado')->dropDownList(PlantillaPuesto::getEstadosArray()) ?>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="bx bx-info-circle"></i>
        <strong>Nota:</strong> La cantidad mínima es el número crítico de personas necesarias. 
        La cantidad ideal es el número óptimo para el correcto funcionamiento.
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="bx bx-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="bx bx-x"></i> Cancelar', ['puestos', 'area_id' => $area->id], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
