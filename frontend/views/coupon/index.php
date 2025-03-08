<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CouponSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Coupons');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="coupon-index">


    <p>
        <?= Html::a(Yii::t('app', 'Create Coupons'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            'code',
            'discount',
            'quantity',
            [
                'attribute' => 'type',
                'value' => 'formattedType',
                'filter' => \yii\bootstrap5\Html::activeDropDownList(
                    $searchModel,
                    'type',
                    \common\models\Coupon::getFormattedTypes(),
                    [
                        'class' => "form-control",
                        'prompt' => Yii::t('app', "All")
                    ]
                )
            ],
            'expiration:date',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
