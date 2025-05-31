<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $providers \common\models\Provider[] */

$providers = $business->getProviders()->all();

usort($providers, function ($a, $b) {
    $purchasesA = $a->getMovements()->count();
    $purchasesB = $b->getMovements()->count();

    return $purchasesB - $purchasesA;
});

$providers = array_slice($providers, 0, 5);

// Calcular el total general
$totalCompras = 0;
foreach ($providers as $provider) {
    $totalCompras += array_sum(\yii\helpers\ArrayHelper::getColumn($provider->getMovements()->all(), 'total'));
}

// CSS personalizado para el nuevo diseño
$this->registerCss("
    .providers-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    
    .providers-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .providers-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .providers-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .providers-total {
        background: rgba(52, 73, 94, 0.1);
        color: #34495e;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
    }
    
    .provider-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }
    
    .provider-item:last-child {
        border-bottom: none;
    }
    
    .provider-name {
        display: flex;
        align-items: center;
        color: #34495e;
        font-weight: 500;
    }
    
    .provider-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        margin-right: 10px;
        background-color: rgba(52, 152, 219, 0.15);
        color: #2980b9;
        font-size: 0.9rem;
    }
    
    .provider-amount {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .empty-providers {
        text-align: center;
        padding: 2rem 0;
        color: #95a5a6;
    }
    
    .empty-providers i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
");
?>

<div class="providers-container">
    <div class="providers-header">
        <h5>Principales Proveedores</h5>        <div class="providers-total">
            <?= formatPrice($totalCompras) ?>
        </div>
    </div>
    
    <?php if (empty($providers)): ?>
        <div class="empty-providers">
            <i class="fas fa-truck"></i>
            <p>No hay proveedores registrados</p>
        </div>
    <?php else: ?>
        <?php foreach ($providers as $provider): ?>
            <?php 
                $total = array_sum(\yii\helpers\ArrayHelper::getColumn($provider->getMovements()->all(), 'total'));
                $initial = strtoupper(substr($provider->name, 0, 1));
            ?>
            <div class="provider-item">
                <div class="provider-name">
                    <div class="provider-icon">
                        <?= $initial ?>
                    </div>
                    <?= $provider->name ?>
                </div>                <div class="provider-amount">
                    <?= formatPrice($total) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>