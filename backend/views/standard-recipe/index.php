<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @vae $ingredientCount array */

$this->title = 'Recetas Estándar';
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

$this->registerJsFile(Yii::getAlias('@web/js/standard-recipe/index.js'), ['depends' => \yii\web\YiiAsset::class]);
$this->registerJsFile(Yii::getAlias('@web/js/standard-recipe/sort.js'), ['depends' => \yii\web\YiiAsset::class]);
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
');
?>
<div class="standard-recipe-index">
    <p>
        <div class="row">
            <div class="col-md-12">
                <?= Html::a(Yii::t('app', 'Create new recipe'), \yii\helpers\Url::to(['standard-recipe/create', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success']) ?>
                <?= Html::a(Yii::t('app', 'Duplicate'), \yii\helpers\Url::to(['standard-recipe/duplicate-recipes']), ['class' => 'btn btn-success', 'id' => 'btn-duplicate-recipes']) ?>
                <?= Html::a(Yii::t('app', 'Descargar PDF'), \yii\helpers\Url::to(['standard-recipe/download-recipes-pdf', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success', 'id' => 'btn-download-recipes']) ?>
                <?= Html::a(Yii::t('app', 'Descargar Excel'), \yii\helpers\Url::to(['standard-recipe/download-recipes','type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success', 'id' => 'btn-download-recipes']) ?>
                <?= Html::a(Yii::t('app', 'Descargar Plantilla'), \yii\helpers\Url::to(['standard-recipe/export-recipes-plantilla','type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success', 'id' => 'btn-export-recipes-plantilla']) ?>
                <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar recetas', [
                    'icon' => ""
                ]), '#', ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
            </div>
        </div>
        <div class="row" style="margin-top: 10px; margin-bottom: 10px;">
            <div class="col-md-12">
            <?= Html::a('Descargar Recetas Completas', ['standard-recipe/download-complete-recipe-pdf'], ['class' => 'btn btn-success', 'id' => 'btn-download-recipes-complete']) ?>
            <?= Html::a('Exportar Recetas Completas', ['standard-recipe/export-recipes-to-excel'], ['class' => 'btn btn-success', 'id' => 'btn-download-recipes-complete-excel']) ?>
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Eliminar Seleccionados', ['icon' => ""
                ]), ['#'], ['class' => 'btn btn-danger', 'id' => 'btn-delete-recipes']) ?>
            </div>
        </div>
    <?php Pjax::begin(['id' => 'standard-recipes-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row"></div>
    <?= GridView::widget([
        'id' => 'standard-recipes-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
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
                'attribute' => 'title',
                'label' => 'Nombre de la receta',
            ],
            [
                'attribute' => 'recipeLastPrice',
                'format' => 'currency',
                'label' => "Costo",
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; cursor: pointer; font-weight: bold;',
                    'class' => 'sortable-column', 
                    'data-sort-by' => 'recipeLastPrice'
                ],
            ],
            [
                'attribute' => 'price',
                'format' => 'currency',
                'label' => "Precio de<br>venta",
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'costPercent',
                'format' => 'percent',
                'label' => "Porcentaje<br>de costo",
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => [
                    'style' => 'text-align: center; font-weight: bold; cursor: pointer;',
                    'class' => 'sortable-column',
                    'data-sort-by' => 'costPercent'
                ],
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
                'attribute' => 'subRecipeCount',
                'label' => 'Cantidad<br>SubRecetas',
                'value' => function ($model) use ($ingredientCount) {
                    return $ingredientCount[$model->id]['sub_recipe'] ?? 0;
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
                'label' => 'Familia',
                'enableSorting' => true,
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
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
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

<?php Pjax::end(); ?>
</div>

<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-upload-file',
    'title' => Yii::t('app', "Importar recetas")
]);
$url = \yii\helpers\Url::to(['standard-recipe/import-recipes', 'id' => $business->id]);
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
    'title' => Yii::t('app', "Eliminar recetas seleccionadas"),
]);
?>
<p>¿Deseas eliminar todas las recetas seleccionadas o solo las de la página actual?</p>
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
<p>No has seleccionado ninguna receta para eliminar. Por favor, selecciona al menos una receta.</p>
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
<p>¿Estás seguro de que deseas eliminar <span id="selected-count-message"></span> recetas?</p>
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