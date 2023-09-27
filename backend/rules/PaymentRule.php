<?php

namespace backend\rules;

use backend\helpers\RedisKeys;
use common\models\UserPlan;
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
        $hasActivePayment = UserPlan::find()
            ->where([
                'user_id' => $business->user_id,
                'stripe_subscription_status' => 'active'
            ])->exists();
        return $hasActivePayment;
    }
}
