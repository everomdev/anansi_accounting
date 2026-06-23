<?php

namespace common\models;

use phpDocumentor\Reflection\Types\This;
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
 * @property float|null $min_stock [float]
 * @property float|null $max_stock [float]
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

    // Para resumen de pendientes
    public $pending_fields = [];
    public $pending_updated_at;

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
                'numberFields' => ['quantity', 'yield', 'portions_per_unit', 'final_quantity', 'min_stock', 'max_stock'],
            ]
        ];
    }    public function rules()
    {
        return [
            [['ingredient', 'business_id', 'um', 'portions_per_unit', 'category_id', 'key', 'portions_per_unit', 'portion_um', 'yield'], 'required'],
            [['business_id', 'category_id'], 'integer'],
            [['quantity', 'yield', 'portions_per_unit', 'final_quantity', 'min_stock', 'max_stock'], 'number'],
            [['price', 'adjustedPrice'], 'safe'],
            [['price', 'adjustedPrice'], 'validatePrice'],
            [['observations', '_category', 'key', 'brand', 'presentation'], 'string'],
            [['ingredient', 'um', 'portion_um', 'brand', 'presentation'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['ingredient', '_category', 'observations', 'brand', 'presentation'], 'filter', 'filter' => 'trim'],
            [['key'], 'validateUniqueKey'],
            [['providers'], 'each', 'rule' => ['integer']],
            [['min_stock', 'max_stock'], 'number', 'min' => 0],
            [['max_stock'], 'validateMaxStock'],
            [
        ['ingredient', 'business_id'],
        'unique',
        'targetAttribute' => ['ingredient', 'business_id'],
        'message' => Yii::t('app', "This name is already taken"),
        'when' => function($model) {
            // Solo validar si ingredient tiene valor
            return !empty($model->ingredient);
        },
        'filter' => function ($query) {
    \Yii::warning([
        'isNewRecord' => $this->isNewRecord,
        'id' => $this->id,
        'ingredient' => $this->ingredient,
    ], 'UniqueFilter');
    if (!$this->isNewRecord && !empty($this->id)) {
        $query->andWhere(['not', ['id' => $this->id]]);
    }
}
    ],
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
            'portions_per_unit' => Yii::t('app', 'Equivalencia a unidades de uso'),
            'portion_um' => Yii::t('app', 'Unidad de Uso'),
            'observations' => Yii::t('app', 'Observations'),
            'final_quantity' => Yii::t('app', 'Final Quantity'),
            '_category' => Yii::t('app', "Category"),
            'key' => Yii::t('app', "Key"),
            'category_id' => Yii::t('app', "Category"),
            'price' => Yii::t('app', "Purchase Price"),
            'adjustedPrice' => Yii::t('app', "Adjusted Price"),
            'providers' => Yii::t('app', "Proveedores"),
            'brand' => Yii::t('app', "Marca"),
            'presentation' => Yii::t('app', "Presentación"),
            'min_stock' => Yii::t('app', "Stock Mínimo"),
            'max_stock' => Yii::t('app', "Stock Máximo"),
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

    /**
     * Validación personalizada para campos de precio que pueden venir formateados
     */
    public function validatePrice($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return; // Permitir valores vacíos si no son requeridos
        }

        // Si ya es un número, validar directamente
        if (is_numeric($this->$attribute)) {
            if ((float)$this->$attribute < 0) {
                $this->addError($attribute, Yii::t('app', '{attribute} debe ser un número positivo.', ['attribute' => $this->getAttributeLabel($attribute)]));
            }
            return;
        }

        // Si es string, intentar parsearlo con NumberFormatter
        if (is_string($this->$attribute)) {
            try {
                $parsedValue = \common\helpers\NumberFormatter::parseNumber($this->$attribute);
                
                if (!is_numeric($parsedValue)) {
                    $this->addError($attribute, Yii::t('app', '{attribute} debe ser un número válido.', ['attribute' => $this->getAttributeLabel($attribute)]));
                    return;
                }
                
                if ((float)$parsedValue < 0) {
                    $this->addError($attribute, Yii::t('app', '{attribute} debe ser un número positivo.', ['attribute' => $this->getAttributeLabel($attribute)]));
                    return;
                }
                
                // Convertir el valor parseado al atributo
                $this->$attribute = (float)$parsedValue;
                
            } catch (\Exception $e) {
                $this->addError($attribute, Yii::t('app', '{attribute} debe ser un número válido.', ['attribute' => $this->getAttributeLabel($attribute)]));
            }
        } else {
            $this->addError($attribute, Yii::t('app', '{attribute} debe ser un número.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
    }

    /**
     * Validación personalizada para verificar que el stock máximo sea mayor que el mínimo
     */
    public function validateMaxStock($attribute, $params)
    {
        if (!empty($this->min_stock) && !empty($this->max_stock)) {
            if ((float)$this->max_stock <= (float)$this->min_stock) {
                $this->addError($attribute, Yii::t('app', 'El stock máximo debe ser mayor que el stock mínimo.'));
            }
        }
    }

    /**
     * Validación personalizada para verificar unicidad de clave
     */
    public function validateUniqueKey($attribute, $params)
    {
        $query = self::find()
            ->where([
                'key' => $this->key,
                'business_id' => $this->business_id
            ]);

        // Si estamos editando un registro existente, excluirlo de la búsqueda
        if (!$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        if ($query->exists()) {
            $this->addError('key', Yii::t('app', "You already have registered this key ({value})", ['value' => $this->key]));
        }
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

        // Guardar campos pendientes si vienen en la request
        $pendingFields = \Yii::$app->request->post('pending_fields', []);
        if (is_string($pendingFields)) {
            $pendingFields = json_decode($pendingFields, true);
        }
        if (is_array($pendingFields)) {
            $this->savePendingFields($pendingFields);
        }

        if ($insert && !empty($this->price) && $this->price > 0) {
            $stockPrice = new StockPrice([
                'stock_id' => $this->id,
                'price' => $this->price,
                'date' => date('Y-m-d'),
                'unit_price' => $this->price,
                'adjusted_price' => $this->adjustedPrice,
                //'adjusted_price' => $this->price,
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

        /**
     * Relación para obtener los movimientos del insumo
     */
    public function getMovements()
    {
        return $this->hasMany(Movement::class, ['ingredient_id' => 'id']);
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
        $higherPrice = $this->getStockPrices()->orderBy(['adjusted_price' => SORT_DESC, 'id' => SORT_DESC])->one();
        return empty($higherPrice) ? 0.0 : $higherPrice->adjusted_price;
    }

    public function getAvgPrice()
    {
        return $this->getStockPrices()
            ->select(["avg(adjusted_price) as avg_price"])
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
        $higherPrice = $this->getStockPrices()->orderBy(['price' => SORT_DESC, 'id' => SORT_DESC])->one();
        return empty($higherPrice) ? 0.0 : round($higherPrice->price, 2);
    }

    public function getAvgUnitPrice()
    {
        return round($this->getStockPrices()
            ->select(["avg(price) as avg_price"])
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
        //return $this->quantity * $latestPrice / $this->portions_per_unit;
        return $this->quantity * $latestPrice;
    }

    public function getName()
    {
        return $this->ingredient;
    }

    /**
     * Verifica si el stock actual está por debajo del mínimo
     * @return bool
     */
    public function isLowStock()
    {
        if (empty($this->min_stock)) {
            return false;
        }
        return (float)$this->quantity <= (float)$this->min_stock;
    }

    /**
     * Verifica si el stock actual está por encima del máximo
     * @return bool
     */
    public function isOverStock()
    {
        if (empty($this->max_stock)) {
            return false;
        }
        return (float)$this->quantity >= (float)$this->max_stock;
    }

    /**
     * Obtiene el estado del stock
     * @return string 'low', 'high', 'normal'
     */
    public function getStockStatus()
    {
        if ($this->isLowStock()) {
            return 'low';
        }
        if ($this->isOverStock()) {
            return 'high';
        }
        return 'normal';
    }

    /**
     * Obtiene el porcentaje del stock actual respecto al rango min-max
     * @return float|null
     */
    public function getStockPercentage()
    {
        if (empty($this->min_stock) || empty($this->max_stock)) {
            return null;
        }
        
        $range = (float)$this->max_stock - (float)$this->min_stock;
        if ($range <= 0) {
            return null;
        }
        
        $currentAboveMin = (float)$this->quantity - (float)$this->min_stock;
        return min(100, max(0, ($currentAboveMin / $range) * 100));
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
        $stockPrice->price = $source->unit_price; // precio por unidad, no el total
        $stockPrice->unit_price = $source->unit_price;
        $stockPrice->date = $source->date ?? date('Y-m-d');
        
        // Calcular unit_price_yield si existe un factor de rendimiento
        if ($this->yield && $this->yield > 0) {
            $stockPrice->unit_price_yield = $source->unit_price / ($this->yield / 100);
        }
        
        // Calcular adjusted_price: precio por porción de uso = unit_price / porciones_por_unidad
        // Ej: $300/kg ÷ 12 porciones/kg = $25 por porción
        if ($source->unit_price && $this->portions_per_unit > 0) {
            $stockPrice->adjusted_price = round($source->unit_price / $this->portions_per_unit, 4);
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
    /**
     * Guarda los campos pendientes para este insumo
     * @param string[] $fields
     */
    public function savePendingFields($fields)
    {
        \common\models\PendingField::deleteAll([
            'model_type' => 'ingredient',
            'model_id' => $this->id
        ]);
        $now = time();
        foreach ($fields as $field) {
            $pending = new \common\models\PendingField();
            $pending->model_type = 'ingredient';
            $pending->model_id = $this->id;
            $pending->field = $field;
            $pending->created_at = $now;
            $pending->updated_at = $now;
            $pending->save(false);
        }
    }

    /**
     * Devuelve un array de campos pendientes para este insumo
     * @return string[]
     */
    public function getPendingFields()
    {
        return \common\models\PendingField::find()
            ->select('field')
            ->where([
                'model_type' => 'ingredient',
                'model_id' => $this->id
            ])->column();
    }

}