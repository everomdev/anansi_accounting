<?php

namespace common\models;

use rico\yii2images\behaviors\ImageBehave;
use rico\yii2images\models\Image;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "standard_recipe".
 *
 * @property int $id
 * @property int $business_id
 * @property string|null $flowchart
 * @property string|null $equipment
 * @property string|null $allergies
 * @property string|null $steps
 * @property string $type
 *
 * @property IngredientStandardRecipe[] $ingredientStandardRecipes
 * @property MainStepsPictures[] $mainStepsPictures
 * @property MenuStandardRecipe[] $menuStandardRecipes
 * @property Business $business
 * @property StandardRecipeSubStandardRecipe[] $standardRecipeSubStandardRecipes
 * @property StandardRecipeSubStandardRecipe[] $standardRecipeSubStandardRecipes0
 * @property string $title [varchar(255)]
 * @property string $time_of_preparation [varchar(255)]
 * @property float $yield [float]
 * @property string $yield_um [varchar(255)]
 * @property float $portions [varchar(255)]
 * @property string $lifetime [varchar(255)]
 * @property-read mixed $higherPrice
 * @property-read mixed $lastPrice
 * @property-read mixed $subStandardRecipes
 * @property-read mixed $subRecipeLastPrice
 * @property-read \yii\db\ActiveQuery $ingredients
 * @property-read mixed $recipeHigherPrice
 * @property-read mixed $formattedType
 * @property-read mixed $ingredientRelations
 * @property-read mixed $recipeAvgPrice
 * @property-read mixed $subRecipeHigherPrice
 * @property-read mixed $avgPrice
 * @property-read mixed $subRecipeAvgPrice
 * @property-read mixed $recipeLastPrice
 * @property-read RecipeStep[] $recipeSteps
 * @property bool $in_construction [tinyint(1)]
 */
class StandardRecipe extends \yii\db\ActiveRecord
{
    const STANDARD_RECIPE_TYPE_MAIN = 'main';
    const STANDARD_RECIPE_TYPE_SUB = 'sub';

    public $mainImage;
    public $stepsImages;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'standard_recipe';
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => ImageBehave::class,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_id', 'type', 'title'], 'required'],
            [['business_id'], 'integer'],
            [['yield', 'yield_um', 'portions'], 'required', 'when' => function(){
                return !$this->isNewRecord;
            }],
            [['flowchart', 'equipment', 'steps', 'allergies', 'title', 'time_of_preparation', 'yield_um', 'lifetime',], 'string'],
            [['type'], 'string', 'max' => 255],
            [['type'], 'in', 'range' => [self::STANDARD_RECIPE_TYPE_MAIN, self::STANDARD_RECIPE_TYPE_SUB]],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [[
                'yield',
                'portions',
            ], 'number'],
            [['in_construction'], 'boolean'],
            [['mainImage', 'stepsImages'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_id' => 'Business ID',
            'flowchart' => Yii::t('app', 'Flowchart'),
            'equipment' => Yii::t('app', 'Equipment'),
            'steps' => Yii::t('app', 'Steps or special cares'),
            'allergies' => Yii::t('app', 'Allergies'),
            'type' => Yii::t('app', 'Type'),
            'mainImage' => Yii::t('app', 'Recipe Image'),
            'stepsImages' => Yii::t('app', 'Steps images'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->uploadImages();
    }

    /**
     * Gets query for [[IngredientStandardRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIngredients()
    {
        return $this->hasMany(IngredientStock::className(), ['id' => 'ingredient_id'])
            ->viaTable('ingredient_standard_recipe', ['standard_recipe_id' => 'id']);
    }

    public function getIngredientRelations()
    {
        return $this->hasMany(IngredientStandardRecipe::class, ['standard_recipe_id' => 'id']);
    }

    public function getSubStandardRecipes()
    {
        return StandardRecipe::find()
            ->innerJoin('standard_recipe_sub_standard_recipe', 'standard_recipe_sub_standard_recipe.sub_standard_recipe_id=standard_recipe.id')
            ->where(['standard_recipe_sub_standard_recipe.standard_recipe_id' => $this->id])
            ->andWhere(['type' => self::STANDARD_RECIPE_TYPE_SUB]);
    }

    /**
     * Gets query for [[MainStepsPictures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMainStepsPictures()
    {
        return $this->hasMany(MainStepsPicture::className(), ['standard_recipe_id' => 'id']);
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
     * @return ActiveQuery | RecipeStep[]
     */
    public function getRecipeSteps()
    {
        return $this->hasMany(RecipeStep::class, ['recipe_id' => 'id']);
    }

    public function addUpdateIngredient($ingredientId, $quantity = 1)
    {
        $isLinked = $this->getIngredientRelations()
            ->where([
                'ingredient_id' => $ingredientId,
            ])
            ->exists();

        if ($isLinked) {
            Yii::$app->db->createCommand()
                ->update(
                    'ingredient_standard_recipe',
                    [
                        'quantity' => $quantity
                    ],
                    [
                        'ingredient_id' => $ingredientId,
                        'standard_recipe_id' => $this->id
                    ]
                )
                ->execute();
        } else {
            Yii::$app->db->createCommand()
                ->insert(
                    'ingredient_standard_recipe',
                    ['ingredient_id' => $ingredientId, 'quantity' => $quantity, 'standard_recipe_id' => $this->id],
                )
                ->execute();
        }
    }

    public function addUpdateSubRecipe($recipeId, $quantity)
    {
        $isLinked = $this->getSubStandardRecipes()
            ->andWhere(['standard_recipe.id' => $recipeId])
            ->exists();

        if ($isLinked) {
            Yii::$app->db->createCommand()
                ->update(
                    'standard_recipe_sub_standard_recipe',
                    [
                        'quantity' => $quantity
                    ],
                    [
                        'sub_standard_recipe_id' => $recipeId,
                        'standard_recipe_id' => $this->id
                    ]
                )
                ->execute();
        } else {
            Yii::$app->db->createCommand()
                ->insert(
                    'standard_recipe_sub_standard_recipe',
                    ['sub_standard_recipe_id' => $recipeId, 'quantity' => $quantity, 'standard_recipe_id' => $this->id],
                )
                ->execute();
        }
    }

    public function removeIngredient($id)
    {
        Yii::$app->db->createCommand()
            ->delete(
                'ingredient_standard_recipe',
                ['ingredient_id' => $id, 'standard_recipe_id' => $this->id]
            )->execute();
    }

    public function removeSubRecipe($id)
    {
        Yii::$app->db->createCommand()
            ->delete(
                'standard_recipe_sub_standard_recipe',
                ['sub_standard_recipe_id' => $id, 'standard_recipe_id' => $this->id]
            )->execute();
    }

    public function getQuantityLinked($recipeId)
    {
        $data = (new Query())
            ->select("*")
            ->from("standard_recipe_sub_standard_recipe srssr")
            ->where(['srssr.standard_recipe_id' => $recipeId])
            ->andWhere(['srssr.sub_standard_recipe_id' => $this->id])
            ->one();
        return empty($data) ? 0 : $data['quantity'];
    }

    public function getSubRecipeLastPrice()
    {
        $ingredients = $this->getIngredients()->all();
        $lastPrices = ArrayHelper::getColumn($ingredients, 'lastUnitPrice');
        return array_sum($lastPrices);
    }

    public function getSubRecipeHigherPrice()
    {
        $ingredients = $this->getIngredients()->all();
        $higherPrices = ArrayHelper::getColumn($ingredients, 'higherUnitPrice');
        return array_sum($higherPrices);
    }

    public function getSubRecipeAvgPrice()
    {
        $ingredients = $this->getIngredients()->all();
        $avgPrices = ArrayHelper::getColumn($ingredients, 'avgUnitPrice');
        return array_sum($avgPrices);
    }

    public function getRecipeLastPrice()
    {
        $ingredients = $this->getIngredients()->all();
        $lastPrices = ArrayHelper::getColumn($ingredients, 'lastUnitPrice');

        $subRecipes = $this->getSubStandardRecipes()->all();
        $lastPrices = array_merge($lastPrices, ArrayHelper::getColumn($subRecipes, 'subRecipeLastPrice'));

        return array_sum($lastPrices);
    }

    public function getRecipeHigherPrice()
    {
        $ingredients = $this->getIngredients()->all();
        $higherPrices = ArrayHelper::getColumn($ingredients, 'higherUnitPrice');

        $subRecipes = $this->getSubStandardRecipes()->all();
        $higherPrices = array_merge($higherPrices, ArrayHelper::getColumn($subRecipes, 'subRecipeHigherPrice'));

        return array_sum($higherPrices);
    }

    public function getRecipeAvgPrice()
    {
        $ingredients = $this->getIngredients()->all();
        $avgPrices = ArrayHelper::getColumn($ingredients, 'avgUnitPrice');

        $subRecipes = $this->getSubStandardRecipes()->all();
        $avgPrices = array_merge($avgPrices, ArrayHelper::getColumn($subRecipes, 'subRecipeAvgPrice'));

        return array_sum($avgPrices);
    }

    public function getLastPrice()
    {
        if ($this->type == self::STANDARD_RECIPE_TYPE_MAIN) {
            return $this->getRecipeLastPrice();
        } else {
            return $this->getSubRecipeLastPrice();
        }
    }

    public function getHigherPrice()
    {
        if ($this->type == self::STANDARD_RECIPE_TYPE_MAIN) {
            return $this->getSubRecipeHigherPrice();
        } else {
            return $this->getSubRecipeHigherPrice();
        }
    }

    public function getAvgPrice()
    {
        if ($this->type == self::STANDARD_RECIPE_TYPE_MAIN) {
            return $this->getRecipeAvgPrice();
        } else {
            return $this->getSubRecipeAvgPrice();
        }
    }

    public function getFormattedType()
    {
        if ($this->type == self::STANDARD_RECIPE_TYPE_MAIN) {
            return Yii::t('app', 'Recipe');
        } else {
            return Yii::t('app', 'Sub Recipe');
        }
    }

    public function uploadImages()
    {
        $_mainImage = UploadedFile::getInstance($this, 'mainImage');
        if ($_mainImage) {
            $image = $this->attachImage($_mainImage->tempName, true);
            $this->setMainImage($image);
        }

        $_stepsImages = UploadedFile::getInstances($this, 'stepsImages');
        if ($_stepsImages) {
            foreach ($_stepsImages as $image) {
                $this->attachImage($image->tempName, false);
            }
        }
    }

    public function getRecipeImagesUrl()
    {
        /** @var Image[] $images */
        $images = $this->getImages();
        $urls = [];
        foreach ($images as $image){
            if(($image->getPrimaryKey() != null || $image->getPrimaryKey() > 0) && $image->isMain == 0){
                $urls[] = Yii::$app->request->hostInfo . $image->getUrl(400);
            }
        }

        return $urls;
    }

    public function getRecipeImagesId()
    {
        /** @var Image[] $images */
        $images = $this->getImages();
        $ids = [];
        foreach ($images as $image){
            if(($image->getPrimaryKey() != null || $image->getPrimaryKey() > 0) && $image->isMain == 0){
                $ids[] = ['key' => $image->id];
            }
        }

        return $ids;
    }
    public function getMainImageUrl()
    {
        /** @var Image[] $images */
        $image = $this->getImage();
        if(($image->getPrimaryKey() != null || $image->getPrimaryKey() > 0) && $image->isMain == 1){
            return Yii::$app->request->hostInfo . $image->getUrl(400);
        }
        return null;
    }

    public function getMainImageId()
    {
        /** @var Image $image */
        $image = $this->getImage();

        return ($image) ? ['key' => $image->id] : null;
    }


}
