<?php
use yii\grid\GridView;
use yii\helpers\Html;
use common\models\InventorySearch;

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
    .sticky-col {
        position: sticky;
        left: 0;
        background: #fff !important;
        background-clip: padding-box;
        z-index: 100;
        box-shadow: 2px 0 4px -1px rgba(0,0,0,0.12);
    }
</style>
<div class="inventory-detalle">
    <h2>Inventario del <?= Yii::$app->formatter->asDatetime($fecha) ?></h2>
    <?php
    // Calcular totales
    $totalInventario = 0;
    $totalDinero = 0;
    $allModels = $dataProvider->query->all();
    $areas = ['almacen', 'cocina', 'barra', 'servicio', 'otro'];
    $totalesPorArea = [];
    $cantidadesPorArea = [];
    foreach ($areas as $area) {
        $totalesPorArea[$area] = 0;
        $cantidadesPorArea[$area] = 0;
    }
    foreach ($allModels as $model) {
        $total = $model->inventario_almacen + $model->inventario_cocina + $model->inventario_barra + $model->inventario_servicio + $model->inventario_otro;
        $precio = ($model->ingredientStock && isset($model->ingredientStock->lastUnitPrice)) ? $model->ingredientStock->lastUnitPrice : 0;
        $totalInventario += $total;
        $totalDinero += $total * $precio;
        foreach ($areas as $area) {
            $cantidad = isset($model->{'inventario_' . $area}) ? $model->{'inventario_' . $area} : 0;
            $cantidadesPorArea[$area] += $cantidad;
            $totalesPorArea[$area] += $cantidad * $precio;
        }
    }
    ?>
    <div class="alert alert-info" style="margin-bottom:18px;">
        <strong>Total Inventario:</strong> <?= Yii::$app->formatter->asInteger($totalInventario) ?>
        &nbsp; | &nbsp;
        <strong>Total Dinero:</strong> <?= Yii::$app->formatter->asCurrency($totalDinero) ?>
    </div>
    <div class="alert alert-warning" style="margin-bottom:18px;">
        <strong>Total por área:</strong>
        Almacén: (<?= Yii::$app->formatter->asInteger($cantidadesPorArea['almacen']) ?>) <?= Yii::$app->formatter->asCurrency($totalesPorArea['almacen']) ?>|
        Cocina:  (<?= Yii::$app->formatter->asInteger($cantidadesPorArea['cocina']) ?>) <?= Yii::$app->formatter->asCurrency($totalesPorArea['cocina']) ?> |
        Barra: (<?= Yii::$app->formatter->asInteger($cantidadesPorArea['barra']) ?>) <?= Yii::$app->formatter->asCurrency($totalesPorArea['barra']) ?> |
        Servicio: (<?= Yii::$app->formatter->asInteger($cantidadesPorArea['servicio']) ?>) <?= Yii::$app->formatter->asCurrency($totalesPorArea['servicio']) ?> |
        Otro: (<?= Yii::$app->formatter->asInteger($cantidadesPorArea['otro']) ?>) <?= Yii::$app->formatter->asCurrency($totalesPorArea['otro']) ?>
    </div>
    <div class="table-responsive sticky-header-container" style="overflow-x:auto;">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => isset($searchModel) ? $searchModel : null,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => [
            [
                'attribute' => 'ingredient_stock_id',
                'headerOptions' => ['class' => 'sticky-col', 'style' => 'min-width: 250px; width: 25%;'],
                'contentOptions' => ['class' => 'sticky-col'],
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
                'label' => 'Unidad<br>Compra',
                'encodeLabel' => false,
                'value' => function($model) {
                    return $model->ingredientStock && isset($model->ingredientStock->um) ? $model->ingredientStock->um : '-';
                },
                'headerOptions' => ['style' => 'min-width: 120px; width: 10%;'],
            ],
            [
                'label' => 'Categoría',
                'encodeLabel' => false,
                'value' => function($model) {
                    return $model->ingredientStock && isset($model->ingredientStock->category->name) ? $model->ingredientStock->category->name : '-';
                },
                'headerOptions' => ['style' => 'min-width: 120px; width: 12%;'],
                'filter' => \yii\helpers\Html::activeDropDownList(
                    $searchModel,
                    'categoria',
                    \common\models\Category::find()->select(['name', 'id'])->indexBy('id')->column(),
                    [
                        'class' => 'form-control',
                        'prompt' => 'Todas'
                    ]
                ),
            ],
            [
                'attribute' => 'inventario_almacen',
                'label' => 'Inventario<br>Almacén',
                'encodeLabel' => false,
                'value' => function($model) {
                    return Yii::$app->formatter->asInteger($model->inventario_almacen);
                },
            ],
            [
                'attribute' => 'inventario_cocina',
                'label' => 'Inventario<br>Cocina',
                'encodeLabel' => false,
                'value' => function($model) {
                    return Yii::$app->formatter->asInteger($model->inventario_cocina);
                },
            ],
            [
                'attribute' => 'inventario_barra',
                'label' => 'Inventario<br>Barra',
                'encodeLabel' => false,
                'value' => function($model) {
                    return Yii::$app->formatter->asInteger($model->inventario_barra);
                },
            ],
            [
                'attribute' => 'inventario_servicio',
                'label' => 'Inventario<br>Servicio',
                'encodeLabel' => false,
                'value' => function($model) {
                    return Yii::$app->formatter->asInteger($model->inventario_servicio);
                },
            ],
            [
                'attribute' => 'inventario_otro',
                'label' => 'Inventario<br>Otro',
                'encodeLabel' => false,
                'value' => function($model) {
                    return Yii::$app->formatter->asInteger($model->inventario_otro);
                },
            ],
                [
                    'label' => 'Mínimo',
                    'encodeLabel' => false,
                    'value' => function($model) {
                        if (isset($model->ingredientStock) && $model->ingredientStock->min_stock !== null) {
                            return Yii::$app->formatter->asInteger($model->ingredientStock->min_stock);
                        }
                        return '-';
                    },
                ],
                [
                    'label' => 'Máximo',
                    'encodeLabel' => false,
                    'value' => function($model) {
                        if (isset($model->ingredientStock) && $model->ingredientStock->max_stock !== null) {
                            return Yii::$app->formatter->asInteger($model->ingredientStock->max_stock);
                        }
                        return '-';
                    },
                ],
                [
                    'label' => 'Último movimiento',
                    'encodeLabel' => false,
                        'value' => function($model) {
                            if (isset($model->ingredientStock)) {
                                $ultimo = $model->ingredientStock->getMovements()->orderBy(['created_at' => SORT_DESC])->one();
                                if ($ultimo) {
                                    $tipo = ($ultimo->type === 'input') ? 'Entrada' : (($ultimo->type === 'output') ? 'Salida' : ucfirst($ultimo->type));
                                    return $tipo . ': ' . Yii::$app->formatter->asInteger($ultimo->quantity);
                                }
                            }
                            return '-';
                        },
                ],
                [
                    'label' => 'Proveedor',
                    'encodeLabel' => false,
                    'value' => function($model) {
                        if (isset($model->ingredientStock) && $model->ingredientStock->providers) {
                            $providers = $model->ingredientStock->getProviders()->select('business_name')->column();
                            return !empty($providers) ? implode(', ', $providers) : '-';
                        }
                        return '-';
                    },
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
                    'label' => 'Existencia<br>Almacén',
                    'encodeLabel' => false,
                    'value' => function($model) {
                        return isset($model->ingredientStock) ? Yii::$app->formatter->asInteger($model->ingredientStock->quantity) : '-';
                    },
                    'contentOptions' => ['style' => 'background:#eaf7ea; font-weight:bold;'],
                ],
            [
                'label' => 'Costo<br>Insumo',
                'encodeLabel' => false,
                'value' => function($model) {
                    return $model->ingredientStock && isset($model->ingredientStock->lastUnitPrice) ?
                        Yii::$app->formatter->asCurrency($model->ingredientStock->lastUnitPrice) : '-';
                },
            ],
            [
                'label' => 'Costo<br>Total',
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
