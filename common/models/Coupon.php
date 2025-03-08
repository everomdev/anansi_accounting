<?php

namespace common\models;

use setasign\Fpdi\PdfParser\Filter\Lzw;
use Yii;

/**
 * This is the model class for table "coupon".
 *
 * @property int $id
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
            [['quantity'], 'default', 'value' => null],
            [['quantity'], 'integer'],
            // [['gratitude'], 'string'],
            [['expiration'], 'safe'],
            [['name', 'code', 'type'], 'string', 'max' => 255],
            [['code'], 'unique'],
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
            'gratitude' => Yii::t('app', 'Gratitude')
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

    public function getIsValid()
    {
        // check expiration date and time
        if (empty($this->expiration) || $this->expiration <= date('Y-m-d H:i:s')) {
            return false;
        }
        //die(var_dump(empty($this->expiration) || $this->expiration <= date('Y-m-d H:i:s')));
        // check availability
        if (empty($this->quantity) || $this->quantity <= 0) {
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
}
