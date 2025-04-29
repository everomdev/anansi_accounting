<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $ingredients \common\models\IngredientStock[] */

// Obtener ingredientes con un solo proveedor
$ingredients = $business->getIngredientStocks()->all();
$ingredients = array_filter($ingredients, function($ingredient){
    return $ingredient->getProviders()->count() == 1;
});

// CSS personalizado para el nuevo diseño
$this->registerCss("
    .single-provider-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.25rem;
        transition: all 0.3s ease;
        height: 380px; /* Altura fija como el componente de familias */
        display: flex;
        flex-direction: column;
    }
    
    .single-provider-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .single-provider-header {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0; /* Evitar que el encabezado se encoja */
    }
    
    .single-provider-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.15rem;
    }
    
    .provider-count {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
    }
    
    .single-provider-body {
        overflow-y: auto; /* Agregar scroll vertical */
        flex-grow: 1; /* Permitir que el cuerpo ocupe el espacio restante */
        scrollbar-width: thin; /* Para Firefox */
        scrollbar-color: rgba(231, 76, 60, 0.5) rgba(236, 240, 241, 0.5); /* Para Firefox */
    }
    
    /* Estilizar scrollbar para navegadores webkit (Chrome, Safari, Edge) */
    .single-provider-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .single-provider-body::-webkit-scrollbar-track {
        background: rgba(236, 240, 241, 0.5);
        border-radius: 10px;
    }
    
    .single-provider-body::-webkit-scrollbar-thumb {
        background-color: rgba(231, 76, 60, 0.5);
        border-radius: 10px;
    }
    
    .ingredient-tag {
        display: inline-block;
        background-color: rgba(231, 76, 60, 0.1);
        color: #c0392b;
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
        border-radius: 100px;
        margin: 0.25rem 0.5rem 0.25rem 0;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    .ingredient-tag:hover {
        background-color: rgba(231, 76, 60, 0.2);
    }
    
    .empty-ingredients {
        text-align: center;
        padding: 2rem 0;
        color: #95a5a6;
    }
    
    .empty-ingredients i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
");
?>

<div class="single-provider-container">
    <div class="single-provider-header">
        <h5>Insumos con un solo proveedor</h5>
        <div class="provider-count">
            <?= count($ingredients) ?> insumos
        </div>
    </div>
    
    <div class="single-provider-body">
        <?php if (empty($ingredients)): ?>
            <div class="empty-ingredients">
                <i class="fas fa-box-open"></i>
                <p>Todos los insumos tienen múltiples proveedores</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-wrap">
                <?php foreach ($ingredients as $ingredient): ?>
                    <div class="ingredient-tag">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <?= $ingredient->ingredient ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>