<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "stock_price".
 *
 * @property int $id
 * @property int $stock_id
 * @property float $price
 * @property string|null $date
 * @property float|null $unit_price
 * @property float|null $unit_price_yield
 *
 * @property IngredientStock $stock
 */
class StockPrice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_price';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['stock_id', 'price'], 'required'],
            [['stock_id'], 'integer'],
            [['price', 'unit_price', 'unit_price_yield'], 'number'],
            [['date'], 'safe'],
            [['stock_id'], 'exist', 'skipOnError' => true, 'targetClass' => IngredientStock::className(), 'targetAttribute' => ['stock_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stock_id' => 'Stock ID',
            'price' => 'Price',
            'date' => 'Date',
            'unit_price' => 'Unit Price',
            'unit_price_yield' => 'Unit Price Yield',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        parent::afterDelete();
    }

    /**
     * Gets query for [[Stock]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStock()
    {
        return $this->hasOne(IngredientStock::className(), ['id' => 'stock_id']);
    }


}
