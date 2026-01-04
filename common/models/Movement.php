<?php

namespace common\models;

use backend\traits\ProviderManagerTrait;
use Yii;
use common\behaviors\NumberFormatterBehavior;

/**
 * This is the model class for table "movement".
 *
 * @property int $id
 * @property string $type
 * @property string $provider
 * @property string|null $payment_type
 * @property string|null $invoice
 * @property float $quantity
 * @property string $um
 * @property float|null $amount
 * @property float|null $tax
 * @property float|null $retention
 * @property float|null $unit_price
 * @property float|null $total
 * @property string|null $observations
 * @property int $ingredient_id
 * @property int $business_id
 * @property int|null $consumption_center_id
 * @property string $created_at
 *
 * @property Business $business
 * @property IngredientStock $ingredient
 * @property ConsumptionCenter $consumptionCenter
 */
class Movement extends \yii\db\ActiveRecord
{
    use ProviderManagerTrait;    const TYPE_INPUT = 'input';
    const TYPE_OUTPUT = 'output';
    const TYPE_ORDER = 'order';

    // Tipos de pago genéricos (para compatibilidad)
    const PAYMENT_TYPE_CARD = 'card';
    const PAYMENT_TYPE_BANK_TRANSFERENCE = 'bank_transference';
    const PAYMENT_TYPE_CASH = 'cash';
    const PAYMENT_TYPE_OTHER = 'other';
    
    // Métodos de pago específicos del proveedor
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_TRANSFER = 'transfer';
    const PAYMENT_METHOD_CHECK = 'check';
    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_DEBIT_CARD = 'debit_card';
    const PAYMENT_METHOD_OTHER = 'other';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'numberFormatter' => [
                'class' => NumberFormatterBehavior::class,
                'priceFields' => ['amount', 'unit_price', 'total'],
                'numberFields' => ['quantity', 'tax', 'retention'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {        return [            
            [['type', 'quantity', 'ingredient_id', 'business_id', 'created_at'], 'required'],
            // amount es requerido solo para movimientos de entrada
            [['amount'], 'required', 'when' => function($model) {
                return $model->type === self::TYPE_INPUT;
            }, 'whenClient' => "function (attribute, value) {
                return $('#movement-type').val() === 'input';
            }"],
            [['quantity'], 'number', 'min' => 0.01, 'message' => 'La cantidad debe ser un número mayor a 0'],
            [['amount'], 'number', 'min' => 0, 'message' => 'El precio de compra debe ser un número válido'],
            [['tax', 'retention'], 'number', 'min' => 0, 'message' => 'Este campo debe ser un número válido'],
            [['unit_price', 'total'], 'number', 'min' => 0, 'message' => 'Este campo debe ser un número válido'],
            [['ingredient_id', 'business_id', 'consumption_center_id'], 'integer'],
            [['created_at'], 'safe'],
            [['type', 'provider', 'payment_type', 'invoice', 'um', 'observations'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['ingredient_id'], 'exist', 'skipOnError' => true, 'targetClass' => IngredientStock::className(), 'targetAttribute' => ['ingredient_id' => 'id']],
            [['consumption_center_id'], 'exist', 'skipOnError' => true, 'targetClass' => ConsumptionCenter::className(), 'targetAttribute' => ['consumption_center_id' => 'id']],
            [['type'], 'in', 'range' => array_keys(self::getFormattedTypes())],
            [['payment_type'], 'in', 'range' => array_keys(self::getFormattedPaymentMethods()), 'skipOnEmpty' => true],
            [['provider'], 'validateProvider'],
            [['consumption_center_id'], 'validateConsumptionCenter']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'provider' => Yii::t('app', 'Provider'),
            'payment_type' => Yii::t('app', 'Payment Type'),
            'invoice' => Yii::t('app', 'Invoice'),
            'quantity' => Yii::t('app', 'Quantity'),
            'um' => Yii::t('app', 'Um'),
            'amount' => Yii::t('app', 'Precio de compra'),
            'tax' => Yii::t('app', 'Tax'),
            'retention' => Yii::t('app', 'Retention'),
            'unit_price' => Yii::t('app', 'Unit Price'),
            'total' => Yii::t('app', 'Total'),
            'observations' => Yii::t('app', 'Observations'),
            'ingredient_id' => Yii::t('app', 'Resource'),
            'business_id' => Yii::t('app', 'Business ID'),
            'consumption_center_id' => Yii::t('app', 'Centro de Consumo'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }

        // Calcular unit_price automáticamente si es entrada y hay cantidad y amount
        if ($this->type == self::TYPE_INPUT && $this->quantity > 0 && $this->amount > 0) {
            $this->unit_price = round($this->amount / $this->quantity, 4); // 4 decimales para precisión
        }

        // Para salidas, calcular el costo total basado en el precio por porción
        if ($this->type == self::TYPE_OUTPUT && $this->quantity > 0) {
            $ingredient = $this->ingredient;
            
            // Obtener el último precio de stock_prices
            $lastStockPrice = $ingredient->getStockPrices()
                ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
                ->one();
            
            if ($lastStockPrice && $ingredient->portions_per_unit > 0) {
                // Calcular precio por porción: price de stock_prices / portions_per_unit
                $pricePerPortion = $lastStockPrice->price / $ingredient->portions_per_unit;
                
                // Calcular total: precio por porción * cantidad de salida
                $this->total = round($pricePerPortion * $this->quantity, 2);
                
                // Guardar el precio por porción en unit_price para referencia
                $this->unit_price = round($pricePerPortion, 4);
            }
        }

        // Establecer la unidad de medida según el tipo de movimiento
        if ($this->type == self::TYPE_OUTPUT) {
            // Para salidas, usar la unidad de cocina (portion_um)
            $this->um = $this->ingredient->portion_um;
        } else {
            // Para entradas y órdenes, usar la unidad de compra (um)
            $this->um = $this->ingredient->um;
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if ($this->type == self::TYPE_INPUT) {
                $this->applyInput();
            } elseif ($this->type == self::TYPE_OUTPUT) {
                $this->applyOutput();
            }

            $lastBalance = Balance::find()
                ->where(['business_id' => $this->business_id])
                ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
                ->one();

            if (empty($lastBalance)) {
                $lastBalance = new Balance([
                    'business_id' => $this->business_id,
                    'date' => date('Y-m-d'),
                    'current_balance' => 0,
                    'created_by' => Yii::$app->user->id
                ]);
            }

            $lastBalance->expense = $this->total;
            $lastBalance->save();

            $nextBalance = new Balance([
                'business_id' => $this->business_id,
                'date' => date('Y-m-d'),
                'current_balance' => $lastBalance->current_balance - $lastBalance->expense,
                'created_by' => Yii::$app->user->id,
                'expense' => 0
            ]);
            $nextBalance->save();
        }

//        $this->saveProvider();


        parent::afterSave($insert, $changedAttributes);
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
     * Gets query for [[Ingredient]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIngredient()
    {
        return $this->hasOne(IngredientStock::className(), ['id' => 'ingredient_id']);
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

    public static function getFormattedTypes()
    {
        return [
            self::TYPE_INPUT => Yii::t('app', "Input"),
            self::TYPE_OUTPUT => Yii::t('app', "Output"),
            self::TYPE_ORDER => Yii::t('app', "Order"),
        ];
    }    public static function getFormattedPaymentTypes()
    {
        return [
            self::PAYMENT_TYPE_CARD => Yii::t('app', "Card"),
            self::PAYMENT_TYPE_BANK_TRANSFERENCE => Yii::t('app', "Bank Transfer"),
            self::PAYMENT_TYPE_CASH => Yii::t('app', "Cash"),
            self::PAYMENT_TYPE_OTHER => Yii::t('app', "Otro"),
        ];
    }

    /**
     * Obtiene los métodos de pago específicos que acepta el sistema
     */
    public static function getFormattedPaymentMethods()
    {
        return [
            self::PAYMENT_METHOD_CASH => Yii::t('app', 'Efectivo'),
            self::PAYMENT_METHOD_TRANSFER => Yii::t('app', 'Transferencia'),
            self::PAYMENT_METHOD_CHECK => Yii::t('app', 'Cheque'),
            self::PAYMENT_METHOD_CREDIT_CARD => Yii::t('app', 'Tarjeta Crédito'),
            self::PAYMENT_METHOD_DEBIT_CARD => Yii::t('app', 'Tarjeta Débito'),
            self::PAYMENT_METHOD_OTHER => Yii::t('app', 'Otro')
        ];
    }

    public function getFormattedType()
    {
        return self::getFormattedTypes()[$this->type];
    }    public function getFormattedPaymentType()
    {
        if (empty($this->payment_type)) {
            return '';
        }
        
        // Primero intentar con los métodos específicos
        $specificMethods = self::getFormattedPaymentMethods();
        if (isset($specificMethods[$this->payment_type])) {
            return $specificMethods[$this->payment_type];
        }
        
        // Si no existe, intentar con los tipos genéricos (para compatibilidad)
        $genericTypes = self::getFormattedPaymentTypes();
        if (isset($genericTypes[$this->payment_type])) {
            return $genericTypes[$this->payment_type];
        }
        
        // Si no existe en ninguno, devolver el valor tal como está
        return $this->payment_type;
    }

    private function applyInput()
    {
        $ingredient = $this->ingredient;
        $ingredient->quantity += $this->quantity;
        $ingredient->save();
        $ingredient->addPrice($this);
        $menus = Menu::findAll(['business_id' => $this->business_id]);
        foreach ($menus as $menu) {
            $menu->updatePrices();
        }
    }

    private function applyOutput()
    {
        $ingredient = $this->ingredient;
        $ingredient->quantity -= $this->quantity;
        $ingredient->save();
    }

    /**
     * Validar que el proveedor existe en la base de datos
     * Busca por business_name (valor actual) o por name (compatibilidad hacia atrás)
     */
    public function validateProvider($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }
        
        // Solo validar para tipos que requieren proveedor (input y order)
        if ($this->type === self::TYPE_OUTPUT) {
            return;
        }
        
        // Para importaciones, permitir cualquier valor de proveedor
        // Si no existe, el sistema lo manejará como null
        // No forzamos que exista en la base de datos
        return;
        
        // Código comentado - validación original que causaba problemas en importación
        /*
        // Buscar proveedor por business_name (valor actual) o name (compatibilidad)
        $provider = Provider::find()
            ->where(['business_id' => $this->business_id])
            ->andWhere([
                'or',
                ['business_name' => $this->$attribute],
                ['name' => $this->$attribute]
            ])
            ->one();
            
        if (!$provider) {
            $this->addError($attribute, 'El proveedor seleccionado no existe o no pertenece a este negocio.');
        }
        */
    }

    /**
     * Obtener el proveedor asociado a este movimiento
     * Busca por business_name (valor actual) o name (compatibilidad hacia atrás)
     */
    public function getProviderModel()
    {
        if (empty($this->provider) || $this->type === self::TYPE_OUTPUT) {
            return null;
        }
        
        return Provider::find()
            ->where(['business_id' => $this->business_id])
            ->andWhere([
                'or',
                ['business_name' => $this->provider],
                ['name' => $this->provider]
            ])
            ->one();
    }

    /**
     * Validar que el centro de consumo sea requerido para movimientos de salida
     */
    public function validateConsumptionCenter($attribute, $params)
    {
        // Solo validar para movimientos de salida
        if ($this->type === self::TYPE_OUTPUT && empty($this->$attribute)) {
            $this->addError($attribute, 'El centro de consumo es requerido para movimientos de salida.');
        }
    }


}
