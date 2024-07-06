<?php

$params = array_merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'name' => $params['appName'],
    'language' => $params['defaultLanguage'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@Da/User/resources/views/mail/' => '@common/mail',
                    '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
                ]
            ]
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'google' => [
                        'class' => 'yii\authclient\clients\Google',
                        'clientId' => 'google_client_id',
                        'clientSecret' => 'google_client_secret',
                    ],
                ]
            ]
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
            ]
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '85.239.241.195',
            'port' => 6379,
            'database' => 0,
        ],
        'formatter' => [
            'locale' => 'es_MX',
            'defaultTimeZone' => 'America/Mexico_City',
            'dateFormat' => 'dd/MM/yyyy',
            'timeFormat' => 'HH:mm',
            'datetimeFormat' => 'dd/MM/yyyy HH:mm',
            'currencyCode' => 'mxn',
            'decimalSeparator' => ',',
            'thousandSeparator' => '.',
            'currencyDecimalSeparator' => ','
        ],

    ],
];
