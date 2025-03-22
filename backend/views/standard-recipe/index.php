<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @vae $ingredientCount array */

$this->title = 'Recetas Estándar';
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

$this->registerJsFile(Yii::getAlias('@web/js/standard-recipe/index.js'), ['depends' => \yii\web\YiiAsset::class]);
?>
<div class="standard-recipe-index">
    <p>
        <div class="row">
            <div class="col-md-12">
                <?= Html::a(Yii::t('app', 'Create new recipe'), \yii\helpers\Url::to(['standard-recipe/create', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success']) ?>
                <?= Html::a(Yii::t('app', 'Duplicate'), \yii\helpers\Url::to(['standard-recipe/duplicate-recipes']), ['class' => 'btn btn-success', 'id' => 'btn-duplicate-recipes']) ?>
                <?= Html::a(Yii::t('app', 'Descargar PDF'), \yii\helpers\Url::to(['standard-recipe/download-recipes-pdf', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success', 'id' => 'btn-download-recipes']) ?>
                <?= Html::a(Yii::t('app', 'Descargar Excel'), \yii\helpers\Url::to(['standard-recipe/download-recipes','type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success', 'id' => 'btn-download-recipes']) ?>
                <?= Html::a(Yii::t('app', 'Descargar Plantilla'), \yii\helpers\Url::to(['standard-recipe/export-recipes-plantilla']), ['class' => 'btn btn-success', 'id' => 'btn-export-recipes-plantilla']) ?>
                <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar recetas', [
                    'icon' => ""
                ]), '#', ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
            </div>
        </div>
        <div class="row" style="margin-top: 10px; margin-bottom: 10px;">
            <div class="col-md-12">
            <?= Html::a('Descargar Recetas Completas', ['standard-recipe/download-complete-recipe-pdf'], ['class' => 'btn btn-success', 'id' => 'btn-download-recipes-complete']) ?>
            <?= Html::a('Exportar Recetas Completas', ['standard-recipe/export-recipes-to-excel'], ['class' => 'btn btn-success', 'id' => 'btn-download-recipes-complete-excel']) ?>
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Eliminar Seleccionados', [
                'icon' => ""
            ]), ['standard-recipe/delete', 'id' => $business], ['class' => 'btn btn-danger', 'id' => 'btn-delete-recipes']) ?>
            </div>
        </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row"></div>
    <?= GridView::widget([
        'id' => 'standard-recipes-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => \yii\grid\CheckboxColumn::class],
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            [
                'attribute' => 'recipeLastPrice',
                'format' => 'currency',
                'label' => "Costo"
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
                'headerOptions' => ['style' => 'text-align: center;'],
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
                'label' => 'Cantidad<br>SubRecetas',
                'value' => function ($model) use ($ingredientCount) {
                    return $ingredientCount[$model->id]['sub_recipe'] ?? 0;
                },
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>


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