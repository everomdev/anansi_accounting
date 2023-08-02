<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\PurchaseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Purchases');
$this->params['breadcrumbs'][] = $this->title;

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
?>
<div class="purchase-index">
    <p>
        <?= Html::a(Yii::t('app', 'Register Purchase'), ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'date',
            [
                'attribute' => 'stock.ingredient',
                'filter' => \kartik\select2\Select2::widget([
                    'attribute' => 'stock_id',
                    'model' => $searchModel,
                    'data' => \yii\helpers\ArrayHelper::map(\common\models\IngredientStock::findAll(['business_id' => $business['id']]), 'id', 'ingredient'),
                    'options' => [
                        'placeholder' => '----'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-primary'
                ]
            ],
            'price',
            'provider',
            'quantity',
            //'um',
            //'final_um',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
