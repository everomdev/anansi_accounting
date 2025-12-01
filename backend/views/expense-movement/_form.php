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
                            'placeholder' => 'Seleccionar gasto...'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
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

                <!-- Segunda fila: Monto y Fecha -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <?= $form->field($model, 'amount', [
                        'template' => "{label}<br><div class='input-group'><span class='input-group-text'>{$currencySymbol}</span>{input}</div>{error}"
                    ])->textInput(['type' => 'number', 'step' => '0.01'])->label('Monto') ?>
                </div>

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
