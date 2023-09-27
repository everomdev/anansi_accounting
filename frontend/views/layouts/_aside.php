<?php


use backend\helpers\RedisKeys;
use backend\widgets\Menu;
use backend\components\Menu as MenuItem;

/**
 * @var $this \yii\web\View
 */

$currentControllerId = $this->context->action->controller->id;

$action = $this->context->action->id;
if (in_array($action, ['price-trend', 'storage', 'theoretical-yield', 'real-yield'])) {
    $currentControllerId = $action;
}
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
              <span class="app-brand-logo demo">

              </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">
                <?= "Sistema de costeo" ?>
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item <?= $currentControllerId == 'user' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['//user/admin/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Users') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'business' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['//business/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Businesses') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'category-group' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['//category-group/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Group Categories') ?></div>
            </a>
        </li>

        <li class="menu-item <?= $currentControllerId == 'category' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['//category/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Categories') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'ingredient' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['//ingredient/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Ingredients') ?></div>
            </a>
        </li>
        <li class="menu-item <?= $currentControllerId == 'plan' ? 'active' : '' ?>">
            <a href="<?= \yii\helpers\Url::to(['//plan/index']) ?>" class="menu-link">

                <div><?= Yii::t('app', 'Planes') ?></div>
            </a>
        </li>


        <!-- Layouts -->

    </ul>
</aside>
