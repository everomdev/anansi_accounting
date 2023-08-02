<?php


namespace api\models\user;


use yii\base\InvalidParamException;
use yii\helpers\Url;

class Token extends \Da\User\Model\Token
{
    protected $routes = [
        self::TYPE_CONFIRMATION => '/auth/confirm',
        self::TYPE_CONFIRM_NEW_EMAIL => '/auth/confirm-new-email',

        self::TYPE_RECOVERY => '/auth/change-password',
        self::TYPE_CONFIRM_OLD_EMAIL => '/user/settings/confirm',
    ];

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getUrl()
    {

        $thisBaseUrl = \Yii::$app->urlManager->getBaseUrl();

        // send recovery to app
//        if ($this->type == self::TYPE_RECOVERY) {
        $frontEndBaseUrl = \Yii::$app->params['frontendBaseUrl'];
        \Yii::$app->urlManager->setBaseUrl($frontEndBaseUrl);
//        }

        $url = Url::to([$this->routes[$this->type], 'id' => $this->user_id, 'code' => $this->code], true);

        \Yii::$app->urlManager->setBaseUrl($thisBaseUrl);

        return $url;
    }

}
