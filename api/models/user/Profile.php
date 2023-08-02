<?php


namespace api\models\user;

use common\models\Business;
use Yii;
use yii\base\InvalidConfigException;


/**
 */
class Profile extends \Da\User\Model\Profile
{

    public function formName()
    {
        return '';
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['username'] = function (Profile $profile) {
            return $profile->user->username;
        };
        $fields['email'] = function (Profile $profile) {
            return $profile->user->username;
        };
        $fields['roles'] = function (Profile $profile) {
            $userId = $profile->user_id;
            $authManager = Yii::$app->authManager;
            $roles = $authManager->getRolesByUser($userId);
            return $roles;
        };


        return $fields;
    }

    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields['business'] = function (Profile $model) {
            return Business::findOne(['user_id' => $model->user_id]);
        };
        return $fields;
    }


}
