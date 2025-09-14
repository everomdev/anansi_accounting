<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $families \common\models\RecipeCategory[] */

// Obtener el valor de rentabilidad teórica
$year = (int)date('Y');
$theoreticalYieldData = $business->getTheoreticalYield(null, $year);
$theoreticalYield = $theoreticalYieldData['theoricalTotal'];
$yieldPercentage = formatPercentage($theoreticalYield) === null? formatPercentage(0) : formatPercentage($theoreticalYield);

// Determinar el estado de la rentabilidad para el color
$yieldStatus = 'success'; // Por defecto, asumimos buena rentabilidad


// CSS personalizado para el nuevo diseño más compacto
$this->registerCss("
    .yield-stats-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    
    .yield-stats-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .yield-stats-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
    }
    
    .yield-stats-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .yield-content {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0;
    }
    
    .yield-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.5rem;
        margin-right: 1rem;
    }
    
    .yield-icon.bg-success-light {
        color: #2ecc71;
    }
    
    .yield-icon.bg-warning-light {
        background-color: rgba(243, 156, 18, 0.15);
        color: #f39c12;
    }
    
    .yield-icon.bg-danger-light {
        background-color: rgba(231, 76, 60, 0.15);
        color: #e74c3c;
    }
    
    .yield-info {
        flex: 1;
    }
    
    .yield-percentage {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    
    .yield-percentage.text-success {
        color: #2ecc71 !important;
    }
    
    .yield-percentage.text-warning {
        color: #f39c12 !important;
    }
    
    .yield-percentage.text-danger {
        color: #e74c3c !important;
    }
    
    .yield-label {
        font-size: 0.9rem;
        color: #7f8c8d;
    }
");
?>

<div class="yield-stats-container">
    <div class="yield-stats-header">
        <h5>Rentabilidad Teórica</h5>
    </div>
    
    <div class="yield-content">
        <div class="yield-icon bg-<?= $yieldStatus ?>-light">
            <?php if ($yieldStatus === 'success'): ?>
                <i class="fas fa-chart-line"></i>
            <?php elseif ($yieldStatus === 'warning'): ?>
                <i class="fas fa-exclamation-triangle"></i>
            <?php else: ?>
                <i class="fas fa-arrow-down"></i>
            <?php endif; ?>
        </div>
        <div class="yield-info">
            <div class="yield-percentage text-<?= $yieldStatus ?>"><?= $yieldPercentage ?></div>
           
        </div>
    </div>
</div>