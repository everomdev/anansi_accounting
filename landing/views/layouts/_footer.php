<?php

?>

<div class="container-fluid py-5" style="background: linear-gradient(rgba(0, 0, 0, .7), rgba(0, 0, 0, .7)), url(<?= Yii::getAlias("@web/media/footer.jpeg") ?>) center center no-repeat; background-size: cover;
        color: rgba(255, 255, 255, .7);
        margin-top: 6rem;">
    <div class="container py-5">
        <div class="row g-4 footer-inner">

            <div class="col-lg-6 col-md-6">
                <div class="footer-item">
                    <h4 class="text-white fw-bold mb-4">Enlaces útiles</h4>
                    <div class="d-flex flex-column align-items-start">
                        <a class="btn btn-link ps-0" href="https://institutodeinvestigacionrestaurantero.org/"><i class="fa fa-check me-2"></i>Instituto de investigación restaurantero</a>
                        <a class="btn btn-link ps-0" href="https://coachrestaurantero.com/"><i class="fa fa-check me-2"></i>Coach Restaurantero</a>
                        <a class="btn btn-link ps-0" href="https://clubdelecturapararestauranteros.com/"><i class="fa fa-check me-2"></i>Club de lectura para restauranteros</a>
                        <a class="btn btn-link ps-0" href="https://blog.institutodeinvestigacionrestaurantero.org/"><i class="fa fa-check me-2"></i>Nuestro Blog</a>
                        <a class="btn btn-link ps-0" href=""><i class="fa fa-check me-2"></i>Términos y condiciones</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="footer-item">
                    <h4 class="text-white fw-bold mb-4">Enlaces</h4>
                    <div class="d-flex flex-column align-items-start">
                        <a class="btn btn-link ps-0" href="#"><i class="fa fa-check me-2"></i>Inicio</a>
                        <a class="btn btn-link ps-0" href="#servicios"><i class="fa fa-check me-2"></i>¿Qué ofrecemos?</a>
                        <a class="btn btn-link ps-0" href="#precios"><i class="fa fa-check me-2"></i>Precios</a>
                        <a class="btn btn-link ps-0" href="<?= Yii::$app->params['backendBaseUrl'] . "/user/register" ?>"><i class="fa fa-check me-2"></i>Comenzar</a>
                    </div>

                </div>
            </div>
            <div class="col-lg-3 col-md-6">
<!--                <div class="footer-item">-->
<!--                    <h4 class="text-white fw-bold mb-4">Contact Us</h4>-->
<!--                    <a href="" class="btn btn-link w-100 text-start ps-0 pb-3 border-bottom rounded-0"><i class="fa fa-map-marker-alt me-3"></i>123 Street, CA, USA</a>-->
<!--                    <a href="" class="btn btn-link w-100 text-start ps-0 py-3 border-bottom rounded-0"><i class="fa fa-phone-alt me-3"></i>+012 345 67890</a>-->
<!--                    <a href="" class="btn btn-link w-100 text-start ps-0 py-3 border-bottom rounded-0"><i class="fa fa-envelope me-3"></i>info@example.com</a>-->
<!--                </div>-->
            </div>
        </div>
    </div>
</div>
