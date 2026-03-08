<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\Ingr                    'style' => 'padding-right: 30px; background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS1IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 10px center;'
ientStockSearch */
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
        color: #fca311;
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
                <?php foreach ([10, 25, 50, 100, 500] as $value): ?>
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
                'headerOptions' => ['style' => 'min-width: 150px; width: 25%;'],
                'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'key', [
                    'class' => 'form-control form-control-sm',
                    'data-trigger-change' => 'true',
                    'style' => ' background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 5px center;'


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
                        'style' => ' background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 5px center;'

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
                    'data-trigger-change' => 'true',
                    'style' => ' background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 5px center;'

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
                    'data-trigger-change' => 'true',
                    'style' => ' background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 5px center;'

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
                    'data-trigger-change' => 'true',
                    'style' => ' background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 5px center;'

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
                    'data-trigger-change' => 'true',
                    'style' => ' background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 5px center;'

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
                    },
                    'update' => function ($url, $model, $key) use ($count) {
                        $recipeCount = $count[$model->id]['recipes'] ?? 0;
                        $subRecipeCount = $count[$model->id]['subRecipes'] ?? 0;
                        
                        return '<a href="#" title="' . Yii::t('yii', 'Update') . '" class="update-ingredient-link text-warning" data-update-url="' . \yii\helpers\Html::encode($url) . '" data-recipes="' . $recipeCount . '" data-subrecipes="' . $subRecipeCount . '"><svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg></a>';
                    },
                    'delete' => function ($url, $model, $key) use ($count) {
                        $recipeCount = $count[$model->id]['recipes'] ?? 0;
                        $subRecipeCount = $count[$model->id]['subRecipes'] ?? 0;
                        
                        return '<a href="#" title="' . Yii::t('yii', 'Delete') . '" class="delete-ingredient-link text-warning" data-delete-url="' . \yii\helpers\Html::encode($url) . '" data-recipes="' . $recipeCount . '" data-subrecipes="' . $subRecipeCount . '"><svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z"></path></svg></a>';
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
    
    // ===== MANEJO DE ADVERTENCIAS PARA EDITAR/ELIMINAR INSUMOS =====
    
    // Función para verificar y loggear los links
    function checkIngredientLinks() {
        var updateLinks = $('.update-ingredient-link').length;
        var deleteLinks = $('.delete-ingredient-link').length;
        
        // Verificar el HTML de los primeros links
        $('.update-ingredient-link').slice(0, 2).each(function(i) {
        });
    }
    
    // Ejecutar al inicio
    checkIngredientLinks();
    
    // Re-ejecutar después de cada recarga PJAX
    $(document).on('pjax:success', '#ingredient-stock-pjax', function() {
        checkIngredientLinks();
    });
    
    // Manejar click en botón de editar insumo
    $(document).on('click', '.update-ingredient-link', function(e) {
        e.preventDefault();
        
        var link = $(this);
        var url = link.attr('data-update-url');
        var recipes = parseInt(link.attr('data-recipes')) || 0;
        var subrecipes = parseInt(link.attr('data-subrecipes')) || 0;
        
        
        // Si no tiene recetas ni subrecetas, ir directo
        if (recipes === 0 && subrecipes === 0) {
            window.location.href = url;
            return;
        }
        
        // Construir mensaje de advertencia
        var message = \"⚠️ ADVERTENCIA: Este insumo está siendo utilizado en:\\n\\n\";
        if (recipes > 0) {
            message += \"• \" + recipes + \" receta\" + (recipes > 1 ? \"s\" : \"\") + \"\\n\";
        }
        if (subrecipes > 0) {
            message += \"• \" + subrecipes + \" subreceta\" + (subrecipes > 1 ? \"s\" : \"\") + \"\\n\";
        }
        message += \"\\nModificar este insumo puede afectar los costos y cálculos de estas recetas.\\n\\n¿Desea continuar?\";
        
        if (confirm(message)) {
            window.location.href = url;
        } else {
            console.log('  => Usuario canceló');
        }
    });
    
    // Manejar click en botón de eliminar insumo individual
    $(document).on('click', '.delete-ingredient-link', function(e) {
        e.preventDefault();
        
        var link = $(this);
        var url = link.attr('data-delete-url');
        var recipes = parseInt(link.attr('data-recipes')) || 0;
        var subrecipes = parseInt(link.attr('data-subrecipes')) || 0;
        
        
        var message = '';
        
        if (recipes > 0 || subrecipes > 0) {
            message = \"⚠️ ADVERTENCIA: Este insumo está siendo utilizado en:\\n\\n\";
            if (recipes > 0) {
                message += \"• \" + recipes + \" receta\" + (recipes > 1 ? \"s\" : \"\") + \"\\n\";
            }
            if (subrecipes > 0) {
                message += \"• \" + subrecipes + \" subreceta\" + (subrecipes > 1 ? \"s\" : \"\") + \"\\n\";
            }
            message += \"\\nEliminar este insumo afectará estas recetas y puede causar errores en el sistema.\\n\\n¿Está seguro de que desea eliminarlo?\";
        } else {
            message = '¿Está seguro de que desea eliminar este insumo?';
        }
        
        if (confirm(message)) {
            var form = $('<form>', { method: 'POST', action: url });
            var csrfParam = $('meta[name=\"csrf-param\"]').attr('content');
            var csrfToken = $('meta[name=\"csrf-token\"]').attr('content');
            form.append($('<input>', { type: 'hidden', name: csrfParam, value: csrfToken }));
            $('body').append(form);
            form.submit();
        } else {
            console.log('  => Usuario canceló eliminación');
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