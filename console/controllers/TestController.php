<?php

namespace console\controllers;

use common\models\Business;
use common\models\User;
use Da\User\Factory\TokenFactory;
use Yii;

class TestController extends BaseController
{
    public function actionTest(){
        $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
        $prices = $stripe->prices->search([
            'query' => "product: 'prod_OgqjpThAnSxBaL' AND active: 'true'"
        ]);

        foreach ($prices->data as $price) {
            print_r($price->active);
        }
    }
}
