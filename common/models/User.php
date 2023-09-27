<?php


namespace common\models;


use backend\helpers\RedisKeys;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;


/**
 *
 * @property-read mixed $plan
 * @property-read null $subscription
 * @property-read UserPlan|null $userPlan
 * @property-read array $invoices
 * @property-read string $profileFullName
 */
class User extends \Da\User\Model\User
{

    public function fields()
    {
        return [
            'id',
            'username',
            'email',
        ];
    }

    public function extraFields()
    {
        return ['profile', 'roles', 'profileFullName'];
    }


    /**
     * @return string
     */
    public function getProfileFullName()
    {
        return !is_null($this->profile) ? $this->profile->name . " " . $this->profile->first_surname . " " . $this->profile->second_surname : '';
    }

    /**
     * @inheritdoc
     *
     */
    public function afterSave($insert, $changedAttributes)
    {
        ActiveRecord::afterSave($insert, $changedAttributes);
    }

    public function getUserPlan()
    {
        return $this->hasOne(UserPlan::class, ['user_id' => 'id']);
    }

    public function getBusiness()
    {
        return Business::find()
            ->innerJoin("user_business ub", "ub.business_id=business.id")
            ->where([
                'or',
                ['business.user_id' => $this->id],
                ['ub.user_id' => $this->id],
            ])->one();
    }

    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id'])
            ->viaTable('user_plan', ['user_id' => 'id']);
//        return Plan::find()
//            ->innerJoin('user_plan up', 'up.plan_id=plan.id')
//            ->where(['up.user_id' => $this->id]);
    }

    public function selectPlan($planId)
    {
        $userPlan = new UserPlan([
            'plan_id' => $planId,
            'user_id' => $this->id
        ]);

        return $userPlan->save();
    }

    public function registerInStripe($planId = null)
    {
        if (empty($planId)) {
            $plan = $this->plan;
            $planId = $plan->plan_id;
        }
        try {
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            $customer = $stripe->customers->create([
                'email' => $this->email,
                'tax_exempt' => 'none',
                'preferred_locales' => ['es'],
            ]);

            Yii::$app->db->createCommand()
                ->update(
                    'user_plan',
                    ['stripe_customer_id' => $customer->id],
                    [
                        'user_id' => $this->id,
                        'plan_id' => $planId
                    ]
                )->execute();
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function onCheckoutSessionComplete($session_id)
    {
        try {
            $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe.secretKey']);
            // comprobar si ya el usuario tiene una suscripción
            $session = $stripe->checkout->sessions->retrieve($session_id);
            $subscription = $stripe->subscriptions->retrieve($session->subscription);
            Yii::$app->db->createCommand()
                ->update(
                    'user_plan',
                    [
                        'stripe_subscription_id' => $subscription->id,
                        'stripe_subscription_status' => $subscription->status,
                    ],
                    ['user_id' => $this->id]
                )
                ->execute();

        } catch (\Exception $exception) {
            Yii::error(Yaml::dump([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]));
        }
    }

    public function getSubscription()
    {
        try {
            $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe.secretKey']);
            // comprobar si ya el usuario tiene una suscripción
            $userPlan = $this->userPlan;
            $subscription = $stripe->subscriptions->retrieve($userPlan->stripe_subscription_id);

            return $subscription;

        } catch (\Exception $exception) {
            Yii::error(Yaml::dump([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]));
        }

        return null;
    }

    public function getInvoices()
    {
        try {
            $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe.secretKey']);
            // comprobar si ya el usuario tiene una suscripción
            $customerId = $this->userPlan->stripe_customer_id;
            $subscriptionId = $this->userPlan->stripe_subscription_id;
            $invoicesSearch = $stripe->invoices->search([
                'query' => "customer: '$customerId' AND subscription: '$subscriptionId' AND status: 'paid'"
            ]);
            return $invoicesSearch['data'];
        } catch (\Exception $exception) {
            Yii::error(Yaml::dump([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]));
        }

        return [];
    }

    public function changePlan($newPlanId, $price)
    {
        try {
            $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe.secretKey']);
            $userPlan = $this->userPlan;
            $subscription = $this->getSubscription();
            $stripe->subscriptions->update($subscription->id, [
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $price,
                    ],
                ],
                'proration_behavior' => 'always_invoice',
            ]);
            Yii::$app->db->createCommand()
                ->update(
                    'user_plan',
                    ['plan_id' => $newPlanId],
                    ['user_id' => $this->id]
                )
                ->execute();
            $this->applyRoles();
            return true;
        } catch (\Exception $exception) {
            Yii::error(Yaml::dump([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]));
        }
        return false;
    }

    public function applyRoles()
    {
        $authManager = Yii::$app->authManager;
        $authManager->revokeAll($this->id);

        $permissions = $this->plan->permissions;
        foreach ($permissions as $permissionName) {
            $permission = $authManager->getPermission($permissionName);
            $authManager->assign($permission, $this->id);
        }
    }

    public function canMultiple($permissions = [])
    {
        foreach ($permissions as $permission) {
            if (!Yii::$app->user->can($permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasRestrictions($restriction = 'recipes')
    {
        $plan = $this->plan;
        $business = RedisKeys::getBusiness();
        switch ($restriction) {
            case 'recipes':
                $recipesCount = StandardRecipe::find()
                    ->where(['business_id' => $business->id])
                    ->andWhere([
                        'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                        'in_construction' => 0
                    ])->count();
                return $recipesCount >= $plan->recetas;
            case 'subrecipes':
                $recipesCount = StandardRecipe::find()
                    ->where(['business_id' => $business->id])
                    ->andWhere([
                        'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB,
                        'in_construction' => 0
                    ])->count();
                return $recipesCount >= $plan->subrecetas;
            case 'convoy':
                $convoysCount = Convoy::find()
                    ->where(['business_id' => $business->id])
                    ->count();
                return $convoysCount >= $plan->convoy;
            case 'combos':
                $menuCount = Menu::find()
                    ->where(['business_id' => $business->id])
                    ->count();
                return $menuCount >= $plan->combos;
            case 'users':
                $usersCount = (new Query())
                    ->select("*")
                    ->from('user_business')
                    ->where([
                        'business_id' => $business->id
                    ])
                    ->count();
                return $usersCount >= $plan->users;
            default:
                return false;
        }
    }

    public function cancelSubscription()
    {
        try {
            $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe.secretKey']);
            // comprobar si ya el usuario tiene una suscripción
            $userPlan = $this->userPlan;
            $stripe->subscriptions->cancel($userPlan->stripe_subscription_id);
            $userPlan->stripe_subscription_id = null;
            $userPlan->stripe_subscription_status = 'canceled';
            $userPlan->save(false);
            return true;
        } catch (\Exception $exception) {
            Yii::error(Yaml::dump([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]));
        }

        return false;
    }

}
