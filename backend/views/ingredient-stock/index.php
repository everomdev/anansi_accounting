<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IngredientStockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $mio array */

$this->title = Yii::t('app', 'Resources');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$this->registerJsFile(Yii::getAlias("@web/js/ingredient-stock/index.js"), [
    'depends' => \yii\web\YiiAsset::class,
    'position' => $this::POS_END
]);
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
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Exportar insumos', [
                'icon' => ""
            ]), ['ingredient-stock/export', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Eliminar Seleccionados', [
                'icon' => ""
            ]), ['ingredient-stock/bulk-remove', 'id' => $business->id], ['class' => 'btn btn-danger', 'id' => 'bulk-remove']) ?>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'ingredient-stock-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'ingredient-stock-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => \yii\grid\CheckboxColumn::class],
            ['class' => 'yii\grid\SerialColumn'],
            'key',
            'ingredient',
            [
                'attribute' => 'um',
                'label' => 'Unidad<br>Compra',
                'encodeLabel' => false
            ],
            [
                'attribute' => 'portions_per_unit',
                'label' => 'EQ. Uni.<br>Cocina',
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'portion_um',
                'label' => 'Unidad<br>de Cocina',
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'yield',
                'label' => "Factor de<br>rendimiento",
                'value' => function ($data) {
                    return sprintf('%s %%', $data->yield);
                },
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'lastUnitPrice',
                'format' => 'currency',
                'label' => 'Último<br>precio',
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'avgUnitPrice',
                'format' => 'currency',
                'label' => 'Precio<br>promedio',
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'higherUnitPrice',
                'format' => 'currency',
                'label' => 'Precio<br>más alto',
                'encodeLabel' => false,
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'recipeCount',
                'label' => Yii::t('app', 'Recetas'),
                'value' => function ($model) use ($mio) {
                    return $mio[$model->id]['recipes'] ?? 0;
                },
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'subRecipeCount',
                'label' => Yii::t('app', 'SubRecetas'),
                'value' => function ($model) use ($mio) {
                    return $mio[$model->id]['subRecipes'] ?? 0;
                },
                'contentOptions' => ['style' => 'text-align: center;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],
            //'observations:ntext',

            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => "{priceTrend} {update} {delete}",
                'buttons' => [
                    'priceTrend' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            \yii\bootstrap5\Html::tag('i', '', ['class' => 'bx bx-chart text-warning']),
                            \yii\helpers\Url::to(['ingredient-stock/price-trend', 'id' => $model->id])
                        );
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-download-template',
    'title' => Yii::t('app', "Descargar plantilla")
]);
?>
<p>Vas a descargar la plantilla para la importación automática de insumos. <strong>Recuerda que debes utilizar la tabla
        de referencias para indicar el indicador de las categorías de tus insumos correctamente</strong></p>
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
    'class' => 'form-control'
]);
echo "<br>";
echo \yii\bootstrap5\Html::submitButton(Yii::t('app', "Import"), [
    'class' => 'btn btn-success'
]);

\yii\bootstrap5\ActiveForm::end();

\yii\bootstrap5\Modal::end();
?>