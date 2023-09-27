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

$providerNames = \common\models\Provider::find()->select(['name'])->asArray(true)->all();
$providerNames = array_values(
    array_unique(
        \yii\helpers\ArrayHelper::getColumn($providerNames, 'name')
    )
);
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($business->currency_code));
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
                    <?= $form->field($model, 'ingredient_id')->widget(\kartik\select2\Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map($stock, 'id', 'label'),
                        'options' => [
                            'data-setting' => 'all'
                        ]
                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?php if ($model->type == \common\models\Movement::TYPE_OUTPUT): ?>
                        <?= $form->field($model, 'provider')->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\ConsumptionCenter::find()->all(), 'name', 'name'))->label(Yii::t('app', "Consumption Center")) ?>
                    <?php else: ?>
                        <?= $form->field($model, 'provider')->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\Provider::find()->all(), 'name', 'name'))->label(Yii::t('app', "Provider")) ?>
                    <?php endif; ?>
                </div>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <?= $form->field($model, 'payment_type')->dropDownList(
                            \common\models\Movement::getFormattedPaymentTypes(),
                            [
                                'data-setting' => 'input'
                            ]
                        ) ?>
                    </div>
                <?php endif; ?>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <?= $form->field($model, 'invoice')->textInput(['maxlength' => true, 'data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'quantity')->textInput(['data-setting' => 'all']) ?>
                </div>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'amount',
                            [
                                'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                            ]
                        )->textInput(['data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'tax')->textInput(['data-setting' => 'input'])->label('Impuesto') ?>
                    </div>
                <?php endif; ?>
                <!--                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">-->
                <!--                    --><?php //= $form->field($model, 'retention')->textInput(['data-setting' => 'input']) ?>
                <!--                </div>-->
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'unit_price', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                        ])->textInput(['data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'total', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                        ])->textInput(['data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>

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
