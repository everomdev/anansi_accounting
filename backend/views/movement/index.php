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
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="movement-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create entry'), ['create', 'type' => \common\models\Movement::TYPE_INPUT], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Create output'), ['create', 'type' => \common\models\Movement::TYPE_OUTPUT], ['class' => 'btn btn-info']) ?>
        <?= Html::a(Yii::t('app', 'Create order'), ['create', 'type' => \common\models\Movement::TYPE_ORDER], ['class' => 'btn btn-warning']) ?>
        <?= Html::a(Yii::t('app', 'Download template'), ['movement/download-template'], ['class' => 'btn btn-warning']) ?>
        <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar movimientos', [
            'icon' => "<i class='bx bxs-file-doc'></i>"
        ]), '#', ['class' => 'btn btn-info', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
        <?= Html::a(Yii::t('app', 'Export Movements'), ['movement/export-movements'], ['class' => 'btn btn-warning']) ?>
        <?= Html::a(Yii::t('app', 'Balance'), "#", ['class' => 'btn btn-primary', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#modal-balance']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
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
            'total:currency',
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
                                'class' => 'movement-details text-warning'
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


<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-upload-file',
    'title' => Yii::t('app', "Importar movimientos de entrada")
]);
$url = \yii\helpers\Url::to(['movement/import-movements', 'id' => $business->id]);
\yii\bootstrap5\ActiveForm::begin([
    'action' => $url,
    'method' => 'post',
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
]);

echo \yii\bootstrap5\Html::input('file', 'movement-file', '', [
    'class' => 'form-control'
]);
echo "<br>";
echo \yii\bootstrap5\Html::submitButton(Yii::t('app', "Import"), [
    'class' => 'btn btn-success'
]);

\yii\bootstrap5\ActiveForm::end();

\yii\bootstrap5\Modal::end();

// MODAL BALANCE
\yii\bootstrap5\Modal::begin([
    'title' => Yii::t('app', "Current balance"),
    'id' => 'modal-balance',
    'options' => [
            'data' => [
                    'url' => \yii\helpers\Url::to(['movement/balance'])
            ]
    ]
]);

echo "<div id='balance-container'></div>";

\yii\bootstrap5\Modal::end();


?>
