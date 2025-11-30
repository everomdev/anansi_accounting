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
            [['name'], 'trim'], // Eliminar espacios al inicio y final
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
        
        $units = [
            ["Kilogramo", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 1, 1, 1, 1, 1],
            ["Litro", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 1, 1, 1, 1, 1],
            ["Pieza", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 1, 1, 1, 1, 1],
            // ["Kilogramo", $this->id, UnitOfMeasurement::TYPE_KITCHEN, 1, 1, 1, 1, 1, 1],
            // ["Litro", $this->id, UnitOfMeasurement::TYPE_KITCHEN, 1, 1, 1, 1, 1, 1],
            // ["Pieza", $this->id, UnitOfMeasurement::TYPE_KITCHEN, 0, 0, 0, 0, 0, 0],

            // 2) Porción => todo en 1 excepto compras y cocina
            ["Porción", $this->id, UnitOfMeasurement::TYPE_KITCHEN, 0, 0, 1, 1, 1, 1],

            // 3) Rebanada => 0,1,0,1,0,1
            ["Rebanada", $this->id, UnitOfMeasurement::TYPE_KITCHEN, 0, 1, 0, 1, 0, 1],

            // 4) Botella, Bote, Caja y Paquete => solo compras en true
            ["Botella", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 0, 0, 0, 0, 0],
            ["Bote", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 0, 0, 0, 0, 0],
            ["Caja", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 0, 0, 0, 0, 0],
            ["Paquete", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 0, 0, 0, 0, 0],

            // 5) Lata => 1,1,0,0,0,0
            ["Lata", $this->id, UnitOfMeasurement::TYPE_PURCHASE, 1, 1, 0, 0, 0, 0],
        ];

        Yii::$app->db->createCommand()
            ->batchInsert(
                'unit_of_measurement',
                [
                    'name',
                    'business_id',
                    'type',
                    'is_purchase',
                    'is_kitchen',
                    'is_subrecipe_yield',
                    'is_subrecipe_um',
                    'is_recipe_yield',
                    'is_recipe_final_um'
                ],
                $units
            )
            ->execute();
    }

    public function initRecipeCategories()
    {
        $data = [
            ["Salsas", $this->id, RecipeCategory::TYPE_SUB],
            ["Transformados", $this->id, RecipeCategory::TYPE_SUB],
            ["Porcionados", $this->id, RecipeCategory::TYPE_SUB],
            ["Fondos", $this->id, RecipeCategory::TYPE_SUB],
            ["Bases", $this->id, RecipeCategory::TYPE_SUB],
            ["Guarnición", $this->id, RecipeCategory::TYPE_SUB],
            ["Masas", $this->id, RecipeCategory::TYPE_SUB],
            ["Mezcladores", $this->id, RecipeCategory::TYPE_SUB],
            ["Mezclas simples", $this->id, RecipeCategory::TYPE_SUB],
            ["Preparados", $this->id, RecipeCategory::TYPE_SUB],
            ["Conservados", $this->id, RecipeCategory::TYPE_SUB],
            ["Mezcla simples", $this->id, RecipeCategory::TYPE_SUB],
            ["Coberturas", $this->id, RecipeCategory::TYPE_SUB],
            ["Bebidas base", $this->id, RecipeCategory::TYPE_SUB],
            ["Decoraciones comestibles", $this->id, RecipeCategory::TYPE_SUB],
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
        ];

        Yii::$app->db->createCommand()
            ->batchInsert('recipe_category', ['name', 'business_id', 'type'], $data)
            ->execute();
    }

    public function initConsumptionCenters()
    {
        $data = [
            ["Cocina", $this->id],
            ["Almacén", $this->id]
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
    public function getRecipeCategoriesMain()
    {
        return $this->hasMany(RecipeCategory::class, ['business_id' => 'id'])
            ->andWhere(['type' => RecipeCategory::TYPE_MAIN]);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('user_business', ['business_id' => 'id']);
    }

    /*
    // MÉTODO ANTIGUO - COMENTADO PARA REFERENCIA
    // Este método realizaba múltiples consultas por categoría (ineficiente)
    public function getTheoreticalYield_OLD($month = null, $year = null)
    {
        // Para rentabilidad teórica no necesitamos mes/año, solo por compatibilidad
        if ($month === null) {
            $month = (int)date('n');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }

        $categories = RecipeCategory::find()
            ->where([
                'OR',
                ['business_id' => $this->id], // Categorías específicas del negocio
                ['business_id' => null]       // Categorías generales (como Combos)
            ])->all();

        $data = [];
        $allRecipes = [];  // Todas las recetas para promedio global
        $allCombos = [];   // Todos los combos para promedio global

        // Arrays separados para alimentos y bebidas (solo recetas)
        $foodRecipes = [];
        $nonFoodRecipes = [];

        foreach ($categories as $category) {
            // UNA CONSULTA POR CATEGORÍA (INEFICIENTE)
            $recipes = StandardRecipe::find()->where([
                'business_id' => $this->id,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
                'type_of_recipe' => $category->name
            ])->all();

            $combos = [];
            if ($category->business_id === null) {
                // OTRA CONSULTA PARA COMBOS (INEFICIENTE)
                $combos = Menu::find()->where([
                    'business_id' => $this->id,
                    'in_menu' => true,
                ])->all();
            }

            if (empty($recipes) && empty($combos)) {
                continue;
            }

            // Agregar recetas a las listas globales y por tipo
            foreach ($recipes as $recipe) {
                $allRecipes[] = $recipe;

                // Clasificar SOLO las recetas por is_food
                if ($recipe->is_food) {
                    $foodRecipes[] = $recipe;
                } else {
                    $nonFoodRecipes[] = $recipe;
                }
            }

            // Agregar combos a la lista global
            foreach ($combos as $combo) {
                $allCombos[] = $combo;
            }

            $data[] = [
                'category' => $category,
                'recipes' => $recipes,
                'combos' => $combos,
            ];
        }

        // Calcular rendimiento teórico global (promedio simple de todos los cost percent)
        $theoricalYield = null;
        $totalCostSum = 0;
        $totalItems = 0;

        foreach ($allRecipes as $recipe) {
            $totalCostSum += $recipe->costPercent;
            $totalItems++;
        }

        foreach ($allCombos as $combo) {
            $totalCostSum += $combo->costPercent;
            $totalItems++;
        }
          if ($totalItems > 0) {
            $averageCost = $totalCostSum / $totalItems;
            $theoricalYield = formatPercentage($averageCost*100);
        }
          // Calcular rendimiento teórico para recetas de alimentos (is_food = true)
        $foodTheoricalYield = null;
        if (!empty($foodRecipes)) {
            $foodCostTotal = array_sum(ArrayHelper::getColumn($foodRecipes, 'costPercent'));
            $foodCostAvg = $foodCostTotal / count($foodRecipes);
            $foodTheoricalYield = formatPercentage($foodCostAvg*100);
        }
          // Calcular rendimiento teórico para recetas de bebidas (is_food = false)
        $nonFoodTheoricalYield = null;
        if (!empty($nonFoodRecipes)) {
            $nonFoodCostTotal = array_sum(ArrayHelper::getColumn($nonFoodRecipes, 'costPercent'));
            $nonFoodCostAvg = $nonFoodCostTotal / count($nonFoodRecipes);
            $nonFoodTheoricalYield = formatPercentage($nonFoodCostAvg*100);
        }

        // Costo total promedio para compatibilidad
        $totalCost = $totalItems > 0 ? $totalCostSum / $totalItems : 0;

        // Devolver datos basados únicamente en promedios de costos
        return [
            'data' => $data,
            'totalCost' => $totalCost,
            'theoricalTotal' => $theoricalYield,
            'month' => $month,
            'year' => $year,
            // Datos para recetas agrupados por is_food (solo promedios de costos)
            'recipesByType' => [
                'food' => [
                    'count' => count($foodRecipes),
                    'theoricalYield' => $foodTheoricalYield,
                ],
                'nonFood' => [
                    'count' => count($nonFoodRecipes),
                    'theoricalYield' => $nonFoodTheoricalYield,
                ]
            ]
        ];
    }
    */

    // MÉTODO OPTIMIZADO - NUEVA VERSIÓN CON MEJORAS DE PERFORMANCE
    public function getTheoreticalYield($month = null, $year = null)
    {
    $t0 = microtime(true);
    die(var_dump("here"));
    \Yii::info(sprintf("getTheoreticalYield START business=%s month=%s year=%s", $this->id, $month, $year), __METHOD__);
        // Para rentabilidad teórica no necesitamos mes/año, solo por compatibilidad
        if ($month === null) {
            $month = (int)date('n');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }

        $categories = RecipeCategory::find()
            ->where([
                'OR',
                ['business_id' => $this->id], // Categorías específicas del negocio
                ['business_id' => null]       // Categorías generales (como Combos)
            ])->all();
    $t1 = microtime(true);
    \Yii::warning(sprintf("getTheoreticalYield: categories loaded count=%d time=%.4fs", count($categories), $t1 - $t0), __METHOD__);

        $data = [];
        $allRecipes = [];  // Todas las recetas para promedio global
        $allCombos = [];   // Todos los combos para promedio global

        // Arrays separados para alimentos y bebidas (solo recetas)
        $foodRecipes = [];
        $nonFoodRecipes = [];

        // OPTIMIZACIÓN: Obtener todas las recetas en una sola query en lugar de múltiples por categoría
        $categoryNames = ArrayHelper::getColumn($categories, 'name');
        $recipes = StandardRecipe::find()->where([
            'business_id' => $this->id,
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
            'in_menu' => true,
            'type_of_recipe' => $categoryNames
        ])->all();
    $t2 = microtime(true);
    \Yii::warning(sprintf("getTheoreticalYield: recipes loaded count=%d time=%.4fs", count($recipes), $t2 - $t1), __METHOD__);

        // OPTIMIZACIÓN: Obtener combos en una sola query
        $combos = Menu::find()->where([
            'business_id' => $this->id,
            'in_menu' => true,
        ])->all();
    $t3 = microtime(true);
    \Yii::warning(sprintf("getTheoreticalYield: combos loaded count=%d time=%.4fs", count($combos), $t3 - $t2), __METHOD__);

        // Agrupar recetas por categoría
        $recipesByCategory = [];
        foreach ($recipes as $recipe) {
            $recipesByCategory[$recipe->type_of_recipe][] = $recipe;
        }

        // Los combos van solo en la categoría general (business_id = null)
        $combosByCategory = [];
        $generalCategory = null;
        foreach ($categories as $category) {
            if ($category->business_id === null) {
                $generalCategory = $category;
                break;
            }
        }
    $t4 = microtime(true);
    \Yii::warning(sprintf("getTheoreticalYield: grouping done categories=%d recipes=%d combos=%d time=%.4fs", count($categories), count($recipes), count($combos), $t4 - $t3), __METHOD__);
        if ($generalCategory) {
            $combosByCategory[$generalCategory->name] = $combos;
        }

        foreach ($categories as $category) {
            $categoryRecipes = $recipesByCategory[$category->name] ?? [];
            $categoryCombos = ($category->business_id === null) ? $combos : [];

            if (empty($categoryRecipes) && empty($categoryCombos)) {
                continue;
            }

            // Agregar recetas a las listas globales y por tipo
            foreach ($categoryRecipes as $recipe) {
                $allRecipes[] = $recipe;

                // Clasificar SOLO las recetas por is_food
                if ($recipe->is_food) {
                    $foodRecipes[] = $recipe;
                } else {
                    $nonFoodRecipes[] = $recipe;
                }
            }

            // Agregar combos a la lista global
            foreach ($categoryCombos as $combo) {
                $allCombos[] = $combo;
            }

            $data[] = [
                'category' => $category,
                'recipes' => $categoryRecipes,
                'combos' => $categoryCombos,
            ];
        }

        // Calcular rendimiento teórico global (promedio simple de todos los cost percent)
        $theoricalYield = null;
        $totalCostSum = 0;
        $totalItems = 0;

        foreach ($allRecipes as $recipe) {
            $totalCostSum += $recipe->costPercent;
            $totalItems++;
        }

        foreach ($allCombos as $combo) {
            $totalCostSum += $combo->costPercent;
            $totalItems++;
        }
          if ($totalItems > 0) {
            $averageCost = $totalCostSum / $totalItems;
            $theoricalYield = formatPercentage($averageCost*100);
        }
          // Calcular rendimiento teórico para recetas de alimentos (is_food = true)
        $foodTheoricalYield = null;
        if (!empty($foodRecipes)) {
            $foodCostTotal = array_sum(ArrayHelper::getColumn($foodRecipes, 'costPercent'));
            $foodCostAvg = $foodCostTotal / count($foodRecipes);
            $foodTheoricalYield = formatPercentage($foodCostAvg*100);
        }
          // Calcular rendimiento teórico para recetas de bebidas (is_food = false)
        $nonFoodTheoricalYield = null;
        if (!empty($nonFoodRecipes)) {
            $nonFoodCostTotal = array_sum(ArrayHelper::getColumn($nonFoodRecipes, 'costPercent'));
            $nonFoodCostAvg = $nonFoodCostTotal / count($nonFoodRecipes);
            $nonFoodTheoricalYield = formatPercentage($nonFoodCostAvg*100);
        }

        // Costo total promedio para compatibilidad
        $totalCost = $totalItems > 0 ? $totalCostSum / $totalItems : 0;
    $t5 = microtime(true);
    \Yii::warning(sprintf("getTheoreticalYield: calculations done totalItems=%d time=%.4fs", $totalItems, $t5 - $t4), __METHOD__);

        // Devolver datos basados únicamente en promedios de costos
    $tEnd = microtime(true);
    \Yii::warning(sprintf("getTheoreticalYield END totalTime=%.4fs", $tEnd - $t0), __METHOD__);

    return [
            'data' => $data,
            'totalCost' => $totalCost,
            'theoricalTotal' => $theoricalYield,
            'month' => $month,
            'year' => $year,
            // Datos para recetas agrupados por is_food (solo promedios de costos)
            'recipesByType' => [
                'food' => [
                    'count' => count($foodRecipes),
                    'theoricalYield' => $foodTheoricalYield,
                ],
                'nonFood' => [
                    'count' => count($nonFoodRecipes),
                    'theoricalYield' => $nonFoodTheoricalYield,
                ]
            ]
        ];
    }

    /*
    // MÉTODO ANTIGUO - COMENTADO PARA REFERENCIA
    // Este método realizaba múltiples consultas por categoría (ineficiente)
    public function getRealYield_OLD($month = null, $year = null)
    {
        // Cálculo de rentabilidad real basado en ventas históricas para un mes y año específicos
        // Fórmula: Σ(porcentaje_ventas * porcentaje_costo) para todas las recetas/combos
        // donde porcentaje_ventas = ventas_individuales_mes / total_ventas_mes

        // Si no se proporciona mes o año, usar los actuales
        if ($month === null) {
            $month = (int)date('n');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }

        $categories = RecipeCategory::find()
            ->where([
                'OR',
                ['business_id' => $this->id], // Categorías específicas del negocio
                ['business_id' => null]       // Categorías generales (como Combos)
            ])->all();

        $totalSales = 0;
        $data = [];

        // Arrays separados para alimentos y bebidas (solo recetas)
        $foodRecipes = [];
        $nonFoodRecipes = [];

        foreach ($categories as $category) {
            // UNA CONSULTA POR CATEGORÍA (INEFICIENTE)
            $recipes = StandardRecipe::find()->where([
                'business_id' => $this->id,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
                'type_of_recipe' => $category->name
            ])->all();

            $combos = [];
            if ($category->business_id === null) {
                // OTRA CONSULTA PARA COMBOS (INEFICIENTE)
                $combos = Menu::find()->where([
                    'business_id' => $this->id,
                    'in_menu' => true,
                ])->all();
            }

            if (empty($recipes) && empty($combos)) {
                continue;
            }

            $recipesSales = 0;
            $combosSales = 0;

            if (!empty($recipes)) {
                // Cargar ventas para el mes y año específicos
                foreach ($recipes as $recipe) {
                    // Obtener las ventas para el mes y año específicos
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_RECIPE, $recipe->id, $year, $month);
                    $recipe->sales = $sales; // Actualizar la propiedad sales con los datos del mes
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
                // Cargar ventas para cada combo para el mes y año específicos
                foreach ($combos as $combo) {
                    // Obtener ventas para el mes y año específicos
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_MENU, $combo->id, $year, $month);
                    $combo->sales = $sales; // Actualizar la propiedad sales con los datos del mes
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

        // Cálculo de rentabilidad real usando la fórmula:
        // Para cada receta/combo: (ventas individuales / total ventas) * porcentaje de costo
        // Luego suma todos los resultados
        $totalPcr = 0;

        foreach ($data as $category) {
            // Calcular PCR total para recipes
            foreach ($category['recipes'] as $recipe) {
                $totalPcr += $recipe->getCpr($totalSales);
            }

            // Calcular PCR total para combos
            foreach ($category['combos'] as $combo) {
                $totalPcr += $combo->getCpr($totalSales);
            }
        }

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
    }
    */

    // MÉTODO OPTIMIZADO - NUEVA VERSIÓN CON MEJORAS DE PERFORMANCE
    public function getRealYield($month = null, $year = null)
    {
        // Cálculo de rentabilidad real basado en ventas históricas para un mes y año específicos
        // Fórmula: Σ(porcentaje_ventas * porcentaje_costo) para todas las recetas/combos
        // donde porcentaje_ventas = ventas_individuales_mes / total_ventas_mes

        // Si no se proporciona mes o año, usar los actuales
        if ($month === null) {
            $month = (int)date('n');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }

        $categories = RecipeCategory::find()
            ->where([
                'OR',
                ['business_id' => $this->id], // Categorías específicas del negocio
                ['business_id' => null]       // Categorías generales (como Combos)
            ])->all();

        $totalSales = 0;
        $data = [];

        // Arrays separados para alimentos y bebidas (solo recetas)
        $foodRecipes = [];
        $nonFoodRecipes = [];

        // OPTIMIZACIÓN: Obtener todas las recipes en una sola query en lugar de múltiples por categoría
        $categoryNames = ArrayHelper::getColumn($categories, 'name');
        $recipes = StandardRecipe::find()->where([
            'business_id' => $this->id,
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
            'in_menu' => true,
            'type_of_recipe' => $categoryNames
        ])->all();

        // OPTIMIZACIÓN: Obtener combos en una sola query
        $combos = Menu::find()->where([
            'business_id' => $this->id,
            'in_menu' => true,
        ])->all();

        // Agrupar recipes por categoría
        $recipesByCategory = [];
        foreach ($recipes as $recipe) {
            $recipesByCategory[$recipe->type_of_recipe][] = $recipe;
        }

        // Los combos van solo en la categoría general (business_id = null)
        $combosByCategory = [];
        $generalCategory = null;
        foreach ($categories as $category) {
            if ($category->business_id === null) {
                $generalCategory = $category;
                break;
            }
        }
        if ($generalCategory) {
            $combosByCategory[$generalCategory->name] = $combos;
        }

        foreach ($categories as $category) {
            $categoryRecipes = $recipesByCategory[$category->name] ?? [];
            $categoryCombos = ($category->business_id === null) ? $combos : [];

            if (empty($categoryRecipes) && empty($categoryCombos)) {
                continue;
            }

            $recipesSales = 0;
            $combosSales = 0;

            if (!empty($categoryRecipes)) {
                // Cargar ventas para el mes y año específicos
                foreach ($categoryRecipes as $recipe) {
                    // Obtener las ventas para el mes y año específicos
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_RECIPE, $recipe->id, $year, $month);
                    $recipe->sales = $sales; // Actualizar la propiedad sales con los datos del mes
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

            if (!empty($categoryCombos)) {
                // Cargar ventas para cada combo para el mes y año específicos
                foreach ($categoryCombos as $combo) {
                    // Obtener ventas para el mes y año específicos
                    $sales = MonthlySales::getTotalSales(MonthlySales::TYPE_MENU, $combo->id, $year, $month);
                    $combo->sales = $sales; // Actualizar la propiedad sales con los datos del mes
                    $combosSales += $sales;
                }
                $totalSales += $combosSales;
            }

            $data[] = [
                'category' => $category,
                'recipes' => $categoryRecipes,
                'combos' => $categoryCombos,
            ];
        }

        // Cálculo de rentabilidad real usando la fórmula:
        // Para cada receta/combo: (ventas individuales / total ventas) * porcentaje de costo
        // Luego suma todos los resultados
        $totalPcr = 0;

        foreach ($data as $category) {
            // Calcular PCR total para recipes
            foreach ($category['recipes'] as $recipe) {
                $totalPcr += $recipe->getCpr($totalSales);
            }

            // Calcular PCR total para combos
            foreach ($category['combos'] as $combo) {
                $totalPcr += $combo->getCpr($totalSales);
            }
        }

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
    }    
public function getBcgData($type = 'all', $year = null)
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
