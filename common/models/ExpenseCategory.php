<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "expense_categories".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_main_category
 * @property int $sort_order
 * @property int $business_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Business $business
 * @property Expense[] $expenses
 * @property ExpenseSubcategory[] $subcategories
 */
class ExpenseCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'expense_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'business_id'], 'required'],
            [['business_id', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['is_main_category'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name', 'business_id'], 'unique', 'targetAttribute' => ['name', 'business_id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Nombre'),
            'description' => Yii::t('app', 'Descripción'),
            'is_main_category' => Yii::t('app', 'Categoría Principal'),
            'sort_order' => Yii::t('app', 'Orden'),
            'business_id' => Yii::t('app', 'Negocio'),
            'created_at' => Yii::t('app', 'Creado'),
            'updated_at' => Yii::t('app', 'Actualizado'),
        ];
    }

    /**
     * Gets query for associated business.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::class, ['id' => 'business_id']);
    }

    /**
     * Gets query for associated expenses.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses()
    {
        return $this->hasMany(Expense::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for associated subcategories.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubcategories()
    {
        return $this->hasMany(ExpenseSubcategory::class, ['category_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * Obtiene el número de subcategorías asociadas
     *
     * @return int
     */
    public function getSubcategoryCount()
    {
        return $this->getSubcategories()->count();
    }
}
