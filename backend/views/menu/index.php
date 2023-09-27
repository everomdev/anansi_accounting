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
        <?= Html::a('Create Combo', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'rowOptions' => [
            'class' => 'text-center'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            'name',
            'total_cost:currency',
//            'totalCostByHigherPrice:currency',
//            'totalCostByAvgPrice:currency',
            'total_price:currency',
            [
                'attribute' => 'cost_percent_last_price',
                'value' => function ($data) {
                    return sprintf("%s %%", $data->cost_percent_last_price);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>


</div>
