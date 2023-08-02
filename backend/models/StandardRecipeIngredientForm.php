<?php

namespace backend\models;

use common\models\IngredientStock;
use common\models\StandardRecipe;

class StandardRecipeIngredientForm extends \yii\base\Model
{
    public $ingredientId;
    public $subRecipeId;
    public $quantity;

    public function rules()
    {
        return [
            [['quantity'], 'required'],
            [['ingredientId', 'subRecipeId'], 'integer'],
            [['quantity'], 'number'],
            [['ingredientId'], 'exist', 'targetClass' => IngredientStock::class, 'targetAttribute' => ['ingredientId' => 'id']],
            [['subRecipeId'], 'exist', 'targetClass' => StandardRecipe::class, 'targetAttribute' => ['subRecipeId' => 'id']],
            [['ingredientId', 'subRecipeId'], 'validateIngredientSubRecipe']
        ];
    }

    public function validateIngredientSubRecipe($attr)
    {
        if (!empty($this->ingredientId) && !empty($this->subRecipeId)) {
            $this->addError('ingredientId', \Yii::t('app', "You cannot select ingredient and subrecipe at the same time"));
            $this->addError('subRecipeId', \Yii::t('app', "You cannot select ingredient and subrecipe at the same time"));
            return false;
        }

        if (empty($this->ingredientId) && empty($this->subRecipeId)) {
            $this->addError('ingredientId', \Yii::t('app', "You must select an ingredient or a sub recipe"));
            $this->addError('subRecipeId', \Yii::t('app', "You must select an ingredient or a sub recipe"));
            return false;
        }

        return true;
    }

    public function attributeLabels()
    {
        return [
            'ingredientId' => \Yii::t('app', 'Ingredient'),
            'subRecipeId' => \Yii::t('app', 'Sub recipe'),
            'quantity' => \Yii::t('app', 'Quantity')
        ];
    }
}
