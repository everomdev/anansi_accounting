<?php

use common\models\Category;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */
/* @var $form yii\widgets\ActiveForm */

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);

// Obtener unidades de medida
$allUms = \common\models\UnitOfMeasurement::find()->where(['business_id' => $business['id']])->all();
$umOptions = \yii\helpers\ArrayHelper::map($allUms, 'name', 'name');

$categories = \yii\helpers\ArrayHelper::map(
    Category::find()->where([
        'or',
        ['business_id' => $business['id']],
        ['builtin' => 1]
    ])->all(),
    'id',
    'name'
);
?>

<style>
/* Solo el asterisco de campos obligatorios en rojo */
.expense-form label .asterisk,
.expense-form label .required {
    color: #dc3545 !important;
    font-weight: bold;
}
</style>

<div class="expense-form">
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-receipt"></i> 
                <?= $model->isNewRecord ? 'Nuevo Gasto' : 'Editar Gasto' ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Ej: Electricidad, Agua, Teléfono...'
                    ])->label('Nombre del Gasto <span class="required">*</span>') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'key')->textInput([
                        'maxlength' => true,
                        'readonly' => true,
                        'placeholder' => 'Se genera automáticamente'
                    ])->label('Clave') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'category_id')->widget(Select2::class, [
                        'data' => $categories,
                        'options' => [
                            'placeholder' => 'Seleccionar categoría...',
                            'id' => 'expense-category_id'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '100%'
                        ],
                    ])->label('Categoría <span class="required">*</span>') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'brand')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Ej: CFE, TELMEX...'
                    ])->label('Proveedor/Empresa') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'presentation')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Ej: Mensual, Anual...'
                    ])->label('Frecuencia/Tipo') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'um')->widget(Select2::class, [
                        'data' => $umOptions,
                        'options' => [
                            'placeholder' => 'Seleccionar unidad...',
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                            'width' => '100%'
                        ],
                    ])->label('Unidad de Medida <span class="required">*</span>') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'quantity')->textInput([
                        'type' => 'number',
                        'step' => '0.001',
                        'min' => '0',
                        'placeholder' => '0.000'
                    ])->label('Cantidad') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'min_stock')->textInput([
                        'type' => 'number',
                        'step' => '0.001',
                        'min' => '0',
                        'placeholder' => '0.000'
                    ])->label('Mínimo') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'max_stock')->textInput([
                        'type' => 'number',
                        'step' => '0.001',
                        'min' => '0',
                        'placeholder' => '0.000'
                    ])->label('Máximo') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'yield')->textInput([
                        'type' => 'number',
                        'step' => '0.001',
                        'min' => '0',
                        'max' => '1',
                        'placeholder' => '0.000'
                    ])->label('Rendimiento %') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'observations')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Observaciones adicionales...'
                    ])->label('Observaciones') ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Crear' : 'Actualizar', [
                    'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
                ]) ?>
                <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generar clave automáticamente cuando se seleccione una categoría
    const categorySelect = document.getElementById('expense-category_id');
    const keyInput = document.getElementById('expense-key');
    
    if (categorySelect && keyInput) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            if (categoryId) {
                // Hacer petición AJAX para generar la clave
                fetch('/expense/generate-key?categoryId=' + categoryId)
                    .then(response => response.json())
                    .then(data => {
                        keyInput.value = data;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    }
});
</script>
