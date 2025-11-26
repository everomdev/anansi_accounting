<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $ingredientCount array */

// Definir las funciones JavaScript globalmente y de forma temprana
$this->registerJs("
console.log('Inicializando funciones globales para sub-standard-recipe');
// Funciones globales para limpiar filtros
window.clearTitleFilter = function() {
    const titleInput = document.getElementById('title-filter');
    const clearTitleBtn = document.getElementById('clear-title-btn');
    
    if (titleInput) {
        titleInput.value = '';
    }
    
    if (clearTitleBtn) {
        clearTitleBtn.style.display = 'none';
    }
    
    // Construir URL con filtros actuales, excluyendo el título
    let url = new URL(window.location);
    url.searchParams.delete('StandardRecipeSearch[title]');
    
    // Recargar la tabla
    $.pjax.reload({
        container: '#sub-standard-recipes-pjax',
        url: url.toString(),
        timeout: 10000
    }).done(function() {
        if(typeof window.setupFilterButtons === 'function') {
            window.setupFilterButtons();
        }
    });
};

window.clearTypeFilter = function() {
    const typeFilter = document.getElementById('type-filter');
    const clearTypeBtn = document.getElementById('clear-type-btn');
    
    if (typeFilter) {
        typeFilter.value = '';
    }
    
    if (clearTypeBtn) {
        clearTypeBtn.style.display = 'none';
    }
    
    // Construir URL con filtros actuales, excluyendo el tipo
    let url = new URL(window.location);
    url.searchParams.delete('StandardRecipeSearch[type_of_recipe]');
    
    // Recargar la tabla
    $.pjax.reload({
        container: '#sub-standard-recipes-pjax',
        url: url.toString(),
        timeout: 10000
    }).done(function() {
        if(typeof window.setupFilterButtons === 'function') {
            window.setupFilterButtons();
        }
    });
};

// Handlers para los eventos
window.titleInputHandler = function(event) {
    const clearTitleBtn = document.getElementById('clear-title-btn');
    if (clearTitleBtn) {
        // Usar event.target.value en lugar de this.value para mayor seguridad
        const value = event && event.target ? event.target.value : '';
        clearTitleBtn.style.display = value ? 'block' : 'none';
    }
};

window.typeSelectHandler = function(event) {
    const clearTypeBtn = document.getElementById('clear-type-btn');
    if (clearTypeBtn) {
        // Usar event.target.value en lugar de this.value para mayor seguridad
        const value = event && event.target ? event.target.value : '';
        clearTypeBtn.style.display = value ? 'block' : 'none';
    }
};

// Función global para configurar botones de filtros
window.setupFilterButtons = function() {
    const titleInput = document.getElementById('title-filter');
    const typeSelect = document.getElementById('type-filter');
    const clearTitleBtn = document.getElementById('clear-title-btn');
    const clearTypeBtn = document.getElementById('clear-type-btn');
    
    // Verificar si cada elemento existe antes de intentar manipularlo
    if (titleInput && clearTitleBtn) {
        // Mostrar/ocultar botón según el estado actual
        clearTitleBtn.style.display = titleInput.value ? 'block' : 'none';
        
        try {
            // Remover listeners anteriores y agregar nuevo
            titleInput.removeEventListener('input', window.titleInputHandler);
            titleInput.addEventListener('input', window.titleInputHandler);
        } catch (e) {
            console.log('Error al configurar event listener para title-filter:', e);
        }
    }
    
    if (typeSelect && clearTypeBtn) {
        // Mostrar/ocultar botón según el estado actual
        clearTypeBtn.style.display = typeSelect.value ? 'block' : 'none';
        
        try {
            // Remover listeners anteriores y agregar nuevo
            typeSelect.removeEventListener('change', window.typeSelectHandler);
            typeSelect.addEventListener('change', window.typeSelectHandler);
        } catch (e) {
            console.log('Error al configurar event listener para type-filter:', e);
        }
    }
};
", \yii\web\View::POS_BEGIN);

$this->title = Yii::t('app', "Subrecetas");
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

// Pasar mensajes de éxito y error a JavaScript
$successMessage = Yii::$app->session->hasFlash('success') ? Yii::$app->session->getFlash('success') : null;
$errorMessage = Yii::$app->session->hasFlash('error') ? Yii::$app->session->getFlash('error') : null;
$importErrors = Yii::$app->session->hasFlash('import_errors') ? Yii::$app->session->getFlash('import_errors') : null;
$this->registerJs("var successMessage = " . json_encode($successMessage) . ";", \yii\web\View::POS_HEAD);
$this->registerJs("var errorMessage = " . json_encode($errorMessage) . ";", \yii\web\View::POS_HEAD);
$this->registerJs("var importErrors = " . json_encode($importErrors) . ";", \yii\web\View::POS_HEAD);

$this->registerJsFile(Yii::getAlias('@web/js/sub-standard-recipe/index.js'), ['depends' => \yii\web\YiiAsset::class]);
$this->registerJsFile(Yii::getAlias('@web/js/sub-standard-recipe/sort.js'), ['depends' => \yii\web\YiiAsset::class]);
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
    
    /* Estilos para botones de limpiar filtros */
    .filter-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .clear-filter-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #999;
        font-size: 16px;
        line-height: 1;
        padding: 0;
        width: 20px;
        height: 20px;
        cursor: pointer;
        z-index: 10;
    }
    
    .clear-filter-btn:hover {
        color: #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
        border-radius: 50%;
    }
');
?>

<div class="sub-standard-recipe-index">

    <p>
        <?= Html::a(Yii::t('app', 'Nueva Subreceta'), \yii\helpers\Url::to(['standard-recipe/create', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-success m-1']) ?>
        <?= Html::a(Yii::t('app', 'Duplicate'), \yii\helpers\Url::to(['sub-standard-recipe/duplicate-recipes']), ['class' => 'btn btn-success m-1', 'id' => 'btn-duplicate-recipes']) ?>
        <?= Html::a(Yii::t('app', 'Descargar PDF'), \yii\helpers\Url::to(['standard-recipe/download-recipes-pdf', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-success m-1', 'id' => 'btn-download-recipes']) ?>
        <?= Html::a(Yii::t('app', 'Descargar Excel'), \yii\helpers\Url::to(['standard-recipe/download-recipes','type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-success m-1', 'id' => 'btn-download-recipes']) ?>
        <?= Html::a(Yii::t('app', 'Descargar Plantilla'), \yii\helpers\Url::to(['standard-recipe/export-recipes-plantilla','type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-success m-1', 'id' => 'btn-export-recipes-plantilla']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar subrecetas', [
            'icon' => ""
        ]), '#', ['class' => 'btn btn-warning m-1', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Eliminar Seleccionados', ['icon' => ""
        ]), ['#'], ['class' => 'btn btn-danger m-1', 'id' => 'btn-delete-recipes']) ?>
        <?= Html::a('Exportar Subrecetas en Excel', ['#'], ['class' => 'btn btn-success', 'id' => 'download-recipes-complete-excel']) ?>

    </p>
    <?php Pjax::begin(['id' => 'sub-standard-recipes-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="row mb-2 align-items-center">
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
            <select id="per-page-selector" class="form-select form-select-sm" style="width: auto; max-width: 78px;">
                <?php foreach ([10, 25, 50, 100] as $value): ?>
                <option value="<?= $value ?>" <?= $dataProvider->pagination->pageSize == $value ? 'selected' : '' ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
            <span class="input-group-text bg-light"><?= Yii::t('app', 'subrecetas por página') ?></span>
        </div>
    </div>
</div>
    <div class="table-responsive sticky-header-container">
    <div class="row"></div>
    <?= GridView::widget([
        'id' => 'sub-standard-recipes-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
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
                'enableSorting' => false,
            ],
            [
                'attribute' => 'title',
                'label' => Yii::t('app', 'Nombre de la Subreceta'),
                'value' => function ($model) {
                    return $model->title . ' (' . $model->um . ')';
                },
                'headerOptions' => ['style' => 'min-width: 250px; width: 25%;'],
                'filter' => '<div style="position: relative;">' . 
                    Html::textInput('StandardRecipeSearch[title]', $searchModel->title, [
                        'class' => 'form-control',
                        'placeholder' => 'Buscar por nombre...',
                        'id' => 'title-filter',
                        'style' => 'padding-right: 30px;'
                    ]) . 
                    Html::button('×', [
                        'class' => 'btn btn-sm',
                        'id' => 'clear-title-btn',
                        'onclick' => 'clearTitleFilter()',
                        'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (empty($searchModel->title) ? 'none' : 'block') . '; z-index: 10; cursor: pointer;',
                        'title' => 'Limpiar filtro'
                    ]) . 
                    '</div>',
            ],            [
                'attribute' => 'custom_cost',
                'label' => "Costo",
                'value' => function($model) {
                    return formatCost($model->custom_cost);
                },
                'contentOptions' => ['style' => 'text-align: right;'],
            ],
            [
                'attribute' => 'ingredientCount',
                'label' => 'Cantidad<br>Ingredientes',
                'value' => function ($model) use ($ingredientCount) {
                    return $ingredientCount[$model->id]['ingredientCount'] ?? 0;
                },
                'encodeLabel' => false,
                'enableSorting' => true,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'ingredientCount'
                ],
            ],
            [
                'attribute' => 'RecipeCount',
                'label' => 'Cantidad<br>Recetas',
                'value' => function ($model) use ($ingredientCount) {
                    return $ingredientCount[$model->id]['RecipeCount'] ?? 0;
                },
               'encodeLabel' => false,
                'enableSorting' => true,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'subRecipeCount'
                ],
            ],
            [
                'attribute' => 'subRecipeCount',
                'label' => 'Cantidad<br>SubRecetas',
                'value' => function ($model) use ($ingredientCount) {
                    return $ingredientCount[$model->id]['subRecipeCount'] ?? 0;
                },
               'encodeLabel' => false,
                'enableSorting' => true,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'subRecipeCount'
                ],
            ],
            [
                'attribute' => 'type_of_recipe',
                'label' => 'Categoría',
                'enableSorting' => true,
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
                'filter' => (function() use ($searchModel, $business) {
                    $categories = \yii\helpers\ArrayHelper::map(
                        \common\models\RecipeCategory::find()
                            ->where(['type' => 'sub', 'business_id' => $business->id])
                            ->orderBy('name')
                            ->all(),
                        'name',
                        'name'
                    );
                    $items = \yii\helpers\ArrayHelper::merge(['' => 'Todas las categorías'], $categories);
                    return '<div style="position: relative;">' .
                        Html::dropDownList('StandardRecipeSearch[type_of_recipe]', $searchModel->type_of_recipe, $items, [
                            'class' => 'form-control',
                            'id' => 'type-filter',
                            'style' => 'padding-right: 30px;'
                        ]) .
                        Html::button('×', [
                            'class' => 'btn btn-sm',
                            'id' => 'clear-type-btn',
                            'onclick' => 'clearTypeFilter()',
                            'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (empty($searchModel->type_of_recipe) ? 'none' : 'block') . '; z-index: 10; cursor: pointer;',
                            'title' => 'Limpiar filtro'
                        ]) .
                        '</div>';
                })(),
            ],
            [
                'attribute' => 'observation',
                'label' => 'Observaciones',
                'format' => 'html', // Esto permite renderizar HTML
                'value' => function($model) {
                    // Elimina Html::encode para permitir que el HTML se renderice
                    return $model->observation ? $model->observation : 'Sin observaciones';
                },
                'encodeLabel' => false,
                'enableSorting' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],

//            'costPercent:percent',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-pencil-alt"></i>', \yii\helpers\Url::to(['standard-recipe/update', 'id' => $model->id, 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'text-warning']);
                    },

                ],
            ],
        ],
    ]); ?>

<?php Pjax::end(); ?>
    </div>
</div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-upload-file',
    'title' => Yii::t('app', "Importar recetas")
]);
$url = \yii\helpers\Url::to(['standard-recipe/import-sub-recipes', 'id' => $business->id]);
\yii\bootstrap5\ActiveForm::begin([
    'action' => $url,
    'method' => 'post',
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
]);

echo \yii\bootstrap5\Html::input('file', 'ingredient-file', '', [
    'class' => 'form-control'
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
    'title' => Yii::t('app', "Eliminar subrecetas seleccionadas"),
]);
?>
<p>¿Deseas eliminar todas las subrecetas seleccionadas o solo las de la página actual?</p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar las seleccionadas'), [
        'class' => 'btn btn-danger',
        'id' => 'delete-current-page'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar todas'), [
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
<p>No has seleccionado ninguna subreceta para eliminar. Por favor, selecciona al menos una subreceta.</p>
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
<p>¿Estás seguro de que deseas eliminar <span id="selected-count-message"></span> subrecetas?</p>
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
// Modal para exportar recetas seleccionadas
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-export-recipes',
    'title' => Yii::t('app', "Exportar subrecetas seleccionadas"),
]);
?>
<p>¿Deseas exportar todas las subrecetas seleccionadas o solo las de la página actual?</p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Exportar las seleccionadas'), [
        'class' => 'btn btn-success',
        'id' => 'export-current-page'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Exportar todas'), [
        'class' => 'btn btn-success',
        'id' => 'export-all'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
// Modal para mostrar error cuando no hay elementos seleccionados para exportar
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-no-export-selection',
    'title' => Yii::t('app', "Selección vacía"),
]);
?>
<p>No has seleccionado ninguna subreceta para exportar. Por favor, selecciona al menos una subreceta.</p>
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
// Modal para mostrar errores de importación
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-import-errors',
    'title' => Yii::t('app', "Errores en la importación"),
]);
?>
<div id="import-errors-content">
    <!-- Los errores se cargarán aquí dinámicamente -->
</div>
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
// Modal para mostrar mensajes de éxito
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-success',
    'title' => Yii::t('app', "¡Éxito!"),
]);
?>
<div id="success-message-content">
    <!-- El mensaje se cargará aquí dinámicamente -->
</div>
<div class="d-flex justify-content-end">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Entendido'), [
        'class' => 'btn btn-success',
        'data-bs-dismiss' => 'modal'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
// Modal para mostrar mensajes de error
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-error',
    'title' => Yii::t('app', "Error"),
]);
?>
<div id="error-message-content">
    <!-- El mensaje se cargará aquí dinámicamente -->
</div>
<div class="d-flex justify-content-end">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Entendido'), [
        'class' => 'btn btn-danger',
        'data-bs-dismiss' => 'modal'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
$this->registerJs("
    // Nuevo código para manejo de exportación
    document.getElementById('download-recipes-complete-excel').addEventListener('click', function(e) {
        e.preventDefault();
        const selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
        if (selectedIds.length === 0) {
            // Mostrar modal de error si no hay selección
            const noSelectionModal = new bootstrap.Modal(document.getElementById('modal-no-export-selection'));
            noSelectionModal.show();
            return;
        }
        // Mostrar el modal de confirmación para exportación
        const exportModal = new bootstrap.Modal(document.getElementById('modal-export-recipes'));
        exportModal.show();
    });

    // Manejar la exportación de las recetas seleccionadas
    document.getElementById('export-current-page').addEventListener('click', function() {
        const selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
        if (selectedIds.length > 0) {
            window.location.href = '" . \yii\helpers\Url::to(['standard-recipe/export-recipes-to-excel']) . "?id=' + selectedIds.join(',') + '&type=sub';
        }
    });

    // Manejar la exportación de todas las recetas (todas las páginas)
    document.getElementById('export-all').addEventListener('click', function() {
        const selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
        if (selectedIds.length > 0) {
            window.location.href = '" . \yii\helpers\Url::to(['standard-recipe/export-recipes-to-excel']) . "?id=' + selectedIds.join(',') + '&all=true' + '&type=sub';
        }
    });
", \yii\web\View::POS_HEAD);
?>
<script>
    // Función para guardar elementos por página en localStorage
    function savePerPageToStorage(pageSize) {
        localStorage.setItem('sub-standard-recipe-per-page', pageSize);
    }
    
    // Función para obtener elementos por página del localStorage
    function getPerPageFromStorage() {
        const saved = localStorage.getItem('sub-standard-recipe-per-page');
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
                container: '#sub-standard-recipes-pjax',
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
            container: '#sub-standard-recipes-pjax',
            url: url.toString(),
            timeout: 10000
        });
    });
</script>
<?php
// Aquí había una definición duplicada de las funciones de filtrado que ahora está al inicio del archivo
?>
<style>
    #btn-delete-recipes:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
<?php
$this->registerJs("
    // Configurar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof window.setupFilterButtons === 'function') {
            try {
                window.setupFilterButtons();
            } catch(e) {
                console.error('Error al configurar filtros en DOMContentLoaded:', e);
            }
        }
    });

    // Reconfigurar después de PJAX
    $(document).on('pjax:complete', '#sub-standard-recipes-pjax', function() {
        if(typeof window.setupFilterButtons === 'function') {
            try {
                window.setupFilterButtons();
                console.log('Filtros configurados después de pjax:complete');
            } catch(e) {
                console.error('Error al configurar filtros después de PJAX:', e);
            }
        } else {
            console.warn('setupFilterButtons no está definido después de PJAX');
        }
    });

    // Verificar si hay errores de importación al cargar la página
    $(document).ready(function() {
        if (typeof importErrors !== 'undefined' && importErrors.length > 0) {
            var errorHtml = '<ul class=\"list-group\">';
            importErrors.forEach(function(error) {
                errorHtml += '<li class=\"list-group-item list-group-item-danger\">' + error + '</li>';
            });
            errorHtml += '</ul>';
            $('#import-errors-content').html(errorHtml);
            var importErrorsModal = new bootstrap.Modal(document.getElementById('modal-import-errors'));
            importErrorsModal.show();
        }
        
        // Verificar si hay mensaje de éxito
        if (typeof successMessage !== 'undefined' && successMessage !== null) {
            $('#success-message-content').html('<p>' + successMessage + '</p>');
            var successModal = new bootstrap.Modal(document.getElementById('modal-success'));
            successModal.show();
        }
        
        // Verificar si hay mensaje de error
        if (typeof errorMessage !== 'undefined' && errorMessage !== null) {
            $('#error-message-content').html('<p>' + errorMessage + '</p>');
            var errorModal = new bootstrap.Modal(document.getElementById('modal-error'));
            errorModal.show();
        }
    });
", \yii\web\View::POS_READY);
?>
