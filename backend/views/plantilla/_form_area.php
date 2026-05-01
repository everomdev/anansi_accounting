<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\AreaTrabajo;

/* @var $this yii\web\View */
/* @var $model common\models\AreaTrabajo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="area-trabajo-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true, 'placeholder' => 'Ej: Cocina, Servicio, Administración']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'orden')->textInput(['type' => 'number', 'min' => 0]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'estado')->dropDownList(AreaTrabajo::getEstadosArray()) ?>
        </div>
    </div>

    <?= $form->field($model, 'descripcion')
        ->textarea(['rows' => 3, 'placeholder' => 'Descripción del área (opcional)', 'maxlength' => 255, 'id' => 'area-descripcion'])
        ->hint('<small class="text-muted"><span id="desc-count">' . (255 - strlen($model->descripcion ?? '')) . '</span> caracteres restantes de 255</small>') ?>

    <?php $this->registerJs(<<<JS
        var descInput = document.getElementById('area-descripcion');
        var descCount = document.getElementById('desc-count');
        descInput.addEventListener('input', function () {
            var remaining = 255 - this.value.length;
            descCount.textContent = remaining;
            descCount.style.color = remaining < 20 ? '#dc3545' : '';
        });
    JS); ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="bx bx-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="bx bx-x"></i> Cancelar', ['areas'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
