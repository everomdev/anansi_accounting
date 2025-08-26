<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UnitOfMeasurement */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Unit Of Measurements'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="unit-of-measurement-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute' => 'type',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->type === \common\models\UnitOfMeasurement::TYPE_PURCHASE) {
                        return '<span class="badge bg-primary"><i class="fas fa-shopping-cart"></i> Unidad de compra</span>';
                    } else {
                        return '<span class="badge bg-info"><i class="fas fa-utensils"></i> Unidad de cocina</span>';
                    }
                },
            ],
            [
                'attribute' => 'custom',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->custom == 1) {
                        return '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Personalizada</span>';
                    } else {
                        return '<span class="badge bg-success"><i class="fas fa-check"></i> Estándar</span>';
                    }
                },
            ],
            'business_id',
        ],
    ]) ?>

</div>
