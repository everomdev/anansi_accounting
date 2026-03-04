<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseMovement */
/* @var $form yii\widgets\ActiveForm */

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($business->currency_code));

// Obtener todos los gastos activos del negocio
$expenses = \common\models\Expense::find()
    ->where(['business_id' => $business->id, 'is_active' => 1])
    ->orderBy(['name' => SORT_ASC])
    ->all();

$expenseOptions = [];
foreach ($expenses as $expense) {
    $label = $expense->key ? $expense->key . ' - ' . $expense->name : $expense->name;
    $expenseOptions[$expense->id] = $label;
}
?>

<div class="expense-movement-form">

    <?php $form = ActiveForm::begin([
        'id' => 'expense-movement-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validateOnSubmit' => true
    ]); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <?= $model->isNewRecord ? 'Registrar Movimiento de Gasto' : 'Actualizar Movimiento de Gasto' ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Primera fila: Gasto y Tipo -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <?= $form->field($model, 'expense_id')->widget(Select2::class, [
                        'data' => $expenseOptions,
                        'options' => [
                            'placeholder' => 'Seleccionar gasto...',
                            'id' => 'expensemovement-expense_id'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'pluginEvents' => [
                            'change' => 'function() { checkIfInventoriable(); }'
                        ]
                    ])->label('Gasto') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-6">
                    <?= $form->field($model, 'type')->dropDownList(
                        \common\models\ExpenseMovement::getFormattedTypes(),
                        [
                            'prompt' => 'Seleccionar tipo...'
                        ]
                    )->label('Tipo de Movimiento') ?>
                </div>

                <!-- Campos para gastos inventariables (ocultos por defecto) -->
                <div id="inventoriable-fields" style="display: none; width: 100%;">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-box"></i> Este gasto requiere información de inventario
                        </div>
                    </div>
                    
                    <div class="col-sm-12 col-md-4 col-lg-4">
                        <?= $form->field($model, 'quantity')->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'id' => 'expensemovement-quantity'
                        ])->label('Cantidad') ?>
                    </div>

                    <div class="col-sm-12 col-md-4 col-lg-4">
                        <label class="form-label">Unidad</label>
                        <input type="text" class="form-control" id="unit-display" readonly disabled>
                    </div>

                    <div class="col-sm-12 col-md-4 col-lg-4">
                        <?= $form->field($model, 'unit_amount', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>{$currencySymbol}</span>{input}</div>{error}"
                        ])->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'id' => 'expensemovement-unit_amount'
                        ])->label('Monto Unitario') ?>
                    </div>

                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <?= $form->field($model, 'amount', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>{$currencySymbol}</span>{input}</div>{hint}{error}",
                            'options' => ['class' => 'form-group']
                        ])->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'readonly' => true,
                            'id' => 'expensemovement-amount'
                        ])->label('Total')->hint('Este campo se calcula automáticamente (Cantidad × Monto Unitario)') ?>
                    </div>
                </div>

                <!-- Campo de monto para gastos NO inventariables -->
                <div id="non-inventoriable-amount" class="col-sm-12 col-md-6 col-lg-6">
                    <?= $form->field($model, 'amount', [
                        'template' => "{label}<br><div class='input-group'><span class='input-group-text'>{$currencySymbol}</span>{input}</div>{error}"
                    ])->textInput(['type' => 'number', 'step' => '0.01'])->label('Monto') ?>
                </div>

                <!-- Segunda fila: Fecha -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <?= $form->field($model, 'movement_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'Seleccionar fecha...'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ])->label('Fecha del Movimiento') ?>
                </div>

                <!-- Tercera fila: Tipo de Pago y Factura -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <?= $form->field($model, 'payment_type')->dropDownList(
                        \common\models\ExpenseMovement::getPaymentTypes(),
                        [
                            'prompt' => 'Seleccionar tipo de pago...'
                        ]
                    )->label('Tipo de Pago') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-6">
                    <?= $form->field($model, 'invoice')->textInput(['maxlength' => true])->label('Factura') ?>
                </div>

                <!-- Observaciones -->
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <?= $form->field($model, 'observations')->textarea(['rows' => 3])->label('Observaciones') ?>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
                <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// Preparar datos de gastos para JavaScript
$expensesData = [];
foreach ($expenses as $expense) {
    $expensesData[$expense->id] = [
        'is_inventoriable' => $expense->subcategory && $expense->subcategory->is_inventoriable ? true : false,
        'unit_name' => $expense->unitMeasurement ? $expense->unitMeasurement->name : ''
    ];
}
$expensesDataJson = json_encode($expensesData);

$this->registerJs("
var expensesData = {$expensesDataJson};
window.isInventoriableExpense = false;

function checkIfInventoriable() {
    var expenseId = $('#expensemovement-expense_id').val();
    
    if (!expenseId || !expensesData[expenseId]) {
        $('#inventoriable-fields').hide();
        $('#non-inventoriable-amount').show();
        window.isInventoriableExpense = false;
        return;
    }
    
    var expenseData = expensesData[expenseId];
    window.isInventoriableExpense = expenseData.is_inventoriable;
    
    if (expenseData.is_inventoriable) {
        $('#inventoriable-fields').show();
        $('#non-inventoriable-amount').hide();
        $('#unit-display').val(expenseData.unit_name);
        
        // Limpiar el campo de monto y hacerlo readonly
        $('#expensemovement-amount').prop('readonly', true);
        
        // Calcular total cuando cambie cantidad o monto unitario
        calculateTotal();
    } else {
        $('#inventoriable-fields').hide();
        $('#non-inventoriable-amount').show();
        
        // Limpiar campos de inventariable
        $('#expensemovement-quantity').val('');
        $('#expensemovement-unit_amount').val('');
        $('#unit-display').val('');
        
        // Hacer el monto editable
        $('#expensemovement-amount').prop('readonly', false);
    }
}

function calculateTotal() {
    var quantity = parseFloat($('#expensemovement-quantity').val()) || 0;
    var unitAmount = parseFloat($('#expensemovement-unit_amount').val()) || 0;
    var total = quantity * unitAmount;
    
    $('#expensemovement-amount').val(total.toFixed(2));
}

// Event listeners
$(document).ready(function() {
    // Verificar al cargar si ya hay un gasto seleccionado
    checkIfInventoriable();
    
    // Calcular total cuando cambien cantidad o monto unitario
    $('#expensemovement-quantity, #expensemovement-unit_amount').on('input change', function() {
        if (window.isInventoriableExpense) {
            calculateTotal();
        }
    });
});
", \yii\web\View::POS_READY);
?>
