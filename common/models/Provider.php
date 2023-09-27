<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "provider".
 *
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $second_phone
 * @property string|null $email
 * @property int $business_id
 * @property string|null $payment_method
 * @property string|null $account
 * @property string|null $credit_days
 * @property string|null $rfc
 * @property string|null $business_name
 * @property string|null $advantages
 * @property string|null $disadvantages
 * @property string|null $observations
 *
 * @property Business $business
 */
class Provider extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'provider';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'business_id'], 'required'],
            [['business_id'], 'integer'],
            [['name', 'address', 'phone', 'second_phone', 'email', 'payment_method', 'account', 'credit_days', 'rfc', 'business_name', 'advantages', 'disadvantages', 'observations'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
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
            'address' => Yii::t('app', 'Address'),
            'phone' => Yii::t('app', 'Phone'),
            'second_phone' => Yii::t('app', 'Second Phone'),
            'email' => Yii::t('app', 'Email'),
            'business_id' => Yii::t('app', 'Business ID'),
            'payment_method' => Yii::t('app', 'Payment Method'),
            'account' => Yii::t('app', 'Account'),
            'credit_days' => Yii::t('app', 'Credit Days'),
            'rfc' => Yii::t('app', 'Rfc'),
            'business_name' => Yii::t('app', 'Business Name'),
            'advantages' => Yii::t('app', 'Advantages'),
            'disadvantages' => Yii::t('app', 'Disadvantages'),
            'observations' => Yii::t('app', 'Observations'),
        ];
    }

    /**
     * Gets query for [[Business]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }
}
