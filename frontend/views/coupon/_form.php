<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap5\BootstrapAsset;

/* @var $this yii\web\View */
/* @var $model common\models\Coupon */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile(Yii::getAlias("@web/js/coupon/form.js"), [
    'depends' => [\yii\web\YiiAsset::class],
    'position' => $this::POS_END
]);

BootstrapAsset::register($this);
?>

<div class="coupon-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-coupon',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true
    ]); ?>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'discount')->textInput() ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'quantity')->textInput() ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'type')->dropDownList(\common\models\Coupon::getFormattedTypes(), ['class' => "form-control"]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'expiration')->input('date') ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'expiration_date')->input('date') ?>
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