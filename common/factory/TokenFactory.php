<?php

namespace common\factory;

use api\models\user\Token;

class TokenFactory extends \Da\User\Factory\TokenFactory
{
    public static function make($userId, $type)
    {
        return \Yii::createObject(['class' => Token::class, 'user_id' => $userId, 'type' => $type]);
    }
}
