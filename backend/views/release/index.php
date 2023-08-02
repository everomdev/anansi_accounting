<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReleaseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Releases');
$this->params['breadcrumbs'][] = $this->title;
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);

?>
<div class="release-index">

    <p>
        <?= Html::a(Yii::t('app', 'Register Release'), ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

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
            'quantity',
            'date',
            'observations:ntext',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
