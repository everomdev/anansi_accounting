<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\InventorySearch;

$this->title = 'Editar Inventario';
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
    .inventory-input {
        width: 80px;
        text-align: center;
    }
</style>

<div class="inventory-edit">
    <h2>Editar Inventario del <?= date('d/m/Y H:i', strtotime($dateEnd)) ?></h2>
    <?php if (!empty($dateEnd)) : ?>
        <div class="text-muted" style="font-size:16px;margin-bottom:8px;">Iniciado el <?= date('d/m/Y H:i', strtotime($fecha)) ?></div>
    <?php endif; ?>
    
    <div class="alert alert-warning" style="margin-bottom:18px;">
        <strong><i class="fas fa-edit"></i> Modo Edición:</strong> Modifica las cantidades según necesites y haz clic en "Guardar Cambios" al final.
        <br><strong>Nota:</strong> Se muestran todos los insumos sin paginación para asegurar que todos los cambios se guarden correctamente.
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'inventory-edit-form',
        'action' => ['edit', 'fecha' => $fecha],
        'method' => 'post'
    ]); ?>

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
                    'id' => 'edit-title-filter',
                    'style' => 'padding-right: 30px;'
                ]) . 
                Html::button('×', [
                    'class' => 'btn btn-sm',
                    'id' => 'clear-edit-title-btn',
                    'onclick' => 'document.getElementById(\'edit-title-filter\').value=\'\';this.form.submit();',
                    'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (isset($searchModel) && !empty($searchModel->insumo) ? 'block' : 'none') . '; z-index: 10; cursor: pointer;',
                    'title' => 'Limpiar filtro'
                ]) . 
                '</div>',
        ],
        [
            'label' => 'Unidad<br>Cocina',
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
                'format' => 'raw',
                'headerOptions' => ['style' => 'text-align:center; min-width:120px;'],
                'contentOptions' => ['style' => 'text-align:center;'],
                'value' => function($model) use ($center) {
                    $value = '';
                    foreach ($model->inventoryConsumptionCenters as $icc) {
                        if ($icc->consumption_center_id == $center->id) {
                            $value = $icc->quantity;
                            break;
                        }
                    }
                    return Html::textInput("inventario[{$model->ingredient_stock_id}][{$center->id}]", $value, [
                        'class' => 'form-control inventory-input',
                        'type' => 'number',
                        'step' => '0.001',
                        'min' => '0'
                    ]);
                }
            ];
        }
    }

    // Agregar las columnas restantes (solo lectura)
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
            'label' => 'Costo<br>Insumo',
            'encodeLabel' => false,
            'headerOptions' => ['style' => 'text-align:center;'],
            'contentOptions' => ['style' => 'text-align:center;'],
            'value' => function($model) {
                return $model->ingredientStock && isset($model->ingredientStock->lastUnitPrice) ?
                    formatPrice($model->ingredientStock->lastUnitPrice) : '-';
            },
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

    <div style="position: fixed; bottom: 32px; right: 32px; z-index: 1000;">
        <div style="display: flex; gap: 16px;">
            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Cambios', [
                'class' => 'btn btn-success',
                'style' => 'box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 18px; padding: 12px 32px;',
                'id' => 'btn-guardar-inventario'
            ]) ?>
            <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['detalle', 'fecha' => $fecha], [
                'class' => 'btn btn-outline-danger',
                'style' => 'box-shadow: 0 2px 8px rgba(0,0,0,0.10); font-size: 18px; padding: 12px 32px;'
            ]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="mt-3">
        <?= Html::a('<i class="fas fa-eye"></i> Ver Detalle (Solo Lectura)', ['detalle', 'fecha' => $fecha], ['class' => 'btn btn-info mr-2']) ?>
        <?= Html::a('<i class="fas fa-list"></i> Volver al listado de fechas', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Permitir decimales con coma o punto
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('inventory-input')) {
            if (e.target.value.includes(',')) {
                e.target.value = e.target.value.replace(/,/g, '.');
            }
        }
    });
    
    // Confirmación antes de guardar
    document.getElementById('btn-guardar-inventario').addEventListener('click', function(e) {
        if (!confirm('¿Estás seguro de que quieres guardar los cambios en este inventario?')) {
            e.preventDefault();
        }
    });
});
</script>
