<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $families \common\models\RecipeCategory[] */

// Obtener el valor de rentabilidad real
$month = (int)date('n');
$year = (int)date('Y');
$realYield = $business->getRealYield($month, $year)['totalPcr'];
$yieldPercentage = formatPercentage($realYield*100);

// Determinar el estado de la rentabilidad para el color
$yieldStatus = 'success'; // Por defecto, asumimos buena rentabilidad


// CSS personalizado para el nuevo diseño más compacto
$this->registerCss("
    .real-yield-stats-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    
    .real-yield-stats-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .real-yield-stats-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
    }
    
    .real-yield-stats-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .real-yield-content {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0;
    }
    
    .real-yield-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.5rem;
        margin-right: 1rem;
    }
    
    .real-yield-icon.bg-success-light {
        color: #2ecc71;
    }
    
    .real-yield-icon.bg-warning-light {
        background-color: rgba(243, 156, 18, 0.15);
        color: #f39c12;
    }
    
    .real-yield-icon.bg-danger-light {
        background-color: rgba(231, 76, 60, 0.15);
        color: #e74c3c;
    }
    
    .real-yield-info {
        flex: 1;
    }
    
    .real-yield-percentage {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    
    .real-yield-percentage.text-success {
        color: #2ecc71 !important;
    }
    
    .real-yield-percentage.text-warning {
        color: #f39c12 !important;
    }
    
    .real-yield-percentage.text-danger {
        color: #e74c3c !important;
    }
    
    .real-yield-label {
        font-size: 0.9rem;
        color: #7f8c8d;
    }
");
?>

<div class="real-yield-stats-container">
    <div class="real-yield-stats-header">
        <h5>Rentabilidad Real</h5>
    </div>
    
    <div class="real-yield-content">
        <div class="real-yield-icon bg-<?= $yieldStatus ?>-light">
            <?php if ($yieldStatus === 'success'): ?>
                <i class="fas fa-chart-pie"></i>
            <?php elseif ($yieldStatus === 'warning'): ?>
                <i class="fas fa-chart-area"></i>
            <?php else: ?>
                <i class="fas fa-chart-bar"></i>
            <?php endif; ?>
        </div>
        <div class="real-yield-info">
            <div class="real-yield-percentage text-<?= $yieldStatus ?>"><?= $yieldPercentage ?></div>
            
        </div>
    </div>
</div>