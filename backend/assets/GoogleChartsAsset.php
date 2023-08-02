<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class GoogleChartsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        [
            'https://www.gstatic.com/charts/loader.js',
            'type' => 'text/javascript'
        ]
    ];
    public $depends = [
        'yii\web\YiiAsset',
//        'yii\bootstrap\BootstrapAsset',
        \rmrevin\yii\fontawesome\CdnFreeAssetBundle::class,
    ];
}
