<?php

namespace common\models;

use Yii;
use common\behaviors\NumberFormatterBehavior;

/**
 * This is the model class for table "convoy_ingredient".
 *
 * @property int $id
 * @property int $convoy_id
 * @property string $entity_class
 * @property int $entity_id
 *
 * @property Convoy $convoy
 * @property float $quantity [float]
 */
class ConvoyIngredient extends \yii\db\ActiveRecord
{
    public $selectedEntity;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'numberFormatter' => [
                'class' => NumberFormatterBehavior::class,
                'numberFields' => ['quantity'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'convoy_ingredient';
    }

    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $record->populateEntity();
    }

    /**
     * {@inheritdoc}
     */    public function rules()
    {
        return [
            [['convoy_id', 'entity_class', 'entity_id'], 'required'],
            [['convoy_id', 'entity_id'], 'integer'],
            [['quantity'], 'number'],
            [['entity_class', 'selectedEntity'], 'string', 'max' => 255],
            [['convoy_id'], 'exist', 'skipOnError' => true, 'targetClass' => Convoy::className(), 'targetAttribute' => ['convoy_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'convoy_id' => Yii::t('app', 'Convoy ID'),
            'entity_class' => Yii::t('app', 'Entity Class'),
            'entity_id' => Yii::t('app', 'Entity ID'),
            'quantity' => Yii::t('app', 'Quantity'),
        ];
    }

    public function beforeSave($insert)
    {
        if(!parent::beforeSave($insert)){
            return false;
        }

        $this->processEntity();

        return true;
    }

    /**
     * Gets query for [[Convoy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConvoy()
    {
        return $this->hasOne(Convoy::className(), ['id' => 'convoy_id']);
    }

    public function getModel()
    {
        if (empty($this->entity_class)) {
            return null;
        }

        return $this->entity_class::findOne(['id' => $this->entity_id]);
    }

    public function getAmount()
    {
        try {
            $model = $this->getModel();

            if (empty($model)) {
               // Entidad faltante: registrar y devolver 0 para evitar excepción
                Yii::warning("ConvoyIngredient#{$this->id}: entidad no encontrada (entity_class={$this->entity_class}, entity_id={$this->entity_id})", __METHOD__);
                return 0;
            }

            if ($model instanceof IngredientStock) {
                $lastPrice = isset($model->adjustedPrice) ? $model->adjustedPrice : 0;
            } else {
                $lastPrice = isset($model->custom_cost) ? $model->custom_cost : 0;
            }

            return floatval($this->quantity) * floatval($lastPrice);
        } catch (\Throwable $e) {
            Yii::error('Error calculando amount en ConvoyIngredient#' . ($this->id ?? 'n/a') . ': ' . $e->getMessage(), __METHOD__);
            return 0;
        }
    }

    private function processEntity()
    {
        $entity = explode('_', $this->selectedEntity);
        if (count($entity) < 2) return;
        $type = $entity[0];
        $id = $entity[1];

        if ($type == 'ingredient') {
            $this->entity_class = IngredientStock::class;
        } else {
            $this->entity_class = StandardRecipe::class;
        }

        $this->entity_id = $id;
    }

    private function populateEntity()
    {
        if ($this->entity_class == IngredientStock::class) {
            $this->selectedEntity = sprintf("ingredient_%s", $this->entity_id);
        } else {
            $this->selectedEntity = sprintf("recipe_%s", $this->entity_id);
        }
    }
}
