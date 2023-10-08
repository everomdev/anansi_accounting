<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Main backend application asset bundle.
 */
class SneatAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        [
            "https://fonts.googleapis.com",
            'rel' => 'preconnect',
        ],
        [
            "https://fonts.gstatic.com",
            "rel" => 'preconnect',
            'crossorigin'
        ],
        [
            'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap',
            'rel' => 'stylesheet'
        ],
        "vendors/sneat/assets/vendor/fonts/boxicons.css",
        [
            "vendors/sneat/assets/vendor/css/core.css",
            "class" => "template-customizer-core-css"
        ],
        [
            "vendors/sneat/assets/vendor/css/theme-default.css",
            "class" => "template-customizer-core-css"
        ],
        "vendors/sneat/assets/css/demo.css",
        "vendors/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css",
        "vendors/sneat/assets/vendor/libs/apex-charts/apex-charts.css",

    ];
    public $js = [
//        "vendors/sneat/assets/vendor/libs/jquery/jquery.js",
        "vendors/sneat/assets/vendor/libs/popper/popper.js",
        "vendors/sneat/assets/vendor/js/helpers.js",
        "vendors/sneat/assets/vendor/js/bootstrap.js",
        "vendors/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js",
        "vendors/sneat/assets/vendor/js/menu.js",
        "vendors/sneat/assets/vendor/libs/apex-charts/apexcharts.js",
        "vendors/sneat/assets/js/config.js",
        "vendors/sneat/assets/js/main.js",
        "vendors/sneat/assets/js/dashboards-analytics.js",
        "vendors/sneat/assets/js/extended-ui-perfect-scrollbar.js",
//        "vendors/sneat/assets/js/form-basic-inputs.js",
//        "vendors/sneat/assets/js/ui-modals.js",
        "vendors/sneat/assets/js/ui-popover.js",
        "vendors/sneat/assets/js/ui-toasts.js",
        "https://cdn.ckeditor.com/ckeditor5/39.0.2/decoupled-document/ckeditor.js",
        [
            "https://buttons.github.io/buttons.js",
            "async",
            "defer"
        ],
        'js/site.js'
    ];
    public $depends = [
        YiiAsset::class,
//        'yii\bootstrap\BootstrapAsset',
        \rmrevin\yii\fontawesome\CdnFreeAssetBundle::class,

    ];
}
