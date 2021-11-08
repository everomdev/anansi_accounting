<?php

use backend\controllers\user\SecurityController;
use Da\User\Controller\AdminController;

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
    'modules' => [
        'user' => [
            'class' => Da\User\Module::class,
            'enableRegistration' => false,
            'mailParams' => [
                'fromEmail' => $params['senderEmail'],
                'reconfirmationMailSubject' => "Nuevo email en {$params['appName']}"
            ],
            'controllerMap' => [
                'admin' => [
                    'class' => AdminController::class
                ],
                'security' => [
                    'class' => SecurityController::class
                ],
            ],
//            'enableEmailConfirmation' => false,
//            'administrators' => ['administrator'],
            'administratorPermissionName' => 'admin',
            // ...other configs from here: [Configuration Options](installation/configuration-options.md), e.g.
            'generatePasswords' => true,
            'switchIdentitySessionKey' => 'myown_usuario_admin_user_key',
            'enableSwitchIdentities' => true,
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
// ...
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
                    'disabledListItemSubTagOptions' => ['class' => 'page-link']
                ]
            ]
        ]
    ],
    'params' => $params,
];
