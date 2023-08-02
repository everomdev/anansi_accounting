<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', "Sub recipes");
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="standard-recipe-index">


    <p>
        <?= Html::a(Yii::t('app', 'New sub recipe'), \yii\helpers\Url::to(['standard-recipe/create', 'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB]), ['class' => 'btn btn-primary']) ?>
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
            'subRecipeLastPrice:currency',
            'subRecipeAvgPrice:currency',
            'subRecipeHigherPrice:currency',
            [
                'value' => function (\common\models\StandardRecipe $model) {
                    return Yii::t('app', "{amount} ingredients", [
                        'amount' => $model->getIngredientRelations()->count()
                    ]);
                },
                'label' => Yii::t('app', "Ingredients")
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
