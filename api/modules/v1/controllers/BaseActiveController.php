<?php


namespace api\modules\v1\controllers;


use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

class BaseActiveController extends ActiveController
{

    public function actions()
    {
        $actions = parent::actions();

        $searchModel = isset($this->searchModelClass) ? $this->searchModelClass : "{$this->modelClass}Search";
        $actions ['index']['prepareDataProvider'] = function ($action) use ($searchModel){
            $a = \Yii::createObject([
                'class' => 'api\helpers\ListActionDataProviderHelper',
                'modelClass' => $this->modelClass,
                'dataFilter' => [
                    'class' => 'yii\data\ActiveDataFilter',
                    'searchModel' => $searchModel,
                ],
                'prepareQuery' => function($action){
                    return $this->modelClass::find();
                },
            ]);

            return $a->getDataProvider();
        };

        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];

        $behaviors['authenticator']['except'] = [
            'options',
        ];

        return $behaviors;
    }

}
