<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "recipe_category".
 *
 * @property int $id
 * @property string $name
 * @property int|null $business_id
 * @property int $custom
 *
 * @property Business $business
 * @property string $type [varchar(255)]
 */
class RecipeCategory extends \yii\db\ActiveRecord
{
    const TYPE_MAIN = 'main';
    const TYPE_SUB = 'sub';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['business_id', 'custom'], 'integer'],
            [['name', 'type'], 'string', 'max' => 255],
            [['custom'], 'default', 'value' => 0],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['type'], 'in', 'range' => [self::TYPE_MAIN, self::TYPE_SUB]],
            [['name', 'type'], 'unique', 'targetAttribute' => ['name', 'business_id', 'type'], 'message' => "Ya existe una categoría con este nombre"],
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
            'type' => Yii::t('app', 'Type'),
            'business_id' => Yii::t('app', 'Business ID'),
            'custom' => Yii::t('app', 'Categoría personalizada'),
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

    public static function getFormattedTypes()
    {
        return [
            \common\models\RecipeCategory::TYPE_MAIN => Yii::t('app', 'For recipes'),
            \common\models\RecipeCategory::TYPE_SUB => Yii::t('app', 'For sub-recipes'),
        ];
    }

    public function getTotalSales($month = null, $year = null)
    {
        // If month and year are provided, use MonthlySales table
        if ($month !== null && $year !== null) {
            $recipesTotalSales = 0;
            $combosTotalSales = 0;

            // Get all recipes in this category
            $recipes = StandardRecipe::find()
                ->where([
                    'type_of_recipe' => $this->name,
                    'business_id' => $this->business_id,
                    'in_construction' => 0,
                    'in_menu' => true
                ])->all();

            // Sum monthly sales for each recipe
            foreach ($recipes as $recipe) {
                $recipesTotalSales += \common\models\MonthlySales::getTotalSales(
                    \common\models\MonthlySales::TYPE_RECIPE,
                    $recipe->id,
                    $year,
                    $month
                );
            }

            // Get all combos in this category
            $combos = Menu::find()
                ->where([
                    'category_id' => $this->id,
                    'business_id' => $this->business_id,
                    'in_menu' => true
                ])->all();

            // Sum monthly sales for each combo
            foreach ($combos as $combo) {
                $combosTotalSales += \common\models\MonthlySales::getTotalSales(
                    \common\models\MonthlySales::TYPE_MENU,
                    $combo->id,
                    $year,
                    $month
                );
            }

            return $recipesTotalSales + $combosTotalSales;
        }

        // Fallback to original method if no month/year provided
        $recipesTotalSales = StandardRecipe::find()
            ->where([
                'type_of_recipe' => $this->name,
                'business_id' => $this->business_id,
                'in_construction' => 0,
                'in_menu' => true
            ])->sum('sales');

        $combosTotalSales = Menu::find()
            ->where([
                'category_id' => $this->id,
                'business_id' => $this->business_id,
                'in_menu' => true
            ])->sum('sales');

        return $recipesTotalSales + $combosTotalSales;
    }

    public function getCpr($month = null, $year = null)
    {
        $totalSales = $this->getTotalSales($month, $year);

        $totalPcr = 0;
        $recipes = StandardRecipe::find()
            ->where([
                'type_of_recipe' => $this->name,
                'business_id' => $this->business_id,
                'in_construction' => 0,
                'in_menu' => true
            ])->all();

        $combos = Menu::find()
            ->where([
                'category_id' => $this->id,
                'business_id' => $this->business_id,
                'in_menu' => true
            ])->all();

        $totalPcr += array_sum(ArrayHelper::getColumn($recipes, function($recipe) use ($totalSales){
            return $recipe->getCpr($totalSales);
        }));
        $totalPcr += array_sum(ArrayHelper::getColumn($combos, function($combo) use ($totalSales){
            return $combo->getCpr($totalSales);
        }));

        return $totalPcr;
    }

    public function getSalesPercent($totalSales, $month = null, $year = null)
    {
        if(empty($totalSales)){
            return 0;
        }
        return round($this->getTotalSales($month, $year) / $totalSales, 2);
    }    public function getRecipes($type)
    {
        return StandardRecipe::find()
            ->where(['business_id' => $this->business_id])
            ->andWhere([
                'type' => isset($type) ? $type : \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_construction' => 0,
                'type_of_recipe' => $this->name
            ]);
    }

}
