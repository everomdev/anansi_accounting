<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_business".
 *
 * @property int $id
 * @property int $user_id
 * @property int $business_id
 *
 * @property User $user
 * @property Business $business
 */
class UserBusiness extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_business';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'business_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'business_id' => 'Business ID',
        ];
    }

    /**
     * Gets query for the associated user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for the associated business.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::class, ['id' => 'business_id']);
    }
}
