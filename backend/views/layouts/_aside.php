<?php
use backend\widgets\Menu;
use backend\components\Menu as MenuItem;

/**
 * @var $this \yii\web\View
 */
$currentControllerId = $this->context->action->controller->id;
$business = \backend\helpers\RedisKeys::getBusiness();
$plan = $business->user->plan;
$action = $this->context->action->id;
$actions = [
    'price-trend',
    'storage',
    'theoretical-yield',
    'real-yield',
    'sales',
    'menu-recipes',
    'saved-menus',
    'analytics',
    'menu-improvement',
    'profit-comparison',
    'matrix-bcg',
    'charts',
    'ingredients',
    'users',
    'control-insumos',
    'planeacion-compras',
    'comparativa-costo',
    'eficiencia-uso',
    'mix-ventas',
    'factibilidad',
    'estado-resultados'
];
if (in_array($action, $actions)) {
    $currentControllerId = $action;
}

if($currentControllerId == 'standard-recipe'){
    $isSubRecipe = Yii::$app->request->get('type') == 'sub';
    if($isSubRecipe){
        $currentControllerId = 'sub-standard-recipe';
    }
}

// Verificar si algún elemento dentro de cada menú está activo
$configBaseActive = in_array($currentControllerId, ['category', 'recipe-category', 'unit-of-measurement']);
$gestionInsumosActive = in_array($currentControllerId, ['ingredient-stock', 'provider', 'ingredients']);
$costeoActive = in_array($currentControllerId, ['sub-standard-recipe', 'standard-recipe', 'convoy', 'menu']);
$almacenMovimientosActive = in_array($currentControllerId, ['consumption-center', 'storage', 'movement', 'price-trend']);
$menuVentasActive = in_array($currentControllerId, ['sales', 'menu-recipes','saved-menus']);
$rentabilidadAnalisisActive = in_array($currentControllerId, ['theoretical-yield', 'real-yield', 'charts', 'analytics', 'menu-improvement', 'profit-comparison', 'matrix-bcg']);
$kpisControlActive = in_array($currentControllerId, ['control-insumos', 'planeacion-compras', 'comparativa-costo', 'eficiencia-uso', 'mix-ventas', 'factibilidad', 'estado-resultados']);
$administracionConfiguracionActive = in_array($currentControllerId, ['users', 'business']);
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo" style="width: 100%; height: 80px; display: flex; justify-content: center; align-items: center; position: relative;">
        <div style="background: #fff; border-radius: 50px; padding: 8px 20px; display: flex; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="app-brand-link" style="display: flex; justify-content: center; align-items: center;">
                <img src="<?= Yii::getAlias("@web/images/logo.png") ?>" alt=""
                     style="object-fit: contain; max-width: 200px; height: 60px;">
            </a>
        </div>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none" style="position: absolute; right: 10px;">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item <?= $currentControllerId == 'site' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="menu-link">
                <div><?= Yii::t('app', 'Home') ?></div>
            </a>
        </li>

        <!-- Configuración Base -->
        <li class="menu-item <?= $configBaseActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#configuracionBase" role="button" 
               aria-expanded="<?= $configBaseActive ? 'true' : 'false' ?>" 
               aria-controls="configuracionBase">
                <div><?= Yii::t('app', 'Configuración Base') ?></div>
            </a>
            <div class="collapse <?= $configBaseActive ? 'show' : '' ?>" id="configuracionBase">
                <ul class="sub-menu">
                    <?php if (Yii::$app->user->can('ingredients_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'category' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['category/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Familias de insumos') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->identity->canMultiple(['recipe_list', 'subrecipe_list'])): ?>
                        <li class="menu-item <?= $currentControllerId == 'recipe-category' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['recipe-category/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Categorías de recetas') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('ingredients_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'unit-of-measurement' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['unit-of-measurement/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Unidades de medida') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <!-- Gestión de Insumos y Proveedores -->
        <li class="menu-item <?= $gestionInsumosActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#gestionInsumos" role="button" 
               aria-expanded="<?= $gestionInsumosActive ? 'true' : 'false' ?>" 
               aria-controls="gestionInsumos">
                <div><?= Yii::t('app', 'Gestión de Insumos y Proveedores') ?></div>
            </a>
            <div class="collapse <?= $gestionInsumosActive ? 'show' : '' ?>" id="gestionInsumos">
                <ul class="sub-menu">
                    <?php if (Yii::$app->user->can('providers_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'provider' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['provider/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Providers') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('ingredients_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'ingredient-stock' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['ingredient-stock/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Catálogo de insumos') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('ingredients_list')): ?>
                         <li class="menu-item <?= $currentControllerId == 'ingredients' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['provider/ingredients']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Insumos por Proveedores') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                </ul>
            </div>
        </li>

        <!-- Costeo -->
        <li class="menu-item <?= $costeoActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#costeo" role="button" 
               aria-expanded="<?= $costeoActive ? 'true' : 'false' ?>" 
               aria-controls="costeo">
                <div><?= Yii::t('app', 'Costeo') ?></div>
            </a>
            <div class="collapse <?= $costeoActive ? 'show' : '' ?>" id="costeo">
                <ul class="sub-menu">
                    <?php if (Yii::$app->user->can('subrecipe_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'sub-standard-recipe' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['sub-standard-recipe/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Subrecetas') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('recipe_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'standard-recipe' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Recipes') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('convoy_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'convoy' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['convoy/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Convoy') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('combo_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'menu' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['menu/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Combos') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <!-- Menú y Ventas -->
        <li class="menu-item <?= $menuVentasActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#menuVentas" role="button" 
               aria-expanded="<?= $menuVentasActive ? 'true' : 'false' ?>" 
               aria-controls="menuVentas">
                <div><?= Yii::t('app', 'Menú y Ventas') ?></div>
            </a>
            <div class="collapse <?= $menuVentasActive ? 'show' : '' ?>" id="menuVentas">
                <ul class="sub-menu">
                    <?php if (Yii::$app->user->can('menu_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'menu-recipes' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/menu-recipes']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Menú') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('menu_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'saved-menus' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['menu/saved-menus']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Menú histórico') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('sales_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'sales' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/sales']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Ventas') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <!-- Almacén y Movimientos -->
        <li class="menu-item <?= $almacenMovimientosActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#almacenMovimientos" role="button" 
               aria-expanded="<?= $almacenMovimientosActive ? 'true' : 'false' ?>" 
               aria-controls="almacenMovimientos">
                <div><?= Yii::t('app', 'Almacén y Movimientos') ?></div>
            </a>
            <div class="collapse <?= $almacenMovimientosActive ? 'show' : '' ?>" id="almacenMovimientos">
                <ul class="sub-menu">
                    <?php if (Yii::$app->user->identity->canMultiple(['movements_list'])): ?>
                        <li class="menu-item <?= $currentControllerId == 'consumption-center' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['consumption-center/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Consumption Centers') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('storage_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'storage' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['ingredient-stock/storage']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Storage') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('movements_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'movement' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['movement/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Movements') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="menu-item <?= $currentControllerId == 'inventory' ? 'active' : '' ?>">
                        <a href="<?= \yii\helpers\Url::to(['inventory/index']) ?>" class="menu-link">
                            <div><?= Yii::t('app', 'Inventario') ?></div>
                        </a>
                    </li>
                    <?php if (Yii::$app->user->can('price_trend_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'price-trend' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['ingredient-stock/price-trend']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Price Trend') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <!-- Rentabilidad y Análisis -->
        <li class="menu-item <?= $rentabilidadAnalisisActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#rentabilidadAnalisis" role="button" 
               aria-expanded="<?= $rentabilidadAnalisisActive ? 'true' : 'false' ?>" 
               aria-controls="rentabilidadAnalisis">
                <div><?= Yii::t('app', 'Rentabilidad y Análisis') ?></div>
            </a>
            <div class="collapse <?= $rentabilidadAnalisisActive ? 'show' : '' ?>" id="rentabilidadAnalisis">
                <ul class="sub-menu">
                    <?php if (Yii::$app->user->can('theoretical_profitability_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'theoretical-yield' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/theoretical-yield']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Rentabilidad Teórica del Menú') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('real_profitability_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'real-yield' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/real-yield']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Rentabilidad Real del Menú') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('charts_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'charts' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/charts']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Charts') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('menu_analysis_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'analytics' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/analytics']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Menu Analysis') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('menu_improvements_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'menu-improvement' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/menu-improvement']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Menu improvements') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('profitability_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'profit-comparison' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/profit-comparison']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Profit comparison') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('matrix_bcg')): ?>
                        <li class="menu-item <?= $currentControllerId == 'matrix-bcg' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/matrix-bcg']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Matriz BCG') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>
        <!-- KPI's y Control -->
        <li class="menu-item <?= $kpisControlActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#kpisControl" role="button" 
               aria-expanded="<?= $kpisControlActive ? 'true' : 'false' ?>" 
               aria-controls="kpisControl">
                <div><?= Yii::t('app', "KPI's y Control") ?></div>
            </a>
            <div class="collapse <?= $kpisControlActive ? 'show' : '' ?>" id="kpisControl">
                <ul class="sub-menu">
                    <!-- Control de Insumos - HABILITADO -->
                    <?php if (Yii::$app->user->can('movements_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'control-insumos' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['kpi/control-insumos']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Control de Insumos') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Planeación de Compras - HABILITADO -->
                    <!-- <?php if (Yii::$app->user->can('movements_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'planeacion-compras' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['kpi/planeacion-compras']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Proyección de Compras Inteligentes') ?></div>
                            </a>
                        </li>
                    <?php endif; ?> -->
                    
                    <!-- SECCIONES FUTURAS - COMENTADAS POR AHORA -->
                    <?php /* 
                    // Comparativa de % de Costo - DESHABILITADO
                    if (Yii::$app->user->can('analytics_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'comparativa-costo' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['kpi/comparativa-costo']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Comparativa de % de Costo') ?></div>
                            </a>
                        </li>
                    <?php endif;
                    
                    // Eficiencia de Uso de Insumos - DESHABILITADO
                    if (Yii::$app->user->can('analytics_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'eficiencia-uso' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['kpi/eficiencia-uso']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Eficiencia de Uso de Insumos') ?></div>
                            </a>
                        </li>
                    <?php endif;
                    
                    // Mix de Ventas por Categoría - DESHABILITADO
                    if (Yii::$app->user->can('sales_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'mix-ventas' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['kpi/mix-ventas']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Mix de Ventas por Categoría') ?></div>
                            </a>
                        </li>
                    <?php endif;
                    
                    // Análisis de Factibilidad - DESHABILITADO
                    if (Yii::$app->user->can('analytics_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'factibilidad' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['kpi/factibilidad']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Análisis de Factibilidad') ?></div>
                            </a>
                        </li>
                    <?php endif;
                    
                    // Estado de Resultados - DESHABILITADO
                    if (Yii::$app->user->can('analytics_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'estado-resultados' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['kpi/estado-resultados']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Estado de Resultados') ?></div>
                            </a>
                        </li>
                    <?php endif;
                    */ ?>
                </ul>
            </div>
        </li>

        <!-- Administración y Configuración -->
        <li class="menu-item <?= $administracionConfiguracionActive ? 'active open' : '' ?>">
            <a class="menu-link" data-bs-toggle="collapse" href="#administracionConfiguracion" role="button" 
               aria-expanded="<?= $administracionConfiguracionActive ? 'true' : 'false' ?>" 
               aria-controls="administracionConfiguracion">
                <div><?= Yii::t('app', 'Administración y Configuración') ?></div>
            </a>
            <div class="collapse <?= $administracionConfiguracionActive ? 'show' : '' ?>" id="administracionConfiguracion">
                <ul class="sub-menu">
                    <?php if (Yii::$app->user->can('manage_users') and $business != null && $business->user_id == Yii::$app->user->identity->getId() and !Yii::$app->user->identity->hasRestrictions('users')): ?>
                        <li class="menu-item <?= $currentControllerId == 'users' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['//user/admin/users']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Users') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                        <li class="menu-item <?= $currentControllerId == 'business' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['//business/my-business']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Settings') ?></div>
                            </a>
                        </li>
                </ul>
            </div>
        </li>
    </ul>
</aside>

<?php
// CSS simplificado para evitar problemas de focus/blur
$css = <<<CSS
/* Resetear cualquier transformación problemática */
* {
    -webkit-transform: none !important;
    -moz-transform: none !important;
    -ms-transform: none !important;
    transform: none !important;
    -webkit-tap-highlight-color: transparent !important;
    -webkit-touch-callout: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: text !important;
}

/* Permitir selección de texto donde sea necesario */
input, textarea, [contenteditable] {
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    -ms-user-select: text !important;
    user-select: text !important;
}

/* Menú lateral - usando left en lugar de transform */
.layout-menu {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    z-index: 1045 !important;
    width: 260px !important;
    transition: left 0.3s ease !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1) !important;
}

/* Estado contraído del menú - movemos con left */
.layout-menu.collapsed {
    left: -260px !important;
}

/* Ajustar el contenido principal */
.layout-page {
    margin-left: 260px !important;
    transition: margin-left 0.3s ease !important;
    min-height: 100vh !important;
    position: relative !important;
}

/* Cuando el menú está contraído, quitar el margen */
body.menu-collapsed .layout-page {
    margin-left: 0 !important;
}

/* Logo en navbar - oculto por defecto */
.navbar-logo {
    display: none !important;
    align-items: center !important;
    margin-left: 15px !important;
    opacity: 1 !important;
    transition: all 0.3s ease !important;
}

.navbar-logo img {
    max-height: 40px !important;
    object-fit: contain !important;
    width: auto !important;
}

/* Mostrar logo centrado cuando el menú está contraído */
body.menu-collapsed .navbar-logo {
    display: flex !important;
    position: absolute !important;
    left: 50% !important;
    top: 50% !important;
    transform: translate(-50%, -50%) !important;
    margin: 0 !important;
    z-index: 10 !important;
}

/* Ajustar el navbar para permitir el centrado del logo */
body.menu-collapsed .layout-navbar {
    position: relative !important;
}

/* En móvil, mantener el logo a la izquierda */
@media (max-width: 1199px) {
    .navbar-logo {
        display: flex !important;
        position: static !important;
        transform: none !important;
        margin-left: 15px !important;
    }
    
    body.menu-collapsed .navbar-logo {
        position: static !important;
        transform: none !important;
        left: auto !important;
        top: auto !important;
        margin-left: 15px !important;
    }
}

/* Estilos del botón hamburger */
.layout-menu-toggle {
    color: #333 !important;
    text-decoration: none !important;
    padding: 8px !important;
    border-radius: 6px !important;
    cursor: pointer !important;
}

.layout-menu-toggle:hover {
    color: #333 !important;
    background: rgba(0,0,0,0.05) !important;
    text-decoration: none !important;
}

.layout-menu-toggle:focus {
    outline: none !important;
    box-shadow: none !important;
}

/* Asegurar que el navbar mantenga su tamaño */
.layout-navbar {
    min-height: 60px !important;
    width: 100% !important;
    position: relative !important;
}

/* Asegurar que el contenido mantenga su tamaño */
html, body {
    overflow-x: hidden !important;
    min-height: 100vh !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Responsive: en pantallas pequeñas */
@media (max-width: 1199px) {
    .layout-menu {
        left: -260px !important;
    }
    
    .layout-menu.show {
        left: 0 !important;
    }
    
    .layout-page {
        margin-left: 0 !important;
    }
    
    /* Overlay para móvil */
    .layout-menu-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, 0.5) !important;
        z-index: 1040 !important;
        opacity: 0 !important;
        visibility: hidden !important;
        transition: opacity 0.3s ease, visibility 0.3s ease !important;
    }
    
    .layout-menu-overlay.show {
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    body.menu-open {
        overflow: hidden !important;
    }
    
    .navbar-logo {
        display: flex !important;
    }
}

/* Prevenir cualquier zoom o escala */
@media screen and (-webkit-min-device-pixel-ratio: 0) {
    html {
        zoom: 1 !important;
        -webkit-text-size-adjust: 100% !important;
    }
}
CSS;

$this->registerCss($css);

// JavaScript simplificado sin eventos problemáticos
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var layoutMenu = document.getElementById('layout-menu');
    var menuToggleNavbar = document.querySelector('.layout-menu-toggle a');
    var body = document.body;
    var overlay;
    
    // Verificar tamaño de pantalla
    function isMobile() {
        return window.innerWidth < 1200;
    }
    
    // Crear overlay para móvil
    function createOverlay() {
        if (!overlay && isMobile()) {
            overlay = document.createElement('div');
            overlay.className = 'layout-menu-overlay';
            body.appendChild(overlay);
            
            overlay.addEventListener('click', function() {
                closeMobileMenu();
            });
        }
    }
    
    // Comportamiento para escritorio
    function toggleDesktopMenu() {
        var isCollapsed = layoutMenu.classList.contains('collapsed');
        
        if (isCollapsed) {
            layoutMenu.classList.remove('collapsed');
            body.classList.remove('menu-collapsed');
            localStorage.setItem('menuCollapsed', 'false');
        } else {
            layoutMenu.classList.add('collapsed');
            body.classList.add('menu-collapsed');
            localStorage.setItem('menuCollapsed', 'true');
        }
    }
    
    // Comportamiento para móvil
    function openMobileMenu() {
        createOverlay();
        layoutMenu.classList.add('show');
        if (overlay) {
            overlay.classList.add('show');
        }
        body.classList.add('menu-open');
    }
    
    function closeMobileMenu() {
        layoutMenu.classList.remove('show');
        if (overlay) {
            overlay.classList.remove('show');
        }
        body.classList.remove('menu-open');
    }
    
    function toggleMobileMenu() {
        if (layoutMenu.classList.contains('show')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }
    
    // Función principal de toggle
    function toggleMenu() {
        if (isMobile()) {
            toggleMobileMenu();
        } else {
            toggleDesktopMenu();
        }
    }
    
    // Restaurar estado del menú
    function restoreMenuState() {
        if (!isMobile()) {
            var isCollapsed = localStorage.getItem('menuCollapsed') === 'true';
            if (isCollapsed) {
                layoutMenu.classList.add('collapsed');
                body.classList.add('menu-collapsed');
            } else {
                layoutMenu.classList.remove('collapsed');
                body.classList.remove('menu-collapsed');
            }
        } else {
            layoutMenu.classList.remove('collapsed');
            body.classList.remove('menu-collapsed');
        }
    }
    
    // Solo event listener esencial
    if (menuToggleNavbar) {
        menuToggleNavbar.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMenu();
        });
    }
    
    // Cerrar menú móvil al hacer clic en enlaces
    document.querySelectorAll('.menu-item .menu-link:not([data-bs-toggle="collapse"])').forEach(function(link) {
        link.addEventListener('click', function() {
            if (isMobile()) {
                setTimeout(closeMobileMenu, 150);
            }
        });
    });
    
    // Manejar resize con timeout
    var resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(restoreMenuState, 250);
    });
    
    // Inicializar
    restoreMenuState();
});
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>