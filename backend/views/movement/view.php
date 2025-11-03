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

    <?php if ($model->type == $model::TYPE_ORDER): ?>
        <div class="mb-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-clipboard"></i> Orden Registrada</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Esta orden está lista para ser convertida en una entrada cuando recibas los productos del proveedor. 
                        Al convertirla, se creará automáticamente un nuevo movimiento de entrada con todos los datos de esta orden.
                    </p>
                    <div class="d-flex gap-2">
                        <?= Html::a(
                            '<i class="bx bx-transfer"></i> Registrar Entrada', 
                            ['convert-to-entry', 'id' => $model->id], 
                            [
                                'class' => 'btn btn-success btn-lg',
                                'data-confirm' => '¿Confirmas que quieres convertir esta orden en una entrada?\n\nSe creará un nuevo movimiento de entrada con:\n• Mismo proveedor\n• Mismo producto\n• Misma cantidad\n• Mismos datos financieros\n• Fecha actual',
                                'title' => 'Convertir orden a entrada'
                            ]
                        ) ?>
                        <?= Html::a(
                            '<i class="bx bx-edit"></i> Editar Orden', 
                            ['update', 'id' => $model->id], 
                            [
                                'class' => 'btn btn-outline-primary',
                                'title' => 'Editar esta orden antes de convertirla'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

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
