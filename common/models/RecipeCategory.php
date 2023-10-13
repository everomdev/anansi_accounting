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
            [['business_id'], 'integer'],
            [['name', 'type'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['type'], 'in', 'range' => [self::TYPE_MAIN, self::TYPE_SUB]]
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

    public function getTotalSales()
    {
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

    public function getCpr()
    {
        $totalSales = $this->getTotalSales();

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

    public function getSalesPercent($totalSales)
    {
        if(empty($totalSales)){
            return 0;
        }
        return round($this->getTotalSales() / $totalSales, 2);
    }

    public function getRecipes()
    {
        return StandardRecipe::find()
            ->where(['business_id' => $this->business_id])
            ->andWhere([
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_construction' => 0,
                'type_of_recipe' => $this->name
            ]);
    }

}
