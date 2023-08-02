<?php

namespace backend\helpers;
use yii\web\UrlManager;

class RedisKeys
{
    const USER_KEY = "user_information";
    const PROFILE_KEY = "profile_information";
    const BUSINESS_KEY = "business_information";

    public static function getValue($key)
    {
        $value = \Yii::$app->cache->get($key);
        if($value == null || empty($value)){
            \Yii::$app->response->redirect(['user/login']);
        }
        return json_decode(\Yii::$app->cache->get($key), true);
    }

    public static function setValue($key, $data)
    {
        \Yii::$app->cache->set($key, $data);
    }
}
