<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Purchase */
/* @var $form yii\widgets\ActiveForm */
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$ingredients = (new \yii\db\Query())
    ->select("*")
    ->from('ingredient')
    ->all();

$autocompleteUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'um')));
$autocompletePortionsUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'portion_um')));

$providers = \common\models\Purchase::find()
    ->select(['provider'])
    ->innerJoin('ingredient_stock', 'ingredient_stock.id=purchase.stock_id')
    ->where(['ingredient_stock.business_id' => $business['id']])
    ->asArray()
    ->all();

$autocompleteProviders = $providers == null ? [] : array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($providers, 'provider')));

?>

<div class="purchase-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <?php if ($model->isNewRecord): ?>
            <div class="card-header">
                <?= \yii\bootstrap5\Html::a(Yii::t('app', "The product does not exist in the warehouse?"),
                    \yii\helpers\Url::to(['ingredient-stock/create']),
                    [
                    'class' => 'btn btn-warning btn-sm'
                ]) ?>
            </div>
        <?php endif; ?>
        <div class="card-body">
            <div class="row gap-3">
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'stock_id')->widget(\kartik\select2\Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map(\common\models\IngredientStock::findAll(['business_id' => $business['id']]), 'id', 'label'),
                        'disabled' => !$model->isNewRecord
                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'date')->widget(\kartik\date\DatePicker::class, [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ],
                        'disabled' => !$model->isNewRecord
                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'price')->textInput() ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'unit_price')->textInput() ?>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'quantity')->textInput() ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'um')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompleteUm,
                        'dataset' => [
                            [
                                'local' => $autocompleteUm,
                                'limit' => 10,

                            ]
                        ],

                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'final_um')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompletePortionsUm,
                        'dataset' => [
                            [
                                'local' => $autocompletePortionsUm,
                                'limit' => 10,

                            ]
                        ],

                    ]) ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'provider')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompleteProviders,
                        'dataset' => [
                            [
                                'local' => $autocompleteProviders,
                                'limit' => 10,

                            ]
                        ],

                    ]) ?>
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
