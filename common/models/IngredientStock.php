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
 * @property string $key [varchar(255)]
 * @property float $final_quantity [float]
 * @property int $category_id [int]
 * @property string[] $providers
 *
 * @property Business $business
 * @property Purchase[] $purchases
 * @property StockPrice[] $stockPrices
 * @property-read float $higherPrice
 * @property-read float $lastPrice
 * @property-read mixed $avgUnitPrice
 * @property-read float $lastUnitPrice
 * @property-read mixed $label
 * @property-read mixed $avgPrice
 * @property-read float $higherUnitPrice
 * @property-read mixed $valueInMoney
 * @property-read mixed $category
 */
class IngredientStock extends \yii\db\ActiveRecord
{
    public $_category;
    public $_oldPrice;
    public $_oldAdjustedPrice;
    public $price;
    public $adjustedPrice;
    public $_key;
    public $providers = [];

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
        $lastStockPrice = $record->getStockPrices()->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])->one();
        $record->price = $lastStockPrice->price ?? 0.0;
        $record->adjustedPrice = $lastStockPrice->adjusted_price ?? 0.0;
        $record->_oldPrice = $record->price;
        $record->_oldAdjustedPrice = $record->adjustedPrice;
        if (!empty(($category = $record->category))) {
            $record->_category = $category->name;
        }
    }

    public function behaviors()
    {
        return [
            'numberFormatter' => [
                'class' => \common\behaviors\NumberFormatterBehavior::class,
                'priceFields' => ['price', 'adjustedPrice'],
                'numberFields' => ['quantity', 'yield', 'portions_per_unit', 'final_quantity'],
            ]
        ];
    }

    public function rules()
    {
        return [
            [['ingredient', 'business_id', 'um', 'portions_per_unit', 'category_id', 'key'], 'required'],
            [['business_id', 'category_id'], 'integer'],
            [['quantity', 'yield', 'portions_per_unit', 'final_quantity', 'price', 'adjustedPrice'], 'number'],
            [['observations', '_category', 'key'], 'string'],
            [['ingredient', 'um', 'portion_um'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['ingredient', 'um', 'business_id'], 'unique', 'targetAttribute' => ['ingredient', 'um', 'business_id'], 'message' => Yii::t('app', "You already have registered this ingredient ({value})")],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['ingredient', '_category', 'observations'], 'filter', 'filter' => 'trim'],
            [['key'], 'unique', 'targetAttribute' => ['key', 'business_id'], 'message' => Yii::t('app', "You already have registered this key ({value})")],
            [['providers'], 'each', 'rule' => ['integer']],
        ];
    }

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
            'price' => Yii::t('app', "Purchase Price"),
            'adjustedPrice' => Yii::t('app', "Adjusted Price"),
            'providers' => Yii::t('app', "Proveedores"),
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
        $this->_key = $this->key;

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
        if (!$insert) {
            if ($this->price != $this->_oldPrice || $this->adjustedPrice != $this->_oldAdjustedPrice) {
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

        // Save providers
        if (isset($this->providers) && is_array($this->providers)) {
            $this->unlinkAll('providers', true);
            foreach ($this->providers as $providerId) {
                $provider = Provider::findOne($providerId);
                if ($provider) {
                    $this->link('providers', $provider);
                }
            }
        }
        
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->providers = $this->getProviders()->select('id')->column();
    }

    public function getProviders()
    {
        return $this->hasMany(Provider::class, ['id' => 'provider_id'])
            ->viaTable('ingredient_provider', ['ingredient_id' => 'id']);
    }

    public function getBusiness()
    {
        return $this->hasOne(Business::className(), ['id' => 'business_id']);
    }

    public function getPurchases()
    {
        return $this->hasMany(Purchase::className(), ['stock_id' => 'id']);
    }

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
        /** @var StockPrice $lastPrice */
        $lastPrice = $this->getStockPrices()->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])->one();

        return empty($lastPrice) ? 0.0 : round($lastPrice->adjusted_price, 2);
    }

    public function getHigherUnitPrice()
    {
        $higherPrice = $this->getStockPrices()->orderBy(['price' => SORT_DESC])->one();
        return empty($higherPrice) ? 0.0 : round($higherPrice->adjusted_price, 2);
    }

    public function getAvgUnitPrice()
    {
        return round($this->getStockPrices()
            ->select(["avg(adjusted_price) as avg_price"])
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

    public function getRecipes()
    {
        return $this->hasMany(IngredientStandardRecipe::class, ['ingredient_id' => 'id']);
    }

    public function getSubRecipes()
    {
        return $this->hasMany(IngredientStandardRecipe::class, ['ingredient_id' => 'id'])
            ->innerJoinWith('standardRecipe')
            ->andWhere(['standard_recipe.type' => 'sub']);
    }
    public function duplicate()
{
    $newInsumo = new IngredientStock();
    $newInsumo->attributes = $this->attributes;
    $newInsumo->ingredient = $this->ingredient;

    $splitText = explode(" ", $newInsumo->ingredient);

    if (is_numeric(end($splitText))) {
        $iteration = intval(end($splitText)) + 1;
        array_pop($splitText);
        $newInsumo->ingredient = implode(" ", $splitText);
    } else {
        $iteration = 1;
        $newInsumo->ingredient .= " (Copia)";
    }

    while (IngredientStock::find()->where(['ingredient' => $newInsumo->ingredient . " $iteration"])->exists()) {
        $iteration++;
    }

    $newInsumo->ingredient .= " $iteration";
    // Generate a random 8-digit key
    $newInsumo->key = (string)str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    // Primero guardamos el nuevo insumo para obtener su ID
    if (!$newInsumo->save()) {
        die(var_dump($newInsumo->getErrors()));
        throw new \Exception('No se pudo guardar el nuevo insumo');
    }

    // Copy providers
    $providers = (new Query())
        ->select(['provider_id'])
        ->from("ingredient_provider")
        ->where(['ingredient_id' => $this->id])
        ->all();

    foreach ($providers as $provider) {
        Yii::$app->db->createCommand()
            ->insert('ingredient_provider', [
                'provider_id' => $provider['provider_id'],
                'ingredient_id' => $newInsumo->id
            ])
            ->execute();
    }

    // Copy stock prices
    $stockPrices = (new Query())
        ->select(['price', 'date', 'unit_price', 'adjusted_price'])
        ->from("stock_price")
        ->where(['stock_id' => $this->id])
        ->all();

    foreach ($stockPrices as $stockPrice) {
        $stockPrice['stock_id'] = $newInsumo->id;
        Yii::$app->db->createCommand()
            ->insert('stock_price', $stockPrice)
            ->execute();
    }    return $newInsumo;
}

/**
 * Agrega un precio al historial de precios del ingrediente
 * @param Movement|Purchase $source El movimiento o compra que genera el precio
 */
public function addPrice($source)
{
    $stockPrice = new StockPrice();
    $stockPrice->stock_id = $this->id;
    
    if ($source instanceof Movement) {
        // Para movimientos, usar los datos del movimiento
        $stockPrice->price = $source->amount;
        $stockPrice->unit_price = $source->unit_price;
        $stockPrice->date = $source->date ?? date('Y-m-d');
        
        // Calcular unit_price_yield si existe un factor de rendimiento
        if ($this->yield && $this->yield > 0) {
            $stockPrice->unit_price_yield = $source->unit_price / ($this->yield / 100);
        }
        
        // Calcular adjusted_price si existe unit_price
        if ($source->unit_price && $this->yield && $this->yield > 0) {
            $stockPrice->adjusted_price = $source->unit_price / ($this->yield / 100);
        }
        
    } elseif ($source instanceof Purchase) {
        // Para compras, usar los datos de la compra
        $stockPrice->price = $source->price;
        $stockPrice->unit_price = $source->unit_price ?? ($source->price / $source->quantity);
        $stockPrice->date = $source->date;
        
        // Calcular unit_price_yield si existe un factor de rendimiento
        if ($this->yield && $this->yield > 0) {
            $stockPrice->unit_price_yield = $stockPrice->unit_price / ($this->yield / 100);
        }
        
        // Calcular adjusted_price
        if ($stockPrice->unit_price && $this->yield && $this->yield > 0) {
            $stockPrice->adjusted_price = $stockPrice->unit_price / ($this->yield / 100);
        }
    }
    
    // Guardar el precio en el historial
    if (!$stockPrice->save()) {
        \Yii::error('Error al guardar el precio del stock: ' . json_encode($stockPrice->errors), __METHOD__);
    }
}
}