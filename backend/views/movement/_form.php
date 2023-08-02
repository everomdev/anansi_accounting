<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */
/* @var $form yii\widgets\ActiveForm */

$stock = (new \yii\db\Query())
    ->select([
        "ingredient_stock.*",
        "CONCAT(ingredient_stock.ingredient, ' (',ingredient_stock.um,')') as label"
    ])
    ->from('ingredient_stock')
    ->all();

$this->registerJsVar('movementTypeInput', \common\models\Movement::TYPE_INPUT);
$this->registerJsVar('movementTypeOutput', \common\models\Movement::TYPE_OUTPUT);
$this->registerJsVar('movementTypeOrder', \common\models\Movement::TYPE_ORDER);

$this->registerJsFile(Yii::getAlias("@web/js/movement/form.js"), [
    'depends' => \yii\web\YiiAsset::class,
    ['position' => $this::POS_END]
]);
?>

<div class="movement-form">

    <?php $form = ActiveForm::begin([
        'id' => 'movement-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true
    ]); ?>
    <div class="card">
        <div class="card-header">
            <?= \yii\bootstrap5\Html::a(
                Yii::t('app', "Can't find the input? add it"),
                \yii\helpers\Url::to(['ingredient-stock/create']),
                [
                    'class' => 'btn btn-sm btn-success'
                ]
            ) ?>
        </div>
        <div class="card-body">
            <div class="row gap-1">
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'type')->dropDownList(
                        \common\models\Movement::getFormattedTypes(),
                        [
                            'data-setting' => 'all',
                            'prompt' => "----"
                        ]
                    ) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'ingredient_id')->widget(\kartik\select2\Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map($stock, 'id', 'label'),
                        'options' => [
                            'data-setting' => 'all'
                        ]
                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'provider')->textInput(['maxlength' => true, 'data-setting' => 'all']) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'payment_type')->dropDownList(
                        \common\models\Movement::getFormattedPaymentTypes(),
                        [
                            'data-setting' => 'input'
                        ]
                    ) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'invoice')->textInput(['maxlength' => true, 'data-setting' => 'input']) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'quantity')->textInput(['data-setting' => 'all']) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'amount')->textInput(['data-setting' => 'input']) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'tax')->textInput(['data-setting' => 'input']) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'retention')->textInput(['data-setting' => 'input']) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'unit_price')->textInput(['data-setting' => 'input']) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'total')->textInput(['data-setting' => 'input']) ?>
                </div>

                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <?= $form->field($model, 'observations')->textarea(['data-setting' => 'all']) ?>
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
