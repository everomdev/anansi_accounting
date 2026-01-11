<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "requisition_item".
 *
 * @property int $id
 * @property int $requisition_id
 * @property int $ingredient_id
 * @property float $quantity_requested
 * @property float|null $quantity_fulfilled
 * @property string|null $availability_status
 * @property float|null $availability_percentage
 * @property float|null $cost_at_request
 * @property string|null $observations
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Movement $requisition
 * @property IngredientStock $ingredient
 */
class RequisitionItem extends ActiveRecord
{
    const STATUS_AVAILABLE = 'available';
    const STATUS_LOW = 'low';
    const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'requisition_item';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['requisition_id', 'ingredient_id', 'quantity_requested'], 'required'],
            [['requisition_id', 'ingredient_id'], 'integer'],
            [['quantity_requested', 'quantity_fulfilled', 'availability_percentage', 'cost_at_request'], 'number'],
            [['quantity_requested'], 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => 'La cantidad debe ser mayor a 0'],
            [['quantity_fulfilled'], 'compare', 'compareValue' => 0, 'operator' => '>=', 'message' => 'La cantidad entregada no puede ser negativa'],
            [['availability_status'], 'string', 'max' => 20],
            [['observations'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['requisition_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movement::class, 'targetAttribute' => ['requisition_id' => 'id']],
            [['ingredient_id'], 'exist', 'skipOnError' => true, 'targetClass' => IngredientStock::class, 'targetAttribute' => ['ingredient_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'requisition_id' => Yii::t('app', 'Requisition ID'),
            'ingredient_id' => Yii::t('app', 'Ingredient'),
            'quantity_requested' => Yii::t('app', 'Quantity Requested'),
            'quantity_fulfilled' => Yii::t('app', 'Quantity Fulfilled'),
            'availability_status' => Yii::t('app', 'Availability Status'),
            'availability_percentage' => Yii::t('app', 'Availability Percentage'),
            'cost_at_request' => Yii::t('app', 'Cost At Request'),
            'observations' => Yii::t('app', 'Observations'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Requisition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequisition()
    {
        return $this->hasOne(Movement::class, ['id' => 'requisition_id']);
    }

    /**
     * Gets query for [[Ingredient]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIngredient()
    {
        return $this->hasOne(IngredientStock::class, ['id' => 'ingredient_id']);
    }

    /**
     * Calcular estado de disponibilidad basado en el stock actual
     * 
     * @param int $businessId
     * @return array ['status' => string, 'percentage' => float, 'icon' => string, 'class' => string]
     */
    public function calculateAvailability($businessId)
    {
        // Cargar ingrediente explícitamente si no está cargado
        $ingredient = $this->ingredient;
        if (!$ingredient && $this->ingredient_id) {
            $ingredient = IngredientStock::findOne($this->ingredient_id);
        }
        
        if (!$ingredient) {
            return [
                'status' => self::STATUS_UNAVAILABLE,
                'percentage' => 0,
                'icon' => '🔴',
                'class' => 'text-danger'
            ];
        }

        $config = RequisitionConfig::getForBusiness($businessId);
        return $config->calculateAvailability($this->quantity_requested, $ingredient->quantity);
    }

    /**
     * Actualizar estado de disponibilidad antes de guardar
     * 
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Obtener business_id desde la requisición
        if ($this->requisition_id) {
            $requisition = Movement::findOne($this->requisition_id);
            
            if ($requisition) {
                $availability = $this->calculateAvailability($requisition->business_id);
                $this->availability_status = $availability['status'];
                $this->availability_percentage = $availability['percentage'];
            }
        }

        // Guardar el costo actual del insumo al momento de la requisición
        if ($insert && $this->ingredient_id) {
            $ingredient = IngredientStock::findOne($this->ingredient_id);
            
            if ($ingredient) {
                $this->cost_at_request = $ingredient->getLastPrice();
            }
        }

        return true;
    }

    /**
     * Obtener icono según el estado de disponibilidad
     * 
     * @return string
     */
    public function getAvailabilityIcon()
    {
        switch ($this->availability_status) {
            case self::STATUS_AVAILABLE:
                return '🟢';
            case self::STATUS_LOW:
                return '🟡';
            case self::STATUS_UNAVAILABLE:
                return '🔴';
            default:
                return '⚪';
        }
    }

    /**
     * Obtener clase CSS según el estado de disponibilidad
     * 
     * @return string
     */
    public function getAvailabilityClass()
    {
        switch ($this->availability_status) {
            case self::STATUS_AVAILABLE:
                return 'text-success';
            case self::STATUS_LOW:
                return 'text-warning';
            case self::STATUS_UNAVAILABLE:
                return 'text-danger';
            default:
                return 'text-muted';
        }
    }

    /**
     * Verificar si el item fue entregado completamente
     * 
     * @return bool
     */
    public function isFullyDelivered()
    {
        return $this->quantity_fulfilled !== null && 
               $this->quantity_fulfilled >= $this->quantity_requested;
    }

    /**
     * Verificar si el item fue entregado parcialmente
     * 
     * @return bool
     */
    public function isPartiallyDelivered()
    {
        return $this->quantity_fulfilled !== null && 
               $this->quantity_fulfilled > 0 && 
               $this->quantity_fulfilled < $this->quantity_requested;
    }

    /**
     * Verificar si el item no ha sido entregado
     * 
     * @return bool
     */
    public function isPending()
    {
        return $this->quantity_fulfilled === null || $this->quantity_fulfilled == 0;
    }

    /**
     * Obtener porcentaje de entrega
     * 
     * @return float
     */
    public function getDeliveryPercentage()
    {
        if ($this->quantity_requested == 0) {
            return 0;
        }
        
        $delivered = $this->quantity_fulfilled ?? 0;
        return ($delivered / $this->quantity_requested) * 100;
    }
}
