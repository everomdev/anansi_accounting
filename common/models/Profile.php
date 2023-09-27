<?php

namespace common\models;

use Yii;

class Profile extends \Da\User\Model\Profile
{
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


}
