<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
            'basePath' => '@app/modules/v1',
            'controllerNamespace' => 'api\modules\v1\controllers',
        ],
        'user' => [
            'class' => Da\User\Module::class,
            'enableRegistration' => true,
            'enableFlashMessages' => false,
            'allowUnconfirmedEmailLogin' => false,
            'enableEmailConfirmation' => true,
            'mailParams' => [
                'fromEmail' => $params['senderEmail'],
                'reconfirmationMailSubject' => "Nuevo email en {$params['appName']}"
            ],
            'classMap' => [
                'User' => \api\models\user\User::class,
                'Profile' => \api\models\user\Profile::class,
                'Token' => \api\models\user\Token::class
            ],
            'routes' => [

            ],
        ],
        'yii2images' => [
            'class' => 'rico\yii2images\Module',
            //be sure, that permissions ok
            //if you cant avoid permission errors you have to create "images" folder in web root manually and set 777 permissions
            'imagesStorePath' => 'images/store', //path to origin images
            'imagesCachePath' => 'images/cache', //path to resized copies
            'graphicsLibrary' => 'Imagick', //but really its better to use 'Imagick'
            'placeHolderPath' => '@webroot/images/placeHolder.png', // if you want to get placeholder when image not exists, string will be processed by Yii::getAlias
        ],
    ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@Da/User/resources/views' => '@api/views/user'
                ]
            ]
        ],
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'user' => [
            'identityClass' => api\models\user\User::class,
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-api',
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

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'OPTIONS <action:(.*)>' => 'options',
                        'POST login' => 'login',
                        'POST signup' => 'signup',
                        'POST confirm' => 'confirm'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/me',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'OPTIONS <action:(.*)>' => 'options',
                    ]
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'v1/business',
                    'extraPatterns' => [
                        'OPTIONS <action:(.*)>' => 'options',
                    ]
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'v1/ingredient',
                    'extraPatterns' => [
                        'OPTIONS <action:(.*)>' => 'options',
                    ]
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'v1/stock',
                    'extraPatterns' => [
                        'OPTIONS <action:(.*)>' => 'options',
                    ]
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'v1/recipes',
                    'extraPatterns' => [
                        'OPTIONS <action:(.*)>' => 'options',
                    ]
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'v1/menu',
                    'extraPatterns' => [
                        'OPTIONS <action:(.*)>' => 'options',
                    ]
                ],
            ],
        ],

        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->format == 'html') {
                    return $response;
                }


                $responseData = $response->data;

                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }

                if ($response->statusCode >= 200 && $response->statusCode <= 299) {
                    $response->data = [
                        'success' => true,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                } else {
                    $response->data = [
                        'success' => false,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                }
                return $response;
            },
        ],

    ],
    'on beforeRequest' => function ($event) {
        $lang = substr(Yii::$app->request->headers->get('Accept-Language', false, true), 0, 2);
        if ($lang)
            Yii::$app->language = $lang;
    },
    'params' => $params,
];
