<?php

namespace api\modules\v1\controllers;

use api\models\user\Profile;
use api\models\user\User;
use yii\filters\AccessControl;

class MeController extends BaseActiveController
{
    public $modelClass = Profile::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = [
            'options',
        ];

        // setup access
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
//            'rules' => [
//                [
//                    'allow' => true,
//                    'actions' => ['view', 'me', 'me-update-account', 'me-update-profile', 'me-change-password', 'test-auth', 'delete'],
//                    'roles' => ['@']
//                ],
//            ],
//        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    public function actionIndex(){
        return User::find()->where(['id' => \Yii::$app->user->identity->id])->one();
    }
}
