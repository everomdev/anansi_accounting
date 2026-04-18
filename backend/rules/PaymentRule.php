<?php

namespace backend\rules;

use backend\helpers\RedisKeys;
use common\models\UserPlan;
use Yii;
use yii\rbac\Rule;

class PaymentRule extends Rule
{
    public $name = 'active_payment';

    /**
     * @inheritDoc
     */
    public function execute($user, $item, $params)
    {
        $business = RedisKeys::getBusiness();
        $userPlan = UserPlan::find()
            ->where(['user_id' => $business->user_id])
            ->one();

        if (!$userPlan || empty($userPlan->stripe_subscription_id)) {
            return false;
        }

        try {
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            $subscription = $stripe->subscriptions->retrieve($userPlan->stripe_subscription_id);
            $status = $subscription->status;

            // Sincronizar estado local si difiere
            if ($userPlan->stripe_subscription_status !== $status) {
                $userPlan->stripe_subscription_status = $status;
                $userPlan->save(false);
            }

            return in_array($status, ['active', 'trialing']);
        } catch (\Exception $e) {
            Yii::error('PaymentRule: Error al verificar suscripción en Stripe: ' . $e->getMessage(), __METHOD__);
            // Si Stripe falla, usar el estado local como fallback
            return in_array($userPlan->stripe_subscription_status, ['active', 'trialing']);
        }
    }
}
