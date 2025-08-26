<?php

?>
<div class="container-fluid">
    <div class="container">
        <nav class="navbar navbar-dark navbar-expand-lg py-lg-0">
            <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="navbar-brand">
                <img src="<?= Yii::getAlias("@web/images/logo.png") ?>" alt="" width="150">
            </a>
            <button class="navbar-toggler bg-primary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars text-dark"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto">
<!--                    <a href="index.html" class="nav-item nav-link text-primary">Acerca del IIR</a>-->
                    <a href="#" class="nav-item nav-link text-primary">Inicio</a>
                    <a href="#servicios" class="nav-item nav-link text-primary">¿Qué ofrecemos?</a>
                    <a href="#precios" class="nav-item nav-link text-primary">Precios</a>
                    <a href="<?= Yii::$app->params['backendBaseUrl'] . "/user/register" ?>" class="nav-item nav-link text-primary">Comenzar</a>
                    <a href="<?= Yii::$app->params['backendBaseUrl'] . "/user/login" ?>" class="nav-item nav-link text-primary">Iniciar Sesión</a>

                </div>
            </div>
        </nav>
    </div>
</div>
