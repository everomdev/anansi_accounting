<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $families \common\models\RecipeCategory[] */

$families = $business->getRecipeCategories()->andWhere(['type' => \common\models\RecipeCategory::TYPE_MAIN])->all();

usort($families, function($a, $b){
    return $b->getRecipes(\common\models\RecipeCategory::TYPE_MAIN)->count() - $a->getRecipes(\common\models\RecipeCategory::TYPE_MAIN)->count();
});

// Calcular el total de recetas en todas las familias
$totalRecipes = 0;
foreach ($families as $family) {
    $totalRecipes += $family->getRecipes(\common\models\RecipeCategory::TYPE_MAIN)->count();
}

// CSS personalizado para el nuevo diseño con altura fija y scroll
$this->registerCss("
    .family-stats-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        transition: all 0.3s ease;
        height: 380px; /* Altura fija */
        display: flex;
        flex-direction: column;
    }
    
    .family-stats-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .family-stats-header {
        padding-bottom: 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
        flex-shrink: 0; /* Evitar que el encabezado se encoja */
    }
    
    .family-stats-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        font-size: 1.25rem;
    }
    
    .family-stats-header p {
        color: #7f8c8d;
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    
    .family-stats-body {
        overflow-y: auto; /* Agregar scroll vertical */
        flex-grow: 1; /* Permitir que el cuerpo ocupe el espacio restante */
        scrollbar-width: thin; /* Para Firefox */
        scrollbar-color: rgba(52, 152, 219, 0.5) rgba(236, 240, 241, 0.5); /* Para Firefox */
    }
    
    /* Estilizar scrollbar para navegadores webkit (Chrome, Safari, Edge) */
    .family-stats-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .family-stats-body::-webkit-scrollbar-track {
        background: rgba(236, 240, 241, 0.5);
        border-radius: 10px;
    }
    
    .family-stats-body::-webkit-scrollbar-thumb {
        background-color: rgba(52, 152, 219, 0.5);
        border-radius: 10px;
    }
    
    .family-stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    
    .family-stat-item:last-child {
        border-bottom: none;
    }
    
    .family-name {
        display: flex;
        align-items: center;
        color: #34495e;
        font-weight: 500;
    }
    
    .family-name i {
        margin-right: 10px;
        color: #3498db;
    }
    
    .family-count {
        display: flex;
        align-items: center;
    }
    
    .family-count-number {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.1rem;
    }
    
    .family-count-badge {
        background-color: rgba(52, 152, 219, 0.15);
        color: #3498db;
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        border-radius: 100px;
        margin-left: 8px;
        font-weight: 500;
    }
    
    .total-badge {
        background: rgba(52, 73, 94, 0.1);
        color: #34495e;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
    }
    
    .empty-families {
        text-align: center;
        padding: 2rem 0;
        color: #95a5a6;
    }
    
    .empty-families i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
");
?>

<div class="family-stats-container">
    <div class="family-stats-header d-flex justify-content-between align-items-center">
        <div>
            <h5>Familias de Recetas</h5>
            <p>Distribución de recetas por categoría</p>
        </div>
        <div class="total-badge">
            <?= $totalRecipes ?> recetas
        </div>
    </div>
    
    <div class="family-stats-body">
        <?php if (empty($families)): ?>
            <div class="empty-families">
                <i class="fas fa-folder-open"></i>
                <p>No hay familias de recetas disponibles</p>
            </div>
        <?php else: ?>
            <?php foreach ($families as $family): ?>
                <?php 
                    $count = $family->getRecipes(\common\models\RecipeCategory::TYPE_MAIN)->count();
                    $percent = $totalRecipes > 0 ? round(($count / $totalRecipes) * 100) : 0;
                ?>
                <div class="family-stat-item">
                    <div class="family-name">
                        <i class="fas fa-folder"></i>
                        <?= $family['name'] ?>
                    </div>
                    <div class="family-count">
                        <div class="family-count-number"><?= $count ?></div>
                        <div class="family-count-badge"><?= $percent ?>%</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>