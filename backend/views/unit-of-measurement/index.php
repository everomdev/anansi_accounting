<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UnitOfMeasurementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Unit Of Measurements');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(Yii::getAlias("@web/js/um/index.js"), [
    'depends' => \yii\web\YiiAsset::class
]);
?>
    <div class="unit-of-measurement-index">

        <p>
            <?= Html::a(Yii::t('app', 'Add Unit Of Measurement'), ['create'], [
                    'class' => 'btn btn-success',
                'id' => 'create-um'
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
                    'class' => 'yii\grid\ActionColumn',
                    'template' => "{update} {delete}",
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return \yii\bootstrap5\Html::a(
                                "<i class='bx bx-edit'></i>",
                                $url,
                                [
                                    'class' => 'update-um text-warning'
                                ]
                            );
                        },
                    ]
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-form-um',
]);

echo '<div id="form-um-container"></div>';

\yii\bootstrap5\Modal::end();
?>
