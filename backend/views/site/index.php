<?php
/* @var $this yii\web\View */

$this->title = Yii::$app->name;
?>

<!-- <div class="alert alert-info" role="alert">
    <h4 class="alert-heading">¡Trabajando en mejoras del Dashboard!</h4>
    <p>Estamos trabajando en optimizar y mejorar el dashboard para ofrecerte una mejor experiencia. Pronto estará disponible con mejoras.</p>
    <hr>
    <p class="mb-0">Gracias por tu paciencia.</p>
</div> -->

<?php

$this->title = Yii::$app->name;
$business = \backend\helpers\RedisKeys::getBusiness();

// CSS personalizado para el layout del dashboard
$this->registerCss("
    .dashboard-container {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 1.25rem;
        width: 100%;
        padding: 1rem 0;
    }
    
    /* Tarjetas por columnas en pantallas grandes */
    .dashboard-card-small {
        grid-column: span 3;
    }
    
    .dashboard-card-medium {
        grid-column: span 6;
    }
    
    .dashboard-card-large {
        grid-column: span 9;
    }
    
    .dashboard-card-full {
        grid-column: span 12;
    }
    
    /* Estilos para mejorar espaciado y dimensiones */
    .dashboard-card-content {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    /* Responsivo para tabletas */
    @media screen and (max-width: 992px) {
        .dashboard-card-small {
            grid-column: span 6;
        }
        
        .dashboard-card-medium, .dashboard-card-large {
            grid-column: span 12;
        }
    }
    
    /* Responsivo para móviles */
    @media screen and (max-width: 576px) {
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .dashboard-card-small,
        .dashboard-card-medium,
        .dashboard-card-large,
        .dashboard-card-full {
            width: 100%;
        }
    }
");
?>
<div class="dashboard-container">
    <!-- Indicadores pequeños en la primera fila -->
    <?php if (Yii::$app->user->can('theoretical_profitability_view')): ?>
        <div class="dashboard-card-small">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_theoretical_yield', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (Yii::$app->user->can('real_profitability_view')): ?>
        <div class="dashboard-card-small">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_real_yield', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (Yii::$app->user->can('storage_list')): ?>
        <div class="dashboard-card-small">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_value_in_storage', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (Yii::$app->user->can('movements_list')): ?>
        <div class="dashboard-card-small">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_last_three_days', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    
    <!-- Componente de estadísticas si está disponible -->
    <?php if (Yii::$app->user->can('recipe_list')): ?>
        <div class="dashboard-card-medium">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_number_of_subrecipe_recipes_combos', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('movements_list')): ?>
        <div class="dashboard-card-medium">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_movements', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- <?php if (Yii::$app->user->can('ingredients_list')): ?>
        <div class="dashboard-card-medium">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_ingredients_with_one_provider', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?> -->
    
    <!-- Tercera fila - Tablas y otros componentes -->
    <?php if (Yii::$app->user->can('recipe_list')): ?>
        <div class="dashboard-card-medium">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_by_cost_center', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (Yii::$app->user->can('providers_list')): ?>
        <div class="dashboard-card-medium">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_providers', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Cuarta fila - Componentes grandes -->
    <?php if (Yii::$app->user->can('matrix_bcg')): ?>
        <div class="dashboard-card-medium">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_matrix_bcg', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
    
    <!-- Segunda fila - Datos con scroll -->
    <?php if (Yii::$app->user->can('recipe_list')): ?>
        <div class="dashboard-card-medium">
            <div class="dashboard-card-content">
                <?= $this->render('cards/_families', ['business' => $business]) ?>
            </div>
        </div>
    <?php endif; ?>
    
</div>
?>