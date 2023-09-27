<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IngredientStockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Resources');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="ingredient-stock-index">


    <p>
        <?= Html::a(Yii::t('app', 'Add resource'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Descargar referencias', [
            'icon' => "<i class='bx bxs-file-doc'></i>"
        ]), ['ingredient-stock/download-references', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Descargar plantilla', [
            'icon' => "<i class='bx bxs-file-doc'></i>"
        ]), ['ingredient-stock/download-template', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar insumos', [
            'icon' => "<i class='bx bxs-file-doc'></i>"
        ]), '#', ['class' => 'btn btn-info', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Exportar insumos', [
            'icon' => "<i class='bx bx-download'></i>"
        ]), ['ingredient-stock/export', 'id' => $business->id], ['class' => 'btn btn-warning']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'key',
            'ingredient',
//            'quantity',
//            'final_quantity',
            'um',
            'portions_per_unit',
            'portion_um',
            ['attribute' => 'yield', 'label' => "Factor de rendimiento", 'value' => function ($data) {
                return sprintf('%s %%', $data->yield);
            },],
            'lastUnitPrice:currency',
            'avgUnitPrice:currency',
            'higherUnitPrice:currency',
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
