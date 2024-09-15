<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Recetas Estándar';
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

$this->registerJsFile(Yii::getAlias('@web/js/standard-recipe/index.js'), ['depends' => \yii\web\YiiAsset::class]);
?>
<div class="standard-recipe-index">
    <p>
        <?= Html::a(Yii::t('app', 'Create new recipe'), \yii\helpers\Url::to(['standard-recipe/create', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Duplicate'), \yii\helpers\Url::to(['standard-recipe/duplicate-recipes']), ['class' => 'btn btn-success', 'id' => 'btn-duplicate-recipes']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


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
                'label' => "Precio de venta"
            ],
            [
                'attribute' => 'costPercent',
                'format' => 'percent',
                'label' => "Porcentaje de costo"
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>


</div>
