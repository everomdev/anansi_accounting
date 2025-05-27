<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;
use common\models\MonthlySales;

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
            //["Paquete", $this->id],
            ["Rebanada", $this->id],
            ["Porción", $this->id],
            //["Onza", $this->id],
            //["Libra", $this->id],
            //["Gramo", $this->id],
            //["Taza", $this->id],
            //["Cucharadita", $this->id],
            //["Cucharada", $this->id],
            //["Mililitro", $this->id],
            //["Pizca", $this->id],
            //["Botella", $this->id],
            //["Gota", $this->id],
            //["Lata", $this->id],
            //["Bote", $this->id],
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
            ["Hamburguesas", $this->id, RecipeCategory::TYPE_MAIN],
            ["Bebidas Calientes", $this->id, RecipeCategory::TYPE_MAIN],
            ["Bebidas Frías", $this->id, RecipeCategory::TYPE_MAIN],
            ["Refrescos", $this->id, RecipeCategory::TYPE_MAIN],
            ["Cervezas", $this->id, RecipeCategory::TYPE_MAIN],
            ["Vinos", $this->id, RecipeCategory::TYPE_MAIN],
            ["Destilados", $this->id, RecipeCategory::TYPE_MAIN],
            ["Cócteles", $this->id, RecipeCategory::TYPE_MAIN],
            ["Mezcladores", $this->id, RecipeCategory::TYPE_MAIN],
            ["Mezcladores", $this->id, RecipeCategory::TYPE_SUB],
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
    }   public function getTheoreticalYield($month = null, $year = null)
{
    // Si no se proporciona mes o año, usar los actuales
    if ($month === null) {
        $month = (int)date('n');
    }
    if ($year === null) {
        $year = (int)date('Y');
    }
    $categories = RecipeCategory::find()
        ->where([
            'business_id' => $this->id
        ])->all();

    $totalSales = 0;
    $total = 0;
    $data = [];
    
    // Arrays separados para alimentos y bebidas (solo recetas)
    $foodRecipes = [];
    $nonFoodRecipes = [];
    
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
        ])->all();        if (empty($recipes) && empty($combos)) {
            continue;
        } else {
            $recipesSales = 0;
            $combosSales = 0;
            
            if (!empty($recipes)) {
                // Cargar ventas totales de todas las recetas para el año (sumando todos los meses con ventas)
                foreach ($recipes as $recipe) {
                    // Obtener las ventas totales del año para la receta
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_RECIPE, $recipe->id, $year);
                    $recipe->sales = $sales; // Actualizar la propiedad sales con los datos anuales
                    $recipesSales += $sales;
                    $total += 1;
                    
                    // Clasificar SOLO las recetas por is_food
                    if ($recipe->is_food) {
                        $foodRecipes[] = $recipe;
                    } else {
                        $nonFoodRecipes[] = $recipe;
                    }
                }
                $totalSales += $recipesSales;
            }
            
            if (!empty($combos)) {
                // Cargar ventas para cada combo para todo el año
                foreach ($combos as $combo) {
                    // Obtener ventas totales del año para el combo
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_MENU, $combo->id, $year);
                    $combo->sales = $sales; // Actualizar la propiedad sales con los datos anuales
                    $combosSales += $sales;
                    $total += 1;
                }
                $totalSales += $combosSales;
            }

            $data[] = [
                'category' => $category,
                'recipes' => $recipes,
                'combos' => $combos,
            ];
        }
    }
    
    // Cálculo original para todas las recetas/combos
    $totalCostPercent = 0;
    $categoryCount = 0;
    $totalCostCombo = 0;
    $recipeCount = 0;
    $averageCostPercent = 0;
    
    foreach ($data as $category) {
        $totalCostPercentRecipe = 0;
        $totalCostPercent = 0;
        $recipeCountt = count($category['recipes']);
        $recipeCountt += count($category['combos']);
        $recipeCount = 0;
        
        foreach ($category['recipes'] as $recipe) {
            $totalCostPercentRecipe += $recipe->costPercent;
            $recipeCount++;
        }
        
        foreach ($category['combos'] as $combo) {
            $totalCostPercent += $combo->costPercent;
            $recipeCount++;
        }
        
        if ($recipeCount > 0) {
            $averageCostPercent += ($totalCostPercentRecipe+$totalCostPercent) / $recipeCount;
            $categoryCount++;
        }   
    }
    
    // Calcular rendimiento teórico global
    $theoricalYield = null;
    if ($categoryCount > 0) {
        $theoricalYield = $this->getFormatter()->asPercent($averageCostPercent/$categoryCount, 2);
    }
    
    // Calcular rendimiento teórico para recetas de alimentos (is_food = true)
    $foodTheoricalYield = null;
    $foodCostTotal = 0;
    if (!empty($foodRecipes)) {
        $foodCostTotal = array_sum(ArrayHelper::getColumn($foodRecipes, 'costPercent'));
        $foodCostAvg = $foodCostTotal / count($foodRecipes);
        $foodTheoricalYield = $this->getFormatter()->asPercent($foodCostAvg, 2);
    }
    
    // Calcular rendimiento teórico para recetas de bebidas (is_food = false)
    $nonFoodTheoricalYield = null;
    $nonFoodCostTotal = 0;
    if (!empty($nonFoodRecipes)) {
        $nonFoodCostTotal = array_sum(ArrayHelper::getColumn($nonFoodRecipes, 'costPercent'));
        $nonFoodCostAvg = $nonFoodCostTotal / count($nonFoodRecipes);
        $nonFoodTheoricalYield = $this->getFormatter()->asPercent($nonFoodCostAvg, 2);
    }
    
    // Resto del cálculo original
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
    }    // Devolver datos originales más los agrupados solo para recetas
    return [
        'data' => $data, 
        'totalCost' => $totalCost, 
        'tehoricalTotal' => isset($theoricalYield) ? $theoricalYield : null,
        'month' => $month,
        'year' => $year,
        // Nuevos datos para recetas agrupados por is_food
        'recipesByType' => [
            'food' => [
                'count' => count($foodRecipes),
                'theoricalYield' => $foodTheoricalYield,
                'sales' => array_sum(ArrayHelper::getColumn($foodRecipes, 'sales')),
            ],
            'nonFood' => [
                'count' => count($nonFoodRecipes),
                'theoricalYield' => $nonFoodTheoricalYield,
                'sales' => array_sum(ArrayHelper::getColumn($nonFoodRecipes, 'sales')),
            ]
        ]
    ];
}

public function getRealYield($month = null, $year = null)
{
    // Si no se proporciona mes o año, usar los actuales
    if ($month === null) {
        $month = (int)date('n');
    }
    if ($year === null) {
        $year = (int)date('Y');
    }
    
    $categories = RecipeCategory::find()
        ->where([
            'business_id' => $this->id
        ])->all();

    $totalSales = 0;
    $data = [];
    
    // Arrays separados para alimentos y bebidas (solo recetas)
    $foodRecipes = [];
    $nonFoodRecipes = [];
    
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
            $recipesSales = 0;
            $combosSales = 0;
            
            if (!empty($recipes)) {
                // Cargar ventas totales de todas las recetas para el año (sumando todos los meses con ventas)
                foreach ($recipes as $recipe) {
                    // Obtener las ventas totales del año para la receta
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_RECIPE, $recipe->id, $year);
                    $recipe->sales = $sales; // Actualizar la propiedad sales con los datos anuales
                    $recipesSales += $sales;
                    
                    // Clasificar SOLO las recetas por is_food
                    if ($recipe->is_food) {
                        $foodRecipes[] = $recipe;
                    } else {
                        $nonFoodRecipes[] = $recipe;
                    }
                }
                $totalSales += $recipesSales;
            }
            
            if (!empty($combos)) {
                // Cargar ventas para cada combo para todo el año
                foreach ($combos as $combo) {
                    // Obtener ventas totales del año para el combo
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_MENU, $combo->id, $year);
                    $combo->sales = $sales; // Actualizar la propiedad sales con los datos anuales
                    $combosSales += $sales;
                }
                $totalSales += $combosSales;
            }

            $data[] = [
                'category' => $category,
                'recipes' => $recipes,
                'combos' => $combos,
            ];
        }
    }

    // Cálculo PCR global (original)
    $totalPcr = 0;
    array_walk($data, function ($el) use (&$totalPcr, $totalSales) {
        $totalPcr += array_sum(ArrayHelper::getColumn($el['recipes'], function ($recipe) use ($totalSales) {
            return $recipe->getCpr($totalSales);
        }));
        $totalPcr += array_sum(ArrayHelper::getColumn($el['combos'], function ($combo) use ($totalSales) {
            return $combo->getCpr($totalSales);
        }));
    });
    
    // Calcular PCR para recetas de alimentos (is_food = true)
    $foodPcr = 0;
    if (!empty($foodRecipes)) {
        foreach ($foodRecipes as $recipe) {
            $foodPcr += $recipe->getCpr($totalSales);
        }
    }
    
    // Calcular PCR para recetas de bebidas (is_food = false)
    $nonFoodPcr = 0;
    if (!empty($nonFoodRecipes)) {
        foreach ($nonFoodRecipes as $recipe) {
            $nonFoodPcr += $recipe->getCpr($totalSales);
        }
    }
    
    // Calcular las ventas por tipo
    $foodSales = !empty($foodRecipes) ? array_sum(ArrayHelper::getColumn($foodRecipes, 'sales')) : 0;
    $nonFoodSales = !empty($nonFoodRecipes) ? array_sum(ArrayHelper::getColumn($nonFoodRecipes, 'sales')) : 0;

    return [
        'data' => $data, 
        'totalPcr' => $totalPcr, 
        'totalSales' => $totalSales,
        'month' => $month,
        'year' => $year,
        // Nuevos datos para recetas agrupados por is_food
        'recipesByType' => [
            'food' => [
                'count' => count($foodRecipes),
                'pcr' => $foodPcr,
                'sales' => $foodSales,
            ],
            'nonFood' => [
                'count' => count($nonFoodRecipes),
                'pcr' => $nonFoodPcr,
                'sales' => $nonFoodSales,
            ]
        ]
    ];
}    public function getBcgData($type = 'all', $year = null)
    {
        // Si no se proporciona año, usar el actual
        if ($year === null) {
            $year = (int)date('Y');
        }
        
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
        
        // Cargar las ventas totales del año para cada receta y combo
        foreach ($recipes as $recipe) {
            $recipe->sales = MonthlySales::getTotalSales(MonthlySales::TYPE_RECIPE, $recipe->id, $year);
        }
        
        foreach ($combos as $combo) {
            $combo->sales = MonthlySales::getTotalSales(MonthlySales::TYPE_MENU, $combo->id, $year);
        }

        $data = array_merge($recipes, $combos);

        $totalSales = array_sum(ArrayHelper::getColumn($data, 'sales'));

        return [
            "data" => $data,
            'business' => $this,
            'totalSales' => $totalSales,
            'type' => $type,
            'year' => $year
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
