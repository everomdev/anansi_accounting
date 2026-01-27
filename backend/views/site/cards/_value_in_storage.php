<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $ingredients \common\models\IngredientStock[] */

$ingredients = $business->ingredientStocks;
$total = array_sum(\yii\helpers\ArrayHelper::getColumn($ingredients, 'valueInMoney'));

// CSS personalizado para el nuevo diseño compacto
$this->registerCss("
    .storage-value-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
        overflow: hidden;
        max-width: 100%;
        min-height: 150px;
        display: flex;
        flex-direction: column;
    }
    
    .storage-value-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .storage-value-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
    }
    
    .storage-value-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .storage-value-content {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0;
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }
    
    .storage-value-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.5rem;
        margin-right: 1rem;
        color: #3498db;
    }
    
    .storage-value-info {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }
    
    .storage-value-amount {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
        color: #3498db;
        word-break: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }
    
    .storage-value-label {
        font-size: 0.9rem;
        color: #7f8c8d;
    }
    
    /* Responsive adjustments */
    @media (max-width: 576px) {
        .storage-value-container {
            padding: 1rem;
            min-height: 130px;
        }
        
        .storage-value-header h5 {
            font-size: 1rem;
        }
        
        .storage-value-amount {
            font-size: 1.5rem;
        }
        
        .storage-value-icon {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
            margin-right: 0.75rem;
        }
        
        .storage-value-content {
            padding: 0.25rem 0;
        }
    }
    
    @media (max-width: 400px) {
        .storage-value-amount {
            font-size: 1.25rem;
        }
        
        .storage-value-icon {
            width: 35px;
            height: 35px;
            font-size: 1.1rem;
            margin-right: 0.5rem;
        }
    }
");
?>

<div class="storage-value-container">
    <div class="storage-value-header">
        <h5>Dinero en Almacén</h5>
    </div>
    
    <div class="storage-value-content">
        <div class="storage-value-icon">
            <i class="fas fa-warehouse"></i>
        </div>
        <div class="storage-value-info">
            <div class="storage-value-amount"><?= formatPrice($total) ?></div>
           
        </div>
    </div>
</div>