<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Empleado;
use common\models\AreaTrabajo;
use common\models\PlantillaPuesto;

/* @var $this yii\web\View */
/* @var $model common\models\Empleado */
/* @var $form yii\widgets\ActiveForm */

// Obtener áreas activas
$areas = AreaTrabajo::find()
    ->where(['business_id' => $model->business_id, 'estado' => AreaTrabajo::ESTADO_ACTIVO])
    ->orderBy(['orden' => SORT_ASC, 'nombre' => SORT_ASC])
    ->all();
$areasArray = ArrayHelper::map($areas, 'nombre', 'nombre');

// Obtener todos los puestos activos organizados por área
$puestos = PlantillaPuesto::find()
    ->where(['business_id' => $model->business_id, 'estado' => PlantillaPuesto::ESTADO_ACTIVO])
    ->with('areaTrabajo')
    ->orderBy(['nombre_puesto' => SORT_ASC])
    ->all();

// Organizar puestos por área para JavaScript
$puestosPorArea = [];
foreach ($puestos as $puesto) {
    $areaNombre = $puesto->areaTrabajo->nombre ?? '';
    if (!isset($puestosPorArea[$areaNombre])) {
        $puestosPorArea[$areaNombre] = [];
    }
    $puestosPorArea[$areaNombre][$puesto->nombre_puesto] = $puesto->nombre_puesto;
}

// Array de todos los puestos para el dropdown inicial
$puestosArray = ArrayHelper::map($puestos, 'nombre_puesto', 'nombre_puesto');
?>

<div class="empleado-form">

    <?php if (empty($areasArray)): ?>
        <div class="alert alert-warning">
            <i class="bx bx-error-circle"></i>
            <strong>No hay áreas de trabajo configuradas.</strong>
            Debe crear áreas y puestos en el módulo de 
            <?= Html::a('Plantilla Estándar', ['/plantilla/index'], ['target' => '_blank', 'class' => 'alert-link']) ?>
            antes de crear empleados.
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'apellido')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'area')->dropDownList($areasArray, [
                'prompt' => 'Seleccione un área',
                'id' => 'area-select'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'puesto')->dropDownList($puestosArray, [
                'prompt' => 'Seleccione un puesto',
                'id' => 'puesto-select'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_ingreso')->input('date') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_salida')->input('date') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?= $form->field($model, 'direccion')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'estado')->dropDownList([
        Empleado::ESTADO_ACTIVO => 'Activo',
        Empleado::ESTADO_INACTIVO => 'Inactivo',
    ], ['prompt' => 'Seleccione un estado']) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="bx bx-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="bx bx-x"></i> Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// JavaScript para filtrar puestos según área seleccionada
$puestosJson = json_encode($puestosPorArea);
$js = <<<JS
var puestosPorArea = $puestosJson;
var puestoActual = $('#puesto-select').val(); // Guardar el puesto actual (para edición)
var areaActual = $('#area-select').val(); // Guardar área actual

$('#area-select').on('change', function() {
    var areaSeleccionada = $(this).val();
    var puestoSelect = $('#puesto-select');
    
    // Remover mensaje de advertencia previo
    puestoSelect.parent().find('.text-muted').remove();
    
    // Limpiar opciones
    puestoSelect.empty();
    puestoSelect.append('<option value="">Seleccione un puesto</option>');
    
    // Si hay área seleccionada, cargar sus puestos
    if (areaSeleccionada && puestosPorArea[areaSeleccionada]) {
        $.each(puestosPorArea[areaSeleccionada], function(key, value) {
            puestoSelect.append('<option value="' + key + '">' + value + '</option>');
        });
        
        // Si estamos cargando la misma área que tenía originalmente, restaurar el puesto
        if (areaSeleccionada === areaActual && puestoActual) {
            puestoSelect.val(puestoActual);
        }
    }
    
    // Si no hay puestos para el área, mostrar mensaje
    if (!areaSeleccionada || !puestosPorArea[areaSeleccionada] || Object.keys(puestosPorArea[areaSeleccionada]).length === 0) {
        puestoSelect.parent().append('<small class="text-muted mt-1 d-block"><i class="bx bx-info-circle"></i> No hay puestos definidos para esta área. Puede crearlos en <a href="/plantilla/index" target="_blank">Plantilla Estándar</a>.</small>');
    }
});

// Trigger change on load para cargar puestos si ya hay área seleccionada
if ($('#area-select').val()) {
    $('#area-select').trigger('change');
}
JS;
$this->registerJs($js);
?>