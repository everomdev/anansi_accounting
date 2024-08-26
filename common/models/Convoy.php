<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "convoy".
 *
 * @property int $id
 * @property int $business_id
 * @property string $quantity
 * @property string|null $um
 * @property string $entity
 * @property int $entity_id
 *
 * @property Business $business
 * @property int $plates [int]
 * @property string $type [varchar(255)]
 * @property-read mixed $convoyIngredients
 * @property-read float|int $amount
 * @property-read mixed $totalAmount
 * @property string $name [varchar(255)]
 */
class Convoy extends \yii\db\ActiveRecord
{
    const TYPE_GENERAL = 'general';
    const TYPE_FAMILY = 'family';
    public $selectedEntities;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'convoy';
    }

    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $record->selectedEntities = ArrayHelper::getColumn($record->convoyIngredients, 'selectedEntity');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_id', 'type', 'name'], 'required'],
            [['business_id', 'plates'], 'integer'],
            [['um', 'type', 'name'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['type'], 'in', 'range' => [self::TYPE_GENERAL, self::TYPE_FAMILY]]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'business_id' => Yii::t('app', 'Business ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'um' => Yii::t('app', 'Um'),
            'selectedEntity' => Yii::t('app', 'Description'),
            'type' => Yii::t('app', 'Type'),
            'name' => Yii::t('app', 'Name'),
            'amount' => Yii::t('app', 'Amount'),
            'plates' => Yii::t('app', 'Sold Plates'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }


        return true;
    }

    public function getConvoyIngredients()
    {
        return $this->hasMany(ConvoyIngredient::class, ['convoy_id' => 'id']);
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

    public function getTotalAmount()
    {
        $ingredients = $this->convoyIngredients;
        $totalAmount = array_reduce($ingredients, function ($carry, $item) {
            return $carry + $item->amount;
        }, 0);
        return $totalAmount;
    }

    public function getAmount()
    {
        if (empty($this->plates)) {
            return 0;
        }
        return $this->totalAmount / $this->plates;
    }

    public function getLabel()
    {
        return sprintf("%s - %s", $this->name, $this->business->formatter->asCurrency($this->amount));
    }

}
