<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $movements \common\models\Movement[] */

$now = new DateTime();
$hoy = $now->format("Y-m-d");
$ayer = $now->modify("-1 day")->format('Y-m-d');
$antier = $now->modify("-1 day")->format('Y-m-d');

$antierTotal = array_sum(\yii\helpers\ArrayHelper::getColumn($business->getMovements()->andWhere(['DATE(created_at)' => $antier, 'type' => \common\models\Movement::TYPE_INPUT])->all(), 'total'));
$ayerTotal = array_sum(\yii\helpers\ArrayHelper::getColumn($business->getMovements()->andWhere(['DATE(created_at)' => $ayer, 'type' => \common\models\Movement::TYPE_INPUT])->all(), 'total'));;
$hoyTotal = array_sum(\yii\helpers\ArrayHelper::getColumn($business->getMovements()->andWhere(['DATE(created_at)' => $hoy, 'type' => \common\models\Movement::TYPE_INPUT])->all(), 'total'));;

// Total de los tres días
$totalGastos = $antierTotal + $ayerTotal + $hoyTotal;

// CSS personalizado para el nuevo diseño
$this->registerCss("
    .three-days-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    
    .three-days-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .three-days-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .three-days-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .days-total {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
    }
    
    .days-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }
    
    .days-row:last-child {
        border-bottom: none;
    }
    
    .day-label {
        display: flex;
        align-items: center;
        color: #34495e;
        font-weight: 500;
    }
    
    .day-icon {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 10px;
        font-size: 0.8rem;
    }
    
    .today-icon {
        background-color: rgba(231, 76, 60, 0.15);
        color: #e74c3c;
    }
    
    .yesterday-icon {
        background-color: rgba(243, 156, 18, 0.15);
        color: #f39c12;
    }
    
    .day-before-icon {
        background-color: rgba(52, 152, 219, 0.15);
        color: #3498db;
    }
    
    .day-amount {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .today-amount {
        color: #e74c3c;
    }
    
    .yesterday-amount {
        color: #f39c12;
    }
    
    .day-before-amount {
        color: #3498db;
    }
    
    .empty-days {
        text-align: center;
        padding: 1.5rem 0;
        color: #95a5a6;
    }
    
    .empty-days i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
");
?>

<div class="three-days-container">    <div class="three-days-header">
        <h5>Gastos Recientes</h5>
        <div class="days-total">
            <?= formatPrice($totalGastos) ?>
        </div>
    </div>
    
    <?php if ($antierTotal == 0 && $ayerTotal == 0 && $hoyTotal == 0): ?>
        <div class="empty-days">
            <i class="fas fa-coins"></i>
            <p>No hay gastos registrados</p>
        </div>
    <?php else: ?>
        <!-- Hoy -->
        <div class="days-row">
            <div class="day-label">
                <div class="day-icon today-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                Hoy
            </div>            <div class="day-amount today-amount">
                <?= formatPrice($hoyTotal) ?>
            </div>
        </div>
        
        <!-- Ayer -->
        <div class="days-row">
            <div class="day-label">
                <div class="day-icon yesterday-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                Ayer
            </div>            <div class="day-amount yesterday-amount">
                <?= formatPrice($ayerTotal) ?>
            </div>
        </div>
        
        <!-- Antier -->
        <div class="days-row">
            <div class="day-label">
                <div class="day-icon day-before-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                Antier
            </div>            <div class="day-amount day-before-amount">
                <?= formatPrice($antierTotal) ?>
            </div>
        </div>
    <?php endif; ?>
</div>