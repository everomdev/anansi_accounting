<?php

namespace console\controllers;

use common\models\Plan;
use console\controllers\BaseController;

class PlanController extends BaseController
{
    public function actionRegisterPlansInStripe()
    {
        $plans = Plan::find()->all();
        foreach ($plans as $plan) {
            $plan->registerProductInStripe();
        }
    }
}
