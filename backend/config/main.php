<?php

use backend\controllers\user\RegistrationController;
use backend\controllers\user\SecurityController;
use backend\controllers\user\AdminController;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'name' => $params['appName'],
    'modules' => [
        'user' => [
            'class' => Da\User\Module::class,
            'enableRegistration' => true,
            'mailParams' => [
                'fromEmail' => $params['senderEmail'],
                'reconfirmationMailSubject' => "Nuevo email en {$params['appName']}",

            ],
            'classMap' => [
                'Profile' => [
                    'class' => \common\models\Profile::class
                ]
            ],
            'controllerMap' => [
                'admin' => [
                    'class' => AdminController::class
                ],
                'security' => [
                    'class' => SecurityController::class
                ],
                'registration' => [
                    'class' => RegistrationController::class
                ],
                'recovery' => [
                    'class' => \backend\controllers\user\RecoveryController::class
                ]
            ],
            'enableEmailConfirmation' => true,
            'administrators' => ['administrator'],
            'administratorPermissionName' => 'admin',
            // ...other configs from here: [Configuration Options](installation/configuration-options.md), e.g.
            'generatePasswords' => false,
            'switchIdentitySessionKey' => 'myown_usuario_admin_user_key',
            'enableSwitchIdentities' => true,
            'allowUnconfirmedEmailLogin' => false,
            'enableGdprCompliance' => false
        ],
        'yii2images' => [
            'class' => 'rico\yii2images\Module',
            //be sure, that permissions ok
            //if you cant avoid permission errors you have to create "images" folder in web root manually and set 777 permissions
            'imagesStorePath' => 'images/store', //path to origin images
            'imagesCachePath' => 'images/cache', //path to resized copies
            'graphicsLibrary' => 'Imagick', //but really its better to use 'Imagick'
            'placeHolderPath' => '@web/images/placeholder.svg', // if you want to get placeholder when image not exists, string will be processed by Yii::getAlias
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => \common\models\User::class,
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
            'class' => \yii\web\Session::class,
            'timeout' => (30 * 24 * 60) // 30 days
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'assetManager' => [
            'class' => \yii\web\AssetManager::class,
            'bundles' => [
                'kartik\form\ActiveFormAsset' => [
                    'bsDependencyEnabled' => false // do not load bootstrap assets for a specific asset bundle
                ],
            ],
            'appendTimestamp' => true
        ],

        'view' => [
            'theme' => [
                'pathMap' => [
                    '@Da/User/resources/views' => '@app/views/user',
                ]
            ]
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                'resources' => 'ingredient-stock/index',
                'storage' => 'ingredient-stock/storage',
                'theoretical-yield' => 'standard-recipe/theoretical-yield',
                'comming-soon' => 'site/comming-soon',
                'users' => 'user/admin/users',
                'users/create' => 'user/admin/create-user'
            ],
        ]
    ],
    'container' => [
        'definitions' => [
            \yii\grid\GridView::class => [
                'tableOptions' => ['class' => 'table table-head-fixed text-nowrap table-hover'],
                'layout' => '<div class="card"><div class="card-body table-responsive p-0">{items}</div><div class="card-footer">{pager}</div></div>',
                'pager' => [
                    'class' => \yii\widgets\LinkPager::class,
                    'options' => ['class' => 'pagination pagination-sm m-0 float-right'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link', 'data-pjax-scrollto' => '1'],
                    'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                    'firstPageLabel' => "Página inicial",
                    'lastPageLabel' => "Última página",
                ],
                'headerRowOptions' => [
                    'class' => 'text-warning'
                ]
//                'formatter' => [
//                    'class' => \yii\i18n\Formatter::class,
//                    'currencyCode' => 'eur'
//                ]
            ],
            \yii\grid\ActionColumn::class => [
                'buttonOptions' => [
                    'class' => 'text-warning'
                ]
            ]
        ],
    ],
    'params' => $params,
//    'catchAll' => ['site/comming-soon']
];
