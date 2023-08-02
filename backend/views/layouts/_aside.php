<?php


use backend\widgets\Menu;
use backend\components\Menu as MenuItem;

/**
 * @var $this \yii\web\View
 */

$currentControllerId = $this->context->action->controller->id;
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$action = $this->context->action->id;
if($action == 'price-trend'){
    $currentControllerId = $action;
}
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo">

              </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">
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
        <li class="menu-item <?= $currentControllerId == 'ingredient-stock' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['ingredient-stock/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Storage') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'menu' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['menu/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Menus') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'sub-standard-recipe' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['sub-standard-recipe/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Sub recipes') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'standard-recipe' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['standard-recipe/index']) ?>" class="menu-link">
                <div><?= Yii::t('app', 'Recipes') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'category' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['category/index']) ?>" class="menu-link">
                <div><?= Yii::t('app', 'Categories') ?></div>
            </a>
        </li>

        <li class="menu-item <?= $currentControllerId == 'movement' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['movement/index']) ?>" class="menu-link">
                <div><?= Yii::t('app', 'Movements') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'provider' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['provider/index']) ?>" class="menu-link">
                <div><?= Yii::t('app', 'Providers') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'price-trend' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['ingredient-stock/price-trend']) ?>" class="menu-link">
                <div><?= Yii::t('app', 'Price Trend') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'business' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['business/my-business']) ?>" class="menu-link">
                <div><?= Yii::t('app', 'My business') ?></div>
            </a>
        </li>



        <!-- Layouts -->

    </ul>
</aside>
