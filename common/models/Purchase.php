<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purchase".
 *
 * @property int $id
 * @property string $date
 * @property float|null $price
 * @property string|null $provider
 * @property float|null $quantity
 * @property int $stock_id
 * @property string|null $um
 * @property string|null $final_um
 *
 * @property IngredientStock $stock
 * @property float $unit_price [float]
 */
class Purchase extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purchase';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'stock_id', 'date', 'price'], 'required'],
            [['date'], 'safe'],
            [['price', 'quantity', 'unit_price'], 'number'],
            [['stock_id'], 'integer'],
            [['provider', 'um', 'final_um'], 'string', 'max' => 255],
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
            'date' => Yii::t('app', 'Date'),
            'price' => Yii::t('app', 'Price'),
            'provider' => Yii::t('app', 'Provider'),
            'quantity' => Yii::t('app', 'Quantity'),
            'stock_id' => Yii::t('app', 'Ingredient'),
            'um' => Yii::t('app', 'Um'),
            'final_um' => Yii::t('app', 'Final Um'),
            'unit_price' => Yii::t('app', 'Unit Price'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (empty($this->unit_price)) {
            $this->unit_price = round($this->price / $this->quantity, 2);
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $ingredient = $this->stock;
        if ($insert) {
            // actualizar el ingrediente vinculado
            $ingredient->quantity += $this->quantity;
            $ingredient->save();
            $ingredient->addPrice($this);
        } else {
            /// si se modifica la cantidad, hay que aumentar
            /// o disminuir la cantidad del ingrediente vinculado
            if (!empty($changedAttributes['quantity']) && $changedAttributes['quantity'] != $this->quantity) {
                $diff = abs($changedAttributes['quantity'] - $this->quantity);
                if ($changedAttributes['quantity'] > $this->quantity) { // disminuye la cantidad
                    $ingredient->quantity -= $diff;
                } else {
                    $ingredient->quantity += $diff;
                }
                $ingredient->save();
            }
            if (!empty($changedAttributes['price']) && $changedAttributes['price'] != $this->quantity) {
                $price = $ingredient->getStockPrices()->andWhere(['date' => $this->date])->one();
                if ($price) {
                    $price->delete();
                }

                $ingredient->addPrice($this);
            }
        }
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
