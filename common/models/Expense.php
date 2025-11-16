<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "expense".
 *
 * @property int $id
 * @property string $name
 * @property string|null $brand
 * @property string|null $presentation
 * @property int $business_id
 * @property float|null $quantity
 * @property string $um
 * @property float|null $yield
 * @property float|null $portions_per_unit
 * @property string|null $portion_um
 * @property string|null $observations
 * @property string $key
 * @property float $final_quantity
 * @property int $category_id
 * @property float|null $min_stock
 * @property float|null $max_stock
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Business $business
 * @property Category $category
 */
class Expense extends \yii\db\ActiveRecord
{
    public $_category;
    public $price;
    public $adjustedPrice;
    public $_key;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'expenses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'business_id', 'um', 'key'], 'required'],
            [['business_id', 'category_id'], 'integer'],
            [['quantity', 'yield', 'portions_per_unit', 'min_stock', 'max_stock', 'final_quantity'], 'number'],
            [['observations'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'brand', 'presentation', 'um', 'portion_um'], 'string', 'max' => 255],
            [['key'], 'string', 'max' => 255],
            [['key'], 'unique', 'targetAttribute' => ['key', 'business_id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Nombre del Gasto'),
            'brand' => Yii::t('app', 'Marca'),
            'presentation' => Yii::t('app', 'Presentación'),
            'business_id' => Yii::t('app', 'Negocio'),
            'quantity' => Yii::t('app', 'Cantidad'),
            'um' => Yii::t('app', 'Unidad de Medida'),
            'yield' => Yii::t('app', 'Rendimiento'),
            'portions_per_unit' => Yii::t('app', 'Porciones por Unidad'),
            'portion_um' => Yii::t('app', 'Unidad de Porción'),
            'observations' => Yii::t('app', 'Observaciones'),
            'key' => Yii::t('app', 'Clave'),
            'final_quantity' => Yii::t('app', 'Cantidad Final'),
            'category_id' => Yii::t('app', 'Categoría'),
            'min_stock' => Yii::t('app', 'Stock Mínimo'),
            'max_stock' => Yii::t('app', 'Stock Máximo'),
            'created_at' => Yii::t('app', 'Creado'),
            'updated_at' => Yii::t('app', 'Actualizado'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        $this->updated_at = date('Y-m-d H:i:s');

        return true;
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
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        if (!empty($this->_category)) {
            return $this->_category;
        }

        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public static function keyGenerator($categoryId, $businessId)
    {
        $category = Category::findOne(['id' => $categoryId]);
        $business = Business::findOne(['id' => $businessId]);

        if (!$category || !$business) {
            return '';
        }

        $categoryInitial = strtoupper(substr($category->name, 0, 3));
        $businessInitial = strtoupper(substr($business->business_name, 0, 3));
        
        // Buscar el siguiente número disponible
        $count = self::find()
            ->where(['like', 'key', $categoryInitial . $businessInitial])
            ->andWhere(['business_id' => $businessId])
            ->count();

        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        return $categoryInitial . $businessInitial . $nextNumber;
    }

    public function getLabel()
    {
        $parts = [$this->name];
        if ($this->brand) {
            $parts[] = $this->brand;
        }
        if ($this->presentation) {
            $parts[] = $this->presentation;
        }
        return implode(' - ', $parts);
    }
}
