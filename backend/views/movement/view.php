<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */

$this->title = $model->type === $model::TYPE_REQUISITION 
    ? 'Requisición #' . $model->requisition_number 
    : 'Movimiento #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Movements'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// CSS para mejorar la presentación
$this->registerCss("
    .movement-view .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    
    .movement-view .card-header {
        border-radius: 8px 8px 0 0;
        font-weight: 600;
    }
    
    .movement-view .table-responsive {
        border-radius: 4px;
        overflow-x: auto;
        overflow-y: visible;
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }
    
    .movement-view .table-responsive:hover::after {
        opacity: 0.8;
    }
    
    /* Mejorar el scrollbar horizontal */
    .movement-view .table-responsive::-webkit-scrollbar {
        height: 10px;
    }
    
    .movement-view .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .movement-view .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .movement-view .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Para Firefox */
    .movement-view .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }
    
    .movement-view .table {
        min-width: 1200px; /* Asegura que la tabla tenga un ancho mínimo para activar scroll */
        margin-bottom: 0;
    }
    
    .movement-view .table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px;
        white-space: nowrap; /* Evita que los encabezados se rompan en múltiples líneas */
    }
    
    .movement-view .table tbody td {
        padding: 10px 12px;
        vertical-align: middle;
    }
    
    .movement-view .badge {
        padding: 6px 12px;
        font-weight: 500;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    
    .movement-view .btn-lg {
        padding: 12px 24px;
        font-size: 1rem;
    }
    
    /* Indicador de ayuda para scroll */
    
");
?>
<div class="movement-view">

    <?php if ($model->type == $model::TYPE_ORDER): ?>
        <div class="mb-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-clipboard"></i> Orden Registrada</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Esta orden está lista para ser convertida en una entrada cuando recibas los productos del proveedor. 
                        Al convertirla, se creará automáticamente un nuevo movimiento de entrada con todos los datos de esta orden.
                    </p>
                    <div class="d-flex gap-2">
                        <?= Html::a(
                            '<i class="bx bx-transfer"></i> Registrar Entrada', 
                            ['convert-to-entry', 'id' => $model->id], 
                            [
                                'class' => 'btn btn-success btn-lg',
                                'data-confirm' => '¿Confirmas que quieres convertir esta orden en una entrada?\n\nSe creará un nuevo movimiento de entrada con:\n• Mismo proveedor\n• Mismo producto\n• Misma cantidad\n• Mismos datos financieros\n• Fecha actual',
                                'title' => 'Convertir orden a entrada'
                            ]
                        ) ?>
                        <?= Html::a(
                            '<i class="bx bx-edit"></i> Editar Orden', 
                            ['update', 'id' => $model->id], 
                            [
                                'class' => 'btn btn-outline-primary',
                                'title' => 'Editar esta orden antes de convertirla'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($model->type == $model::TYPE_REQUISITION): ?>
        <div class="mb-4">
            <div class="card border-primary">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bx bx-receipt"></i> Requisición #<?= Html::encode($model->requisition_number) ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Centro de Consumo:</strong> 
                            <?= $model->consumptionCenter ? Html::encode($model->consumptionCenter->name) : '-' ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha Requerida:</strong> 
                            <?= $model->required_date ? Yii::$app->formatter->asDate($model->required_date, 'php:d/m/Y') : '-' ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Estado:</strong> 
                            <span class="badge bg-<?= $model->status === 'fulfilled' ? 'success' : ($model->status === 'partially_fulfilled' ? 'warning' : 'secondary') ?>">
                                <?= $model->status === 'fulfilled' ? 'Surtida' : ($model->status === 'partially_fulfilled' ? 'Parcialmente Surtida' : 'Pendiente') ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Creada:</strong> 
                            <?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i') ?>
                        </div>
                    </div>

                    <?php if ($model->observations): ?>
                        <div class="mb-3">
                            <strong>Observaciones:</strong><br>
                            <?= Html::encode($model->observations) ?>
                        </div>
                    <?php endif; ?>

                    <h6 class="mt-4 mb-3">Insumos Solicitados</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Insumo</th>
                                    <th>Cantidad Solicitada</th>
                                    <th>Cantidad Surtida</th>
                                    <th>Disponibilidad</th>
                                    <?php if (!Yii::$app->user->can('consumption_requester')): ?>
                                        <th>Costo Estimado</th>
                                    <?php endif; ?>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $items = $model->requisitionItems;
                                $totalEstimated = 0;
                                foreach ($items as $item): 
                                    $totalEstimated += $item->cost_at_request;
                                ?>
                                    <tr>
                                        <td>
                                            <?= $item->ingredient ? Html::encode($item->ingredient->ingredient) : '-' ?>
                                            <?php if ($item->ingredient && $item->ingredient->brand): ?>
                                                <br><small class="text-muted"><?= Html::encode($item->ingredient->brand) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?= Yii::$app->formatter->asDecimal($item->quantity_requested, 2) ?>
                                            <?= $item->ingredient ? Html::encode($item->ingredient->um) : '' ?>
                                        </td>
                                        <td class="text-end">
                                            <?= Yii::$app->formatter->asDecimal($item->quantity_fulfilled ?? 0, 2) ?>
                                            <?= $item->ingredient ? Html::encode($item->ingredient->um) : '' ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusMap = [
                                                'available' => ['text' => 'Disponible', 'class' => 'success', 'icon' => '🟢'],
                                                'warning' => ['text' => 'Advertencia', 'class' => 'warning', 'icon' => '🟡'],
                                                'low' => ['text' => 'Advertencia', 'class' => 'warning', 'icon' => '🟡'],
                                                'insufficient' => ['text' => 'Insuficiente', 'class' => 'danger', 'icon' => '🔴'],
                                                'unavailable' => ['text' => 'No Disponible', 'class' => 'danger', 'icon' => '🔴'],
                                            ];
                                            $status = $statusMap[$item->availability_status] ?? ['text' => 'Pendiente', 'class' => 'secondary', 'icon' => '⚪'];
                                            ?>
                                            <span class="badge bg-<?= $status['class'] ?>">
                                                <?= $status['icon'] ?> <?= $status['text'] ?>
                                            </span>
                                            <?php if ($item->availability_percentage !== null): ?>
                                                <small class="text-muted ms-1">(<?= round($item->availability_percentage) ?>%)</small>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (!Yii::$app->user->can('consumption_requester')): ?>
                                            <td class="text-end">
                                                <?= Yii::$app->formatter->asCurrency($item->cost_at_request ?? 0) ?>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if ($item->isFullyDelivered()): ?>
                                                <span class="badge bg-success">Completo</span>
                                            <?php elseif ($item->isPartiallyDelivered()): ?>
                                                <span class="badge bg-warning">Parcial (<?= round($item->getDeliveryPercentage()) ?>%)</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $item->observations ? Html::encode($item->observations) : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!Yii::$app->user->can('consumption_requester')): ?>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total Estimado:</strong></td>
                                        <td class="text-end"><strong><?= Yii::$app->formatter->asCurrency($totalEstimated) ?></strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <?php if ($model->status !== 'fulfilled' && !Yii::$app->user->can('consumption_requester')): ?>
                        <div class="d-flex gap-2 mt-4">
                            <?= Html::a(
                                '<i class="bx bx-transfer-alt"></i> Convertir a Salida', 
                                ['convert-to-output', 'id' => $model->id], 
                                [
                                    'class' => 'btn btn-danger btn-lg',
                                    'data-confirm' => '¿Confirmas que quieres convertir esta requisición en salidas?\n\nSe crearán movimientos de salida para cada insumo según disponibilidad.',
                                    'title' => 'Convertir requisición a salidas'
                                ]
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($model->type !== $model::TYPE_REQUISITION): ?>
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
    //            'id',
                [
                    'attribute' => 'type',
                    'value' => function ($model) {
                        return $model->formattedType;
                    },
                ],
                'provider',
                [
                    'attribute' => 'payment_type',
                    'value' => function ($model) {
                        return $model->formattedPaymentType;
                    },
                    'visible' => $model->type == $model::TYPE_INPUT,
                ],
                'ingredient.ingredient',
                [
                    'attribute' => 'invoice',
                    'visible' => $model->type == $model::TYPE_INPUT,
                ],
                'quantity',
                'um',
                [
                    'attribute' => 'amount',
                    'visible' => $model->type == $model::TYPE_INPUT,
                ],
                [
                    'attribute' => 'tax',
                    'visible' => $model->type == $model::TYPE_INPUT,
                ],
                [
                    'attribute' => 'retention',
                    'visible' => $model->type == $model::TYPE_INPUT,
                ],
                [
                    'attribute' => 'unit_price',
                    'visible' => $model->type == $model::TYPE_INPUT,
                ],
                [
                    'attribute' => 'total',
                    'visible' => $model->type == $model::TYPE_INPUT,
                ],

                'created_at',
                'observations',
            ],
        ]) ?>
    <?php endif; ?>

</div>
