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
                <img src="<?= Yii::getAlias("@web/images/logo1.png") ?>" alt=""
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
                                <div><?= Yii::t('app', 'Familias de recetas') ?></div>
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
                    <?php if (Yii::$app->user->can('ingredients_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'ingredient-stock' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['ingredient-stock/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Catálogo de insumos') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (Yii::$app->user->can('providers_list')): ?>
                        <li class="menu-item <?= $currentControllerId == 'provider' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['provider/index']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Providers') ?></div>
                            </a>
                        </li>
                        <li class="menu-item <?= $currentControllerId == 'ingredients' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['provider/ingredients']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Ingredients by Providers') ?></div>
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
                    <?php if (Yii::$app->user->can('sales_view')): ?>
                        <li class="menu-item <?= $currentControllerId == 'sales' ? 'active' : '' ?>">
                            <a href="<?= \yii\helpers\Url::to(['standard-recipe/sales']) ?>" class="menu-link">
                                <div><?= Yii::t('app', 'Ventas') ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
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
// Agregar JavaScript para controlar el comportamiento de los menús desplegables
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    // Manejar los clics en los encabezados de menú
    document.querySelectorAll('.menu-link[data-bs-toggle="collapse"]').forEach(function(menuLink) {
        menuLink.addEventListener('click', function(e) {
            var menuItem = this.closest('.menu-item');
            
            // Si el elemento ya está abierto y no tiene submenu activo, permitir que se cierre
            if (this.getAttribute('aria-expanded') === 'true' && !menuItem.classList.contains('active')) {
                // Bootstrap se encargará de cerrarlo
            } 
            // Si estamos abriendo y no es activo, cerrar los otros que no sean activos
            else if (this.getAttribute('aria-expanded') === 'false' && !menuItem.classList.contains('active')) {
                document.querySelectorAll('.menu-item:not(.active) .menu-link[data-bs-toggle="collapse"][aria-expanded="true"]').forEach(function(openMenu) {
                    new bootstrap.Collapse(document.querySelector(openMenu.getAttribute('href'))).hide();
                    openMenu.setAttribute('aria-expanded', 'false');
                });
            }
        });
    });
});
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>