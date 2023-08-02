<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Standard Recipes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="standard-recipe-index">


    <p>
        <?= Html::a(Yii::t('app', 'Create new recipe'), \yii\helpers\Url::to(['standard-recipe/create', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN]), ['class' => 'btn btn-primary']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => [
            'class' => \yii\i18n\Formatter::class,
            'currencyCode' => 'usd',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            'recipeLastPrice:currency',
            'recipeAvgPrice:currency',
            'recipeHigherPrice:currency',
            [
                'value' => function (\common\models\StandardRecipe $model) {
                    $total = $model->getIngredientRelations()->count();
                    $total += $model->getSubStandardRecipes()->count();
                    return Yii::t('app', "{amount} ingredients", [
                        'amount' => $total
                    ]);
                },
                'label' => Yii::t('app', "Ingredients")
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>


</div>
