<?php

namespace backend\models;

use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\Profile;
use common\models\User;

class UpdateAccountForm extends \yii\base\Model
{
    public $businessName;
    public $name;
    public $password;
    public $businessId;
    public $userId;
    public $currency_code;
    public $decimal_separator;
    public $thousands_separator;
    public $timezone;
    public $locale;

    public function rules()
    {
        return [
            [[
                'businessName',
                'name',
                'currency_code',
                'decimal_separator',
                'thousands_separator',
                'timezone',
                'locale',
            ], 'required'],
            [[
                'businessName',
                'name',
                'password',
                'currency_code',
                'decimal_separator',
                'thousands_separator',
                'timezone',
                'locale',
            ], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => \Yii::t('app', 'Name'),
            'businessName' => \Yii::t('app', 'My business name'),
            'password' => \Yii::t('app', 'New password'),
        ];
    }

    public function save()
    {
        $business = $this->getBusiness();
        $user = $this->getUser();
        $profile = $user->profile;

        $business->name = $this->businessName;
        $business->currency_code = $this->currency_code;
        $business->decimal_separator = $this->decimal_separator;
        $business->thousands_separator = $this->thousands_separator;
        $business->timezone = $this->timezone;
        $business->locale = $this->locale;
        $profile->name = $this->name;
        if (isset($this->password)) {
            $user->password = $this->password;
        }

        if (!$user->save() or !$profile->save() or !$business->save()) {
            if ($user->hasErrors()) {
                $this->addErrors($user->errors);
            }
            if ($profile->hasErrors()) {
                $this->addErrors($profile->errors);
            }
            if ($business->hasErrors()) {
                $this->addErrors($business->errors);
            }

            return false;
        }

        if ($profile) {
            RedisKeys::setValue(RedisKeys::PROFILE_KEY, json_encode($profile->attributes));

        }
        if ($business) {
            RedisKeys::setValue(RedisKeys::BUSINESS_KEY, json_encode($business->attributes));
        }
        return true;

    }

    public function getBusiness()
    {
        return Business::findOne(['id' => $this->businessId]);
    }

    public function getUser()
    {
        return User::findOne(['id' => $this->userId]);
    }

}
