<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Release */
/* @var $form yii\widgets\ActiveForm */
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
?>

<div class="release-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-body">
            <div class="row gap-3">
                <div class="col-12">
                    <?= $form->field($model, 'stock_id')->widget(\kartik\select2\Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map(\common\models\IngredientStock::findAll(['business_id' => $business['id']]), 'id', 'label'),
                        'disabled' => !$model->isNewRecord
                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <?= $form->field($model, 'quantity')->textInput() ?>
                </div>
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <?= $form->field($model, 'date')->widget(\kartik\date\DatePicker::class, [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ],
                        'disabled' => !$model->isNewRecord
                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'observations')->textarea(['rows' => 6]) ?>
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
