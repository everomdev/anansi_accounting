<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Detalle de Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Inventario de Insumos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventory-view">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data-confirm' => '¿Está seguro que desea eliminar este inventario?',
            'data-method' => 'post',
        ]) ?>
    </p>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'ingredient_stock_id',
                'value' => $model->ingredientStock ? $model->ingredientStock->name : $model->ingredient_stock_id,
                'label' => 'Insumo',
            ],
            [
                'attribute' => 'business_id',
                'value' => $model->business ? $model->business->name : $model->business_id,
                'label' => 'Negocio',
            ],
            'inventario_almacen',
            'inventario_cocina',
            'inventario_barra',
            'inventario_servicio',
            'inventario_otro',
            [
                'attribute' => 'fecha',
                'format' => 'datetime',
                'label' => 'Fecha de inventario',
            ],
        ],
    ]) ?>
</div>
