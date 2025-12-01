<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseUnitMeasurement */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Unidades de Medida', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-unit-measurement-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar esta unidad de medida?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
