<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Category */
/* @var $form yii\widgets\ActiveForm */

$controller = $model->isNewRecord
    ? \yii\helpers\Url::to(['category/create'])
    : \yii\helpers\Url::to(['category/update', 'id' => $model->id])

?>

<div class="category-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-category',
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
