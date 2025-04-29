<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $families \common\models\RecipeCategory[] */

$families = $business->getRecipeCategories()->andWhere(['type' => \common\models\RecipeCategory::TYPE_MAIN])->all();

$data = [];

// Procesar datos BCG
foreach ($families as $family) {
    $bcgData = $business->getBcgData($family->name);
    $totalCostEffectiveness = 0;
    array_walk($bcgData['data'], function ($item) use (&$totalCostEffectiveness) {
        $totalCostEffectiveness += ($item->sales * $item->price) - ($item->sales * $item->cost);
    });
    if (!empty($bcgData['totalSales'])) {
        $popularityAxis = round(0.7 * (100 / $bcgData['totalSales']) * 100, 2);
        $costEffectivenessAxis = round($totalCostEffectiveness / $bcgData['totalSales'], 2);
    } else {
        $popularityAxis = 0;
        $costEffectivenessAxis = 0;
    }
    $estrella = 0;
    $vaca = 0;
    $perro = 0;
    $rata = 0;

    foreach ($bcgData['data'] as $item) {
        if ($item instanceof \common\models\StandardRecipe) {
            /** @var $item \common\models\StandardRecipe */
            $quadrant = $item->getBcg($popularityAxis, $costEffectivenessAxis, $bcgData['totalSales']);
            switch ($quadrant) {
                case 'ESTRELLA':
                    $estrella++;
                    break;
                case 'VACA':
                    $vaca++;
                    break;
                case 'PERRO':
                    $perro++;
                    break;
                case 'ENIGMA':
                    $rata++;
                    break;
            }
        }
    }
    $data[$family->name] = [$estrella, $vaca, $perro, $rata];
}

// CSS personalizado para el nuevo diseño con scrollbar
$this->registerCss("
    .bcg-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
        height: 380px; /* Altura fija */
        display: flex;
        flex-direction: column;
    }
    
    .bcg-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .bcg-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0; /* Evitar que el encabezado se encoja */
    }
    
    .bcg-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .bcg-content {
        overflow-y: auto; /* Agregar scroll vertical */
        flex-grow: 1; /* Permitir que el contenido ocupe el espacio restante */
        scrollbar-width: thin; /* Para Firefox */
        scrollbar-color: rgba(52, 152, 219, 0.5) rgba(236, 240, 241, 0.5); /* Para Firefox */
    }
    
    /* Estilizar scrollbar para navegadores webkit (Chrome, Safari, Edge) */
    .bcg-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .bcg-content::-webkit-scrollbar-track {
        background: rgba(236, 240, 241, 0.5);
        border-radius: 10px;
    }
    
    .bcg-content::-webkit-scrollbar-thumb {
        background-color: rgba(52, 152, 219, 0.5);
        border-radius: 10px;
    }
    
    .bcg-header .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
    }
    
    .bcg-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .bcg-table th {
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
    
    .bcg-table td {
        padding: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
    }
    
    .bcg-table tr:last-child td {
        border-bottom: none;
    }
    
    .family-name {
        font-weight: 500;
        color: #2c3e50;
    }
    
    .bcg-badge {
        display: inline-block;
        min-width: 30px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        border-radius: 50%;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .bcg-star {
        background-color: rgba(241, 196, 15, 0.15);
        color: #f39c12;
    }
    
    .bcg-cow {
        background-color: rgba(46, 204, 113, 0.15);
        color: #27ae60;
    }
    
    .bcg-dog {
        background-color: rgba(231, 76, 60, 0.15);
        color: #c0392b;
    }
    
    .bcg-rat {
        background-color: rgba(52, 152, 219, 0.15);
        color: #2980b9;
    }
    
    .empty-bcg {
        text-align: center;
        padding: 2rem 0;
        color: #95a5a6;
    }
    
    .empty-bcg i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .bcg-legend {
        display: flex;
        justify-content: space-around;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        flex-wrap: wrap;
        flex-shrink: 0; /* Evitar que la leyenda se encoja */
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        font-size: 0.8rem;
        margin: 0.25rem 0.5rem;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
    }
");
?>

<div class="bcg-container">
    <div class="bcg-header">
        <h5>Matriz BCG</h5>
        <span class="badge bg-secondary"><?= count($data) ?> familias</span>
    </div>
    
    <?php if (empty($data)): ?>
        <div class="empty-bcg">
            <i class="fas fa-chart-pie"></i>
            <p>No hay datos disponibles para la matriz BCG</p>
        </div>
    <?php else: ?>
        <div class="bcg-content">
            <div class="table-responsive">
                <table class="bcg-table">
                    <thead>
                        <tr>
                            <th>Familia</th>
                            <th class="text-center">Estrella</th>
                            <th class="text-center">Vaca</th>
                            <th class="text-center">Perro</th>
                            <th class="text-center">Rata</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $family => $bcg): ?>
                        <tr>
                            <td class="family-name"><?= $family ?></td>
                            <td class="text-center">
                                <?php if ($bcg[0] > 0): ?>
                                    <span class="bcg-badge bcg-star"><?= $bcg[0] ?></span>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($bcg[1] > 0): ?>
                                    <span class="bcg-badge bcg-cow"><?= $bcg[1] ?></span>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($bcg[2] > 0): ?>
                                    <span class="bcg-badge bcg-dog"><?= $bcg[2] ?></span>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($bcg[3] > 0): ?>
                                    <span class="bcg-badge bcg-rat"><?= $bcg[3] ?></span>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bcg-legend">
            <div class="legend-item">
                <div class="legend-color" style="background-color: #f39c12;"></div>
                <span>Estrella: Alta participación, alto crecimiento</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #27ae60;"></div>
                <span>Vaca: Alta participación, bajo crecimiento</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #c0392b;"></div>
                <span>Perro: Baja participación, bajo crecimiento</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #2980b9;"></div>
                <span>Enigma: Baja participación, alto crecimiento</span>
            </div>
        </div>
    <?php endif; ?>
</div>