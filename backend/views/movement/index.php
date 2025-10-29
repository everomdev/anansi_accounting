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
    <div class="d-flex flex-wrap">
        <div class="p-2">
            <?= Html::a(Yii::t('app', 'Create entry'), ['create', 'type' => \common\models\Movement::TYPE_INPUT], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= Html::a(Yii::t('app', 'Create output'), ['create', 'type' => \common\models\Movement::TYPE_OUTPUT], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= Html::a(Yii::t('app', 'Create order'), ['create', 'type' => \common\models\Movement::TYPE_ORDER], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= Html::a(Yii::t('app', 'Download template'), ['movement/download-template'], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Cargar movimientos', [
                'icon' => ""
            ]), '#', ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => "#modal-upload-file"]) ?>
        </div>
        <div class="p-2">
            <?= Html::a(Yii::t('app', 'Exportar movimientos'), ['movement/export-movements'], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= Html::a(Yii::t('app', 'Balance'), "#", ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#modal-balance']) ?>
        </div>
    </div>


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
                'label' => 'Insumo',
                'value' => function ($model) {
                    $ingredient = $model->ingredient;
                    $parts = [];
                    
                    // Agregar el nombre del insumo
                    $parts[] = $ingredient->ingredient;
                    
                    // Agregar marca si existe
                    if (!empty($ingredient->brand)) {
                        $parts[] = $ingredient->brand;
                    }
                    
                    // Agregar presentación si existe
                    if (!empty($ingredient->presentation)) {
                        $parts[] = $ingredient->presentation;
                    }
                    
                    return implode('  ', $parts);
                },
                'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'name', [
                    'class' => 'form-control',
                    'placeholder' => 'Buscar por nombre del insumo...'
                ]),
            ],
            'invoice',            [
                'attribute' => 'provider',
                'value' => function ($data) {
                    // Solo mostrar proveedor para movimientos de entrada
                    if ($data->type === \common\models\Movement::TYPE_INPUT) {
                        // Buscar el proveedor por nombre para mostrar el business_name
                        $provider = \common\models\Provider::find()
                            ->where(['name' => $data->provider, 'business_id' => $data->business_id])
                            ->one();
                        
                        if ($provider) {
                            return $provider->business_name ?? $provider->getBusiness()->one()->name ?? $provider->name;
                        }
                        
                        return $data->provider;
                    }
                    
                    return '-'; // No mostrar proveedor para salidas
                },
                'filter' => \kartik\typeahead\Typeahead::widget([
                    'scrollable' => true,
                    'dataset' => [
                        [
                            'local' => \yii\helpers\ArrayHelper::getColumn(\common\models\Movement::find()->where(['type' => \common\models\Movement::TYPE_INPUT])->all(), 'provider'),
                            'limit' => 10,

                        ]
                    ],
                    'model' => $searchModel,
                    'attribute' => 'provider'
                ])
            ],
            [
                'attribute' => 'consumption_center_id',
                'label' => 'Centro de Consumo',
                'value' => function ($data) {
                    // Solo mostrar centro de consumo para movimientos de salida
                    if ($data->type === \common\models\Movement::TYPE_OUTPUT && $data->consumptionCenter) {
                        return $data->consumptionCenter->name;
                    }
                    
                    return '-'; // No mostrar centro de consumo para entradas
                },
                'filter' => \yii\helpers\Html::activeDropDownList($searchModel, 'consumption_center_id', 
                    \yii\helpers\ArrayHelper::map(
                        \common\models\ConsumptionCenter::find()->where(['business_id' => $business->id])->all(), 
                        'id', 
                        'name'
                    ), 
                    ['class' => 'form-control', 'prompt' => 'Todos']
                ),
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
            [
                'attribute' => 'total',
                'label' => Yii::t('app', 'Total'),
                'value' => function($model) {
                    // Si es un movimiento de salida, calcular el costo basado en el precio del insumo
                    if ($model->type === \common\models\Movement::TYPE_OUTPUT) {
                        $unitPrice = $model->ingredient->lastUnitPrice ?? 0;
                        $total = $unitPrice * $model->quantity;
                        return formatPrice(-$total); // Mostrar en negativo
                    }
                    
                    // Para entradas y otros tipos, mostrar el total normal
                    return formatPrice($model->total);
                },
                'contentOptions' => ['style' => 'text-align: right;'],
            ],
            [
                'attribute' => 'created_at',
                'label' => Yii::t('app', 'Fecha de creación'),
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i');
                },
                'contentOptions' => ['style' => 'text-align: center; white-space: nowrap;'],
            ],
            //'observations',
            //'business_id',

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
