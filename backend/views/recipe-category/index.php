<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\RecipeCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Recipe Categories');
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile(Yii::getAlias("@web/js/recipe-category/index.js"), [
    'depends' => \yii\web\YiiAsset::class
]);
?>
<div class="recipe-category-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Category'), ['create'], [
            'class' => 'btn btn-success',
            'id' => 'create-recipe-category'
        ]) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            [
                'attribute' => 'type',
                'value' => function ($data) {
                    return \common\models\RecipeCategory::getFormattedTypes()[$data->type];
                },
                'filter' => \yii\bootstrap5\Html::activeDropDownList($searchModel, 'type', \common\models\RecipeCategory::getFormattedTypes(), ['class' => 'form-control', 'prompt' => Yii::t('app', "All")])
            ],
            [
                'value' => function ($data) {
                    return $data->getRecipes()->count();
                },
                'label' => "Recetas"
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-edit'></i>",
                            $url,
                            [
                                'class' => 'update-recipe-category text-warning'
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-trash'></i>",
                            $url,
                            [
                                'class' => 'delete-recipe-category text-warning',
                                'data' => [
                                    'confirm' => Yii::t('app', "Are you sure you want to delete this category?"),
                                    'method' => 'post'

                                ]
                            ]
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
    'id' => 'modal-form-recipe-category',
]);

echo '<div id="form-recipe-category-container"></div>';

\yii\bootstrap5\Modal::end();
?>
