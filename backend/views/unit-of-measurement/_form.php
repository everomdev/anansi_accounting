<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UnitOfMeasurement */
/* @var $form yii\widgets\ActiveForm */
$controller = $model->isNewRecord
    ? \yii\helpers\Url::to(['unit-of-measurement/create'])
    : \yii\helpers\Url::to(['unit-of-measurement/update', 'id' => $model->id])
?>

<div class="unit-of-measurement-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-um',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validationUrl' => $controller,
        'action' => $controller
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>


    <div class="form-group mt-3">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
