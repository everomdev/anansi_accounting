<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $consumptionCenters \common\models\ConsumptionCenter[] */

$consumptionCenters = $business->getConsumptionCenters()->all();

// Calcular el consumo total para los porcentajes
$totalConsumption = 0;
$centerData = [];

foreach ($consumptionCenters as $consumptionCenter) {
    $movements = $business->getMovements()
        ->where([
            'type' => \common\models\Movement::TYPE_OUTPUT,
            'provider' => $consumptionCenter->name
        ])->all();
    
    $amount = array_sum(\yii\helpers\ArrayHelper::getColumn($movements, function($movement) {
        return $movement->quantity * $movement->ingredient->lastPrice;
    }));
    
    $totalConsumption += $amount;
    $centerData[] = [
        'name' => $consumptionCenter->name,
        'amount' => $amount
    ];
}

// CSS personalizado para el nuevo diseño
$this->registerCss("
    .cost-center-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    
    .cost-center-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .cost-center-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .cost-center-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .cost-center-total {
        background: rgba(52, 73, 94, 0.1);
        color: #34495e;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
    }
    
    .cost-center-table {
        width: 100%;
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    
    .cost-center-table th {
        color: #7f8c8d;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .cost-center-table td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
    }
    
    .cost-center-table tr:last-child td {
        border-bottom: none;
    }
    
    .center-name {
        display: flex;
        align-items: center;
    }
    
    .center-icon {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(41, 128, 185, 0.1);
        color: #2980b9;
        border-radius: 50%;
        margin-right: 10px;
        font-size: 0.8rem;
    }
    
    .center-amount {
        font-weight: 600;
        color: #2c3e50;
        text-align: right;
    }
    
    .center-bar {
        height: 4px;
        background: #ecf0f1;
        border-radius: 100px;
        margin-top: 6px;
        position: relative;
    }
    
    .center-bar-fill {
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        background: linear-gradient(to right, #3498db, #2980b9);
        border-radius: 100px;
        transition: width 1s ease;
    }
    
    .empty-centers {
        text-align: center;
        padding: 1.5rem 0;
        color: #95a5a6;
    }
    
    .empty-centers i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
");

// JavaScript para animar las barras
$this->registerJs("
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('.center-bar-fill').forEach(function(bar) {
                bar.style.width = bar.dataset.percent + '%';
            });
        }, 300);
    });
");
?>

<div class="cost-center-container">
    <div class="cost-center-header">
        <h5>Consumo por Centro de Costo</h5>        <div class="cost-center-total">
            <?= formatPrice($totalConsumption) ?>
        </div>
    </div>
    
    <?php if (empty($centerData)): ?>
        <div class="empty-centers">
            <i class="fas fa-chart-pie"></i>
            <p>No hay datos de consumo disponibles</p>
        </div>
    <?php else: ?>
        <table class="cost-center-table">
            <thead>
                <tr>
                    <th style="width: 60%">Centro</th>
                    <th style="width: 40%; text-align: right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($centerData as $center): ?>
                    <?php 
                        $percent = $totalConsumption > 0 ? round(($center['amount'] / $totalConsumption) * 100) : 0;
                        $initial = strtoupper(substr($center['name'], 0, 1));
                    ?>
                    <tr>
                        <td>
                            <div class="center-name">
                                <div class="center-icon"><?= $initial ?></div>
                                <?= $center['name'] ?>
                            </div>
                            <div class="center-bar">
                                <div class="center-bar-fill" data-percent="<?= $percent ?>"></div>
                            </div>
                        </td>                        <td class="center-amount">
                            <?= formatPrice($center['amount']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>