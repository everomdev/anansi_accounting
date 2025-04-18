<?php
namespace backend\components;

use Yii;
use common\models\UserPlan;

trait SubscriptionTrait
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Excluir rutas que no requieren validación
        $excludedRoutes = [
            'site/login',
            'site/enable-subscription',
            'user/security/login',
            'site/error',
            'site/comming-soon',
            'site/check-coupon',
            'site/captcha'
        ];

        $currentRoute = Yii::$app->controller->route;

        if (!in_array($currentRoute, $excludedRoutes) && !Yii::$app->user->isGuest) {
            $userPlan = UserPlan::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->cache(60)
                ->one();
            if (!$userPlan || $userPlan->stripe_subscription_status === 'canceled') {
                Yii::$app->session->setFlash('warning',
                    'Tu suscripción ha expirado. Por favor renueva para continuar.');
                return Yii::$app->controller->redirect(['/site/enable-subscription']);
            }
            // Si la suscripción está activa, permitir acceso
            if ($userPlan->stripe_subscription_status === 'active') {
                return true;
            }
        }

        return true;
    }
}