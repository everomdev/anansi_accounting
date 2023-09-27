<?php

namespace common\models;

use Stripe\Price;
use Stripe\Product;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

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
 */
class Plan extends \yii\db\ActiveRecord
{
    public $permissions = [];

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
            [['users', 'recetas', 'subrecetas', 'convoy', 'combos'], 'integer'],
            [['name', 'stripe_product_id', 'description'], 'string', 'max' => 255],
            [['permissions'], 'safe']
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
                'description' => $this->description,
                'active' => true,
                'metadata' => $this->getAttributes(),
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
        return sprintf("%s - %s USD/mes o %s USD/año", $this->name, $this->monthly_price, $this->yearly_price);
    }

    public function generateCheckoutSession(User $user, $priceId)
    {
        try {
            /** @var UserPlan $userPlan */
            $userPlan = $user->userPlan;
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
                'metadata' => $this->getAttributes(),
                'subscription_data' => [
                    'metadata' => [
                        "plan" => json_encode($this->getAttributes()),
                        "user" => json_encode($user->getAttributes(['id', 'email']))
                    ]
                ],
                'automatic_tax' => [
                    'enabled' => true
                ],
                'billing_address_collection' => 'required',
                'tax_id_collection' => [
                    'enabled' => true
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
    }

    private function propagatePermissions()
    {
        $users = User::find()
            ->innerJoin('user_plan up', 'up.user_id=user.id')
            ->where(['up.plan_id' => $this->id])
            ->all();

        foreach ($users as $user){
            $user->applyRoles();
        }
    }
}
