<?php

namespace common\models;

use setasign\Fpdi\PdfParser\Filter\Lzw;
use Yii;

/**
 * This is the model class for table "coupon".
 *
 * @property int $id
 * @property int $expiration_date
 * @property string $name
 * @property string|null $code
 * @property float|null $discount
 * @property int|null $quantity
 * @property string|null $expiration
 * @property-read bool $isValid
 * @property-read mixed $payments
 * @property-read mixed $formattedType
 * @property string|null $type
 * @property string $gratitude
 */
class Coupon extends \yii\db\ActiveRecord
{
    const TYPE_AMOUNT = 'amount';
    const TYPE_PERCENT = 'per_cent';
    public $expiration_formatted;
    public $expiration_date_formatted;
    const ALL_PLANS = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupon';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['discount'], 'number'],
            [['stripe_coupon_id'], 'string', 'max' => 255],
            [['quantity'], 'default', 'value' => null],
            [['quantity','usages'], 'integer'],
            // [['gratitude'], 'string'],
            [['expiration','expiration_date'], 'safe'],
            [['name', 'code', 'type'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['plan_id'], 'integer'],
            [['all_plans'], 'boolean'],
            ['plan_id', 'validatePlan'],
            [['type'], 'in', 'range' => array_keys(self::getFormattedTypes())]
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
            'code' => Yii::t('app', 'Code'),
            'discount' => Yii::t('app', 'Descuento'),
            'quantity' => Yii::t('app', 'Quantity'),
            'expiration' => Yii::t('app', 'Fecha de expiración'),
            'type' => Yii::t('app', 'Type'),
            'gratitude' => Yii::t('app', 'Gratitude'),
            'expiration_date' => Yii::t('app', 'Fecha de vigencia'),
            'plan_id' => Yii::t('app', 'Plan'),
            'stripe_coupon_id' => Yii::t('app', 'ID del cupón en Stripe'),
            'all_plans' => Yii::t('app', 'Aplicar a todos los planes'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
       /* if (!$this->private) {
            $this->gratitude = null;
        }*/
        return true;
    }
    /**
     * Valida que el plan_id sea válido o igual a ALL_PLANS
     * @param string $attribute
     * @param array $params
     */
    public function validatePlan($attribute, $params)
    {
        // Si all_plans es true, no necesitamos verificar plan_id
        if ($this->all_plans) {
            return;
        }
        
        // Si plan_id está vacío y all_plans es false
        if ($this->$attribute === null) {
            $this->addError($attribute, Yii::t('app', 'El plan es requerido cuando el cupón no aplica a todos los planes.'));
            return;
        }
        
        // Verificar si el plan existe
        $plan = Plan::findOne($this->$attribute);
        if (!$plan) {
            $this->addError($attribute, Yii::t('app', 'Plan no válido.'));
        }
    }


    public static function getFormattedTypes()
    {
        return [
            self::TYPE_AMOUNT => Yii::t('app', "Amount"),
            self::TYPE_PERCENT => Yii::t('app', "Percentage"),
        ];
    }

    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['coupon_code' => 'code']);
    }

    public function getFormattedType()
    {
        return self::getFormattedTypes()[$this->type];
    }

    public function getIsValid($plan_id)
    {
        // check expiration date and time
        if (empty($this->expiration) || $this->expiration <= date('Y-m-d H:i:s') || 
        empty($this->expiration_date) || $this->expiration_date < time()) {
        return false;
        }

        // Si all_plans es true, el cupón es válido para cualquier plan
        if (!$this->all_plans && (int)$this->plan_id !== (int)$plan_id) {
        return false;
        }

        // check availability
        if ($this->quantity > 0 && $this->usages >= $this->quantity) {
        return false;
        }

        return true;
    }

    public static function applyDiscount($price, $type, $discount)
    {
        switch ($type) {
            case self::TYPE_AMOUNT:
                return $price - $discount;
            case self::TYPE_PERCENT:
                return $price - ($price * ($discount / 100));
            default:
                return null;
        }
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }

    /**
     * Obtiene el nombre del plan o "Todos los planes" si corresponde
     * @return string
     */
    public function getPlanName()
    {
        if ($this->all_plans) {
            return Yii::t('app', 'Todos los planes');
        }
        
        return $this->plan ? $this->plan->name : Yii::t('app', 'Sin plan');
    }
}
