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

            <?=
            \yii\bootstrap5\Html::dropDownList(
                'ingredientId',
                $ingredientId,
                ArrayHelper::map(\common\models\IngredientStock::findAll(['business_id' => $business['id']]), 'id', 'label'),
                [
                    'class' => "form-control",
                    'prompt' => '----',
                    'id' => 'ingredientId'
                ]
            )
            ?>
            <?=
            \yii\bootstrap5\Html::dropDownList(
                'categoryId',
                $categoryId,
                ArrayHelper::map(\common\models\Category::find()
                    ->where([
                        'or',
                        ['business_id' => $business['id']],
                        ['builtin' => 1]
                    ])
                    ->all(), 'id', 'name'),
                [
                    'class' => "form-control",
                    'prompt' => '----',
                    'id' => 'categoryId'
                ]
            )
            ?>

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
