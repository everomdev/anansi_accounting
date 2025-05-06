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
            [
                'attribute' => 'usages',
                'label' => Yii::t('app', 'Uso'),
                'format' => 'raw',
                'value' => function ($model) {
                    // Si no hay cantidad definida (cupones ilimitados)
                    if ($model->quantity <= 0) {
                        return Html::tag('span', 
                            "{$model->usages} " . Yii::t('app', 'usos'),
                            ['class' => 'text-muted']
                        ) . ' ' . 
                        Html::tag('span', 
                            Yii::t('app', '(ilimitado)'), 
                            ['class' => 'badge bg-secondary']
                        );
                    }
                    
                    // Calcular porcentaje de uso
                    $percentage = min(100, round(($model->usages / $model->quantity) * 100));
                    
                    // Determinar clase de color según porcentaje
                    $progressClass = 'bg-success';
                    if ($percentage >= 70 && $percentage < 90) {
                        $progressClass = 'bg-warning';
                    } elseif ($percentage >= 90) {
                        $progressClass = 'bg-danger';
                    }
                    
                    $progressBar = <<<HTML
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar {$progressClass}" role="progressbar" 
                            style="width: {$percentage}%;" aria-valuenow="{$percentage}" 
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small>{$model->usages}/{$model->quantity}</small>
                        <small>{$percentage}%</small>
                    </div>
                    HTML;
                    
                    return $progressBar;
                },
                'headerOptions' => ['style' => 'width: 150px;'],
            ],
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
