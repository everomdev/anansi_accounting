<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MovementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$isConsumptionRequester = Yii::$app->user->can('consumption_requester');
$this->title = $isConsumptionRequester ? Yii::t('app', 'Mis Requisiciones') : Yii::t('app', 'Movements');
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
    
    /* Estilos para el desplegable de insumos en requisiciones */
    .items-list {
        overflow: hidden;
    }
    
    .items-list ul {
        padding-left: 1.5rem;
        background-color: rgba(52, 152, 219, 0.05);
        border-left: 3px solid #3498db;
        padding: 0.5rem 1rem;
        border-radius: 4px;
    }
    
    .items-list ul li {
        padding: 0.25rem 0;
    }
    
    .requisition-toggle {
        cursor: pointer;
        color: #3498db !important;
        text-decoration: none;
        font-weight: 500;
    }
    
    .requisition-toggle .chevron-icon {
        transition: transform 0.3s ease;
        display: inline-block;
    }
    
    .requisition-toggle[aria-expanded="true"] .chevron-icon {
        transform: rotate(180deg);
    }
    
    .requisition-toggle:hover {
        color: #2980b9 !important;
    }
    </style>

    <div class="d-flex flex-wrap">
        <?php if (Yii::$app->user->can('consumption_requester')): ?>
            <div class="p-2">
                <?= Html::a(Yii::t('app', 'Crear requisición'), ['create-requisition'], ['class' => 'btn btn-warning']) ?>
            </div>
        <?php else: ?>
            <div class="p-2">
                <?= Html::a(Yii::t('app', 'Create entry'), ['create', 'type' => \common\models\Movement::TYPE_INPUT], ['class' => 'btn btn-warning']) ?>
            </div>
            <div class="p-2">
                <?= Html::a(Yii::t('app', 'Create output'), ['create', 'type' => \common\models\Movement::TYPE_OUTPUT], ['class' => 'btn btn-warning']) ?>
            </div>
            <div class="p-2">
                <?= Html::a(Yii::t('app', 'Create order'), ['create', 'type' => \common\models\Movement::TYPE_ORDER], ['class' => 'btn btn-warning']) ?>
            </div>
            <!-- <div class="p-2">
                <?= Html::a(Yii::t('app', 'Create requisition'), ['create-requisition'], ['class' => 'btn btn-info']) ?>
            </div> -->
            <div class="p-2">
                <?= Html::a(Yii::t('app', 'Download template'), ['movement/download-template'], ['class' => 'btn btn-warning']) ?>
            </div>
            <div class="p-2">
                <?= \yii\bootstrap5\Html::a(Yii::t('app', 'Cargar movimientos', [
                ]), '#', [
                    'class' => 'btn btn-warning',
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#modal-upload-file'
                ]) ?>
            </div>
            <div class="p-2">
                <?= Html::a(Yii::t('app', 'Exportar movimientos'), '#', [
                    'class' => 'btn btn-warning',
                    'id' => 'btn-export-movements'
                ]) ?>
            </div>
            <div class="p-2">
                <?= Html::a(Yii::t('app', 'Balance'), "#", ['class' => 'btn btn-warning', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#modal-balance']) ?>
            </div>
        <?php endif; ?>
    </div>


    <?php Pjax::begin(['id' => 'movements-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php
    // Definir columnas según el rol del usuario
    $columns = [
        ['class' => \yii\grid\CheckboxColumn::class],
    ];

    // Columna de tipo (solo si NO es consumption_requester)
    if (!$isConsumptionRequester) {
        $columns[] = [
            'attribute' => 'type',
            'value' => function ($model) {
                $type = $model->formattedType;
                if ($model->type === \common\models\Movement::TYPE_ORDER) {
                    return $type . ' <span class="badge bg-info ms-1">Convertible a entrada</span>';
                }
                if ($model->type === \common\models\Movement::TYPE_REQUISITION) {
                    return $type . ' <span class="badge bg-warning ms-1">Convertible a salida</span>';
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
        ];
    }

    // Columna número de requisición (solo para consumption_requester)
    if ($isConsumptionRequester) {
        $columns[] = [
            'attribute' => 'requisition_number',
            'label' => 'Número de Requisición',
            'value' => function ($model) {
                return $model->requisition_number ?? '-';
            },
        ];
    }

    // Columna de insumos
    $columns[] = [
        'attribute' => 'ingredient_id',
        'label' => $isConsumptionRequester ? 'Insumos Solicitados' : 'Insumo',
        'format' => 'raw',
        'value' => function ($model) {
            // Para requisiciones, mostrar botón desplegable con lista de insumos
            if ($model->type === 'requisition') {
                $items = $model->requisitionItems ?? [];
                $itemCount = count($items);
                
                if ($itemCount === 0) {
                    return "Requisición (sin insumos)";
                }
                
                // Generar ID único para el collapse
                $collapseId = 'collapse-items-' . $model->id;
                
                // Construir la lista de insumos (inicialmente oculta)
                $itemsList = '<div class="items-list" id="' . $collapseId . '" style="display: none;"><div class="mt-2"><ul class="list-unstyled mb-0 small">';
                foreach ($items as $item) {
                    $ingredient = $item->ingredient;
                    if ($ingredient) {
                        $itemsList .= '<li class="mb-1"><i class="bx bx-package text-muted"></i> ';
                        $itemsList .= Html::encode($ingredient->ingredient);
                        if (!empty($ingredient->brand)) {
                            $itemsList .= ' <span class="text-muted">(' . Html::encode($ingredient->brand) . ')</span>';
                        }
                        $itemsList .= ' - <strong>' . Yii::$app->formatter->asDecimal($item->quantity_requested, 2) . '</strong> ';
                        $itemsList .= Html::encode($ingredient->um ?? '');
                        $itemsList .= '</li>';
                    }
                }
                $itemsList .= '</ul></div></div>';
                
                // Botón para expandir/colapsar
                $button = '<a href="javascript:void(0);" class="btn btn-sm btn-link p-0 text-decoration-none requisition-toggle" data-target="' . $collapseId . '" data-expanded="false">';
                $button .= '<i class="bx bx-list-ul"></i> Requisición (' . $itemCount . ' insumos) <i class="bx bx-chevron-down chevron-icon"></i>';
                $button .= '</a>';
                
                return '<div>' . $button . $itemsList . '</div>';
            }
            
            // Para otros tipos de movimiento
            $ingredient = $model->ingredient;
            if (!$ingredient) {
                return '-';
            }
            
            $parts = [];
            $parts[] = $ingredient->ingredient;
            
            if (!empty($ingredient->brand)) {
                $parts[] = $ingredient->brand;
            }
            
            if (!empty($ingredient->presentation)) {
                $parts[] = $ingredient->presentation;
            }
            
            return implode('  ', $parts);
        },
        'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'name', [
            'class' => 'form-control',
            'placeholder' => 'Buscar por nombre del insumo...'
        ]),
    ];

    // Columnas adicionales solo para usuarios que no son consumption_requester
    if (!$isConsumptionRequester) {
        $columns[] = 'invoice';
        $columns[] = [
            'attribute' => 'provider',
            'value' => function ($data) {
                if ($data->type === \common\models\Movement::TYPE_INPUT) {
                    $provider = \common\models\Provider::find()
                        ->where(['name' => $data->provider, 'business_id' => $data->business_id])
                        ->one();
                    
                    if ($provider) {
                        return $provider->business_name ?? $provider->getBusiness()->one()->name ?? $provider->name;
                    }
                    
                    return $data->provider;
                }
                
                return '-';
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
        ];
    }

    // Centro de consumo (para requisiciones y salidas)
    $columns[] = [
        'attribute' => 'consumption_center_id',
        'label' => 'Centro de Consumo',
        'value' => function ($data) use ($isConsumptionRequester) {
            // Para requisiciones, siempre mostrar
            if ($data->type === \common\models\Movement::TYPE_REQUISITION && $data->consumptionCenter) {
                return $data->consumptionCenter->name;
            }
            // Para salidas, solo si no es consumption_requester
            if (!$isConsumptionRequester && $data->type === \common\models\Movement::TYPE_OUTPUT && $data->consumptionCenter) {
                return $data->consumptionCenter->name;
            }
            return '-';
        },
        'filter' => \yii\helpers\Html::activeDropDownList($searchModel, 'consumption_center_id', 
            \yii\helpers\ArrayHelper::map(
                \common\models\ConsumptionCenter::find()->where(['business_id' => $business->id])->all(), 
                'id', 
                'name'
            ), 
            ['class' => 'form-control', 'prompt' => 'Todos']
        ),
    ];

    // Fecha requerida (solo para requisiciones/consumption_requester)
    if ($isConsumptionRequester) {
        $columns[] = [
            'attribute' => 'required_date',
            'label' => 'Fecha Requerida',
            'value' => function($model) {
                if ($model->required_date) {
                    $dt = new \DateTime($model->required_date);
                    return $dt->format('d/m/Y');
                }
                return '-';
            },
            'contentOptions' => ['style' => 'text-align: center; white-space: nowrap;'],
        ];
    }

    // Estado (solo para requisiciones/consumption_requester)
    if ($isConsumptionRequester) {
        $columns[] = [
            'attribute' => 'status',
            'label' => 'Estado',
            'value' => function($model) {
                if ($model->status === 'fulfilled') {
                    return '<span class="badge bg-success">Surtida</span>';
                } elseif ($model->status === 'partially_fulfilled') {
                    return '<span class="badge bg-warning">Parcialmente Surtida</span>';
                } else {
                    return '<span class="badge bg-secondary">Pendiente</span>';
                }
            },
            'format' => 'raw',
            'contentOptions' => ['style' => 'text-align: center;'],
        ];
    }

    // Columnas de pago y cantidad solo para usuarios normales
    if (!$isConsumptionRequester) {
        $columns[] = [
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
        ];

        $columns[] = 'quantity';
        
        $columns[] = [
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
        ];

        $columns[] = [
            'attribute' => 'total',
            'label' => Yii::t('app', 'Total'),
            'value' => function($model) {
                if ($model->type === \common\models\Movement::TYPE_OUTPUT) {
                    return formatPrice(-$model->total);
                }
                return formatPrice($model->total);
            },
            'contentOptions' => ['style' => 'text-align: right;'],
        ];
    }

    // Fecha de creación (para todos)
    $columns[] = [
        'attribute' => 'created_at',
        'label' => Yii::t('app', 'Fecha de creación'),
        'value' => function($model) {
            $dt = new \DateTime($model->created_at);
            return $dt->format('d/m/Y H:i');
        },
        'contentOptions' => ['style' => 'text-align: center; white-space: nowrap;'],
    ];

    // Columna de acciones
    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'template' => $isConsumptionRequester ? "{view}" : "{view} {update} {convert}",
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
                // Mostrar botón de convertir para órdenes (a entradas)
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
                // Mostrar botón de convertir para requisiciones (a salidas)
                if ($model->type === \common\models\Movement::TYPE_REQUISITION) {
                    return \yii\bootstrap5\Html::a(
                        '<i class="bx bx-transfer-alt"></i>',
                        ['convert-to-output', 'id' => $model->id],
                        [
                            'class' => 'text-danger ms-2 convert-requisition',
                            'title' => 'Convertir a salida',
                            'data-confirm' => '¿Confirmas que quieres convertir esta requisición en una salida?'
                        ]
                    );
                }
                return '';
            }
        ]
    ];

    // Renderizar GridView con las columnas definidas
    ?>
    <?= GridView::widget([
        'id' => 'movements-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => $columns,
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<?php
// JavaScript para manejar el collapse de insumos con toggle manual
$this->registerJs("
$(document).on('click', '.requisition-toggle', function(e) {
    e.preventDefault();
    var button = $(this);
    var targetId = button.data('target');
    var target = $('#' + targetId);
    var isExpanded = button.data('expanded');
    
    
    if (isExpanded) {
        // Ocultar
        target.slideUp(300);
        button.data('expanded', false);
        button.attr('aria-expanded', 'false');
    } else {
        // Mostrar
        target.slideDown(300);
        button.data('expanded', true);
        button.attr('aria-expanded', 'true');
    }
});
");
?>

<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-details-movement',
    'size' => \yii\bootstrap5\Modal::SIZE_EXTRA_LARGE, // Modal más grande para ver mejor los detalles
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

<?php
// Modal para exportar movimientos seleccionados
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-export-movements',
    'title' => Yii::t('app', "Exportar movimientos seleccionados"),
]);
?>
<p>¿Deseas exportar todos los movimientos seleccionados o solo los de la página actual?</p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Exportar los seleccionados'), [
        'class' => 'btn btn-success',
        'id' => 'export-current-page-movements'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Exportar todos'), [
        'class' => 'btn btn-success',
        'id' => 'export-all-movements'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
// Modal para mostrar error cuando no hay elementos seleccionados para exportar
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-no-export-selection-movements',
    'title' => Yii::t('app', "Selección vacía"),
]);
?>
<p>No has seleccionado ningún movimiento para exportar. Por favor, selecciona al menos un movimiento.</p>
<div class="d-flex justify-content-end">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Entendido'), [
        'class' => 'btn btn-primary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

