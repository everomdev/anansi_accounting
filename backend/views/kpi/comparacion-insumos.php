<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'Comparación de Insumos';
$this->params['breadcrumbs'][] = ['label' => 'KPI', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="kpi-comparacion-insumos">
    <h2><?= Html::encode($this->title) ?></h2>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-striped'],
            'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
            'columns' => [
                [
                    'attribute' => 'nombre',
                    'label' => 'Insumo',
                ],
                [
                    'attribute' => 'existencia_almacen',
                    'label' => 'Existencia almacén',
                    'format' => ['integer'],
                ],
                [
                    'attribute' => 'inventario_almacen',
                    'label' => 'Inventario almacén',
                    'format' => ['integer'],
                ],
                [
                    'attribute' => 'compras_menos_consumo',
                    'label' => 'Compras - Consumo real',
                ],
            ],
        ]) ?>
    </div>
    <div class="mt-3">
        <?= Html::a('Volver al inventario', ['/inventory/detalle'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
