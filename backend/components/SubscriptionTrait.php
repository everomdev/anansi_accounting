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
            $user = \common\models\User::findOne(['id' => Yii::$app->user->id]);
            $userPlan = UserPlan::findOne(['user_id' => Yii::$app->user->id]);
            
            // Verificar si es un usuario empleado (está en user_business)
            $userBusiness = \common\models\UserBusiness::findOne(['user_id' => $user->id]);
            $planToCheck = $userPlan; // Por defecto usar el plan del usuario actual
            
            if ($userBusiness) {
                // Es un empleado, necesita verificar la suscripción del dueño del business
                $businessOwner = \common\models\Business::findOne(['id' => $userBusiness->business_id]);
                if ($businessOwner) {
                    $planToCheck = UserPlan::findOne(['user_id' => $businessOwner->user_id]);
                }
            }
            
            if (!$planToCheck || $planToCheck->stripe_subscription_status === 'canceled') {
                Yii::$app->session->setFlash('warning',
                    'Tu suscripción ha expirado. Por favor renueva para continuar.');
                return Yii::$app->controller->redirect(['/site/enable-subscription']);
            }
            
            // Verificar si la suscripción está activa
            if ($planToCheck->stripe_subscription_status === 'active') {
                return true;
            }
        }

        return true;
    }
}