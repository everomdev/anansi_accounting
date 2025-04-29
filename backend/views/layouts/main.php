<?php

/* @var $this \yii\web\View */

/* @var $content string */

use backend\assets\AdminLtePluginAsset;
use rmrevin\yii\fontawesome\FAS;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

\backend\assets\SneatAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?>
    </title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container d-flex">
    <aside class="layout-menu fixed-top vh-100 overflow-auto" style="width: 260px; margin-top: 0;">
        <?= $this->render('_aside') ?>
    </aside>
    <div class="layout-page ms-auto" style="width: calc(100% - 260px); overflow-y: auto; height: 100vh;">
            <?= $this->render('_navbar') ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <?= \backend\widgets\FlashMessages::widget(); ?>
                    <?= $content ?>
                </div>
                <?= $this->render('_footer') ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
