<?php

namespace landing\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        "https://fonts.google.com",
        [
            "https://fonts.gstatic.com",
            "rel" => "preconnect"
        ],
        "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css",
        "https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800&display=swap",
        "https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500&display=swap",
        "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css",
        "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css",
        "https://cdn.quilljs.com/1.3.6/quill.snow.css",
        "https://cdn.quilljs.com/1.3.6/quill.bubble.css",
        "https://cdn.quilljs.com/1.3.6/quill.core.css",
        "vendors/theme/lib/animate/animate.min.css",
        "vendors/theme/lib/owlcarousel/assets/owl.carousel.min.css",
        "vendors/theme/css/bootstrap.min.css",
        "vendors/theme/css/style.css",
    ];
    public $js = [
        "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js",
        "https://code.jquery.com/jquery-3.4.1.min.js",
        "https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js",
        "https://cdn.quilljs.com/1.3.6/quill.js",
        "https://cdn.quilljs.com/1.3.6/quill.min.js",
        "https://cdn.quilljs.com/1.3.6/quill.core.js",
        "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js",
        "vendors/theme/lib/wow/wow.min.js",
        "vendors/theme/lib/easing/easing.min.js",
        "vendors/theme/lib/waypoints/waypoints.min.js",
        "vendors/theme/lib/owlcarousel/owl.carousel.min.js",
        "vendors/theme/js/main.js",
        "js/site.js"
    ];
    public $depends = [
        'yii\web\YiiAsset',
//        'yii\bootstrap5\BootstrapAsset',
    ];
}
