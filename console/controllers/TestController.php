<?php

namespace console\controllers;

use common\models\User;
use Da\User\Factory\TokenFactory;

class TestController extends BaseController
{
    public function actionTest(){
        $user = User::findOne(['id' => 2]);
        $token = TokenFactory::makeConfirmationToken($user->id);

        var_dump(get_class($token));
        var_dump($token->getUrl());
    }
}
