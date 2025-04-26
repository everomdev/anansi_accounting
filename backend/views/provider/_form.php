<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Provider */
/* @var $form yii\widgets\ActiveForm */

// Registrar JavaScript para validación de campos
$this->registerJs("
// Validación para campos de teléfono
document.querySelectorAll('#provider-phone, #provider-second_phone').forEach(function(input) {
    input.addEventListener('input', function() {
        // Permitir solo números, espacios, guiones y signo +
        this.value = this.value.replace(/[^0-9\\s\\-+]/g, '');
    });
});

// Validación para días de crédito (solo números)
document.getElementById('provider-credit_days').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
");
?>

<div class="provider-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-provider',
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0"><?= Yii::t('app', 'Información del Proveedor') ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Nombre del negocio primero -->
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'business_name')
                        ->textInput(['maxlength' => true, 'class' => 'form-control'])
                        ->label(Yii::t('app', 'Nombre del Negocio')) ?>
                </div>
                
                <!-- RFC en la misma fila -->
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'rfc')
                        ->textInput(['maxlength' => true, 'class' => 'form-control'])
                        ->label('RFC') ?>
                </div>
                
                <!-- Cambiar el label de "name" a "nombre del contacto" -->
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'name')
                        ->textInput(['maxlength' => true])
                        ->label(Yii::t('app', 'Nombre del Contacto')) ?>
                </div>
                
                <!-- Email con validación -->
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'email')
                        ->input('email', ['maxlength' => true])
                        ->label(Yii::t('app', 'Correo Electrónico')) ?>
                </div>
                
                <!-- Teléfono del negocio -->
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'phone')
                        ->textInput(['maxlength' => true, 'placeholder' => '+52 55 1234 5678'])
                        ->label(Yii::t('app', 'Teléfono del Negocio')) ?>
                </div>
                
                <!-- Teléfono del contacto -->
                <div class="col-md-6 mb-3">
                    <?= $form->field($model, 'second_phone')
                        ->textInput(['maxlength' => true, 'placeholder' => '+52 55 1234 5678'])
                        ->label(Yii::t('app', 'Teléfono del Contacto')) ?>
                </div>
                
                <!-- Dirección usando todo el ancho -->
                <div class="col-12 mb-3">
                    <?= $form->field($model, 'address')
                        ->textarea(['rows' => 2])
                        ->label(Yii::t('app', 'Dirección')) ?>
                </div>
                
                <div class="col-md-4 mb-3">
                    <?= $form->field($model, 'payment_method')
                        ->dropDownList([
                            'Efectivo' => Yii::t('app', 'Efectivo'),
                            'Transferencia' => Yii::t('app', 'Transferencia'),
                            'Cheque' => Yii::t('app', 'Cheque'),
                            'Tarjeta' => Yii::t('app', 'Tarjeta de Crédito/Débito'),
                            'Otro' => Yii::t('app', 'Otro')
                        ], ['prompt' => Yii::t('app', 'Seleccionar método de pago')])
                        ->label(Yii::t('app', 'Método de Pago')) ?>
                </div>
                
                <div class="col-md-4 mb-3">
                    <?= $form->field($model, 'account')
                        ->textInput(['maxlength' => true])
                        ->label(Yii::t('app', 'Cuenta/Referencia')) ?>
                </div>
                
                <div class="col-md-4 mb-3">
                    <?= $form->field($model, 'credit_days')
                        ->textInput(['type' => 'number', 'min' => '0'])
                        ->label(Yii::t('app', 'Días de Crédito')) ?>
                </div>
                
                <div class="col-md-12">
                    <hr>
                    <h6><?= Yii::t('app', 'Valoración del Proveedor') ?></h6>
                </div>
                
                <div class="col-md-4 mb-3">
                    <?= $form->field($model, 'advantages')
                        ->textarea(['rows' => 3])
                        ->label(Yii::t('app', 'Ventajas')) ?>
                </div>
                
                <div class="col-md-4 mb-3">
                    <?= $form->field($model, 'disadvantages')
                        ->textarea(['rows' => 3])
                        ->label(Yii::t('app', 'Desventajas')) ?>
                </div>
                
                <div class="col-md-4 mb-3">
                    <?= $form->field($model, 'observations')
                        ->textarea(['rows' => 3])
                        ->label(Yii::t('app', 'Observaciones')) ?>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div class="form-group">
                <?= Html::a(Yii::t('app', 'Cancelar'), ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Guardar'), ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>