<?php

use common\models\Provider;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */
/* @var $form yii\widgets\ActiveForm */

$business = \backend\helpers\RedisKeys::getBusiness();

// Obtener proveedores del negocio
$providers = \yii\helpers\ArrayHelper::map(
    Provider::find()->where(['business_id' => $business->id])->all(),
    'id',
    'business_name'
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
    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Ej: Electricidad, Agua, Teléfono, Renta...'
                    ])->label('Nombre del Gasto <span class="required">*</span>') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'placeholder' => '0.00'
                    ])->label('Monto <span class="required">*</span>') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'key')->textInput([
                        'maxlength' => true,
                        'readonly' => true,
                        'placeholder' => 'Se genera automáticamente'
                    ])->label('Clave') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'expense_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'Seleccionar fecha...'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true
                        ]
                    ])->label('Fecha del Gasto <span class="required">*</span>') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'frequency')->dropDownList(
                        $model::getFrequencyOptions(),
                        ['prompt' => 'Seleccionar frecuencia...']
                    )->label('Frecuencia <span class="required">*</span>') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'unit_measurement_id')->widget(Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map(
                            \common\models\ExpenseUnitMeasurement::find()
                                ->where(['business_id' => $business->id])
                                ->orderBy(['name' => SORT_ASC])
                                ->all(),
                            'id',
                            'name'
                        ),
                        'options' => [
                            'placeholder' => 'Seleccionar unidad...',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '100%',
                        ],
                    ])->label('Unidad de Medida (Opcional)') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'category_id')->widget(Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map(
                            \common\models\ExpenseCategory::find()
                                ->where(['business_id' => $business->id])
                                ->orderBy(['name' => SORT_ASC])
                                ->all(),
                            'id',
                            'name'
                        ),
                        'options' => [
                            'placeholder' => 'Seleccionar categoría...',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '100%',
                        ],
                    ])->label('Categoría (Opcional)') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'provider_id')->widget(Select2::class, [
                        'data' => $providers,
                        'options' => [
                            'placeholder' => 'Seleccionar proveedor...',
                            'id' => 'expense-provider_id'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '100%',
                            'ajax' => [
                                'url' => \yii\helpers\Url::to(['provider-list']),
                                'dataType' => 'json',
                                'delay' => 250,
                                'data' => new \yii\web\JsExpression('function(params) {
                                    return {
                                        q: params.term
                                    };
                                }'),
                                'cache' => true
                            ],
                            'minimumInputLength' => 1,
                        ],
                    ])->label('Proveedor (Opcional)') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'description')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Descripción del gasto...'
                    ])->label('Descripción') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'observations')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Observaciones adicionales...'
                    ])->label('Observaciones') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'is_active')->checkbox()->label('Gasto Activo') ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? 'Crear Gasto' : 'Actualizar Gasto', [
                    'class' =>'btn btn-warning'
                ]) ?>
                <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar el monto prorrateado cuando cambie la frecuencia o el monto
    const frequencySelect = document.getElementById('expense-frequency');
    const amountInput = document.getElementById('expense-amount');
    
    function updateMonthlyAmount() {
        const frequency = frequencySelect ? frequencySelect.value : 'unico';
        const amount = parseFloat(amountInput ? amountInput.value : 0) || 0;
        
        let monthlyAmount = amount;
        
        switch (frequency) {
            case 'diario':
                monthlyAmount = amount * 30;
                break;
            case 'semanal':
                monthlyAmount = amount * 4.33;
                break;
            case 'quincenal':
                monthlyAmount = amount * 2;
                break;
            case 'mensual':
                monthlyAmount = amount;
                break;
            case 'bimestral':
                monthlyAmount = amount / 2;
                break;
            case 'trimestral':
                monthlyAmount = amount / 3;
                break;
            case 'semestral':
                monthlyAmount = amount / 6;
                break;
            case 'anual':
                monthlyAmount = amount / 12;
                break;
        }
        
        // // Mostrar el monto prorrateado si existe el elemento
        // const monthlyAmountElement = document.querySelector('.monthly-amount');
        // if (monthlyAmountElement) {
        //     monthlyAmountElement.textContent = '$' + monthlyAmount.toFixed(2);
        // }
    }
    
    if (frequencySelect) {
        frequencySelect.addEventListener('change', updateMonthlyAmount);
    }
    
    if (amountInput) {
        amountInput.addEventListener('input', updateMonthlyAmount);
    }
});
</script>
