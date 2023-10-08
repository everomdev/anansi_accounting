<?php

/** @var yii\web\View $this */

/** @var string $content */

use backend\assets\AppAsset;
use yii\helpers\Html;

\backend\assets\ThemeAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>
    <!-- Topbar Start -->
    <?= $this->render('_topbar') ?>
    <!-- Topbar End -->


    <!-- Navbar Start -->
    <?= $this->render('_navbar') ?>
    <!-- Navbar End -->
    <div class="container-fluid py-5">
        <div class="container">
            <?= $content ?>
        </div>
    </div>


    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage();
