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
            'expiration_date:date',
            [
                'label' => Yii::t('app', 'Plan Asociado'),
                'format' => 'html',
                'value' => function ($model) {
                    // Eliminar el die(var_dump($model)) que detiene la ejecución
                    
                    // Usar la relación definida en el modelo
                    if ($model->plan) {
                        return Html::tag('span', Html::encode($model->plan->name), [
                            'class' => 'badge bg-info',
                            'title' => Html::encode($model->plan->description ?? '')
                        ]);
                    }
                    
                    // Si no tiene plan asociado
                    return '<span class="text-muted">' . Yii::t('app', 'Sin plan asociado') . '</span>';
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{delete}"
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
