<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Categories');
$this->params['breadcrumbs'][] = $this->title;

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);

$this->registerJsFile(Yii::getAlias("@web/js/category/index.js"), [
    'depends' => \yii\web\YiiAsset::class
]);

?>
<div class="category-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Category'), ['create'], [
            'class' => 'btn btn-success',
            'id' => 'create-category'
        ]) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            [
                'attribute' => 'group.color',
                'format' => 'raw',
                'value' => function ($data) {
                    return "<div style='width: 30px; height: 30px; background-color:  {$data->group->color}; border-radius: 30px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);'></div>";
                },
            ],
            [
                'attribute' => 'group.name',
                'filter' => \yii\bootstrap5\Html::activeDropDownList($searchModel, 'group_id', \yii\helpers\ArrayHelper::map(\common\models\CategoryGroup::find()->all(), 'id', 'name'), ['class' => 'form-control', 'prompt' => Yii::t('app', "All")])
            ],
            'name',
            'key_prefix',
//            'builtin',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                'visibleButtons' => [
                    'update' => function ($model) use ($business) {
                        return $business['id'] == $model->business_id;
                    },
                    'delete' => function ($model) use ($business) {
                        return $business['id'] == $model->business_id;
                    }
                ],
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-edit'></i>",
                            $url,
                            [
                                'class' => 'update-category'
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-trash'></i>",
                            $url,
                            [
                                'class' => 'delete-category',
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
    'id' => 'modal-form-category',
]);

echo '<div id="form-category-container"></div>';

\yii\bootstrap5\Modal::end();
?>
