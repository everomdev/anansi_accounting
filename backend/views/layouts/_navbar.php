<?php

use yii\helpers\Url;
use yii\bootstrap5\Html;
use rmrevin\yii\fontawesome\FAS;

$profile = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::PROFILE_KEY);
?>
<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar"
>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center" style="font-weight: bold; font-size: 22px">
                <?= $this->title ?>
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Place this tag where you want the button to render. -->


            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <?=
                Html::a(
                    "<i class='bx bx-power-off me-2'></i>
                            ",
                    Url::to(['//user/security/logout']), [
                    'class' => 'btn btn-outline-warning',
                    'data' => [
                        'method' => 'post'
                    ]
                ])
                ?>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>
