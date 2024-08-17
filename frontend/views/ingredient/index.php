<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IngredientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Ingredients');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ingredient-index">


    <p>
        <?= Html::a(Yii::t('app', 'Create Ingredient'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Descargar plantilla', [
            'icon' => ""
        ]), ['ingredient/download-template'], ['class' => 'btn btn-warning']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar ingredientes', [
            'icon' => ""
        ]), '#', ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            'um',
            'category.name',
//            'portions_per_unit',
//            'portion_um',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-upload-file',
    'title' => Yii::t('app', "Importar ingredientes")
]);
$url = \yii\helpers\Url::to(['ingredient/import-ingredients',]);
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
