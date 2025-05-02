<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $ingredientCount array */

$this->title = Yii::t('app', "Subrecetas");
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$this->registerJsFile(Yii::getAlias('@web/js/sub-standard-recipe/index.js'), ['depends' => \yii\web\YiiAsset::class]);
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
        <?= Html::a('Exportar Subrecetas Completas', ['#'], ['class' => 'btn btn-success', 'id' => 'download-recipes-complete-excel']) ?>

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
            ],
            [
                'attribute' => 'custom_cost',
                'format' => 'currency',
                'label' => "Costo"
            ],
            [
                'attribute' => 'ingredientCount',
                'label' => 'Cantidad<br>Ingredientes',
                'value' => function ($model) use ($ingredientCount) {
                    return $ingredientCount[$model->id]['ingredientCount'] ?? 0;
                },
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'subRecipeCount',
                'label' => 'Cantidad<br>Recetas',
                'value' => function ($model) use ($ingredientCount) {
                    return $ingredientCount[$model->id]['subRecipeCount'] ?? 0;
                },
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'type_of_recipe',
                'label' => 'Familia',
                'format' => 'text',
                'enableSorting' => true, // Enable sorting for this column
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
                        return Html::a('<i class="fa fa-edit"></i>', \yii\helpers\Url::to(['standard-recipe/update', 'id' => $model->id, 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'text-warning']);
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
    'title' => Yii::t('app', "Exportar recetas seleccionadas"),
]);
?>
<p>¿Deseas exportar todas las recetas seleccionadas o solo las de la página actual?</p>
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
<p>No has seleccionado ninguna receta para exportar. Por favor, selecciona al menos una receta.</p>
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
$this->registerJs("
    // Nuevo código para manejo de exportación
document.getElementById('download-recipes-complete-excel').addEventListener('click', function(e) {
    e.preventDefault();
    
    // Obtener IDs de las filas seleccionadas
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
");
?>
<style>
    #btn-delete-recipes:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
<script>
    // Detector de cambio en elementos por página
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    
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
