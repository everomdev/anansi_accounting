<?php

namespace common\models;

use Stripe\Price;
use Stripe\Product;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use common\behaviors\NumberFormatterBehavior;

/**
 * This is the model class for table "plan".
 *
 * @property int $id
 * @property string $name
 * @property float $monthly_price
 * @property float $yearly_price
 * @property string|null $stripe_product_id
 * @property int|null $users
 *
 * @property UserPlan[] $userPlans
 * @property string $description [varchar(255)]
 * @property int $subrecetas [int]
 * @property int $recetas [int]
 * @property int $convoy [int]
 * @property int $combos [int]
 * @property int $trial_days [int]
 */
class Plan extends \yii\db\ActiveRecord
{
    public $permissions = [];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'numberFormatter' => [
                'class' => NumberFormatterBehavior::class,
                'priceFields' => ['monthly_price', 'yearly_price'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan';
    }

    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $rows = (new Query())
            ->from('plan_permission')
            ->select(['item_name'])
            ->where(['plan_id' => $record->id])
            ->all();

        $record->permissions = ArrayHelper::getColumn($rows, 'item_name');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'monthly_price', 'yearly_price'], 'required'],
            [['monthly_price', 'yearly_price'], 'number'],
            [['users', 'recetas', 'subrecetas', 'convoy', 'combos', 'trial_days'], 'integer'],
            [['name', 'stripe_product_id'], 'string', 'max' => 255],
            [['permissions'], 'safe'],
            [['description', 'intro'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'monthly_price' => Yii::t('app', 'Monthly Price'),
            'yearly_price' => Yii::t('app', 'Yearly Price'),
            'stripe_product_id' => Yii::t('app', 'Stripe Product ID'),
            'users' => Yii::t('app', 'Users'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->registerProductInStripe();
        }

        if (!$insert) {
            $updateMonthly = isset($changedAttributes['monthly_price']) && $changedAttributes['monthly_price'] != $this->monthly_price;
            $updateYearly = isset($changedAttributes['yearly_price']) && $changedAttributes['yearly_price'] != $this->yearly_price;
            if ($updateMonthly or $updateYearly) {
                $this->updatePrices($updateYearly, $updateMonthly);
            }
        }

        // Save permissions
        Yii::$app->db->createCommand()
            ->delete('plan_permission', ['plan_id' => $this->id])
            ->execute();

        Yii::$app->db->createCommand()
            ->batchInsert(
                'plan_permission',
                ['plan_id', 'item_name'],
                array_map(function ($item) {
                    return [$this->id, $item];
                }, $this->permissions)
            )->execute();

        $this->propagatePermissions();
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $this->deleteProduct();
    }

    public function deleteProduct()
    {
        try {
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);

            // disable prices
            $prices = $stripe->prices->search([
                'query' => "product: '{$this->stripe_product_id}' AND active: 'true'"
            ]);
            foreach ($prices->data as $price) {
                $stripe->prices->update($price->id, [
                    'active' => false
                ]);
            }

            // delete product
            $stripe->products->delete($this->stripe_product_id);
        } catch (\Exception $exception) {

        }
    }

    /**
     * Gets query for [[UserPlans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserPlans()
    {
        return $this->hasMany(UserPlan::className(), ['plan_id' => 'id']);
    }

    public function registerProductInStripe()
    {
        try {
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            $product = $stripe->products->create([
                'name' => $this->name,
                'description' => $this->name,
                'active' => true,
                'metadata' => $this->getAttributes(null, ['intro', 'description']),
            ]);
            $stripe->prices->create([
                'product' => $product->id,
                'currency' => 'usd',
                'unit_amount' => $this->monthly_price * 100,
                'active' => true,
                'nickname' => 'Mensual',
                'recurring' => [
                    'interval' => 'month',
                    'interval_count' => 1
                ],
                'tax_behavior' => 'exclusive',
            ]);
            $stripe->prices->create([
                'product' => $product->id,
                'currency' => 'usd',
                'unit_amount' => $this->yearly_price * 100,
                'active' => true,
                'nickname' => 'Anual',
                'recurring' => [
                    'interval' => 'year',
                    'interval_count' => 1
                ],
                'tax_behavior' => 'exclusive',
            ]);

            Yii::$app->db->createCommand()
                ->update(
                    'plan',
                    ['stripe_product_id' => $product->id],
                    ['id' => $this->id]
                )
                ->execute();
        } catch (\Exception $exception) {
            Yii::error($exception->getMessage());
            Yii::error(Yaml::dump($exception->getTrace()));
        }
    }

    public function updatePrices($updateYearly = false, $updateMonthly = false)
    {
        if (!$updateYearly && !$updateMonthly) {
            return;
        }
        try {
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            $prices = $stripe->prices->search([
                'query' => "product: '{$this->stripe_product_id}' AND active: 'true'"
            ]);

            foreach ($prices->data as $price) {
                /** @var $price Price */
                if ($price->recurring->interval == 'year' && $updateYearly) {
                    $stripe->prices->update($price->id, [
                        'active' => false
                    ]);
                    $stripe->prices->create([
                        'product' => $this->stripe_product_id,
                        'currency' => 'usd',
                        'unit_amount' => $this->yearly_price * 100,
                        'active' => true,
                        'nickname' => 'Anual',
                        'recurring' => [
                            'interval' => 'year',
                            'interval_count' => 1
                        ],
                        'tax_behavior' => 'exclusive',
                    ]);
                } elseif ($price->recurring->interval == 'month' && $updateMonthly) {
                    $stripe->prices->update($price->id, [
                        'active' => false
                    ]);
                    $stripe->prices->create([
                        'product' => $this->stripe_product_id,
                        'currency' => 'usd',
                        'unit_amount' => $this->monthly_price * 100,
                        'active' => true,
                        'nickname' => 'Mensual',
                        'recurring' => [
                            'interval' => 'month',
                            'interval_count' => 1
                        ],
                        'tax_behavior' => 'exclusive',
                    ]);
                }
            }


        } catch (\Exception $exception) {

        }
    }

    /**
     * @return array|Price[]|\Stripe\StripeObject[]|void
     */
    public function getPrices()
    {
        try {
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            $prices = $stripe->prices->search([
                'query' => "product: '{$this->stripe_product_id}' AND active: 'true'"
            ]);
            return $prices->data;
        } catch (\Exception $e) {

        }
    }

    public function getLabel()
    {
        return sprintf("%s - $%s USD/mes o $%s USD/año - %s dias de prueba", $this->name, $this->monthly_price, $this->yearly_price, $this->trial_days);
    }

    public function generateCheckoutSession(User $user, $priceId, $priceAmount, $coupon_id, $nickname = null)
    {
        try {
            /** @var UserPlan $userPlan */
            $userPlan = $user->userPlan;
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);

            // Definir los datos de la sesión de checkout
            $sessionData = [
                'success_url' => Url::toRoute(['payment/stripe-checkout-success', 'plan' => $this->id, 'user' => $user->id], true) . "&session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => Url::toRoute(['payment/stripe-checkout-cancel', 'plan' => $this->id, 'user' => $user->id], true),
                'customer' => $userPlan->stripe_customer_id,
                'currency' => 'usd',
                'locale' => 'es',
                'line_items' => [],
                'mode' => 'subscription',
                'metadata' => $this->getAttributes(null, ['intro', 'description']),
                'subscription_data' => [
                    'metadata' => [
                        "plan" => json_encode($this->getAttributes(null, ['intro', 'description'])),
                        "user" => json_encode($user->getAttributes(['id', 'email']))
                    ],
                    'trial_period_days' => $this->trial_days // Días de prueba gratuita
                ],
                'automatic_tax' => [
                    'enabled' => false // TODO: habilitar esto cuando se complete la cuenta en Stripe
                ],
                'billing_address_collection' => 'required', // Requerir dirección de facturación
                'tax_id_collection' => [
                    'enabled' => false // TODO: habilitar esto cuando se complete la cuenta en Stripe
                ],
                'locale' => 'es', // Idioma de la página de checkout
                'customer_update' => [
                    'name' => 'auto', // Actualizar automáticamente el nombre del cliente
                    'address' => 'auto', // Actualizar automáticamente la dirección del cliente
                ],
            ];
            // Resolver si hay un cupón de DB válido
            $stripeCouponId = null;
            if ($coupon_id !== null && $coupon_id !== 'null') {
                $coupon = Coupon::findOne(['id' => $coupon_id]);
                if ($coupon && $coupon->stripe_coupon_id) {
                    $stripeCouponId = $coupon->stripe_coupon_id;
                }
            }

            if ($stripeCouponId) {
                // Hay un cupón de DB: usar el precio original de Stripe + descuento vía cupón
                $sessionData['line_items'][] = [
                    'price' => $priceId,
                    'quantity' => 1
                ];
                $sessionData['discounts'] = [
                    ['coupon' => $stripeCouponId]
                ];
            } elseif ($priceAmount !== null) {
                // No hay cupón pero hay un monto custom (ej. promo 15%): recuperar precio de Stripe para comparar
                $stripePrice = $stripe->prices->retrieve($priceId);
                $stripeUnitAmount = $stripePrice->unit_amount; // en centavos
                $requestedUnitAmount = (int) round($priceAmount * 100);

                if ($requestedUnitAmount !== $stripeUnitAmount) {
                    // El monto es diferente (hay descuento): usar price_data con el monto descontado
                    $sessionData['line_items'][] = [
                        'price_data' => [
                            'currency'    => $stripePrice->currency,
                            'unit_amount' => $requestedUnitAmount,
                            'product'     => $stripePrice->product,
                            'recurring'   => [
                                'interval'       => $stripePrice->recurring->interval,
                                'interval_count' => $stripePrice->recurring->interval_count,
                            ],
                        ],
                        'quantity' => 1
                    ];
                } else {
                    // Mismo precio: usar el price ID original
                    $sessionData['line_items'][] = [
                        'price'    => $priceId,
                        'quantity' => 1
                    ];
                }
            } else {
                // Sin descuento: usar el precio original de Stripe
                $sessionData['line_items'][] = [
                    'price' => $priceId,
                    'quantity' => 1
                ];
            }
            //die(var_dump('session'));

            // Crear la sesión de checkout en Stripe
            $session = $stripe->checkout->sessions->create($sessionData);
            //die(var_dump($session));

            return $session;
        } catch (\Exception $e) {
            // Registrar el error en los logs
            Yii::error('generateCheckoutSession ERROR: ' . $e->getMessage(), __METHOD__);
            Yii::error(Yaml::dump([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]));
            // Lanzar la excepción para que el controlador pueda mostrarla
            throw $e;
        }

        return null;
    }
    /*public function generateCheckoutSession(User $user, $priceId, $priceAmount)
    {
        try {*/
            /** @var UserPlan $userPlan */
            /*$userPlan = $user->userPlan;
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            $sessionData = [
                'success_url' => Url::toRoute(['payment/stripe-checkout-success', 'plan' => $this->id, 'user' => $user->id], true) . "&session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => Url::toRoute(['payment/stripe-checkout-cancel', 'plan' => $this->id, 'user' => $user->id], true),
                'customer' => $userPlan->stripe_customer_id,
                'currency' => 'usd',
                'line_items' => [
                    [
                        'price' => $priceId,
                        'quantity' => 1
                    ]
                ],
                'mode' => 'subscription',
                'metadata' => $this->getAttributes(null, ['intro', 'description']),
                'subscription_data' => [
                    'metadata' => [
                        "plan" => json_encode($this->getAttributes(null, ['intro', 'description'])),
                        "user" => json_encode($user->getAttributes(['id', 'email']))
                    ],
                    'trial_period_days' => $this->trial_days
                ],
                'automatic_tax' => [
                    'enabled' => false // TODO: habilitar esto cuando se complete la cuenta en Stripe
                ],
                'billing_address_collection' => 'required',
                'tax_id_collection' => [
                    'enabled' => false // TODO: habilitar esto cuando se complete la cuenta en Stripe
                ],
                'locale' => 'en',
                'customer_update' => [
                    'name' => 'auto',
                    'address' => 'auto',
                ]
            ];

            $session = $stripe->checkout->sessions->create($sessionData);

            return $session;
        } catch (\Exception $e) {
            Yii::error(Yaml::dump([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]));
        }

        return null;
    }*/
    public function createManualSubscription(User $user, $coupon_id)
    {
        try {
            $coupon = Coupon::findOne(['id' => $coupon_id]);
            
            if ($coupon) {
                // Disminuir el quantity en 1
                $newQuantity = max(0, $coupon->quantity - 1); // Asegúrate que no sea negativo
                // Actualiza el valor en la base de datos
                $updated = Yii::$app->db->createCommand()
                    ->update('coupon', ['quantity' => $newQuantity], ['id' => $coupon_id])
                    ->execute();
            } else {
                Yii::error('Cupón no encontrado en la base de datos con ID ' . $coupon_id);
                return [
                    'success' => false,
                    'error' => 'Cupón no encontrado en la base de datos'
                ];
            }
    
            // Obtener la fecha de expiración del cupón
            $expirationDate = $coupon->expiration_date;
    
            $userPlan = $user->userPlan;
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            $freePriceId = $this->getFreePriceId($stripe);
    
            // Crear la suscripción gratuita con fecha de expiración
            $subscription = $stripe->subscriptions->create([
                'customer' => $userPlan->stripe_customer_id,
                'items' => [['price' => $freePriceId]],
                'metadata' => [
                    'plan_id' => $this->id,
                    'user_id' => $user->id
                ],
                'cancel_at' => $expirationDate, // Establecer la fecha de expiración de la suscripción
            ]);

            
            // Guardar en base de datos
            $userPlan->stripe_subscription_id = $subscription->id;
            $userPlan->stripe_subscription_status = $subscription->status;
            $userPlan->save();
            return [
                'success' => true,
                'stripe_subscription_id' => $subscription->id,
                'stripe_subscription_status' => $subscription->status,
                'message' => 'Suscripción gratuita creada correctamente con fecha de expiración.'
            ];
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al crear suscripción gratuita'
            ];
        }
    }
    
    private function getFreePriceId(\Stripe\StripeClient $stripe)
{
    try {
        // Crear el producto
        $product = $stripe->products->create([
            'name' => 'Plan Gratuito',
            'description' => 'Suscripción gratuita con 100% de descuento',
        ]);
    
        // Crear el precio asociado al producto
        $freePrice = $stripe->prices->create([
            'currency' => 'usd',
            'unit_amount' => 0,
            'product' => $product->id,  // Asociamos el precio al producto creado
            'recurring' => ['interval' => 'month']
        ]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Capturar el error y mostrar el mensaje
        die('Error al crear el precio: ' . $e->getMessage());
    }

    return $freePrice->id;
}

    private function propagatePermissions()
    {
        $users = User::find()
            ->innerJoin('user_plan up', 'up.user_id=user.id')
            ->where(['up.plan_id' => $this->id])
            ->all();

        foreach ($users as $user) {
            $user->applyRoles();
        }
    }
}
