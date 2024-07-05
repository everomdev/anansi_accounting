<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sales".
 *
 * @property int $id
 * @property string|null $date
 * @property float|null $amount_food
 * @property float|null $amount_drinking
 * @property float|null $amount_other
 * @property int $business_id [int]
 */
class Sales extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sales';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['amount_food', 'amount_drinking', 'amount_other'], 'number'],
            [['business_id'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'date' => Yii::t('app', 'Date'),
            'amount_food' => Yii::t('app', 'Food sales'),
            'amount_drinking' => Yii::t('app', 'Drinking sales'),
            'amount_other' => Yii::t('app', 'Other sales'),
        ];
    }
}
