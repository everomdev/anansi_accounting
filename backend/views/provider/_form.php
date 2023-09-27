<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Provider */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="provider-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-provider',
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-body gap-3">
            <div class="row gap-3">
                <div class="col-12">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-12">
                    <?= $form->field($model, 'second_phone')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-12">
                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'payment_method')->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'account')->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'credit_days')->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'rfc')->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'business_name')->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'advantages')->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'disadvantages')->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'observations')->textInput() ?>
                </div>
            </div>


        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>
