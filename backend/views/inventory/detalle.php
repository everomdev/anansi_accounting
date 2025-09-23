<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Detalle de Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Inventario de Insumos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    .sticky-header-container {
        position: relative;
        overflow: auto;
        max-height: calc(90vh - 180px);
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .sticky-header-table {
        margin-bottom: 0;
    }
    .sticky-header-table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        white-space: normal;
        vertical-align: middle;
    }
</style>
<div class="inventory-detalle">
    <h2>Inventario del <?= Yii::$app->formatter->asDatetime($fecha) ?></h2>
    <div class="table-responsive sticky-header-container">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => isset($searchModel) ? $searchModel : null,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => [
            [
                'attribute' => 'ingredient_stock_id',
                'headerOptions' => ['style' => 'min-width: 250px; width: 25%;'],
                'value' => function($model) {
                    if ($model->ingredientStock) {
                        $insumo = $model->ingredientStock->ingredient;
                        $marca = $model->ingredientStock->brand ?? '';
                        $presentacion = $model->ingredientStock->presentation ?? '';
                        $txt = $insumo;
                        if ($marca) $txt .= ' ' . $marca;
                        if ($presentacion) $txt .= ' ' . $presentacion;
                        return $txt;
                    }
                    return $model->ingredient_stock_id;
                },
                'label' => 'Insumo',
                'filter' => '<div style="position: relative;">' . 
                    Html::textInput('InventorySearch[insumo]', isset($searchModel) ? $searchModel->insumo : '', [
                        'class' => 'form-control',
                        'placeholder' => 'Buscar por insumo, marca o presentación...',
                        'id' => 'detalle-title-filter',
                        'style' => 'padding-right: 30px;'
                    ]) . 
                    Html::button('×', [
                        'class' => 'btn btn-sm',
                        'id' => 'clear-detalle-title-btn',
                        'onclick' => 'document.getElementById(\'detalle-title-filter\').value=\'\';this.form.submit();',
                        'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (isset($searchModel) && !empty($searchModel->insumo) ? 'block' : 'none') . '; z-index: 10; cursor: pointer;',
                        'title' => 'Limpiar filtro'
                    ]) . 
                    '</div>',
            ],
            [
                'attribute' => 'inventario_almacen',
                'label' => 'Inventario Almacén',
            ],
            [
                'attribute' => 'inventario_cocina',
                'label' => 'Inventario<br>Cocina',
                'encodeLabel' => false,
            ],
            [
                'attribute' => 'inventario_barra',
                'label' => 'Inventario<br>Barra',
                'encodeLabel' => false,
            ],
            [
                'attribute' => 'inventario_servicio',
                'label' => 'Inventario<br>Servicio',
                'encodeLabel' => false,
            ],
            [
                'attribute' => 'inventario_otro',
                'label' => 'Inventario<br>Otro',
                'encodeLabel' => false,
            ],
            [
                'label' => 'Total<br>Inventario',
                'encodeLabel' => false,
                'value' => function($model) {
                    return $model->inventario_almacen + $model->inventario_cocina + $model->inventario_barra + $model->inventario_servicio + $model->inventario_otro;
                },
                'contentOptions' => ['style' => 'font-weight:bold; background:#f8f9fa;'],
            ],
            [
                'label' => 'Precio<br>Insumo',
                'encodeLabel' => false,
                'value' => function($model) {
                    return $model->ingredientStock && isset($model->ingredientStock->lastUnitPrice) ?
                        Yii::$app->formatter->asCurrency($model->ingredientStock->lastUnitPrice) : '-';
                },
            ],
            [
                'label' => 'Total<br>Dinero',
                'encodeLabel' => false,
                'value' => function($model) {
                    $total = $model->inventario_almacen + $model->inventario_cocina + $model->inventario_barra + $model->inventario_servicio + $model->inventario_otro;
                    $precio = $model->ingredientStock && isset($model->ingredientStock->lastUnitPrice) ? $model->ingredientStock->lastUnitPrice : 0;
                    return Yii::$app->formatter->asCurrency($total * $precio);
                },
                'contentOptions' => ['style' => 'font-weight:bold; background:#eaf7ea;'],
            ],
        ],
    ]) ?>
    </div>
    <div class="mt-3">
        <?= Html::a('Volver al listado de fechas', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
