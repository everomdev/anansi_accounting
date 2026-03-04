<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "expense_movements".
 *
 * @property int $id
 * @property int $business_id
 * @property int $expense_id
 * @property string $type
 * @property float $amount
 * @property float|null $quantity
 * @property float|null $unit_amount
 * @property string|null $payment_type
 * @property string|null $invoice
 * @property string|null $observations
 * @property string $movement_date
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Business $business
 * @property Expense $expense
 */
class ExpenseMovement extends ActiveRecord
{
    const TYPE_PAYMENT = 'payment';
    const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'expense_movements';
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
                'value' => new \yii\db\Expression('NOW()'),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_id', 'expense_id', 'type', 'movement_date'], 'required'],
            [['business_id', 'expense_id'], 'integer'],
            [['amount', 'quantity', 'unit_amount'], 'number'],
            // Si quantity está presente, unit_amount es requerido y amount se calcula automáticamente
            [['quantity'], 'required', 'when' => function($model) {
                return $model->isInventoriableExpense();
            }, 'whenClient' => "function (attribute, value) {
                return $('#expensemovement-expense_id').val() && window.isInventoriableExpense;
            }"],
            [['unit_amount'], 'required', 'when' => function($model) {
                return !empty($model->quantity);
            }],
            // Si NO hay quantity, amount es requerido
            [['amount'], 'required', 'when' => function($model) {
                return empty($model->quantity);
            }],
            [['observations'], 'string'],
            [['movement_date', 'created_at', 'updated_at'], 'safe'],
            [['type'], 'string', 'max' => 50],
            [['type'], 'in', 'range' => [self::TYPE_PAYMENT, self::TYPE_ADJUSTMENT]],
            [['payment_type', 'invoice'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
            [['expense_id'], 'exist', 'skipOnError' => true, 'targetClass' => Expense::class, 'targetAttribute' => ['expense_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_id' => 'Negocio',
            'expense_id' => 'Gasto',
            'type' => 'Tipo de Movimiento',
            'amount' => 'Monto Total',
            'quantity' => 'Cantidad',
            'unit_amount' => 'Monto Unitario',
            'payment_type' => 'Tipo de Pago',
            'invoice' => 'Factura',
            'observations' => 'Observaciones',
            'movement_date' => 'Fecha del Movimiento',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * Gets query for [[Business]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::class, ['id' => 'business_id']);
    }

    /**
     * Gets query for [[Expense]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpense()
    {
        return $this->hasOne(Expense::class, ['id' => 'expense_id']);
    }

    /**
     * Retorna los tipos de movimiento formateados
     */
    public static function getFormattedTypes()
    {
        return [
            self::TYPE_PAYMENT => 'Pago',
            self::TYPE_ADJUSTMENT => 'Ajuste',
        ];
    }

    /**
     * Retorna el tipo de movimiento formateado
     */
    public function getFormattedType()
    {
        $types = self::getFormattedTypes();
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Retorna los tipos de pago disponibles
     */
    public static function getPaymentTypes()
    {
        return [
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'tarjeta_debito' => 'Tarjeta de Débito',
            'tarjeta_credito' => 'Tarjeta de Crédito',
            'cheque' => 'Cheque',
            'otro' => 'Otro',
        ];
    }

    /**
     * Retorna el tipo de pago formateado
     */
    public function getFormattedPaymentType()
    {
        if (empty($this->payment_type)) {
            return '-';
        }
        $types = self::getPaymentTypes();
        return $types[$this->payment_type] ?? $this->payment_type;
    }
    
    /**
     * Verifica si el gasto asociado es inventariable
     */
    public function isInventoriableExpense()
    {
        if (!$this->expense) {
            return false;
        }
        
        return $this->expense->subcategory && $this->expense->subcategory->is_inventoriable;
    }
    
    /**
     * Calcula el monto total antes de guardar
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        
        // Si tiene cantidad y monto unitario, calcular el total
        if (!empty($this->quantity) && !empty($this->unit_amount)) {
            $this->amount = $this->quantity * $this->unit_amount;
        }
        // Si no tiene cantidad pero sí monto total, limpiar quantity y unit_amount
        elseif (!empty($this->amount) && empty($this->quantity)) {
            $this->quantity = null;
            $this->unit_amount = null;
        }
        
        return true;
    }
}
