<?php


$mapUrl = "https://www.google.com/maps/dir//Ciudad+de+M%C3%A9xico,+CDMX/@19.4326853,-99.1682275,13z/data=!4m9!4m8!1m0!1m5!1m1!1s0x85ce0026db097507:0x54061076265ee841!2m2!1d-99.133208!2d19.4326077!3e0?entry=ttu";
$phoneLink = "https://wa.me/+525518639427/?text=Hola%2C%20necesito%20hacer%20hacer%20una%20consulta.";
?>

<div class="container-fluid topbar-top bg-danger">
    <div class="container">
        <div class="d-flex justify-content-between topbar py-1">
            <div class="d-flex align-items-center flex-shrink-0 topbar-info">
                <a href="<?= $mapUrl ?>" class="me-4 text-white" target="_blank"><i class="fas fa-map-marker-alt me-2 text-white"></i>México, CDMX</a>
                <a href="<?= $phoneLink ?>" class="me-4 text-white" target="_blank"><i class="fas fa-phone-alt me-2 text-white"></i>+525518639427</a>
            </div>

            <div class="d-flex align-items-center justify-content-center topbar-icon">
                <a href="https://facebook.com/coachrestaurantero" class="me-4"><i class="fab fa-facebook-f text-white"></i></a>
                <a href="https://instagram.com/coachingrestaurantero/" class="me-4"><i class="fab fa-instagram text-white"></i></a>
                <a href="https://linktr.ee/coachrestaurantero" class="">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 25" height="18" style="display: block; width: auto;"><path d="M13.5108 5.85343L17.5158 1.73642L19.8404 4.11701L15.6393 8.12199H21.5488V11.4268H15.6113L19.8404 15.5345L17.5158 17.8684L11.7744 12.099L6.03299 17.8684L3.70842 15.5438L7.93745 11.4361H2V8.12199H7.90944L3.70842 4.11701L6.03299 1.73642L10.038 5.85343V0H13.5108V5.85343ZM10.038 16.16H13.5108V24.0019H10.038V16.16Z" fill="#ffffff"></path></svg>
                </a>
            </div>
        </div>
    </div>
</div>
