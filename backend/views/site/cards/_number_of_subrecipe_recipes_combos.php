<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */

// Obtenemos los contadores para usar en los porcentajes y gráficos
$recipesCount = $business->getStandardRecipes()->andWhere(['type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN, 'in_construction' => false])->count();
$subrecipesCount = $business->getStandardRecipes()->andWhere(['type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB, 'in_construction' => false])->count();
$combosCount = $business->getMenus()->count();
$total = $recipesCount + $subrecipesCount + $combosCount;

// Calculamos los porcentajes (evitando división por cero)
$recipesPercent = $total > 0 ? round(($recipesCount / $total) * 100) : 0;
$subrecipesPercent = $total > 0 ? round(($subrecipesCount / $total) * 100) : 0;
$combosPercent = $total > 0 ? round(($combosCount / $total) * 100) : 0;

// CSS personalizado para el nuevo diseño
$this->registerCss("
    .recipe-stats-container {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .recipe-stats-container:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .recipe-stats-header {
        padding-bottom: 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
    }
    
    .recipe-stats-header h5 {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #2c3e50;
        margin-bottom: 0.25rem;
        font-size: 1.25rem;
    }
    
    .recipe-stats-header p {
        color: #7f8c8d;
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    
    .recipe-stat-item {
        margin-bottom: 1.75rem;
    }
    
    .recipe-stat-item:last-child {
        margin-bottom: 0;
    }
    
    .recipe-stat-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .recipe-stat-label {
        display: flex;
        align-items: center;
        color: #34495e;
        font-weight: 500;
    }
    
    .recipe-stat-label i {
        margin-right: 10px;
        font-size: 1.2rem;
    }
    
    .recipe-stat-value {
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .recipe-stat-value span {
        font-size: 0.85rem;
        color: #95a5a6;
        font-weight: 400;
        margin-left: 5px;
    }
    
    .recipe-stat-progress {
        height: 8px;
        background-color: #ecf0f1;
        border-radius: 100px;
        overflow: hidden;
        position: relative;
    }
    
    .recipe-stat-progress-bar {
        height: 100%;
        border-radius: 100px;
        transition: width 1s ease-in-out;
        width: 0;
    }
    
    .stat-bar-main {
        background: linear-gradient(to right, #3498db, #2980b9);
    }
    
    .stat-bar-sub {
        background: linear-gradient(to right, #2ecc71, #27ae60);
    }
    
    .stat-bar-combo {
        background: linear-gradient(to right, #9b59b6, #8e44ad);
    }
    
    .total-badge {
        background: rgba(52, 73, 94, 0.1);
        color: #34495e;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
    }
");

// JavaScript para animar las barras de progreso
$this->registerJs("
    // Función para animar las barras de progreso
    function animateProgressBars() {
        document.querySelectorAll('.recipe-stat-progress-bar').forEach(function(bar) {
            setTimeout(function() {
                bar.style.width = bar.getAttribute('data-width') + '%';
            }, 300);
        });
    }
    
    // Ejecutar animación al cargar
    document.addEventListener('DOMContentLoaded', animateProgressBars);
");
?>

<div class="recipe-stats-container">
    <div class="recipe-stats-header d-flex justify-content-between align-items-center">
        <div>
            <h5>Inventario de Recetas</h5>
            <p>Distribución de sus recetas, subrecetas y combos</p>
        </div>
        <div class="total-badge">
            <?= $total ?> elementos
        </div>
    </div>
    
    <div class="recipe-stats-body">
        <!-- Recetas Principales -->
        <div class="recipe-stat-item">
            <div class="recipe-stat-meta">
                <div class="recipe-stat-label">
                    <i class="fas fa-utensils text-primary"></i>
                    Recetas Principales
                </div>
                <div class="recipe-stat-value">
                    <?= $recipesCount ?> <span><?= $recipesPercent ?>%</span>
                </div>
            </div>
            <div class="recipe-stat-progress">
                <div class="recipe-stat-progress-bar stat-bar-main" 
                     data-width="<?= $recipesPercent ?>"></div>
            </div>
        </div>
        
        <!-- Subrecetas -->
        <div class="recipe-stat-item">
            <div class="recipe-stat-meta">
                <div class="recipe-stat-label">
                    <i class="fas fa-mortar-pestle text-success"></i>
                    Subrecetas
                </div>
                <div class="recipe-stat-value">
                    <?= $subrecipesCount ?> <span><?= $subrecipesPercent ?>%</span>
                </div>
            </div>
            <div class="recipe-stat-progress">
                <div class="recipe-stat-progress-bar stat-bar-sub"
                     data-width="<?= $subrecipesPercent ?>"></div>
            </div>
        </div>
        
        <!-- Combos -->
        <div class="recipe-stat-item">
            <div class="recipe-stat-meta">
                <div class="recipe-stat-label">
                    <i class="fas fa-hamburger text-purple"></i>
                    Combos y Menús
                </div>
                <div class="recipe-stat-value">
                    <?= $combosCount ?> <span><?= $combosPercent ?>%</span>
                </div>
            </div>
            <div class="recipe-stat-progress">
                <div class="recipe-stat-progress-bar stat-bar-combo"
                     data-width="<?= $combosPercent ?>"></div>
            </div>
        </div>
    </div>
</div>