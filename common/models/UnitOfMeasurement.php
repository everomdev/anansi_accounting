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
 * @property int $custom
 * @property string $type
 *
 * @property Business $business
 */
class UnitOfMeasurement extends \yii\db\ActiveRecord
{
    const TYPE_KITCHEN = 'kitchen';
    const TYPE_PURCHASE = 'purchase';
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
            [['business_id', 'custom'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 20],
            [['custom'], 'default', 'value' => 0],
            [['type'], 'default', 'value' => self::TYPE_KITCHEN],
            [['type'], 'in', 'range' => [self::TYPE_KITCHEN, self::TYPE_PURCHASE]],
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
            'custom' => Yii::t('app', 'Unidad personalizada'),
            'type' => Yii::t('app', 'Tipo de unidad'),
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
    
    /**
     * Obtiene unidades de cocina del negocio actual
     */
    public static function getKitchenUnits(): \yii\db\ActiveQuery
    {
        $business = RedisKeys::getBusinessData();
        return self::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['type' => self::TYPE_KITCHEN]);
    }
    
    /**
     * Obtiene unidades de compra del negocio actual
     */
    public static function getPurchaseUnits(): \yii\db\ActiveQuery
    {
        $business = RedisKeys::getBusinessData();
        return self::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['type' => self::TYPE_PURCHASE]);
    }
    
    /**
     * Obtiene el texto amigable del tipo
     */
    public function getTypeText()
    {
        return $this->type === self::TYPE_KITCHEN ? 'Cocina' : 'Compra';
    }
    
    /**
     * Obtiene las opciones de tipo para dropdowns
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_KITCHEN => 'Unidad de cocina',
            self::TYPE_PURCHASE => 'Unidad de compra',
        ];
    }
}
