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

    <?php
    // Obtener y consumir todos los mensajes flash para evitar duplicados en el layout
    $allFlashes = Yii::$app->session->getAllFlashes();
    // Limpiar los flashes para que no aparezcan en el layout
    Yii::$app->session->removeAllFlashes();
    
    // Mostrar mensajes flash encima de los botones
    foreach ($allFlashes as $key => $messages) {
        $alertClass = '';
        $iconClass = '';
        
        switch ($key) {
            case 'error':
                $alertClass = 'alert-danger';
                $iconClass = 'bx bx-error-circle';
                break;
            case 'success':
                $alertClass = 'alert-success';
                $iconClass = 'bx bx-check-circle';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                $iconClass = 'bx bx-info-circle';
                break;
            default:
                $alertClass = 'alert-info';
                $iconClass = 'bx bx-info-circle';
        }
        
        foreach ((array) $messages as $message) {
            echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show mb-3" role="alert">';
            echo '<i class="' . $iconClass . ' me-2"></i>';
            
            // Permitir HTML en mensajes de éxito para mostrar botones
            if ($key === 'success') {
                echo $message;
            } else {
                echo Html::encode($message);
            }
            
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
    }
    ?>

    <style>
    .alert-success {
        background-color: #d1e7dd;
        border-color: #badbcc;
        color: #0f5132;
    }
    
    .alert-success .btn-outline-primary {
        border-color: #0f5132;
        color: #0f5132;
    }
    
    .alert-success .btn-outline-primary:hover {
        background-color: #0f5132;
        border-color: #0f5132;
        color: white;
    }
    
    .alert {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .alert i {
        font-size: 1.2em;
        vertical-align: middle;
    }
    </style>

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
        <div class="p-2">
            <?php if (Yii::$app->user->can('manage_users') || Yii::$app->user->can('admin') || Yii::$app->user->can('administrator') || Yii::$app->user->can('storage_admin')): ?>
                <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Eliminar Seleccionados', ['icon' => ""
                    ]), ['#'], ['class' => 'btn btn-danger', 'id' => 'btn-delete-movements']) ?>
            <?php endif; ?>
        </div>
    </div>


    <?php Pjax::begin(['id' => 'movements-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'movements-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => \yii\grid\CheckboxColumn::class],

            [
                'attribute' => 'type',
                'value' => function ($model) {
                    $type = $model->formattedType;
                    if ($model->type === \common\models\Movement::TYPE_ORDER) {
                        return $type . ' <span class="badge bg-info ms-1">Convertible</span>';
                    }
                    return $type;
                },
                'format' => 'raw',
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
                    // Mostrar la fecha tal cual está guardada (evita conversión de zonas horarias)
                    $dt = new \DateTime($model->created_at);
                    return $dt->format('d/m/Y H:i');
                },
                'contentOptions' => ['style' => 'text-align: center; white-space: nowrap;'],
            ],
            //'observations',
            //'business_id',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{view} {update} {convert}",
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            '<i class="bx bx-show"></i>',
                            $url,
                            [
                                'class' => 'movement-details text-warning',
                                'title' => 'Ver detalles'
                            ]
                        );
                    },
                    'update' => function ($url, $model, $key) {
                        // Solo mostrar el botón de editar si el usuario es administrador
                        if (Yii::$app->user->can('manage_users') || Yii::$app->user->can('admin') || Yii::$app->user->can('administrator')) {
                            return \yii\bootstrap5\Html::a(
                                '<i class="bx bx-edit-alt"></i>',
                                ['update', 'id' => $model->id],
                                [
                                    'class' => 'text-warning ms-2',
                                    'title' => 'Editar movimiento'
                                ]
                            );
                        }
                        return '';
                    },
                    'convert' => function ($url, $model, $key) {
                        // Solo mostrar el botón de convertir para órdenes
                        if ($model->type === \common\models\Movement::TYPE_ORDER) {
                            return \yii\bootstrap5\Html::a(
                                '<i class="bx bx-transfer"></i>',
                                ['convert-to-entry', 'id' => $model->id],
                                [
                                    'class' => 'text-success ms-2 convert-order',
                                    'title' => 'Convertir a entrada',
                                    'data-confirm' => '¿Confirmas que quieres convertir esta orden en una entrada?'
                                ]
                            );
                        }
                        return '';
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

// MODALES PARA ELIMINACIÓN MÚLTIPLE
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-bulk-remove-movements',
    'title' => Yii::t('app', "Eliminar movimientos seleccionados"),
]);
?>
<p>¿Deseas eliminar todos los movimientos seleccionados o solo los de la página actual?</p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar las seleccionadas'), [
        'class' => 'btn btn-danger',
        'id' => 'delete-current-page-movements'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar todas'), [
        'class' => 'btn btn-danger',
        'id' => 'delete-all-movements'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
// Modal para mostrar error cuando no hay elementos seleccionados
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-no-selection-movements',
    'title' => Yii::t('app', "Selección vacía"),
]);
?>
<p>No has seleccionado ningún movimiento para eliminar. Por favor, selecciona al menos un movimiento.</p>
<div class="d-flex justify-content-end">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Entendido'), [
        'class' => 'btn btn-primary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
// Modal para confirmar la eliminación de elementos específicos
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-confirm-selected-remove-movements',
    'title' => Yii::t('app', "Confirmar eliminación"),
]);
?>
<p>¿Estás seguro de que deseas eliminar <span id="selected-count-message-movements"></span> movimientos?</p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar'), [
        'class' => 'btn btn-danger',
        'id' => 'confirm-delete-selected-movements'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

