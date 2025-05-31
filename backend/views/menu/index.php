<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Combos';
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="menu-index">


    <p>
        <?= Html::a('Nuevo combo', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'rowOptions' => [
            'class' => 'text-center'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            'name',
            [
                'attribute' => 'total_cost',
                'label' => Yii::t('app', 'Total Cost'),
                'value' => function($model) {
                    return formatCost($model->total_cost);
                },
                'contentOptions' => ['style' => 'text-align: right;'],
            ],
//            'totalCostByHigherPrice:currency',
//            'totalCostByAvgPrice:currency',
            [
                'attribute' => 'total_price',
                'label' => Yii::t('app', 'Total Price'),
                'value' => function($model) {
                    return formatPrice($model->total_price);
                },
                'contentOptions' => ['style' => 'text-align: right;'],
            ],
            [
                'attribute' => 'cost_percent_last_price',
                'label' => "Porcentaje de costo último precio",
                'value' => function ($data) {
                    return formatPercentage($data->cost_percent_last_price);
                },
                'contentOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>


</div>
