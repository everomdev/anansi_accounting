<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IngredientStockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Storage');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="ingredient-stock-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'key',
            [
                'attribute' => 'ingredient',
                'label' => 'Insumo',
                'value' => function ($data) {
                    $parts = [];
                    
                    // Agregar el nombre del insumo
                    $parts[] = $data->ingredient;
                    
                    // Agregar marca si existe
                    if (!empty($data->brand)) {
                        $parts[] = $data->brand;
                    }
                    
                    // Agregar presentación si existe
                    if (!empty($data->presentation)) {
                        $parts[] = $data->presentation;
                    }
                    
                    // Agregar unidad de medida
                    //$parts[] = $data->um;
                    
                    return implode('  ', $parts);
                },
            ],
            'quantity',
            [
                'label' => Yii::t('app', "Value"),
                'value' => function ($data) {
                    return formatPrice($data->valueInMoney);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{priceTrend} {update} {delete}",
                'buttons' => [
                    'priceTrend' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            \yii\bootstrap5\Html::tag('i', '', ['class' => 'bx bx-chart text-primary']),
                            \yii\helpers\Url::to(['ingredient-stock/price-trend', 'id' => $model->id])
                        );
                    }
                ],
                'visible' => false
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
