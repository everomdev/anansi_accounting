<?php

namespace common\models;

use backend\helpers\RedisKeys;
use Yii;

/**
 * This is the model class for table "unit_of_measurement".
 *
 * @property int $id
 * @property string $name
 * @property int|null $business_id
 *
 * @property Business $business
 */
class UnitOfMeasurement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unit_of_measurement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['business_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'business_id' => Yii::t('app', 'Business ID'),
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

    public static function getOwn(): \yii\db\ActiveQuery
    {
        $business = RedisKeys::getBusinessData();
        return self::find()->where(['business_id' => $business['id']]);
    }
}
