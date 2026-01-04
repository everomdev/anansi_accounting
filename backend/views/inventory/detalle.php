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
    <h2>Inventario del <?= date('d/m/Y H:i', strtotime($dateEnd)) ?></h2>
    <?php if (!empty($dateEnd)) : ?>
        <div class="text-muted" style="font-size:16px;margin-bottom:8px;">Iniciado el <?= date('d/m/Y H:i', strtotime($fecha)) ?></div>
    <?php endif; ?>
    <div class="mb-3">
        <a href="/kpi/comparacion-insumos?fecha=<?= urlencode($fecha) ?>" class="btn btn-primary mr-2">
            <i class="fas fa-balance-scale"></i> Comparar con Control de Insumos
        </a>
        <!-- <button type="button" class="btn btn-success" onclick="ajustarMasivoInventario('<?= $fecha ?>')">
            <i class="fas fa-sync-alt"></i> Ajuste Masivo al Inventario
        </button> -->
    </div>
    <div class="alert alert-info" style="margin-bottom:18px;">
        <strong>Total Inventario:</strong> <?= formatNumber($totalInventario ?? 0) ?>
        &nbsp; | &nbsp;
        <strong>Total Dinero:</strong> <?= formatPrice($totalDinero ?? 0) ?>
    </div>
    <div class="alert alert-warning" style="margin-bottom:18px;">
        <strong>Total por área:</strong>
        <?php
        $totalesHtml = [];
        if (!empty($consumptionCenters)) {
            foreach ($consumptionCenters as $center) {
                $cantidad = $totalesPorCentroCantidad[$center->id] ?? 0;
                $costo = $totalesPorCentroCosto[$center->id] ?? 0;
                $totalesHtml[] = Html::encode($center->name) . ': (' . Yii::$app->formatter->asInteger($cantidad) . ') ' . Yii::$app->formatter->asCurrency($costo);
            }
        } else {
            // Fallback a áreas fijas si no hay centros dinámicos
            $totalesHtml[] = 'Almacén: (' . Yii::$app->formatter->asInteger($cantidadesPorArea['almacen'] ?? 0) . ') ' . Yii::$app->formatter->asCurrency($totalesPorArea['almacen'] ?? 0);
            $totalesHtml[] = 'Cocina: (' . Yii::$app->formatter->asInteger($cantidadesPorArea['cocina'] ?? 0) . ') ' . Yii::$app->formatter->asCurrency($totalesPorArea['cocina'] ?? 0);
            $totalesHtml[] = 'Barra: (' . Yii::$app->formatter->asInteger($cantidadesPorArea['barra'] ?? 0) . ') ' . Yii::$app->formatter->asCurrency($totalesPorArea['barra'] ?? 0);
            $totalesHtml[] = 'Servicio: (' . Yii::$app->formatter->asInteger($cantidadesPorArea['servicio'] ?? 0) . ') ' . Yii::$app->formatter->asCurrency($totalesPorArea['servicio'] ?? 0);
            $totalesHtml[] = 'Otro: (' . Yii::$app->formatter->asInteger($cantidadesPorArea['otro'] ?? 0) . ') ' . Yii::$app->formatter->asCurrency($totalesPorArea['otro'] ?? 0);
        }
        echo implode(' | ', $totalesHtml);
        ?>
    </div>
    <div class="table-responsive sticky-header-container" style="overflow-x:auto;">
    <?php
    $columns = [
            [
                'attribute' => 'ingredient_stock_id',
                'headerOptions' => ['class' => 'sticky-col', 'style' => 'min-width: 250px; width: 25%;text-align:center;'],
                'contentOptions' => ['class' => 'sticky-col','style' => 'text-align:center;'],
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
                'label' => 'Unidad<br>Uso',
                'encodeLabel' => false,
                'value' => function($model) {
                    return $model->ingredientStock && isset($model->ingredientStock->portion_um) ? $model->ingredientStock->portion_um : '-';
                },
                'headerOptions' => ['style' => 'min-width: 120px; width: 10%;text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
            ],
            [
                'label' => 'Familias',
                'encodeLabel' => false,
                'value' => function($model) {
                    return $model->ingredientStock && isset($model->ingredientStock->category->name) ? $model->ingredientStock->category->name : '-';
                },
                'headerOptions' => ['style' => 'min-width: 120px; width: 12%;text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
                'filter' => \yii\helpers\Html::activeDropDownList(
                    $searchModel,
                    'categoria',
                    \common\models\Category::find()
                        ->where([
                            'or',
                            ['business_id' => \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY)['id'] ?? null],
                            ['builtin' => 1]
                        ])
                        ->select(['name', 'id'])->indexBy('id')->column(),
                    [
                        'class' => 'form-control',
                        'prompt' => 'Todas'
                    ]
                ),
            ],
    ];

    // Agregar columnas dinámicas por centro de consumo después de Familias
    if (!empty($consumptionCenters)) {
        foreach ($consumptionCenters as $center) {
            $columns[] = [
                'label' => Html::encode($center->name),
                'encodeLabel' => false,
                'headerOptions' => ['style' => 'text-align:center; min-width:120px;'],
                'contentOptions' => ['style' => 'text-align:center;'],
                'value' => function($model) use ($center) {
                    foreach ($model->inventoryConsumptionCenters as $icc) {
                        if ($icc->consumption_center_id == $center->id) {
                            return formatNumber($icc->quantity);
                        }
                    }
                    return '-';
                }
            ];
        }
    }

    // Agregar las columnas restantes
    $columns = array_merge($columns, [
            [
                'label' => 'Mínimo',
                'encodeLabel' => false,
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
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
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
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
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
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
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
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
                'headerOptions' => ['style' => 'text-align:center;'],
                'value' => function($model) {
                    $total = 0;
                    if (!empty($model->inventoryConsumptionCenters)) {
                        foreach ($model->inventoryConsumptionCenters as $icc) {
                            $total += $icc->quantity;
                        }
                    }
                    return $total;
                },
                'contentOptions' => ['style' => 'font-weight:bold; background:#f8f9fa;text-align:center;'],
            ],
            [
                'label' => 'Costo<br>Insumo',
                'encodeLabel' => false,
                'headerOptions' => ['style' => 'text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
                'value' => function($model) {
                    return $model->ingredientStock && isset($model->ingredientStock->lastUnitPrice) ?
                        formatPrice($model->ingredientStock->lastUnitPrice) : '-';
                },
            ],
            [
                'label' => 'Costo<br>Total',
                'encodeLabel' => false,
                'headerOptions' => ['style' => 'text-align:center;'],
                'value' => function($model) {
                    $total = 0;
                    if (!empty($model->inventoryConsumptionCenters)) {
                        foreach ($model->inventoryConsumptionCenters as $icc) {
                            $total += $icc->quantity;
                        }
                    }
                    $precio = $model->ingredientStock && isset($model->ingredientStock->lastUnitPrice) ? $model->ingredientStock->lastUnitPrice : 0;
                    return formatCost($total * $precio);
                },
                'contentOptions' => ['style' => 'font-weight:bold; background:#eaf7ea;text-align:center;'],
            ],
    ]);

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => isset($searchModel) ? $searchModel : null,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => $columns,
    ]);
    ?>
    </div>
    <div class="mt-3">
        <?= Html::a('<i class="fas fa-history"></i> Ver Historial de Ajustes', ['/kpi/historial-ajustes'], ['class' => 'btn btn-secondary mr-2']) ?>
        <?php if (Yii::$app->user->can('storage_admin') || Yii::$app->user->can('manage_users') || Yii::$app->user->can('administrator')): ?>
            <?= Html::a('<i class="fas fa-edit"></i> Editar inventario', ['edit', 'fecha' => $fecha], ['class' => 'btn btn-warning mr-2']) ?>
        <?php endif; ?>
        <?= Html::a('Volver al listado de fechas', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmacionAjusteModal" tabindex="-1" role="dialog" aria-labelledby="confirmacionAjusteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmacionAjusteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Ajuste Masivo
                </h5>
                <button type="button" class="close" onclick="cerrarModalConfirmacion()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Está a punto de ajustar las existencias del sistema para que coincidan con los valores del inventario físico.</p>
                <p class="mb-3">Este cambio reemplazará las existencias actuales con los valores del inventario físico y quedará registrado en el historial.</p>
                <p class="font-weight-bold text-warning">¿Desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalConfirmacion()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="confirmarAjuste()">
                    <i class="fas fa-check"></i> Confirmar Ajuste
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let fechaAjuste = '';

function ajustarMasivoInventario(fecha) {
    fechaAjuste = fecha;
    $('#confirmacionAjusteModal').modal('show');
}

function cerrarModalConfirmacion() {
    $('#confirmacionAjusteModal').modal('hide');
}

function confirmarAjuste() {
    cerrarModalConfirmacion();
    
    // Mostrar progreso
    var progressHtml = '<div class="alert alert-info" id="progressAlert" style="margin-top: 15px;">' +
        '<i class="fas fa-spinner fa-spin"></i> Procesando ajuste masivo...' +
        '</div>';
    $('.mb-3').after(progressHtml);
    
    $.ajax({
        url: '<?= \yii\helpers\Url::to(['/kpi/ajustar-inventario-completo']) ?>',
        type: 'POST',
        data: {
            fecha: fechaAjuste
        },
        success: function(response) {
            $('#progressAlert').remove();
            if (response.success) {
                alert('Se ajustaron ' + response.ajustados + ' insumos correctamente.');
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'No se pudieron ajustar los insumos'));
            }
        },
        error: function() {
            $('#progressAlert').remove();
            alert('Error al conectar con el servidor');
        }
    });
}

$(document).ready(function() {
    // Event listeners para cerrar modal
    $('.close').on('click', function() {
        cerrarModalConfirmacion();
    });
    
    $('#confirmacionAjusteModal').on('click', function(e) {
        if (e.target === this) {
            cerrarModalConfirmacion();
        }
    });
    
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalConfirmacion();
        }
    });
});
</script>
