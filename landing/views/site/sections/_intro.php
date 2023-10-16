<?php

?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6 col-md-12 wow fadeInUp" data-wow-delay=".3s">
                <div style="background: url(<?= Yii::getAlias("@web/media/intro.jpg") ?>); width: auto; max-width: 450px; background-size: cover; height: 300px; border-radius: 15px"></div>
<!--                <img src="--><?php //= Yii::getAlias("@web/images/about.jpg") ?><!--" class="img-fluid h-100" alt="img" style="border-radius: 20px">-->
            </div>
            <div class="col-lg-6 col-md-12 wow fadeInUp" data-wow-delay=".6s">
                <div class="about-item overflow-hidden">
                    <h3 class="mb-2 text-primary"><?= \Yii::$app->name ?></h3>
                    <p class="fs-5" style="text-align: justify;">Nuestra plataforma integral ha sido diseñada pensando en los restauranteros como tú. Con nuestro <?= \Yii::$app->name ?>, puedes tomar el control completo de tu negocio y alcanzar un nuevo nivel de eficiencia y rentabilidad.</p>

<!--                    <button type="button" class="btn btn-primary border-0 rounded-pill px-4 py-3 mt-5">Leer mas...</button>-->
                </div>
            </div>
        </div>
    </div>
</div>
