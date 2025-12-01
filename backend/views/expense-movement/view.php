<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseMovement */

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>

<div class="expense-movement-view">

    <?= DetailView::widget([
        'model' => $model,
        'formatter' => $business->getFormatter(),
        'attributes' => [
            'id',
            [
                'attribute' => 'expense_id',
                'label' => 'Gasto',
                'value' => function($model) {
                    return $model->expense ? $model->expense->name : '-';
                }
            ],
            [
                'attribute' => 'type',
                'label' => 'Tipo',
                'value' => function($model) {
                    return $model->formattedType;
                }
            ],
            [
                'attribute' => 'amount',
                'label' => 'Monto',
                'format' => 'currency',
            ],
            [
                'attribute' => 'payment_type',
                'label' => 'Tipo de Pago',
                'value' => function($model) {
                    return $model->formattedPaymentType;
                }
            ],
            'invoice',
            'observations:ntext',
            [
                'attribute' => 'movement_date',
                'label' => 'Fecha del Movimiento',
                'format' => 'date',
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Fecha de Creación',
                'format' => 'datetime',
            ],
        ],
    ]) ?>

    <div class="mt-3">
        <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar este movimiento?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

</div>
