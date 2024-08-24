<?php

namespace common\models;

use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "ingredient_stock".
 *
 * @property int $id
 * @property string $ingredient
 * @property int $business_id
 * @property float|null $quantity
 * @property string $um
 * @property float|null $yield
 * @property float|null $portions_per_unit
 * @property string|null $portion_um
 * @property string|null $observations
 *
 * @property Business $business
 * @property Purchase[] $purchases
 * @property-read float $higherPrice
 * @property-read float $lastPrice
 * @property-read mixed $avgUnitPrice
 * @property-read float $lastUnitPrice
 * @property-read mixed $label
 * @property-read mixed $avgPrice
 * @property-read float $higherUnitPrice
 * @property StockPrice[] $stockPrices
 * @property float $final_quantity [float]
 * @property int $category_id [int]
 * @property-read mixed $category
 * @property string $key [varchar(255)]
 * @property-read mixed $valueInMoney
 */
class IngredientStock extends \yii\db\ActiveRecord
{
    public $_category;
    public $price;
    public $adjustedPrice;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ingredient_stock';
    }

    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        if (!empty(($category = $record->category))) {
            $record->_category = $category->name;
        }
    }

    public function rules()
    {
        return [
            [['ingredient', 'business_id', 'um', 'portions_per_unit', 'category_id', 'key'], 'required'],
            [['business_id'], 'integer'],
            [['quantity', 'yield', 'portions_per_unit', 'final_quantity', 'price', 'adjustedPrice'], 'number'],
            [['observations', '_category', 'key'], 'string'],
            [['ingredient', 'um', 'portion_um'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['ingredient', 'um', 'business_id'], 'unique', 'targetAttribute' => ['ingredient', 'um', 'business_id'], 'message' => Yii::t('app', "You already have registered this ingredient")],
            [['category_id'], 'integer'],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['ingredient', '_category', 'observations'], 'filter', 'filter' => 'trim']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ingredient' => Yii::t('app', 'Name'),
            'business_id' => Yii::t('app', 'Business ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'um' => Yii::t('app', 'Unidad de Compra'),
            'yield' => Yii::t('app', 'Yield'),
            'portions_per_unit' => Yii::t('app', 'Kitchen unit equivalence'),
            'portion_um' => Yii::t('app', 'Kitchen Unit'),
            'observations' => Yii::t('app', 'Observations'),
            'final_quantity' => Yii::t('app', 'Final Quantity'),
            '_category' => Yii::t('app', "Category"),
            'key' => Yii::t('app', "Key"),
            'category_id' => Yii::t('app', "Category"),
            'price' => Yii::t('app', "Initial Price"),
            'adjustedPrice' => Yii::t('app', "Adjusted Price"),
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields[] = 'lastPrice';
        $fields[] = 'higherPrice';
        $fields[] = 'avgPrice';

        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields[] = "business";
        $extraFields[] = "purchases";
        $extraFields['prices'] = "stockPrices";

        return $extraFields;
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

//        $this->saveCategory();

        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && !empty($this->price)) {
            $stockPrice = new StockPrice([
                'stock_id' => $this->id,
                'price' => $this->price,
                'date' => date('Y-m-d'),
                'unit_price' => $this->price,
                'adjusted_price' => $this->adjustedPrice,
            ]);
            $stockPrice->save(false);
        }
    }

    private function saveCategory()
    {
        $category = Category::findOne(['name' => $this->_category]);

        if (empty($category)) {
            $category = new Category([
                'name' => $this->_category,
                'business_id' => $this->business_id
            ]);
            $category->save();
        }

        $this->category_id = $category->id;

    }

    public function addPrice(Movement $movement)
    {
        $stockPrice = new StockPrice([
            'stock_id' => $this->id,
            'date' => date('Y-m-d', strtotime($movement->created_at)),
            'price' => $movement->amount,
            'unit_price' => $movement->unit_price,
            'unit_price_yield' => round(
                ((($movement->amount / $movement->quantity) / $this->yield) / $this->portions_per_unit),
                2
            )
        ]);

        $stockPrice->save();
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

    /**
     * Gets query for [[Purchases]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchases()
    {
        return $this->hasMany(Purchase::className(), ['stock_id' => 'id']);
    }

    /**
     * Gets query for [[StockPrices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockPrices()
    {
        return $this->hasMany(StockPrice::className(), ['stock_id' => 'id']);
    }

    public function getLastPrice()
    {
        $lastPrice = $this->getStockPrices()->orderBy(['date' => SORT_DESC])->one();
        return empty($lastPrice) ? 0.0 : $lastPrice->price;
    }

    public function getHigherPrice()
    {
        $higherPrice = $this->getStockPrices()->orderBy(['price' => SORT_DESC])->one();
        return empty($higherPrice) ? 0.0 : $higherPrice->price;
    }

    public function getAvgPrice()
    {
        return $this->getStockPrices()
            ->select(["avg(price) as avg_price"])
            ->asArray(true)
            ->one()['avg_price'];
    }

    public function getLastUnitPrice()
    {
        $lastPrice = $this->getStockPrices()->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])->one();
        return empty($lastPrice) ? 0.0 : round($lastPrice->unit_price, 2);
    }

    public function getHigherUnitPrice()
    {
        $higherPrice = $this->getStockPrices()->orderBy(['price' => SORT_DESC])->one();
        return empty($higherPrice) ? 0.0 : round($higherPrice->unit_price, 2);
    }

    public function getAvgUnitPrice()
    {
        return round($this->getStockPrices()
            ->select(["avg(unit_price) as avg_price"])
            ->asArray(true)
            ->one()['avg_price'],
            2
        );
    }

    public function getLabel()
    {
        return sprintf("%s - %s (%s)", $this->key, $this->ingredient, $this->um);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public static function keyGenerator($categoryId, $businessId)
    {
        $category = Category::findOne(['id' => $categoryId]);
        $latestKey = (new Query())
            ->select(['key'])
            ->from('ingredient_stock')
            ->where(['category_id' => $category->id, 'business_id' => $businessId])
            ->andWhere(['like', 'key', "{$category->key_prefix}"])
            ->orderBy(['key' => SORT_DESC])
            ->one();
        if (empty($latestKey)) {
            return sprintf("%s%s", $category->key_prefix, str_pad('1', '3', '0', STR_PAD_LEFT));
        }

        $ingredientKey = str_replace($category->key_prefix, '', $latestKey['key']);
        $ingredientKey = intval($ingredientKey);

        return sprintf("%s%s", $category->key_prefix, str_pad($ingredientKey + 1, '3', '0', STR_PAD_LEFT));

    }

    public function getValueInMoney()
    {
        $latestPrice = $this->lastUnitPrice;
        return $this->quantity * $latestPrice;
    }

    public function getName()
    {
        return $this->ingredient;
    }

    public function getProviders()
    {
        return $this->hasMany(Provider::class, ['name' => 'provider'])
            ->viaTable('movement', ['ingredient_id' => 'id'], function($query){
                $query->andWhere(['movement.type' => Movement::TYPE_INPUT]);
            });
    }

}
