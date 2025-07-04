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
            <?= Html::a(Yii::t('app', 'Export Movements'), ['movement/export-movements'], ['class' => 'btn btn-warning']) ?>
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
                    // Buscar el proveedor por nombre para mostrar el business_name
                    $provider = \common\models\Provider::find()
                        ->where(['name' => $data->provider, 'business_id' => $data->business_id])
                        ->one();
                    
                    if ($provider) {
                        return $provider->business_name ?? $provider->getBusiness()->one()->name ?? $provider->name;
                    }
                    
                    return $data->provider;
                },
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
            [
                'attribute' => 'total',
                'label' => Yii::t('app', 'Total'),
                'value' => function($model) {
                    return formatPrice($model->total);
                },
                'contentOptions' => ['style' => 'text-align: right;'],
            ],
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

<script>
// Debug: Verificar qué valores tienen los campos de filtro al cargar la página
$(document).ready(function() {
    console.log("Valores de filtros al cargar la página:");
    console.log("name:", $("input[name='MovementSearch[name]']").val());
    console.log("type:", $("select[name='MovementSearch[type]']").val());
    console.log("quantity:", $("input[name='MovementSearch[quantity]']").val());
    console.log("total:", $("input[name='MovementSearch[total]']").val());
    console.log("payment_type:", $("select[name='MovementSearch[payment_type]']").val());
    console.log("provider:", $("input[name='MovementSearch[provider]']").val());
    
    // Evento para monitorear cambios en el filtro de ingrediente
    $("input[name='MovementSearch[name]']").on('input', function() {
        console.log("Filtro de ingrediente cambiado a:", $(this).val());
    });
});
</script>
