<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ConsumptionCenter */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="consumption-center-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>
    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>
