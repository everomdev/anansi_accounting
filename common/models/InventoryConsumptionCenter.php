<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "inventory_consumption_center".
 *
 * @property int $id
 * @property int $inventory_id
 * @property int $consumption_center_id
 * @property float $quantity
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Inventory $inventory
 * @property ConsumptionCenter $consumptionCenter
 */
class InventoryConsumptionCenter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventory_consumption_center';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inventory_id', 'consumption_center_id', 'quantity'], 'required'],
            [['inventory_id', 'consumption_center_id'], 'integer'],
            [['quantity'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['inventory_id'], 'exist', 'skipOnError' => true, 'targetClass' => Inventory::className(), 'targetAttribute' => ['inventory_id' => 'id']],
            [['consumption_center_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConsumptionCenter::className(), 'targetAttribute' => ['consumption_center_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'inventory_id' => Yii::t('app', 'Inventory ID'),
            'consumption_center_id' => Yii::t('app', 'Consumption Center ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Inventory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventory()
    {
        return $this->hasOne(Inventory::className(), ['id' => 'inventory_id']);
    }

    /**
     * Gets query for [[ConsumptionCenter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConsumptionCenter()
    {
        return $this->hasOne(ConsumptionCenter::className(), ['id' => 'consumption_center_id']);
    }
}
