<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "expense_subcategories".
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property bool $is_inventoriable
 * @property int $sort_order
 * @property int $business_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property ExpenseCategory $category
 * @property Business $business
 * @property Expense[] $expenses
 */
class ExpenseSubcategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'expense_subcategories';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'name', 'business_id'], 'required'],
            [['category_id', 'business_id', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['is_inventoriable'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name', 'business_id', 'category_id'], 'unique', 'targetAttribute' => ['name', 'business_id', 'category_id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseCategory::class, 'targetAttribute' => ['category_id' => 'id']],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::class, 'targetAttribute' => ['business_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Categoría Principal',
            'name' => 'Nombre de Subcategoría',
            'description' => 'Descripción',
            'is_inventoriable' => 'Requiere Inventario',
            'sort_order' => 'Orden',
            'business_id' => 'Negocio',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];
    }

    /**
     * Gets query for associated category.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ExpenseCategory::class, ['id' => 'category_id']);
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
        return $this->hasMany(Expense::class, ['subcategory_id' => 'id']);
    }

    /**
     * Retorna el nombre completo con categoría
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->category ? $this->category->name . ' → ' . $this->name : $this->name;
    }

    /**
     * Obtiene subcategorías agrupadas por categoría principal
     *
     * @param int $businessId
     * @return array
     */
    public static function getGroupedByCategory($businessId)
    {
        $subcategories = self::find()
            ->joinWith('category')
            ->where(['expense_subcategories.business_id' => $businessId])
            ->orderBy([
                'expense_categories.sort_order' => SORT_ASC,
                'expense_subcategories.sort_order' => SORT_ASC
            ])
            ->all();

        $grouped = [];
        foreach ($subcategories as $sub) {
            $categoryName = $sub->category ? $sub->category->name : 'Sin Categoría';
            if (!isset($grouped[$categoryName])) {
                $grouped[$categoryName] = [];
            }
            $grouped[$categoryName][$sub->id] = $sub->name . ($sub->is_inventoriable ? ' 📦' : '');
        }

        return $grouped;
    }

    /**
     * Obtiene subcategorías para Select2
     *
     * @param int $businessId
     * @return array
     */
    public static function getForSelect2($businessId)
    {
        $subcategories = self::find()
            ->with('category')
            ->where(['expense_subcategories.business_id' => $businessId])
            ->orderBy([
                'category_id' => SORT_ASC,
                'sort_order' => SORT_ASC
            ])
            ->all();

        $result = [];
        foreach ($subcategories as $sub) {
            $result[] = [
                'id' => $sub->id,
                'text' => $sub->getFullName(),
                'category' => $sub->category ? $sub->category->name : null,
                'inventoriable' => $sub->is_inventoriable,
            ];
        }

        return $result;
    }
}
