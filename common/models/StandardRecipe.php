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
 * @property string $type_of_recipe [varchar(255)]
 * @property float $price [float]
 * @property int $convoy_id [int]
 * @property string $other_specs
 * @property float $sales [float]
 * @property-read null|string $mainImageUrl
 * @property-read string $name
 * @property-read array $recipeImagesId
 * @property-read null|array $mainImageId
 * @property-read float $costPercent
 * @property-read array $recipeImagesUrl
 * @property-read mixed $convoy
 * @property bool $in_menu [tinyint(1)]
 * @property float $custom_cost [float]
 * @property-read mixed $cost
 * @property float $custom_price [float]
 */
class StandardRecipe extends \yii\db\ActiveRecord
{
    const STANDARD_RECIPE_TYPE_MAIN = 'main';
    const STANDARD_RECIPE_TYPE_SUB = 'sub';

    public $mainImage;
    public $stepsImages;


    const QUADRANTS = [
        "ALTAALTA" => "ESTRELLA",
        "ALTABAJA" => "VACA",
        "BAJABAJA" => "PERRO",
        "BAJAALTA" => "ENIGMA",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'standard_recipe';
    }

    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        if (empty($record->custom_price)) {
            $record->custom_price = $record->price;
        }

        if (empty($record->custom_cost)) {
            $record->custom_cost = $record->cost;
        }

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
            [['business_id', 'convoy_id'], 'integer'],
            [['yield', 'yield_um', 'portions'], 'required', 'when' => function () {
                return !$this->isNewRecord;
            }, 'message' => "{attribute} no puede estar vacío"],
            [['flowchart', 'equipment', 'steps', 'allergies', 'title', 'time_of_preparation', 'yield_um', 'lifetime', 'type_of_recipe', 'other_specs'], 'string'],
            [['type', 'um'], 'string', 'max' => 255],
            [['type'], 'in', 'range' => [self::STANDARD_RECIPE_TYPE_MAIN, self::STANDARD_RECIPE_TYPE_SUB]],
            [['business_id'], 'exist', 'skipOnError' => true, 'targetClass' => Business::className(), 'targetAttribute' => ['business_id' => 'id']],
            [[
                'yield',
                'portions',
                'price',
                'sales',
                'custom_cost',
                'custom_price'
            ], 'number'],
            [['in_construction', 'in_menu'], 'boolean'],
            [['mainImage', 'stepsImages'], 'safe'],
            [['title', 'type', 'business_id'], 'unique', 'targetAttribute' => ['title', 'type', 'business_id'], 'message' => Yii::t('app', "This name is already taken")],

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
            'allergies' => Yii::t('app', 'Allergens'),
            'type' => Yii::t('app', 'Type'),
            'mainImage' => Yii::t('app', 'Recipe Image'),
            'stepsImages' => Yii::t('app', 'Steps images'),
            'type_of_recipe' => Yii::t('app', 'Type of Recipe'),
            'yield' => Yii::t('app', "Yield"),
            'yield_um' => Yii::t('app', 'Unit of measurement'),
            'price' => Yii::t('app', 'Price'),
            'convoy_id' => Yii::t('app', 'Convoy'),
            'other_specs' => Yii::t('app', 'Other specifications'),
            'sales' => Yii::t('app', 'Sales'),
            'in_menu' => Yii::t('app', 'In menu'),
            'title' => Yii::t('app', 'Title'),
            'time_of_preparation' => Yii::t('app', 'Time Of Preparation'),
            'um' => Yii::t('app', 'Unit of measurement'),
            'portions' => Yii::t('app', 'Porciones'),
            'lifetime' => Yii::t('app', 'Duración'),

        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->uploadImages();
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
            $model = new IngredientStandardRecipe([
                'ingredient_id' => intval($ingredientId),
                'quantity' => floatval($quantity),
                'standard_recipe_id' => intval($this->id)
            ]);
            $model->save();
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
        $ingredients = $this->ingredientRelations;
        $lastPrices = ArrayHelper::getColumn($ingredients, function ($ingredient) {
            return $ingredient->lastUnitPrice * $ingredient->quantity;
        });

        if (empty($this->portions)) {
            return array_sum($lastPrices);
        }

        return array_sum($lastPrices) / $this->portions;
    }

    public function getSubRecipeHigherPrice()
    {
        /** @var IngredientStandardRecipe[] $isr */
        $ingredients = $this->ingredientRelations;
        $lastPrices = ArrayHelper::getColumn($ingredients, function ($ingredient) {
            return $ingredient->higherUnitPrice * $ingredient->quantity;
        });

        if (empty($this->portions)) {
            return array_sum($lastPrices);
        }

        return array_sum($lastPrices) / $this->portions;

    }

    public function getSubRecipeAvgPrice()
    {
        $ingredients = $this->ingredientRelations;
        $lastPrices = ArrayHelper::getColumn($ingredients, function ($ingredient) {
            return $ingredient->avgUnitPrice * $ingredient->quantity;
        });

        if (empty($this->portions)) {
            return array_sum($lastPrices);
        }

        return array_sum($lastPrices) / $this->portions;

    }

    public function getRecipeLastPrice()
    {
        $ingredients = $this->ingredientRelations;
        $lastPrices = ArrayHelper::getColumn($ingredients, function ($ingredient) {
            return $ingredient->lastUnitPrice * $ingredient->quantity;
        });


        $subRecipes = $this->getSubStandardRecipes()->all();
        $lastPrices = array_merge($lastPrices, ArrayHelper::getColumn($subRecipes, function (StandardRecipe $subRecipe) {
            return $subRecipe->custom_cost * $subRecipe->getQuantityLinked($this->id);
        }));

        if (!empty($this->convoy_id)) {
            $lastPrices[] = $this->convoy->amount;
        }
        if (empty($this->portions)) {
            return array_sum($lastPrices);
        }
        return array_sum($lastPrices) / $this->portions / $this->yield;
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
        foreach ($images as $image) {
            if (($image->getPrimaryKey() != null || $image->getPrimaryKey() > 0) && $image->isMain == 0) {
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
        foreach ($images as $image) {
            if (($image->getPrimaryKey() != null || $image->getPrimaryKey() > 0) && $image->isMain == 0) {
                $ids[] = ['key' => $image->id];
            }
        }

        return $ids;
    }

    public function getMainImageUrl()
    {
        /** @var Image[] $images */
        $image = $this->getImage();
        if (($image->getPrimaryKey() != null || $image->getPrimaryKey() > 0) && $image->isMain == 1) {
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

    public function getCostPercent($custom = false)
    {
        if (empty($this->price)) {
            return 0.0;
        }

        if ($custom) {
            return round(($this->custom_cost / $this->custom_price), 2);
        }

        return round(($this->recipeLastPrice / $this->price), 2);
    }

    public function getCategory()
    {
        return $this->hasOne(RecipeCategory::class, ['name' => 'type_of_recipe']);
    }

    public function getName()
    {
        return $this->title;
    }

    public function getConvoy()
    {
        return $this->hasOne(Convoy::class, ['id' => 'convoy_id']);
    }

    public function getCost()
    {
        return $this->recipeLastPrice;
    }

    public function getSalesPercent($totalSales)
    {

        if (empty($totalSales)) {
            return 0;
        }

        return round($this->sales / $totalSales, 2);
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

    public function getCpr($totalSales = 0)
    {
        return $this->costPercent * $this->getSalesPercent($totalSales);
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


        $popularity = $this->getPopularity($popularityAxis, $totalSales);
        $effectiveness = $this->getEffectiveness($costEffectivenessAxis, $totalSales);

        return self::QUADRANTS[$popularity . $effectiveness];
    }

    public function getIngredientsStandardRecipe()
    {
        return $this->hasMany(IngredientStandardRecipe::class, ['standard_recipe_id' => 'id']);
    }

    public function duplicate()
    {
        $newRecipe = new StandardRecipe();
        $newRecipe->attributes = $this->attributes;
        $newRecipe->title = $this->title;

        $splitText = explode(" ", $newRecipe->title);

        if (is_numeric(end($splitText))) {
            $iteration = intval(end($splitText)) + 1;
            array_pop($splitText);
            $newRecipe->title = implode(" ", $splitText);
        } else {
            $iteration = 1;
            $newRecipe->title .= " (Copia)";
        }

        while (StandardRecipe::find()->where(['title' => $newRecipe->title . " $iteration"])->exists()) {
            $iteration++;
        }

        $newRecipe->title .= " $iteration";

        $newRecipe->save();

        /// Copy ingredients
        $ingredients = (new Query())
            ->select(['ingredient_id', 'quantity', 'standard_recipe_id'])
            ->from("ingredient_standard_recipe")
            ->where(['standard_recipe_id' => $this->id])
            ->all();

        foreach ($ingredients as $ingredient) {
            $ingredient['standard_recipe_id'] = $newRecipe->id;
            Yii::$app->db->createCommand()
                ->insert('ingredient_standard_recipe', $ingredient)
                ->execute();
        }

        /// Copy sub recipes
        $subRecipes = (new Query())
            ->select(['sub_standard_recipe_id', 'quantity', 'standard_recipe_id'])
            ->from("standard_recipe_sub_standard_recipe")
            ->where(['standard_recipe_id' => $this->id])
            ->all();

        foreach ($subRecipes as $subRecipe) {
            $subRecipe['standard_recipe_id'] = $newRecipe->id;
            Yii::$app->db->createCommand()
                ->insert('standard_recipe_sub_standard_recipe', $subRecipe)
                ->execute();
        }


        // Copy main steps pictures
        $mainStepsPictures = (new Query())
            ->select(['standard_recipe_id', 'description'])
            ->from("main_steps_pictures")
            ->where(['standard_recipe_id' => $this->id])
            ->all();

        foreach ($mainStepsPictures as $mainStepsPicture) {
            $mainStepsPicture['standard_recipe_id'] = $newRecipe->id;
            Yii::$app->db->createCommand()
                ->insert('main_steps_pictures', $mainStepsPicture)
                ->execute();
        }


        return $newRecipe;
    }

}
