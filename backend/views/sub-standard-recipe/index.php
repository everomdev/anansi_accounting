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
?>
<div class="sub-standard-recipe-index">


    <p>
        <?= Html::a(Yii::t('app', 'Nueva Subreceta'), \yii\helpers\Url::to(['standard-recipe/create', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Duplicate'), \yii\helpers\Url::to(['sub-standard-recipe/duplicate-recipes']), ['class' => 'btn btn-success', 'id' => 'btn-duplicate-recipes']) ?>
        <?= Html::a(Yii::t('app', 'Descargar PDF'), \yii\helpers\Url::to(['standard-recipe/download-recipes-pdf', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-success', 'id' => 'btn-download-recipes']) ?>
        <?= Html::a(Yii::t('app', 'Descargar Excel'), \yii\helpers\Url::to(['standard-recipe/download-recipes','type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-success', 'id' => 'btn-download-recipes']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Eliminar Seleccionados', ['icon' => ""
                ]), ['#'], ['class' => 'btn btn-danger', 'id' => 'btn-delete-recipes']) ?>

    </p>
    <?php Pjax::begin(['id' => 'sub-standard-recipes-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'sub-standard-recipes-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => \yii\grid\CheckboxColumn::class],
            ['class' => 'yii\grid\SerialColumn'],
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
<style>
    #btn-delete-recipes:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
