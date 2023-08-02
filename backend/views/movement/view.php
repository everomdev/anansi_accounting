<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Movements'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="movement-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
//            'id',
            [
                'attribute' => 'type',
                'value' => function ($model) {
                    return $model->formattedType;
                },
            ],
            'provider',
            [
                'attribute' => 'payment_type',
                'value' => function ($model) {
                    return $model->formattedPaymentType;
                },
                'visible' => $model->type == $model::TYPE_INPUT,
            ],
            'ingredient.ingredient',
            [
                'attribute' => 'invoice',
                'visible' => $model->type == $model::TYPE_INPUT,
            ],
            'quantity',
            'um',
            [
                'attribute' => 'amount',
                'visible' => $model->type == $model::TYPE_INPUT,
            ],
            [
                'attribute' => 'tax',
                'visible' => $model->type == $model::TYPE_INPUT,
            ],
            [
                'attribute' => 'retention',
                'visible' => $model->type == $model::TYPE_INPUT,
            ],
            [
                'attribute' => 'unit_price',
                'visible' => $model->type == $model::TYPE_INPUT,
            ],
            [
                'attribute' => 'total',
                'visible' => $model->type == $model::TYPE_INPUT,
            ],

            'created_at',
            'observations',
        ],
    ]) ?>

</div>
