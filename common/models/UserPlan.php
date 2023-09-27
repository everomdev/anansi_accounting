<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_plan".
 *
 * @property int $id
 * @property int|null $plan_id
 * @property int $user_id
 * @property string|null $stripe_subscription_id
 * @property string|null $stripe_subscription_status
 * @property string|null $stripe_customer_id
 *
 * @property Plan $plan
 */
class UserPlan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id', 'user_id'], 'integer'],
            [['user_id'], 'required'],
            [['stripe_subscription_id', 'stripe_subscription_status', 'stripe_customer_id'], 'string', 'max' => 255],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::className(), 'targetAttribute' => ['plan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'plan_id' => Yii::t('app', 'Plan ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'stripe_subscription_id' => Yii::t('app', 'Stripe Subscription ID'),
            'stripe_subscription_status' => Yii::t('app', 'Stripe Subscription Status'),
            'stripe_customer_id' => Yii::t('app', 'Stripe Customer ID'),
        ];
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::className(), ['id' => 'plan_id']);
    }
}
