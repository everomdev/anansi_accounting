<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "menu".
 *
 * @property int $id
 * @property string $name
 * @property float|null $total_cost
 * @property float|null $total_price
 * @property float|null $cost_precent
 * @property int $business_id
 *
 * @property Business $business
 * @property StandardRecipe[] $standardRecipes
 * @property float $total_cost_last_price [float]
 * @property float $total_cost_avg_price [float]
 * @property float $total_cost_higher_price [float]
 * @property float $cost_percent_last_price [float]
 * @property float $cost_percent_avg_price [float]
 * @property float $cost_percent_higher_price [float]
 * @property float $sales [float]
 * @property-read mixed $totalCostByAvgPrice
 * @property-read mixed $totalCostByHigherPrice
 * @property-read mixed $totalCostByLastPrice
 * @property bool $in_menu [tinyint(1)]
 * @property int $category_id [int]
 * @property float $custom_cost [float]
 * @property-read string $title
 * @property-read mixed $category
 * @property-read float $costPercent
 * @property-read null|float $price
 * @property-read float $cost
 * @property float $custom_price [float]
 */
class Menu extends \yii\db\ActiveRecord
{
    public $_recipes = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu';
    }

    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $record->_recipes = ArrayHelper::getColumn($record->standardRecipes, 'id');
        if (empty($record->custom_price)) {
            $record->custom_price = $record->price;
        }

        if (empty($record->custom_cost)) {
            $record->custom_cost = $record->cost;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'business_id'], 'required'],
            [['total_cost', 'total_price', 'cost_precent', 'cost_percent_last_price', 'cost_percent_higher_price', 'cost_percent_avg_price', 'sales', 'custom_cost', 'custom_price'], 'number'],
            [['business_id', 'category_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['_recipes'], 'safe'],
            [['in_menu'], 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'total_cost' => 'Costo total',
            'total_price' => 'Precio total',
            'cost_precent' => 'Porcentaje de costo',
            'business_id' => 'Business ID',
            'category_id' => Yii::t('app', "Category"),
            '_recipes' => Yii::t('app', "Recetas"),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->in_menu = true;
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->unlinkAll('standardRecipes', true);
        foreach ($this->_recipes as $recipeId) {
            $recipe = StandardRecipe::findOne(['id' => $recipeId]);
            $this->link('standardRecipes', $recipe);
        }
        $this->updatePrices();
        parent::afterSave($insert, $changedAttributes);
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

    /**
     * Gets query for [[MenuStandardRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStandardRecipes()
    {
        return $this->hasMany(StandardRecipe::className(), ['id' => 'standard_recipe_id'])
            ->viaTable('menu_standard_recipe', ['menu_id' => 'id']);
    }

    public function getCategory()
    {
        return $this->hasOne(RecipeCategory::class, ['id' => 'category_id']);
    }

    public function addRemoveRecipe($recipeId)
    {
        $recipe = $this->getStandardRecipes()->andWhere(['id' => $recipeId])->one();
        if ($recipe) {
            $this->unlink("standardRecipes", $recipe, true);
        } else {
            $recipe = StandardRecipe::findOne(['id' => $recipeId]);
            $this->link("standardRecipes", $recipe);
        }
    }

    public function getTotalCostByLastPrice()
    {
        $recipes = $this->standardRecipes;

        return array_sum(ArrayHelper::getColumn($recipes, 'lastPrice'));
    }

    public function getTotalCostByHigherPrice()
    {
        $recipes = $this->standardRecipes;

        return array_sum(ArrayHelper::getColumn($recipes, 'higherPrice'));
    }

    public function getTotalCostByAvgPrice()
    {
        $recipes = $this->standardRecipes;

        return array_sum(ArrayHelper::getColumn($recipes, 'avgPrice'));
    }

    public function updatePrices()
    {
        $totalCostByAvgPrice = $this->getTotalCostByAvgPrice();
        $totalCostByHigherPrice = $this->getTotalCostByHigherPrice();
        $totalCostByLastPrice = $this->getTotalCostByLastPrice();
        Yii::$app->db->createCommand()
            ->update(
                "menu",
                [
                    'total_cost' => $totalCostByLastPrice,
                    'cost_precent' => round($totalCostByLastPrice / $this->total_price, 2),
                    'total_cost_avg_price' => $totalCostByAvgPrice,
                    'total_cost_higher_price' => $totalCostByHigherPrice,
                    'total_cost_last_price' => $totalCostByLastPrice,
                    'cost_percent_avg_price' => round($totalCostByAvgPrice / $this->total_price, 2),
                    'cost_percent_last_price' => round($totalCostByLastPrice / $this->total_price, 2),
                    'cost_percent_higher_price' => round($totalCostByHigherPrice / $this->total_price, 2)
                ],
                ['id' => $this->id]
            )
            ->execute();
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function getCost()
    {
        return $this->total_cost_last_price;
    }

    public function getCostPercent($custom = false)
    {
        if ($custom) {
            return round(($this->custom_cost / $this->custom_price), 2);
        }
        return $this->cost_percent_last_price;
    }

    public function getSalesPercent($totalSales)
    {
        if (empty($totalSales)) {
            return 0;
        }

        return round($this->sales / $totalSales, 2);
    }

    public function getCpr($totalSales = 0)
    {
        return $this->costPercent * $this->getSalesPercent($totalSales);
    }

    public function getPrice()
    {
        return $this->total_price;
    }

    public function getPopularity($popularityAxis, $totalSales)
    {
        $salesPercent = $this->getSalesPercent($totalSales);
        if ($salesPercent >= $popularityAxis) {
            return 'ALTA';
        } else {
            return 'BAJA';
        }
    }

    public function getEffectiveness($costEffectivenessAxis, $totalSales)
    {
        $retributionMargin = $this->sales - $this->cost;
        if ($retributionMargin >= $costEffectivenessAxis) {
            return 'ALTA';
        } else {
            return 'BAJA';
        }
    }

    public function getBcg($popularityAxis, $costEffectivenessAxis, $totalSales)
    {
        $quadrants = [
            "ALTAALTA" => "ESTRELLA",
            "ALTABAJA" => "VACA",
            "BAJABAJA" => "PERRO",
            "BAJAALTA" => "ENIGMA",
        ];

        $popularity = $this->getPopularity($popularityAxis, $totalSales);
        $effectiveness = $this->getEffectiveness($costEffectivenessAxis, $totalSales);

        return $quadrants[$popularity . $effectiveness];

    }

    public function getSalesAmount()
    {
        return $this->sales * $this->price;
    }

    public function getSalesAmountPercent($totalSalesAmount)
    {
        if (empty($totalSalesAmount)) {
            return 0;
        }

        return round($this->salesAmount / $totalSalesAmount, 2);
    }
}
