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
    'analytics',
    'menu-improvement',
    'profit-comparison',
    'matrix-bcg',
    'charts',
    'ingredients',
    'users'
];
if (in_array($action, $actions)) {
    $currentControllerId = $action;
}

?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo">

              </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2" style="color: #800e13">
                <?= $business['name'] ?>
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
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
        <?php if (Yii::$app->user->can('ingredients_list')): ?>
            <li class="menu-item <?= $currentControllerId == 'category' ? 'active' : '' ?>">
                <a href="<?= \yii\helpers\Url::to(['category/index']) ?>" class="menu-link">
                    <div><?= Yii::t('app', 'Categories') ?></div>
                </a>
            </li>
        <?php endif; ?>
        <?php if (Yii::$app->user->identity->canMultiple(['recipe_list', 'subrecipe_list'])): ?>
            <li class="menu-item <?= $currentControllerId == 'recipe-category' ? 'active' : '' ?>">
                <a href="<?= \yii\helpers\Url::to(['recipe-category/index']) ?>" class="menu-link">
                    <div><?= Yii::t('app', 'Recipe Categories') ?></div>
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
        <?php if (Yii::$app->user->can('ingredients_list')): ?>
            <li class="menu-item <?= $currentControllerId == 'ingredient-stock' ? 'active' : '' ?>">
                <a href="<?= \yii\helpers\Url::to(['ingredient-stock/index']) ?>" class="menu-link">

                    <div><?= Yii::t('app', 'Insumos') ?></div>
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

        <?php if (Yii::$app->user->identity->canMultiple(['movements_list'])): ?>
            <li class="menu-item <?= $currentControllerId == 'consumption-center' ? 'active' : '' ?>">
                <a href="<?= \yii\helpers\Url::to(['consumption-center/index']) ?>" class="menu-link">
                    <div><?= Yii::t('app', 'Consumption Centers') ?></div>
                </a>
            </li>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('storage')): ?>
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
        <?php if (Yii::$app->user->can('subrecipe_list')): ?>
            <li class="menu-item <?= $currentControllerId == 'sub-standard-recipe' ? 'active' : '' ?>">
                <a href="<?= \yii\helpers\Url::to(['sub-standard-recipe/index']) ?>" class="menu-link">

                    <div><?= Yii::t('app', 'Sub recipes') ?></div>
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
        <?php if (Yii::$app->user->can('manage_users')): ?>
            <li class="menu-item <?= $currentControllerId == 'users' ? 'active' : '' ?>">
                <a href="<?= \yii\helpers\Url::to(['//user/admin/users']) ?>" class="menu-link">
                    <div><?= Yii::t('app', 'Users') ?></div>
                </a>
            </li>
            <li class="menu-item <?= $currentControllerId == 'business' ? 'active' : '' ?>">
                <a href="<?= \yii\helpers\Url::to(['//business/my-business']) ?>" class="menu-link">
                    <div><?= Yii::t('app', 'Settings') ?></div>
                </a>
            </li>
        <?php endif; ?>


        <!-- Layouts -->

    </ul>
</aside>
