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
        if($this->getUserBusiness() != false){
            return Business::find()
                ->innerJoin("user_business ub", "ub.business_id=business.id")
                ->where(['ub.user_id' => $this->id])->one();
        }else{
            return Business::find()->where(['business.user_id' => $this->id])->one();
        }

    }

    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id'])
            ->viaTable('user_plan', ['user_id' => 'id']);
//        return Plan::find()
//            ->innerJoin('user_plan up', 'up.plan_id=plan.id')
//            ->where(['up.user_id' => $this->id]);
    }

    /**
     * Gets query for [[UserConsumptionCenters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserConsumptionCenters()
    {
        return $this->hasMany(UserConsumptionCenter::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ConsumptionCenters]] via UserConsumptionCenter.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConsumptionCenters()
    {
        return $this->hasMany(ConsumptionCenter::class, ['id' => 'consumption_center_id'])
            ->viaTable('user_consumption_center', ['user_id' => 'id']);
    }

    /**
     * Get the default consumption center for this user
     * Si no hay uno marcado como default, retorna el primero
     *
     * @return ConsumptionCenter|null
     */
    public function getDefaultConsumptionCenter()
    {
        // Buscar el centro marcado como default
        $relation = UserConsumptionCenter::find()
            ->where(['user_id' => $this->id, 'is_default' => true])
            ->one();
        
        if ($relation && $relation->consumptionCenter) {
            return $relation->consumptionCenter;
        }
        
        // Si no hay default, buscar el primer centro asignado
        $firstRelation = UserConsumptionCenter::find()
            ->where(['user_id' => $this->id])
            ->orderBy(['created_at' => SORT_ASC])
            ->one();
        
        return $firstRelation ? $firstRelation->consumptionCenter : null;
    }

    /**
     * Verifica si el usuario tiene centros de consumo asignados
     *
     * @return bool
     */
    public function hasConsumptionCenters()
    {
        return UserConsumptionCenter::find()
            ->where(['user_id' => $this->id])
            ->exists();
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
            //die(var_dump($subscription->status));
            return $subscription;

        } catch (\Exception $exception) {
            Yii::error(Yaml::dump([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]));
        }

        return null;
    }

    public function getSubscriptionDetails()
    {
        try {
            $userPlan = $this->userPlan;
            if (!$userPlan || !$userPlan->stripe_subscription_id) {
                return null;
            }

            $stripe = new \Stripe\StripeClient(\Yii::$app->params['stripe.secretKey']);
            $subscription = $stripe->subscriptions->retrieve($userPlan->stripe_subscription_id);
            
            if (!$subscription) {
                return null;
            }

            // Obtener el precio desde items o plan (compatibilidad con ambas estructuras)
            $price = null;
            $amount = 0;
            $interval = '';
            
            if (!empty($subscription->items->data)) {
                $item = $subscription->items->data[0];
                if (isset($item->price)) {
                    $price = $item->price;
                    $amount = $price->unit_amount;
                    $interval = $price->recurring->interval;
                } elseif (isset($item->plan)) {
                    // Para compatibilidad con versiones anteriores de Stripe
                    $price = $item->plan;
                    $amount = $price->amount;
                    $interval = $price->interval;
                }
            } elseif (isset($subscription->plan)) {
                // Fallback al plan directo en la suscripción
                $price = $subscription->plan;
                $amount = $price->amount;
                $interval = $price->interval;
            }

            // Determinar la próxima fecha de pago basándose en el estado
            $nextPaymentDate = null;
            if ($subscription->status === 'active' && !$subscription->cancel_at_period_end) {
                $nextPaymentDate = $subscription->current_period_end;
            } elseif ($subscription->status === 'trialing' && $subscription->trial_end) {
                $nextPaymentDate = $subscription->trial_end;
            }
            
            return [
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end' => $subscription->current_period_end,
                'billing_interval' => $interval, // 'month' or 'year'
                'amount' => $amount ? $amount / 100 : 0, // Amount in dollars
                'currency' => $subscription->currency ? strtoupper($subscription->currency) : 'USD',
                'next_payment_date' => $nextPaymentDate,
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
                'canceled_at' => $subscription->canceled_at,
                'ended_at' => $subscription->ended_at ?? null,
                'trial_end' => $subscription->trial_end,
                'trial_start' => $subscription->trial_start,
                'cancel_at' => $subscription->cancel_at ?? null,
            ];

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
            
            // Obtener el precio actual de la suscripción
            $currentPrice = $subscription->items->data[0]->price;
            $currentPriceAmount = $currentPrice->unit_amount; // Precio en centavos
            
            // Obtener el nuevo precio desde Stripe para comparar
            $newPriceObject = $stripe->prices->retrieve($price);
            $newPriceAmount = $newPriceObject->unit_amount; // Precio en centavos
            
            // Determinar el comportamiento de prorrateo basado en si es upgrade o downgrade
            $prorationBehavior = 'none'; // Por defecto, sin prorrateo
            
            if ($newPriceAmount > $currentPriceAmount) {
                // Es un upgrade (plan más caro) → aplicar prorrateo inmediato
                $prorationBehavior = 'always_invoice';
                \Yii::info("Plan upgrade detected: {$currentPriceAmount} → {$newPriceAmount}. Applying immediate proration.", 'payment');
            } elseif ($newPriceAmount < $currentPriceAmount) {
                // Es un downgrade (plan más barato) → aplicar cambio al siguiente ciclo
                $prorationBehavior = 'none';
                \Yii::info("Plan downgrade detected: {$currentPriceAmount} → {$newPriceAmount}. Change will apply at next billing cycle.", 'payment');
            } else {
                // Mismo precio (cambio de intervalo de facturación) → sin prorrateo
                \Yii::info("Same price plan change: {$currentPriceAmount} → {$newPriceAmount}. No proration needed.", 'payment');
            }
            
            $stripe->subscriptions->update($subscription->id, [
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $price,
                    ],
                ],
                'proration_behavior' => $prorationBehavior,
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
        $business = RedisKeys::getBusiness();
        // Usar el plan del owner del business, no del usuario autenticado
        $plan = $business->user->plan;

        // Verificar directamente en Stripe si la suscripción está activa
        $userPlan = $business->user->userPlan;
        if ($userPlan && $userPlan->stripe_subscription_id) {
            try {
                $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
                $subscription = $stripe->subscriptions->retrieve($userPlan->stripe_subscription_id);
                $activeStatuses = ['active', 'trialing'];
                if (!in_array($subscription->status, $activeStatuses)) {
                    return true; // Suscripción inactiva en Stripe: bloquear creación
                }
            } catch (\Exception $e) {
                Yii::error('Error al verificar suscripción en Stripe: ' . $e->getMessage(), __METHOD__);
                // Si no se puede verificar, bloquear por seguridad
                return true;
            }
        }

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
                return $usersCount >= $plan->users - 1;
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

    public function getUserBusiness()
    {
        $userBusiness = (new Query())
            ->select("*")
            ->from("user_business")
            ->where(['user_id' => $this->id])
            ->one();

        return $userBusiness;
    }

}
