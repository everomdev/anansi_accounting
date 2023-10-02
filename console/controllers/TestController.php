<?php

namespace console\controllers;

use common\models\Business;
use common\models\User;
use Da\User\Factory\TokenFactory;
use Yii;
use yii\symfonymailer\Message;

class TestController extends BaseController
{
    public function actionTest()
    {
        Yii::$app->mailer->send((new Message())
            ->setSubject("Prueba")
            ->setFrom(Yii::$app->params['senderEmail'])
            ->setTo('daironigr@gmail.com')
            ->setTextBody("Probando email")
        );
    }
}
