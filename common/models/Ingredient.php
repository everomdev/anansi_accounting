<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ingredient".
 *
 * @property int $id
 * @property string $name
 * @property string|null $um
 * @property float|null $portions_per_unit
 * @property string|null $portion_um
 *
 * @property IngredientStandardRecipe[] $ingredientStandardRecipes
 * @property int $category_id [int]
 * @property float $unit_price [float]
 */
class Ingredient extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ingredient';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['portions_per_unit', 'unit_price'], 'number'],
            [['name', 'um', 'portion_um'], 'string', 'max' => 255],
            [['category_id'], 'integer'],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'um' => 'Um',
            'portions_per_unit' => 'Portions Per Unit',
            'portion_um' => 'Portion Um',
            'category_id' => 'Category',
        ];
    }

    /**
     * Gets query for [[IngredientStandardRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIngredientStandardRecipes()
    {
        return $this->hasMany(IngredientStandardRecipe::className(), ['ingredient_id' => 'id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }
}
