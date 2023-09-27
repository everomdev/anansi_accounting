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
    <div class="layout-container">
        <?= $this->render('_aside') ?>
        <div class="layout-page">
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
