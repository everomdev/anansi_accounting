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
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'business_id'], 'required'],
            [['total_cost', 'total_price', 'cost_precent', 'cost_percent_last_price', 'cost_percent_higher_price', 'cost_percent_avg_price'], 'number'],
            [['business_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [['_recipes'], 'safe']
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
            'total_cost' => 'Total Cost',
            'total_price' => 'Total Price',
            'cost_precent' => 'Cost Precent',
            'business_id' => 'Business ID',
        ];
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
                    'total_cost_avg_price' => $totalCostByAvgPrice,
                    'total_cost_higher_price' => $totalCostByHigherPrice,
                    'total_cost_last_price' => $totalCostByLastPrice,
                    'cost_percent_avg_price' => round($totalCostByAvgPrice / $this->total_price, 2) * 100,
                    'cost_percent_last_price' => round($totalCostByLastPrice / $this->total_price, 2) * 100,
                    'cost_percent_higher_price' => round($totalCostByHigherPrice / $this->total_price, 2) * 100
                ],
                ['id' => $this->id]
            )
            ->execute();
    }


}
