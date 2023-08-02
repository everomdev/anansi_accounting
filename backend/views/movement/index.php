<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MovementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Movements');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(Yii::getAlias("@web/js/movement/index.js"), [
    'depends' => \yii\web\YiiAsset::class
]);
?>
<div class="movement-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Movement'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'type',
                'value' => function ($model) {
                    return $model->formattedType;
                },
                'filter' => \yii\bootstrap5\Html::activeDropDownList(
                    $searchModel,
                    'type',
                    \common\models\Movement::getFormattedTypes(),
                    [
                        'class' => 'form-control',
                        'prompt' => Yii::t('app', "All")
                    ]
                ),
                'label' => $searchModel->getAttributeLabel('type')
            ],
            [
                'attribute' => 'ingredient_id',
                'value' => function ($model) {
                    return $model->ingredient->ingredient;
                },
                'filter' => \kartik\select2\Select2::widget([
                    'data' => \yii\helpers\ArrayHelper::map(\common\models\IngredientStock::find()->all(), 'id', 'ingredient'),
                    'model' => $searchModel,
                    'attribute' => 'ingredient_id',
                    'options' => [
                        'placeholder' => "----"
                    ]
                ]),
            ],
            'invoice',
            [
                'attribute' => 'provider',
                'filter' => \kartik\typeahead\Typeahead::widget([
                    'scrollable' => true,
                    'dataset' => [
                        [
                            'local' => \yii\helpers\ArrayHelper::getColumn(\common\models\Movement::find()->all(), 'provider'),
                            'limit' => 10,

                        ]
                    ],
                    'model' => $searchModel,
                    'attribute' => 'provider'
                ])
            ],
            [
                'attribute' => 'payment_type',
                'value' => function ($data) {
                    return $data->formattedPaymentType;
                },
                'filter' => \yii\bootstrap5\Html::activeDropDownList(
                        $searchModel,
                    'payment_type',
                    \common\models\Movement::getFormattedPaymentTypes(),
                    [
                            'class' => 'form-control',
                        'prompt' => '----'
                    ]
                )
            ],

            'quantity',
            [
                'attribute' => 'um',
                'filter' => \yii\bootstrap5\Html::activeDropDownList(
                    $searchModel,
                    'um',
                    \yii\helpers\ArrayHelper::map(\common\models\Movement::find()->all(), 'um', 'um'),
                    [
                        'class' => 'form-control',
                        'prompt' => '----'
                    ]
                )
            ],
//            'amount',
//            'tax',
//            'retention',
//            'unit_price',
            'total',
            //'observations',
            //'business_id',
            //'created_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{view}",
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            '<i class="bx bx-show"></i>',
                            $url,
                            [
                                'class' => 'movement-details'
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
    'id' => 'modal-details-movement'
]);
?>
<div id="container-modal-details-movement"></div>
<?php \yii\bootstrap5\Modal::end(); ?>
