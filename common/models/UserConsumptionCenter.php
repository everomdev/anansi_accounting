<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_consumption_center".
 *
 * @property int $id
 * @property int $user_id
 * @property int $consumption_center_id
 * @property boolean $is_default
 * @property int $created_at
 *
 * @property User $user
 * @property ConsumptionCenter $consumptionCenter
 */
class UserConsumptionCenter extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_consumption_center';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'consumption_center_id', 'created_at'], 'required'],
            [['user_id', 'consumption_center_id', 'created_at'], 'integer'],
            [['is_default'], 'boolean'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['consumption_center_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConsumptionCenter::class, 'targetAttribute' => ['consumption_center_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'Usuario'),
            'consumption_center_id' => Yii::t('app', 'Centro de Consumo'),
            'is_default' => Yii::t('app', 'Por Defecto'),
            'created_at' => Yii::t('app', 'Creado'),
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[ConsumptionCenter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConsumptionCenter()
    {
        return $this->hasOne(ConsumptionCenter::class, ['id' => 'consumption_center_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = time();
            }
            return true;
        }
        return false;
    }
}
