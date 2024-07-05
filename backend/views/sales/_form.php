<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Sales */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sales-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-sales',
        'enableAjaxValidation' => true,
        'action' => \yii\helpers\Url::to(['sales/create'])
    ]); ?>

    <div class="row gap-3">
        <div class="col-12">
            <?= $form->field($model, 'date')->widget(\kartik\date\DatePicker::class, [
                    'pluginOptions' => [
                            'format' => 'yyyy-mm-dd'
                    ]
            ]) ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'amount_food')->textInput() ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'amount_drinking')->textInput() ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'amount_other')->textInput() ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
