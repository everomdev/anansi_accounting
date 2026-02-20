<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IngredientStockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $count array */

$this->title = Yii::t('app', 'Catálogo de insumos');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$this->registerJsFile(Yii::getAlias("@web/js/ingredient-stock/index.js"), [
    'depends' => \yii\web\YiiAsset::class,
    'position' => $this::POS_END
]);
$this->registerJsFile(Yii::getAlias('@web/js/ingredient-stock/sort.js'), ['depends' => \yii\web\YiiAsset::class]);
$this->registerCss('
    .grid-view th a {
        color: #333;
        text-decoration: none;
        position: relative;
        display: block;
    }
    .grid-view th a.asc:after {
        content: " ▲";
        font-size: 12px;
    }
    .grid-view th a.desc:after {
        content: " ▼";
        font-size: 12px;
    }
    .grid-view th a:hover {
        color: #333;
        text-decoration: none;
    }
        
    /* Estilos para encabezados fijos */
    .sticky-header-container {
        position: relative;
        overflow: auto;
        max-height: calc(90vh - 80px); /* Ajusta según tu diseño */
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
    }
    
    /* Mejorar la apariencia de las columnas ordenables */
    .sortable-column {
        background-color: rgba(0,0,0,0.01);
    }
    
    /* Asegurar que el texto de los encabezados no se corte */
    .sticky-header-table th {
        white-space: normal;
        vertical-align: middle;
    }
    
    /* Estilos para filtros activos */
    .filter-active {
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        background-color: #f8f9ff !important;
    }
    
    /* Mejorar la apariencia de los campos de filtro */
    .grid-view .filters input[type="text"] {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0.375rem 0.75rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .grid-view .filters input[type="text"]:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
');
?>
<div class="ingredient-stock-index">
    <div class="d-flex flex-wrap">
        <div class="p-2"><?= Html::a(Yii::t('app', 'Add resource'), ['create'], ['class' => 'btn btn-warning']) ?></div>
        <div class="p-2">
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Descargar referencias', [
                'icon' => ""
            ]), ['ingredient-stock/download-references', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Descargar plantilla', [
                'icon' => ""
            ]), '#', ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-download-template"]) ?>
        </div>
        <div class="p-2">
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar insumos', [
                'icon' => ""
            ]), '#', ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
        </div>
        <div class="p-2">
        <?= Html::a(Yii::t('app', 'Duplicate'), \yii\helpers\Url::to(['ingredient-stock/duplicate-insumos']), ['class' => 'btn btn-success', 'id' => 'btn-duplicate-insumos']) ?>
        </div>
        <div class="p-2">
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Exportar insumos', [
                'icon' => ""
            ]), ['ingredient-stock/export', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Eliminar Seleccionados', ['icon' => ""
            ]), ['#'], ['class' => 'btn btn-danger', 'id' => 'bulk-remove']) ?>
        </div>
    </div>
<!-- Selector de elementos por página y filtros mejorados -->
<div class="row mb-2 align-items-center">
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
            <select id="per-page-selector" class="form-select form-select-sm" style="width: auto; max-width: 78px;">
                <?php foreach ([10, 25, 50, 100] as $value): ?>
                <option value="<?= $value ?>" <?= $dataProvider->pagination->pageSize == $value ? 'selected' : '' ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
            <span class="input-group-text bg-light"><?= Yii::t('app', 'insumos por página') ?></span>
        </div>
    </div>
</div>
    <?php Pjax::begin(['id' => 'ingredient-stock-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="table-responsive sticky-header-container">
    <div class="row"></div>    <?= GridView::widget([
        'id' => 'ingredient-stock-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'pager' => [
            'class' => \yii\bootstrap5\LinkPager::class,
            'options' => ['class' => 'pagination pagination-sm'],
            'maxButtonCount' => 10,
            'firstPageLabel' => '<i class="fas fa-angle-double-left">Primera página</i>',
            'lastPageLabel' => '<i class="fas fa-angle-double-right">Última página</i>',
            'prevPageLabel' => '<i class="fas fa-angle-left"></i>',
            'nextPageLabel' => '<i class="fas fa-angle-right"></i>',
        ],
        'columns' => [
            ['class' => \yii\grid\CheckboxColumn::class],
            [
                'label' => '#',
                'value' => function ($model, $key, $index, $grid) use ($dataProvider) {
                    // Calculate overall position based on current page and per page count
                    $pagination = $dataProvider->getPagination();
                    $page = $pagination->getPage();
                    $pageSize = $pagination->getPageSize();
                    return $page * $pageSize + $index + 1;
                },
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'key',
                'label' => 'Clave',
                'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'key', [
                    'class' => 'form-control form-control-sm',
                    'data-trigger-change' => 'true'
                ]),
            ],
            [
                'attribute' => 'ingredient',
                'label' => 'Ingrediente',
                'headerOptions' => ['style' => 'min-width: 250px; width: 25%;'],
                'filter' => '<div style="position: relative;">' . 
                    Html::textInput('IngredientStockSearch[ingredient]', $searchModel->ingredient, [
                        'class' => 'form-control',
                        'placeholder' => 'Buscar por nombre...',
                        'id' => 'title-filter',
                        'style' => 'padding-right: 30px;'
                    ]) . 
                    Html::button('×', [
                        'class' => 'btn btn-sm',
                        'id' => 'clear-title-btn',
                        'onclick' => 'clearTitleFilter()',
                        'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (empty($searchModel->ingredient) ? 'none' : 'block') . '; z-index: 10; cursor: pointer;',
                        'title' => 'Limpiar filtro'
                    ]) . 
                    '</div>',
            ],
            [
                'attribute' => 'categoria',
                'label' => 'Familia',
                'value' => function ($data) {
                    return $data->category ? $data->category->name : '-';
                },
                'filter' => \yii\helpers\Html::activeDropDownList(
                    $searchModel,
                    'categoria',
                    \yii\helpers\ArrayHelper::map(
                        \common\models\Category::find()
                            ->where([
                                'or',
                                ['business_id' => $business->id],
                                ['builtin' => 1]
                            ])
                            ->orderBy(['name' => SORT_ASC])
                            ->all(),
                        'id',
                        'name'
                    ),
                    [
                        'class' => 'form-control form-control-sm',
                        'prompt' => 'Todas las familias',
                        'data-trigger-change' => 'true'
                    ]
                ),
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center; min-width: 120px;'],
            ],
            [
                'attribute' => 'brand',
                'label' => 'Marca',
                'value' => function ($data) {
                    return $data->brand ?: '-';
                },
                'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'brand', [
                    'class' => 'form-control form-control-sm',
                    'data-trigger-change' => 'true'
                ]),
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'presentation',
                'label' => 'Presentación',
                'value' => function ($data) {
                    return $data->presentation ?: '-';
                },
                'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'presentation', [
                    'class' => 'form-control form-control-sm',
                    'data-trigger-change' => 'true'
                ]),
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'um',
                'label' => 'Unidad<br>Compra',
                'encodeLabel' => false,
                'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'um', [
                    'class' => 'form-control form-control-sm',
                    'data-trigger-change' => 'true'
                ]),
            ],
            [
                'attribute' => 'portions_per_unit',
                'label' => 'EQ. Uni.<br>Cocina',
                'encodeLabel' => false,
                'filter' => false, // Columna calculada, no filtrable
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'portion_um',
                'label' => 'Unidad<br>Uso',
                'encodeLabel' => false,
                'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'portion_um', [
                    'class' => 'form-control form-control-sm',
                    'data-trigger-change' => 'true'
                ]),
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],            [
                'attribute' => 'yield',
                'label' => "Factor de<br>rendimiento",
                'value' => function ($data) {
                    return formatPercentage($data->yield);
                },
                'encodeLabel' => false,
                'filter' => false, // Columna calculada, no filtrable
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'lastUnitPrice',
                'label' => 'Último<br>precio',
                'value' => function ($data) {
                    return formatPrice($data->lastUnitPrice);
                },
                'encodeLabel' => false,
                'filter' => false, // Columna calculada, no filtrable
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'lastUnitPrice'
                ],
            ],
            [
                'attribute' => 'avgUnitPrice',
                'label' => 'Precio<br>promedio',
                'value' => function ($data) {
                    return formatPrice($data->avgUnitPrice);
                },
                'encodeLabel' => false,
                'filter' => false, // Columna calculada, no filtrable
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'avgUnitPrice'
                ],
            ],
            [
                'attribute' => 'higherUnitPrice',
                'label' => 'Precio<br>más alto',
                'value' => function ($data) {
                    return formatPrice($data->higherUnitPrice);
                },
                'encodeLabel' => false,
                'filter' => false, // Columna calculada, no filtrable
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'higherUnitPrice'
                ],
            ],
            [
                'attribute' => 'recipeCount',
                'label' => Yii::t('app', 'Recetas'),
                'value' => function ($model) use ($count) {
                    return $count[$model->id]['recipes'] ?? 0;
                },
                'filter' => false, // Columna calculada, no filtrable
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'recipeCount'
                ],
            ],
            [
                'attribute' => 'subRecipeCount',
                'label' => Yii::t('app', 'SubRecetas'),
                'value' => function ($model) use ($count) {
                    return $count[$model->id]['subRecipes'] ?? 0;
                },
                'filter' => false, // Columna calculada, no filtrable
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'subRecipeCount'
                ],
            ],
            //'observations:ntext',

            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => "{priceTrend} {update} {delete}",
                'buttons' => [
                    'priceTrend' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            \yii\bootstrap5\Html::tag('i', '', ['class' => 'bx bx-chart text-warning']),
                            \yii\helpers\Url::to(['ingredient-stock/price-trend', 'ingredientId' => $model->id])
                        );
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
    </div>
</div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-download-template',
    'title' => Yii::t('app', "Descargar plantilla")
]);
?>
<p>Vas a descargar la plantilla para la importación automática de insumos. <strong>Recuerda que debes utilizar la tabla de referencias para indicar las familias de tus insumos correctamente</strong></p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Descargar plantilla', [
        'icon' => ""
    ]), ['ingredient-stock/download-template', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
    <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Descargar referencias', [
        'icon' => ""
    ]), ['ingredient-stock/download-references', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-upload-file',
    'title' => Yii::t('app', "Importar insumos")
]);
$url = \yii\helpers\Url::to(['ingredient-stock/import-ingredients', 'id' => $business->id]);
\yii\bootstrap5\ActiveForm::begin([
    'action' => $url,
    'method' => 'post',
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
]);

echo \yii\bootstrap5\Html::input('file', 'ingredient-file', '', [
    'class' => 'form-control',
    'accept' => '.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
]);
echo "<br>";
echo \yii\bootstrap5\Html::submitButton(Yii::t('app', "Import"), [
    'class' => 'btn btn-success'
]);

\yii\bootstrap5\ActiveForm::end();

\yii\bootstrap5\Modal::end();
?>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-bulk-remove',
    'title' => Yii::t('app', "Eliminar insumos seleccionados"),
]);
?>
<p>¿Deseas eliminar todos los insumos seleccionados o solo los de la página actual?</p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar los seleccionados'), [
        'class' => 'btn btn-danger',
        'id' => 'delete-current-page'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar todos'), [
        'class' => 'btn btn-danger',
        'id' => 'delete-all'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>
<?php
// Modal para mostrar error cuando no hay elementos seleccionados
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-no-selection',
    'title' => Yii::t('app', "Selección vacía"),
]);
?>
<p>No has seleccionado ningún insumo para eliminar. Por favor, selecciona al menos un insumo.</p>
<div class="d-flex justify-content-end">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Entendido'), [
        'class' => 'btn btn-primary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>
<?php
// Modal para confirmar la eliminación de elementos específicos
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-confirm-selected-remove',
    'title' => Yii::t('app', "Confirmar eliminación"),
]);
?>
<p>¿Estás seguro de que deseas eliminar <span id="selected-count-message"></span> insumos?</p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar'), [
        'class' => 'btn btn-danger',
        'id' => 'confirm-delete-selected'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>
<?php
$this->registerJs("
// Definir las funciones globales primero
// Funciones globales para limpiar filtros
window.clearTitleFilter = function() {
    document.getElementById('title-filter').value = '';
    document.getElementById('clear-title-btn').style.display = 'none';
    
    // Construir URL con filtros actuales, excluyendo el título
    let url = new URL(window.location);
    url.searchParams.delete('IngredientStockSearch[ingredient]');
    
    // Recargar la tabla
    $.pjax.reload({
        container: '#ingredient-stock-pjax',
        url: url.toString(),
        timeout: 10000
    }).done(function() {
        setupFilterButtons(); // Reconfigurar botones después de la recarga
    });
};

window.clearTypeFilter = function() {
    document.getElementById('type-filter').value = '';
    document.getElementById('clear-type-btn').style.display = 'none';
    
    // Construir URL con filtros actuales, excluyendo el tipo
    let url = new URL(window.location);
    url.searchParams.delete('IngredientStockSearch[key]');
    
    // Recargar la tabla
    $.pjax.reload({
        container: '#ingredient-stock-pjax',
        url: url.toString(),
        timeout: 10000
    }).done(function() {
        setupFilterButtons(); // Reconfigurar botones después de la recarga
    });
};

// Handlers para los eventos
window.titleInputHandler = function() {
    const clearTitleBtn = document.getElementById('clear-title-btn');
    if (clearTitleBtn) {
        clearTitleBtn.style.display = this.value ? 'block' : 'none';
    }
};

window.typeSelectHandler = function() {
    const clearTypeBtn = document.getElementById('clear-type-btn');
    if (clearTypeBtn) {
        clearTypeBtn.style.display = this.value ? 'block' : 'none';
    }
};

// Función global para configurar botones de filtros
window.setupFilterButtons = function() {
    const titleInput = document.getElementById('title-filter');
    const typeSelect = document.getElementById('type-filter');
    const clearTitleBtn = document.getElementById('clear-title-btn');
    const clearTypeBtn = document.getElementById('clear-type-btn');
    
    if (titleInput && clearTitleBtn) {
        // Mostrar/ocultar botón según el estado actual
        clearTitleBtn.style.display = titleInput.value ? 'block' : 'none';
        
        // Remover listeners anteriores y agregar nuevo
        titleInput.removeEventListener('input', titleInputHandler);
        titleInput.addEventListener('input', titleInputHandler);
    }
    
    if (typeSelect && clearTypeBtn) {
        // Mostrar/ocultar botón según el estado actual
        clearTypeBtn.style.display = typeSelect.value ? 'block' : 'none';
        
        // Remover listeners anteriores y agregar nuevo
        typeSelect.removeEventListener('change', typeSelectHandler);
        typeSelect.addEventListener('change', typeSelectHandler);
    }
};
", \yii\web\View::POS_HEAD);
$this->registerJs("
// Detector de cambio en elementos por página
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    
    // Guardar en localStorage
    localStorage.setItem('ingredient-stock-per-page', pageSize);
    
    // Crear URL con nuevo tamaño de página
    let url = new URL(window.location);
    url.searchParams.set('per-page', pageSize);
    
    // Recargar con el nuevo tamaño de página
    $.pjax.reload({
        container: '#ingredient-stock-pjax',
        url: url.toString(),
        timeout: 10000
    });
});

// Cargar selección guardada al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const savedPerPage = localStorage.getItem('ingredient-stock-per-page');
    if (savedPerPage) {
        const selector = document.getElementById('per-page-selector');
        if (selector) {
            selector.value = savedPerPage;
        }
    }
});

// Destacar la columna al pasar el mouse
const headerCells = document.querySelectorAll('#ingredient-stock-grid thead th');
if (headerCells.length) {
    headerCells.forEach((cell, index) => {
        cell.addEventListener('mouseenter', () => {
            highlightColumn(index);
        });
        
        cell.addEventListener('mouseleave', () => {
            unhighlightColumn(index);
        });
    });
}

function highlightColumn(index) {
    const table = document.getElementById('ingredient-stock-grid');
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        if (cells[index]) {
            cells[index].classList.add('bg-light');
        }
    });
}

function unhighlightColumn(index) {
    const table = document.getElementById('ingredient-stock-grid');
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        if (cells[index]) {
            cells[index].classList.remove('bg-light');
        }
    });
}

// Mejorar la experiencia de filtros
$(document).ready(function() {
    // Buscar solo al presionar Enter
    $(document).on('keypress', '[data-trigger-change]', function(e) {
        if (e.which === 13) { // Enter key
            var form = $(this).closest('form');
            var container = '#ingredient-stock-pjax';
            
            // Agregar clase visual de filtro activo
            if ($(this).val().trim() !== '') {
                $(this).addClass('filter-active');
            } else {
                $(this).removeClass('filter-active');
            }
            
            $.pjax.reload({
                container: container,
                data: form.serialize(),
                timeout: 10000
            });
        }
    });
    
    // También buscar cuando el campo pierde el foco (blur)
    $(document).on('blur', '[data-trigger-change]', function() {
        var form = $(this).closest('form');
        var container = '#ingredient-stock-pjax';
        
        // Agregar clase visual de filtro activo
        if ($(this).val().trim() !== '') {
            $(this).addClass('filter-active');
        } else {
            $(this).removeClass('filter-active');
        }
        
        $.pjax.reload({
            container: container,
            data: form.serialize(),
            timeout: 10000
        });
    });
    
    // Mantener el estado visual de filtros activos después de PJAX
    $(document).on('pjax:success', function() {
        $('[data-trigger-change]').each(function() {
            if ($(this).val().trim() !== '') {
                $(this).addClass('filter-active');
            } else {
                $(this).removeClass('filter-active');
            }
        });
    });
    
    // Aplicar estado inicial de filtros activos
    $('[data-trigger-change]').each(function() {
        if ($(this).val().trim() !== '') {
            $(this).addClass('filter-active');
        }
    });
});
");
?>
<script>
    // Función para guardar elementos por página en localStorage
    function savePerPageToStorage(pageSize) {
        localStorage.setItem('ingredient-stock-per-page', pageSize);
    }
    
    // Función para obtener elementos por página del localStorage
    function getPerPageFromStorage() {
        const saved = localStorage.getItem('ingredient-stock-per-page');
        return saved || '10'; // Default 10 si no hay valor guardado
    }
    
    // Aplicar configuración guardada al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const perPageSelector = document.getElementById('per-page-selector');
        const savedPerPage = getPerPageFromStorage();
        
        // Establecer el valor guardado en el selector
        perPageSelector.value = savedPerPage;
        
        // Si el valor actual es diferente al guardado, aplicar el guardado
        const currentPageSize = '<?= $dataProvider->pagination->pageSize ?>';
        if (currentPageSize != savedPerPage) {
            // Crear URL con el valor guardado y recargar
            let url = new URL(window.location);
            url.searchParams.set('per-page', savedPerPage);
            
            $.pjax.reload({
                container: '#ingredient-stock-pjax',
                url: url.toString(),
                timeout: 10000
            });
        }
    });
    
    // Detector de cambio en elementos por página
    document.getElementById('per-page-selector').addEventListener('change', function() {
        const pageSize = this.value;
        
        // Guardar en localStorage
        savePerPageToStorage(pageSize);
        
        // Crear URL con nuevo tamaño de página
        let url = new URL(window.location);
        url.searchParams.set('per-page', pageSize);
        
        // Recargar con el nuevo tamaño de página
        $.pjax.reload({
            container: '#ingredient-stock-pjax',
            url: url.toString(),
            timeout: 10000
        });
    });
</script>