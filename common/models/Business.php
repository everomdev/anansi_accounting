<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;

/**
 * This is the model class for table "business".
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 *
 * @property User $user
 * @property IngredientStock[] $ingredientStocks
 * @property Menu[] $menus
 * @property StandardRecipe[] $standardRecipes
 * @property string $currency_code [varchar(3)]
 * @property string $decimal_separator [varchar(1)]
 * @property string $thousands_separator [varchar(1)]
 * @property string $timezone [varchar(255)]
 * @property string $locale [varchar(255)]
 * @property int $monthly_plate_sales [int]
 */
class Business extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'business';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'name',
                'user_id',
            ], 'required'],
            [['user_id', 'monthly_plate_sales'], 'integer'],
            [[
                'name',
                'currency_code',
                'decimal_separator',
                'thousands_separator',
                'timezone',
                'locale',
            ], 'string', 'max' => 255],

            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
            'monthly_plate_sales' => Yii::t('app', "Plate sales / Month"),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->initUm();
            $this->initRecipeCategories();
            $this->initConsumptionCenters();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[IngredientStocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIngredientStocks()
    {
        return $this->hasMany(IngredientStock::className(), ['business_id' => 'id']);
    }

    /**
     * Gets query for [[Menus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::className(), ['business_id' => 'id']);
    }

    /**
     * Gets query for [[StandardRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStandardRecipes()
    {
        return $this->hasMany(StandardRecipe::className(), ['business_id' => 'id']);
    }

    private function initUm()
    {
        $data = [
            ["Kilogramo", $this->id],
            ["Litro", $this->id],
            ["Pieza", $this->id],
            ["Paquete", $this->id],
            ["Rebanada", $this->id],
            ["Ración", $this->id],
            ["Onza", $this->id],
            ["Libra", $this->id],
            ["Gramo", $this->id],
            ["Taza", $this->id],
            ["Cucharadita", $this->id],
            ["Cucharada", $this->id],
            ["Mililitro", $this->id],
            ["Pizca", $this->id],
            ["Botella", $this->id],
            ["Gota", $this->id],
        ];
        Yii::$app->db->createCommand()
            ->batchInsert('unit_of_measurement', ['name', 'business_id'], $data)
            ->execute();
    }

    public function initRecipeCategories()
    {
        $data = [
            ["Salsas", $this->id, RecipeCategory::TYPE_SUB],
            ["Fondos", $this->id, RecipeCategory::TYPE_SUB],
            ["Bases", $this->id, RecipeCategory::TYPE_SUB],
            ["Guarnición", $this->id, RecipeCategory::TYPE_SUB],
            ["Masas", $this->id, RecipeCategory::TYPE_SUB],
            ["Sopas", $this->id, RecipeCategory::TYPE_MAIN],
            ["Ensaladas", $this->id, RecipeCategory::TYPE_MAIN],
            ["Aves", $this->id, RecipeCategory::TYPE_MAIN],
            ["Carnes", $this->id, RecipeCategory::TYPE_MAIN],
            ["Plato Fuerte", $this->id, RecipeCategory::TYPE_MAIN],
            ["Especialidad", $this->id, RecipeCategory::TYPE_MAIN],
            ["Pescado", $this->id, RecipeCategory::TYPE_MAIN],
            ["Postres", $this->id, RecipeCategory::TYPE_MAIN],
            ["Vegetariano", $this->id, RecipeCategory::TYPE_MAIN],
            ["Hamburguesas", $this->id, RecipeCategory::TYPE_MAIN]
        ];

        Yii::$app->db->createCommand()
            ->batchInsert('recipe_category', ['name', 'business_id', 'type'], $data)
            ->execute();
    }

    public function initConsumptionCenters()
    {
        $data = [
            ["Cocina", $this->id]
        ];
        Yii::$app->db->createCommand()
            ->batchInsert('consumption_center', ['name', 'business_id'], $data)
            ->execute();

    }

    public function getFormatter()
    {
        return new Formatter([
            'decimalSeparator' => $this->decimal_separator,
            'thousandSeparator' => $this->thousands_separator,
            'defaultTimeZone' => $this->timezone,
            'locale' => $this->locale,
            'currencyCode' => $this->currency_code,
            'currencyDecimalSeparator' => $this->decimal_separator,
        ]);
    }

    public function getRecipeCategories()
    {
        return $this->hasMany(RecipeCategory::class, ['business_id' => 'id']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('user_business', ['business_id' => 'id']);
    }

    public function getTheoreticalYield()
    {
        $categories = RecipeCategory::find()
            ->where([
                'business_id' => $this->id
            ])->all();

        $totalSales = 0;
        $total = 0;
        $data = [];
        foreach ($categories as $category) {
            $recipes = StandardRecipe::find()->where([
                'business_id' => $this->id,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
                'type_of_recipe' => $category->name
            ])->all();

            $combos = Menu::find()->where([
                'business_id' => $this->id,
                'in_menu' => true,
                'category_id' => $category->id
            ])->all();
            if (empty($recipes) && empty($combos)) {
                continue;
            } else {
                if (!empty($recipes)) {
                    $totalSales += array_sum(ArrayHelper::getColumn($recipes, 'sales'));
                    $total += count($recipes);
                }
                if (!empty($combos)) {
                    $totalSales += array_sum(ArrayHelper::getColumn($combos, 'sales'));
                    $total += count($combos);
                }


                $data[] = [
                    'category' => $category,
                    'recipes' => $recipes,
                    'combos' => $combos,
                ];
            }
        }

        $totalPcr = 0;
        $totalCost = 0;
        array_walk($data, function ($el) use (&$totalPcr, $totalSales) {
            $totalPcr += array_sum(ArrayHelper::getColumn($el['recipes'], function ($recipe) use ($totalSales) {
                return $recipe->getCpr($totalSales);
            }));
            $totalPcr += array_sum(ArrayHelper::getColumn($el['combos'], function ($combo) use ($totalSales) {
                return $combo->getCpr($totalSales);
            }));
        });

        array_walk($data, function ($el) use (&$totalCost, $totalSales) {
            $totalCost += array_sum(ArrayHelper::getColumn($el['recipes'], function ($recipe) use ($totalSales) {
                return $recipe->costPercent;
            }));
            $totalCost += array_sum(ArrayHelper::getColumn($el['combos'], function ($combo) use ($totalSales) {
                return $combo->costPercent;
            }));
        });

        if ($total != 0) {
            $totalCost = $totalCost / $total;
        } else {
            $totalCost = 0;
        }

        return ['data' => $data, 'totalCost' => $totalCost];
    }

    public function getRealYield()
    {
        $categories = RecipeCategory::find()
            ->where([
                'business_id' => $this->id
            ])->all();

        $totalSales = 0;
        $data = [];
        foreach ($categories as $category) {
            $recipes = StandardRecipe::find()->where([
                'business_id' => $this->id,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
                'type_of_recipe' => $category->name
            ])->all();

            $combos = Menu::find()->where([
                'business_id' => $this->id,
                'in_menu' => true,
                'category_id' => $category->id
            ])->all();
            if (empty($recipes) && empty($combos)) {
                continue;
            } else {
                if (!empty($recipes)) {
                    $totalSales += array_sum(ArrayHelper::getColumn($recipes, 'sales'));
                }
                if (!empty($combos)) {
                    $totalSales += array_sum(ArrayHelper::getColumn($combos, 'sales'));
                }


                $data[] = [
                    'category' => $category,
                    'recipes' => $recipes,
                    'combos' => $combos,
                ];
            }
        }

        $totalPcr = 0;
        array_walk($data, function ($el) use (&$totalPcr, $totalSales) {
            $totalPcr += array_sum(ArrayHelper::getColumn($el['recipes'], function ($recipe) use ($totalSales) {
                return $recipe->getCpr($totalSales);
            }));
            $totalPcr += array_sum(ArrayHelper::getColumn($el['combos'], function ($combo) use ($totalSales) {
                return $combo->getCpr($totalSales);
            }));
        });

        return ['data' => $data, 'totalPcr' => $totalPcr, 'totalSales' => $totalSales];
    }

    public function getBcgData($type = 'all')
    {
        $recipes = StandardRecipe::find()
            ->where([
                'business_id' => $this->id,
                'in_menu' => true,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN
            ]);
        $combos = Menu::find()
            ->innerJoin('recipe_category', 'recipe_category.id=menu.category_id')
            ->where([
                'menu.business_id' => $this->id,
                'in_menu' => true,
            ]);

        if ($type != 'all') {
            $recipes->andWhere(['type_of_recipe' => $type]);
            $combos->andWhere(['recipe_category.name' => $type]);
        }

        $recipes = $recipes->all();
        $combos = $combos->all();

        $data = array_merge($recipes, $combos);

        $totalSales = array_sum(ArrayHelper::getColumn($data, 'sales'));

        return [
            "data" => $data,
            'business' => $this,
            'totalSales' => $totalSales,
            'type' => $type
        ];
    }

    public function getMovements()
    {
        return $this->hasMany(Movement::class, ['business_id' => 'id']);
    }

    public function getConsumptionCenters()
    {
        return $this->hasMany(ConsumptionCenter::class, ['business_id' => 'id']);
    }

    public function getProviders()
    {
        return $this->hasMany(Provider::class, ['business_id' => 'id']);
    }
}
