<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Menus';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">


    <p>
        <?= Html::a('Create Menu', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'formatter' => [
            'class' => \yii\i18n\Formatter::class,
            'currencyCode' => 'USD',

        ],
        'rowOptions' => [
                'class' => 'text-center'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            'name',
            'totalCostByLastPrice:currency',
            'totalCostByHigherPrice:currency',
            'totalCostByAvgPrice:currency',
            'total_price:currency',
            [
                    'attribute' => 'cost_percent_last_price',
                'value' => function ($data) {
                    return sprintf("%s %%", $data->cost_percent_last_price);
                },
            ],
            [
                    'attribute' => 'cost_percent_higher_price',
                'value' => function ($data) {
                    return sprintf("%s %%", $data->cost_percent_higher_price);
                },
            ],
            [
                    'attribute' => 'cost_percent_avg_price',
                'value' => function ($data) {
                    return sprintf("%s %%", $data->cost_percent_avg_price);
                },
            ],
            //'business_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
