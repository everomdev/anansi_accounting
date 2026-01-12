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
    
    /* Estilos para el desplegable de insumos en requisiciones */
    .requisition-items-list {
        overflow: hidden;
    }
    
    .requisition-items-list ul {
        padding-left: 1rem;
        background-color: rgba(52, 152, 219, 0.05);
        border-left: 3px solid #3498db;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        margin-top: 0.5rem;
        margin-bottom: 0;
    }
    
    .requisition-items-list ul li {
        padding: 0.25rem 0;
        font-size: 0.85rem;
    }
    
    .requisition-toggle-btn {
        cursor: pointer;
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .requisition-toggle-btn:hover {
        color: #2980b9;
        text-decoration: none;
    }
    
    .requisition-toggle-btn .chevron-icon {
        transition: transform 0.3s ease;
        display: inline-block;
        font-size: 0.9rem;
    }
    
    .requisition-toggle-btn[aria-expanded=\"true\"] .chevron-icon {
        transform: rotate(180deg);
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
                        <?php if (!Yii::$app->user->can('consumption_requester')): ?>
                            <th>Cantidad</th>
                            <th style="text-align: right">Total</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
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
                                <td style="white-space: nowrap;">
                                    <?php if ($movement->required_date): ?>
                                        <i class="bx bx-calendar text-primary"></i> <?= (new \DateTime($movement->required_date))->format('d/m/Y') ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="movement-ingredient">
                                <?php if ($movement->type === 'requisition'): ?>
                                    <?php
                                    $items = $movement->requisitionItems ?? [];
                                    $itemCount = count($items);
                                    
                                    if ($itemCount === 0) {
                                        echo "Requisición (sin insumos)";
                                    } else {
                                        // Generar ID único para el collapse
                                        $collapseId = 'dashboard-collapse-items-' . $movement->id;
                                        
                                        // Construir la lista de insumos (inicialmente oculta)
                                        $itemsList = '<div class="requisition-items-list" id="' . $collapseId . '" style="display: none;"><ul class="list-unstyled">';
                                        foreach ($items as $item) {
                                            $ingredient = $item->ingredient;
                                            if ($ingredient) {
                                                $itemsList .= '<li><i class="bx bx-package text-muted"></i> ';
                                                $itemsList .= Html::encode($ingredient->ingredient);
                                                if (!empty($ingredient->brand)) {
                                                    $itemsList .= ' <span class="text-muted">(' . Html::encode($ingredient->brand) . ')</span>';
                                                }
                                                $itemsList .= ' - <strong>' . Yii::$app->formatter->asDecimal($item->quantity_requested, 2) . '</strong> ';
                                                $itemsList .= Html::encode($ingredient->um ?? '');
                                                $itemsList .= '</li>';
                                            }
                                        }
                                        $itemsList .= '</ul></div>';
                                        
                                        // Botón para expandir/colapsar
                                        echo '<div>';
                                        echo '<a href="javascript:void(0);" class="requisition-toggle-btn" data-target="' . $collapseId . '" data-expanded="false">';
                                        echo '<i class="bx bx-list-ul"></i> Requisición (' . $itemCount . ' insumos) <i class="bx bx-chevron-down chevron-icon"></i>';
                                        echo '</a>';
                                        echo $itemsList;
                                        echo '</div>';
                                    }
                                    ?>
                                <?php else: ?>
                                    <?= $movement->ingredient ? $movement->ingredient->ingredient : '-' ?>
                                <?php endif; ?>
                            </td>
                            <!-- <td class="movement-quantity">
                                <?php if ($movement->type === 'requisition'): ?>
                                    -
                                <?php else: ?>
                                    <?= $movement->ingredient ? sprintf("%s %s", $movement->quantity, $movement->ingredient->um) : '-' ?>
                                <?php endif; ?>
                            </td>
                            <td class="movement-total"><?= formatPrice($movement->total) ?></td> -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
// JavaScript para manejar el collapse de insumos en el dashboard
$this->registerJs("
$(document).on('click', '.requisition-toggle-btn', function(e) {
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