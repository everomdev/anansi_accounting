<?php

namespace common\models;

use backend\helpers\RedisKeys;
use Yii;

/**
 * This is the model class for table "unit_of_measurement".
 *
 * @property int $id
 * @property string $name
 * @property int|null $business_id
 * @property int $custom
 * @property string $type
 * @property int $is_purchase
 * @property int $is_kitchen
 * @property int $is_subrecipe_yield
 * @property int $is_subrecipe_um
 * @property int $is_recipe_yield
 * @property int $is_recipe_final_um
 *
 * @property Business $business
 */
class UnitOfMeasurement extends \yii\db\ActiveRecord
{
    const TYPE_KITCHEN = 'kitchen';
    const TYPE_PURCHASE = 'purchase';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unit_of_measurement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'trim'], // Eliminar espacios al inicio y final
            [['name'], 'validateUniqueByFlags'],
            [['business_id', 'custom', 'is_purchase', 'is_kitchen', 'is_subrecipe_yield', 'is_subrecipe_um', 'is_recipe_yield', 'is_recipe_final_um'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 20],
            [['custom'], 'default', 'value' => 0],
            [['is_purchase', 'is_kitchen', 'is_subrecipe_yield', 'is_subrecipe_um', 'is_recipe_yield', 'is_recipe_final_um'], 'default', 'value' => 0],
            [['type'], 'default', 'value' => self::TYPE_KITCHEN],
            [['type'], 'in', 'range' => [self::TYPE_KITCHEN, self::TYPE_PURCHASE]],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    /**
     * Valida que el nombre no esté repetido por cada flag/tipo (por negocio).
     * Ej: no permitir dos registros con name='kilogramo' y is_purchase=1 para el mismo business_id.
     */
    public function validateUniqueByFlags($attribute)
    {
        $businessId = $this->business_id;
        if (empty($businessId)) {
            $business = RedisKeys::getBusinessData();
            $businessId = $business['id'] ?? null;
        }

        // Normalizar nombre para comparación (insensible a mayúsculas)
        $nameNormalized = mb_strtolower(trim($this->name));

        $flags = [
            'is_purchase' => 'compra',
            'is_kitchen' => 'cocina',
            'is_subrecipe_yield' => 'rendimiento subreceta',
            'is_subrecipe_um' => 'unidad subreceta',
            'is_recipe_yield' => 'rendimiento receta',
            'is_recipe_final_um' => 'unidad final receta',
        ];

        foreach ($flags as $flag => $label) {
            if (!empty($this->$flag)) {
                $query = self::find()->where([
                    'business_id' => $businessId,
                    $flag => 1,
                ]);
                if (!$this->isNewRecord) {
                    $query->andWhere(['<>', 'id', $this->id]);
                }
                // Comparación insensible a mayúsculas
                $query->andWhere(new \yii\db\Expression('LOWER(name) = :name', [':name' => $nameNormalized]));
                if ($query->exists()) {
                    $this->addError($attribute, "Ya existe una unidad llamada '{$this->name}' para el tipo ({$label}).");
                    // No seguimos validando otros flags si ya hay conflicto con este
                    return;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'business_id' => Yii::t('app', 'Business ID'),
            'custom' => Yii::t('app', 'Unidad personalizada'),
            'type' => Yii::t('app', 'Tipo de unidad'),
            'is_purchase' => Yii::t('app', '¿Es unidad de compra (insumos)?'),
            'is_kitchen' => Yii::t('app', '¿Es unidad de cocina (insumos)?'),
            'is_subrecipe_yield' => Yii::t('app', '¿Es unidad de rendimiento (subreceta)?'),
            'is_subrecipe_um' => Yii::t('app', '¿Es unidad de insumo (subreceta)?'),
            'is_recipe_yield' => Yii::t('app', '¿Es unidad de rendimiento (receta)?'),
            'is_recipe_final_um' => Yii::t('app', '¿Es unidad final de (receta)?'),
        ];
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

    public static function getOwn(): \yii\db\ActiveQuery
    {
        $business = RedisKeys::getBusinessData();
        return self::find()->where(['business_id' => $business['id']]);
    }
    
    /**
     * Obtiene unidades de cocina del negocio actual
     */
    public static function getKitchenUnits(): \yii\db\ActiveQuery
    {
        $business = RedisKeys::getBusinessData();
        return self::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['type' => self::TYPE_KITCHEN]);
    }
    
    /**
     * Obtiene unidades de compra del negocio actual
     */
    public static function getPurchaseUnits(): \yii\db\ActiveQuery
    {
        $business = RedisKeys::getBusinessData();
        return self::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['type' => self::TYPE_PURCHASE]);
    }
    
    /**
     * Obtiene el texto amigable del tipo
     */
    public function getTypeText()
    {
        return $this->type === self::TYPE_KITCHEN ? 'Cocina' : 'Compra';
    }
    
    /**
     * Obtiene las opciones de tipo para dropdowns
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_KITCHEN => 'Unidad de cocina',
            self::TYPE_PURCHASE => 'Unidad de compra',
        ];
    }
}
