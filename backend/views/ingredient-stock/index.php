<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IngredientStockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Storage');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ingredient-stock-index">


    <p>
        <?= Html::a(Yii::t('app', 'Add supply'), ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => [
            'class' => \yii\i18n\Formatter::class,
            'currencyCode' => 'usd',
            'dateFormat' => 'php:Y-m-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'ingredient',
            'quantity',
            'final_quantity',
            'um',
            'yield',
            'portions_per_unit',
            'portion_um',
            'lastUnitPrice:currency',
            'avgUnitPrice:currency',
            'higherUnitPrice:currency',
            //'observations:ntext',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{priceTrend} {update} {delete}",
                'buttons' => [
                        'priceTrend' => function($url, $model, $key){
                            return \yii\bootstrap5\Html::a(
                                    \yii\bootstrap5\Html::tag('i', '', ['class' => 'bx bx-chart text-primary']),
                                \yii\helpers\Url::to(['ingredient-stock/price-trend', 'id' => $model->id])
                            );
                        }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
