<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ingredient_standard_recipe".
 *
 * @property int $id
 * @property int $standard_recipe_id
 * @property int $ingredient_id
 * @property float|null $quantity
 *
 * @property IngredientStock $ingredient
 * @property StandardRecipe $standardRecipe
 */
class IngredientStandardRecipe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ingredient_standard_recipe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['standard_recipe_id', 'ingredient_id'], 'required'],
            [['standard_recipe_id', 'ingredient_id'], 'integer'],
            [['quantity'], 'number'],
            [['ingredient_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ingredient::className(), 'targetAttribute' => ['ingredient_id' => 'id']],
            [['standard_recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => StandardRecipe::className(), 'targetAttribute' => ['standard_recipe_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'standard_recipe_id' => 'Standard Recipe ID',
            'ingredient_id' => 'Ingredient ID',
            'quantity' => 'Quantity',
        ];
    }

    /**
     * Gets query for [[Ingredient]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIngredient()
    {
        return $this->hasOne(IngredientStock::class, ['id' => 'ingredient_id']);
    }

    /**
     * Gets query for [[StandardRecipe]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStandardRecipe()
    {
        return $this->hasOne(StandardRecipe::className(), ['id' => 'standard_recipe_id']);
    }

    public function getFormattedPrice(){
        return Yii::$app->formatter->asCurrency(0, 'usd');
    }

    public function getLastPrice(){
        return $this->ingredient->lastPrice;
    }

    public function getAvgPrice(){
        return $this->ingredient->avgPrice;
    }

    public function getHigherPrice(){
        return $this->ingredient->higherPrice;
    }

    public function getLastUnitPrice(){
        return $this->ingredient->lastUnitPrice;
    }

    public function getAvgUnitPrice(){
        return $this->ingredient->avgUnitPrice;
    }

    public function getHigherUnitPrice(){
        return $this->ingredient->higherUnitPrice;
    }
}
