<?php

/* @var $this yii\web\View */

$this->title = Yii::$app->name;
?>
<div class="vh-100 d-flex align-items-center"
     style="min-height: 100%; background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url(<?= Yii::getAlias("@web/media/header.jpg") ?>); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="container">
        <div class="animated fadeInDown">
            <h1 class="text-primary"><?= \Yii::$app->name ?></h1>
            <h3 class="text-white">Optimiza tus Ingresos y Maximiza tu Rentabilidad</h3>
            <h3 class="text-white">Descubre la Solución Definitiva para el Control de Costos en tu Restaurante</h3>
        </div>
    </div>
</div>

<?= $this->render('sections/_intro') ?>
<?= $this->render('sections/_services') ?>
<?= $this->render('sections/_benefits') ?>
<?= $this->render('sections/_action') ?>
<?= $this->render('sections/_pricing') ?>
<?= $this->render('sections/_final') ?>
