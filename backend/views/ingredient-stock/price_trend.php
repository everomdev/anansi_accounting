<?php
/** @var $this \yii\web\View */

/** @var $model \common\models\IngredientStock */

use yii\helpers\ArrayHelper;

\backend\assets\ChartJsAsset::register($this);
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$this->title = Yii::t('app', "Price trend");
$this->registerJsVar('labels', ArrayHelper::getColumn($prices, 'date'));
$this->registerJsVar('prices', ArrayHelper::getColumn($prices, 'unit_price'));

$this->registerJsFile(Yii::getAlias("@web/js/ingredient-stock/price_trend.js"),
    [
        'position' => $this::POS_END,
        'depends' => [\yii\web\YiiAsset::class]
    ]);
?>

<div class="card">
    <div class="card-header">
        <?php $form = \yii\bootstrap5\ActiveForm::begin([
            'id' => 'form-filter-price-trend',
            'method' => 'get'
        ]) ?>
        <div class="d-flex gap-4">

            <div class="flex-fill">
            <?=
            \kartik\select2\Select2::widget([
                'name' => 'ingredientId',
                'value' => $ingredientId,
                'data' => ArrayHelper::map(\common\models\IngredientStock::findAll(['business_id' => $business['id']]), 'id', 'label'),
                'theme' => \kartik\select2\Select2::THEME_KRAJEE_BS5,
                'pluginOptions' => [
                    'allowClear' => true,
                    'placeholder' => 'Seleccionar ingrediente...',
                    'minimumResultsForSearch' => 0
                ],
                'options' => [
                    'id' => 'ingredientId',
                    'class' => 'form-control'
                ]
            ])
            ?>
            </div>
            <div class="flex-fill">
            <?=
            \kartik\select2\Select2::widget([
                'name' => 'categoryId',
                'value' => $categoryId,
                'data' => ArrayHelper::map(\common\models\Category::find()
                    ->where([
                        'or',
                        ['business_id' => $business['id']],
                        ['builtin' => 1]
                    ])
                    ->all(), 'id', 'name'),
                'theme' => \kartik\select2\Select2::THEME_KRAJEE_BS5,
                'pluginOptions' => [
                    'allowClear' => true,
                    'placeholder' => 'Seleccionar categoría...',
                    'minimumResultsForSearch' => 0
                ],
                'options' => [
                    'id' => 'categoryId',
                    'class' => 'form-control'
                ]
            ])
            ?>
            </div>

        </div>
        <div class="d-flex gap-4 mt-2">
            <?= \kartik\date\DatePicker::widget([
                'id' => 'from',
                'name' => 'from',
                'value' => $from,
                'pluginOptions' => [
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>

            <?= \kartik\date\DatePicker::widget([
                'id' => 'to',
                'name' => 'to',
                'value' => $to,
                'pluginOptions' => [
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
            <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', "Apply"), [
                'class' => 'btn btn-success'
            ]) ?>
        </div>
        <?php \yii\bootstrap5\ActiveForm::end(); ?>
    </div>
    <div class="card-body">
        <canvas id="priceTrend"></canvas>
    </div>
</div>
