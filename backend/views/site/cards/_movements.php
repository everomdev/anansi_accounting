<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $movements \common\models\Movement[] */
use yii\helpers\Html;

// Construir la consulta base
$movementsQuery = $business->getMovements();

// Si el usuario es consumption_requester, filtrar solo requisiciones
if (Yii::$app->user->can('consumption_requester')) {
    $movementsQuery->andWhere(['type' => \common\models\Movement::TYPE_REQUISITION]);
}

$movements = $movementsQuery
    ->orderBy(['created_at' => SORT_DESC])
    ->limit(10)
    ->all();

// CSS personalizado para el nuevo diseño
$this->registerCss("
    .movements-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
        height: 380px; /* Altura fija */
        display: flex;
        flex-direction: column;
    }
    
    .movements-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .movements-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0; /* Evitar que el encabezado se encoja */
    }
    
    .movements-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .movements-content {
        overflow-y: auto; /* Agregar scroll vertical */
        flex-grow: 1; /* Permitir que el contenido ocupe el espacio restante */
        scrollbar-width: thin; /* Para Firefox */
        scrollbar-color: rgba(52, 152, 219, 0.5) rgba(236, 240, 241, 0.5); /* Para Firefox */
    }
    
    /* Estilizar scrollbar para navegadores webkit (Chrome, Safari, Edge) */
    .movements-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .movements-content::-webkit-scrollbar-track {
        background: rgba(236, 240, 241, 0.5);
        border-radius: 10px;
    }
    
    .movements-content::-webkit-scrollbar-thumb {
        background-color: rgba(52, 152, 219, 0.5);
        border-radius: 10px;
    }
    
    .movements-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }
    
    .movements-table th {
        padding: 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 1;
    }
    
    .movements-table td {
        padding: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
        color: #34495e;
    }
    
    .movements-table tr:last-child td {
        border-bottom: none;
    }
    
    .movements-table tr:hover {
        background-color: rgba(52, 152, 219, 0.03);
    }
    
    .movement-type {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .movement-type-input {
        background-color: rgba(46, 204, 113, 0.15);
        color: #27ae60;
    }
    
    .movement-type-output {
        background-color: rgba(231, 76, 60, 0.15);
        color: #c0392b;
    }
    
    .movement-ingredient {
        font-weight: 500;
    }
    
    .movement-quantity {
        white-space: nowrap;
    }
    
    .movement-total {
        font-weight: 600;
        text-align: right;
    }
    
    .empty-movements {
        text-align: center;
        padding: 2rem 0;
        color: #95a5a6;
    }
    
    .empty-movements i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .movements-badge {
        background: rgba(52, 73, 94, 0.1);
        color: #34495e;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
    }
    
    /* Colores para requisiciones según disponibilidad de stock */
    .requisition-insufficient-stock {
        color: #dc3545 !important; /* Rojo */
        font-weight: 500;
    }
    
    .requisition-available-stock {
        color: #0d6efd !important; /* Azul */
        font-weight: 500;
    }
    
    /* Asegurar que el texto de las celdas herede el color de la fila */
    .requisition-insufficient-stock td {
        color: inherit !important;
    }
    
    .requisition-available-stock td {
        color: inherit !important;
    }
");
?>

<div class="movements-container">
    <div class="movements-header">
        <h5><?= Yii::$app->user->can('consumption_requester') ? 'Últimas Requisiciones' : 'Últimos Movimientos' ?></h5>
        <div>
            <span class="movements-badge"><?= count($movements) ?> recientes</span>
            <?php if (Yii::$app->user->can('consumption_requester')): ?>
                <?= Html::a(Yii::t('app', 'Crear requisición'), ['movement/create-requisition'], ['class' => 'btn btn-sm btn-info ms-2']) ?>
            <?php else: ?>
                <?= Html::a(Yii::t('app', 'Ver todos'), ['movement/index'], ['class' => 'btn btn-sm btn-outline-secondary ms-2']) ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="movements-content">
        <?php if (empty($movements)): ?>
            <div class="empty-movements">
                <i class="fas fa-exchange-alt"></i>
                <p>No hay movimientos recientes</p>
            </div>
        <?php else: ?>
            <table class="movements-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <?php if (Yii::$app->user->can('consumption_requester')): ?>
                            <th>Fecha Requerida</th>
                        <?php endif; ?>
                        <th>Insumo</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Familia</th>
                        <?php if (!Yii::$app->user->can('consumption_requester')): ?>
                            <th style="text-align: right">Total</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Expandir requisiciones en múltiples filas
                    foreach ($movements as $movement): 
                        if ($movement->type === 'requisition'):
                            $items = $movement->requisitionItems ?? [];
                            if (count($items) === 0):
                                // Mostrar requisición sin items
                                ?>
                                <tr>
                                    <td>
                                        <span class="movement-type movement-type-output">
                                            <i class="fas fa-file-alt me-1"></i>
                                            <?= $movement->getFormattedType() ?>
                                            <?php
                                            // Mostrar ícono según estado de tiempo
                                            switch ($movement->requisition_time_status) {
                                                case \common\models\Movement::TIME_STATUS_ON_TIME:
                                                    echo ' <span style="color: #28a745;" title="En tiempo">✅</span>';
                                                    break;
                                                case \common\models\Movement::TIME_STATUS_EXTEMPORANEOUS:
                                                    echo ' <span style="color: #ffc107;" title="Extemporánea">⚠️</span>';
                                                    break;
                                                case \common\models\Movement::TIME_STATUS_OUT_OF_TIME:
                                                    echo ' <span style="color: #dc3545;" title="Fuera de tiempo">⛔</span>';
                                                    break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <?php if (Yii::$app->user->can('consumption_requester')): ?>
                                        <td>
                                            <?php if ($movement->required_date): ?>
                                                <i class="bx bx-calendar text-primary"></i> <?= (new \DateTime($movement->required_date))->format('d/m/Y') ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td colspan="<?= Yii::$app->user->can('consumption_requester') ? '4' : '5' ?>">Sin insumos</td>
                                </tr>
                                <?php
                            else:
                                // Mostrar una fila por cada item de la requisición
                                foreach ($items as $index => $item):
                                    $ingredient = $item->ingredient;
                                    
                                    // Determinar clase de color según disponibilidad de stock
                                    $rowClass = '';
                                    if ($movement->status !== 'fulfilled' && $movement->status !== 'partially_fulfilled') {
                                        if ($ingredient) {
                                            $availableStock = $ingredient->quantity ?? 0;
                                            $requestedQuantity = $item->quantity_requested;
                                            
                                            if ($availableStock < $requestedQuantity) {
                                                $rowClass = 'requisition-insufficient-stock';
                                            } else {
                                                $rowClass = 'requisition-available-stock';
                                            }
                                        }
                                    }
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td>
                                            <span class="movement-type movement-type-output">
                                                <i class="fas fa-file-alt me-1"></i>
                                                <?= $movement->getFormattedType() ?>
                                                <?php
                                                // Mostrar ícono según estado de tiempo
                                                switch ($movement->requisition_time_status) {
                                                    case \common\models\Movement::TIME_STATUS_ON_TIME:
                                                        echo ' <span style="color: #28a745;" title="En tiempo">✅</span>';
                                                        break;
                                                    case \common\models\Movement::TIME_STATUS_EXTEMPORANEOUS:
                                                        echo ' <span style="color: #ffc107;" title="Extemporánea">⚠️</span>';
                                                        break;
                                                    case \common\models\Movement::TIME_STATUS_OUT_OF_TIME:
                                                        echo ' <span style="color: #dc3545;" title="Fuera de tiempo">⛔</span>';
                                                        break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <?php if (Yii::$app->user->can('consumption_requester')): ?>
                                            <td style="white-space: nowrap;">
                                                <?php if ($movement->required_date): ?>
                                                    <i class="bx bx-calendar text-primary"></i> <?= (new \DateTime($movement->required_date))->format('d/m/Y') ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td class="movement-ingredient">
                                            <?php if ($ingredient): ?>
                                                <?= Html::encode($ingredient->ingredient) ?>
                                                <?php if (!empty($ingredient->brand)): ?>
                                                    <span class="text-muted">(<?= Html::encode($ingredient->brand) ?>)</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="movement-quantity" style="text-align: right;">
                                            <strong><?= Yii::$app->formatter->asDecimal($item->quantity_requested, 2) ?></strong>
                                        </td>
                                        <td style="text-align: center;">
                                            <?= Html::encode($ingredient ? $ingredient->um : '-') ?>
                                        </td>
                                        <td>
                                            <?= $ingredient && $ingredient->category ? Html::encode($ingredient->category->name) : '-' ?>
                                        </td>
                                        <?php if (!Yii::$app->user->can('consumption_requester')): ?>
                                            <td class="movement-total">
                                                <?php if ($index === 0): ?>
                                                    <?= formatPrice($movement->total) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php
                                endforeach;
                            endif;
                        else:
                            // Para movimientos normales (entrada/salida)
                            ?>
                            <tr>
                                <td>
                                    <?php 
                                    $typeClass = $movement->type == \common\models\Movement::TYPE_INPUT ? 'movement-type-input' : 'movement-type-output';
                                    $typeIcon = $movement->type == \common\models\Movement::TYPE_INPUT ? 'fas fa-arrow-down' : 'fas fa-arrow-up';
                                    ?>
                                    <span class="movement-type <?= $typeClass ?>">
                                        <i class="<?= $typeIcon ?> me-1"></i>
                                        <?= $movement->getFormattedType() ?>
                                    </span>
                                </td>
                                <?php if (Yii::$app->user->can('consumption_requester')): ?>
                                    <td>-</td>
                                <?php endif; ?>
                                <td class="movement-ingredient">
                                    <?= $movement->ingredient ? Html::encode($movement->ingredient->ingredient) : '-' ?>
                                </td>
                                <td class="movement-quantity" style="text-align: right;">
                                    <strong><?= $movement->quantity ? Yii::$app->formatter->asDecimal($movement->quantity, 2) : '-' ?></strong>
                                </td>
                                <td style="text-align: center;">
                                    <?= $movement->ingredient ? Html::encode($movement->ingredient->um) : '-' ?>
                                </td>
                                <td>
                                    <?= $movement->ingredient && $movement->ingredient->category ? Html::encode($movement->ingredient->category->name) : '-' ?>
                                </td>
                                <?php if (!Yii::$app->user->can('consumption_requester')): ?>
                                    <td class="movement-total"><?= formatPrice($movement->total) ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php
                        endif;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>