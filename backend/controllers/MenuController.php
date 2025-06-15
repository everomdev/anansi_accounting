<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\MenuBundle;
use common\models\MenuBundleProduct;
use common\models\RecipeCategory;
use common\models\StandardRecipe;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Menu;
use common\models\MenuSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends Controller
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'remove-from-menu',
                            'remove-from-menu-in-bulk',
                            'save-menu',
                            'saved-menus',
                            'compare-menus'
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_list',
                            'combo_view',

                        ]
                    ],
                    [
                        'actions' => [
                            'create',
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_create',
                        ]
                    ],
                    [
                        'actions' => [
                            'update',
                            'save-sales'
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_update',
                        ]
                    ],
                    [
                        'actions' => [
                            'delete'
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_delete'
                        ]
                    ],
                ],
            ],
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new MenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([
            'business_id' => $business['id']
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Menu model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        if ($user->hasRestrictions('combos')) {
            Yii::$app->session->setFlash('warning', "Haz alcanzado el límite de combos");
            return $this->redirect(['menu/index']);
        }
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new Menu([
            'business_id' => $business['id']
        ]);
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionSaveSales($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            return $this->asJson(['success' => true]);
        }

        return $this->asJson([
            'success' => false,
            'errors' => array_values(array_values($model->errors))
        ]);
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionRemoveFromMenu(int $id, string $type, $bundle = null)
    {
        if ($bundle !== null) {
            $bundleModel = MenuBundle::findOne(['id' => $bundle]);
            if (!empty($bundleModel)) {
                Yii::$app->db->createCommand()
                    ->delete('menu_bundle_product', [
                        'entity_id' => $id,
                        'bundle_id' => $bundle,
                        'entity_type' => $type
                    ])
                    ->execute();

            }
        } else {
            $model = $type::findOne(['id' => $id]);
            $model->in_menu = 0;
            $model->save();
        }
        $url = Url::previous('menu-recipes');

        return $this->redirect($url ?? ['/standard-recipe/menu-recipes']);
    }

    public function actionRemoveFromMenuInBulk()
    {
        $data = Yii::$app->request->post('data');

        foreach ($data as $item) {
            $model = $item['type']::findOne(['id' => $item['id']]);
            $model->in_menu = 0;
            $model->save();
        }

        $url = Url::previous('menu-recipes');

        return $this->redirect($url ?? ['/standard-recipe/menu-recipes']);

    }

    public function actionSaveMenu()
{
    $post = Yii::$app->request->post();

    $business = Business::findOne(['user_id' => Yii::$app->user->identity->id]);

    // Crear nuevo MenuBundle
    $bundle = new MenuBundle([
        'business_id' => $business->id,
        'date' => $post['date'],
    ]);

    // Obtener recetas en menú
    $recipes = StandardRecipe::find()->where([
        'business_id' => $business['id'],
        'in_construction' => 0,
        'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
        'in_menu' => true
    ])->all();

    // Obtener combos en menú
    $combos = Menu::find()->where([
        'business_id' => $business['id'],
        'in_menu' => true
    ])->all();

    $categoryProfitability = [];
    $categoryProfitabilityCombo = [];
    $recipesByCategory = [];
    $combosByCategory = [];
    $r_recipe = [];
    $r_combo = [];
    

    foreach ($recipes as $recipe) {
        $categoryKey = (string)$recipe->type_of_recipe;
        if (!isset($recipesByCategory[$categoryKey])) {
            $recipesByCategory[$categoryKey] = [];
        }
        $recipesByCategory[$categoryKey][] = $recipe;
    }
    foreach ($combos as $combo) {
        $categoryKey = RecipeCategory::findOne($combo->category_id)->name;
        if (!isset($combosByCategory[$categoryKey])) {
            $combosByCategory[$categoryKey] = [];
        }
        $combosByCategory[$categoryKey][] = $combo;
    }
    foreach ($combosByCategory as $category => $categoryCombos) {
        $totalCostPercentage = 0;
        $totalSales = 0;
        $totalSalesSuma = 0;
        $recipeCount = count($categoryCombos);

        foreach ($categoryCombos as $combo) {
            $totalSales += $combo->sales;
        }
        
        foreach ($categoryCombos as $combo) {
            $totalCostPercentage += $combo->costPercent;
            $totalSalesSuma += ($totalSales > 0 && $combo->sales > 0) ? ($combo->sales / $totalSales) * $combo->costPercent : 0;
        }
        
        $categoryProfitabilityCombo[$category] = ($totalCostPercentage / $recipeCount) * 100;
        $r_combo[$category] = $totalSalesSuma * 100;
    }

    // Calcular rentabilidad teórica por categoría
    foreach ($recipesByCategory as $category => $categoryRecipes) {
        $totalCostPercentage = 0;
        $totalSales = 0;
        $totalSalesSuma = 0;
        $recipeCount = count($categoryRecipes);
        foreach ($categoryRecipes as $recipe) {
            $totalSales += $recipe->sales;
        }
        
        foreach ($categoryRecipes as $recipe) {
            $totalCostPercentage += $recipe->costPercent;
            $totalSalesSuma += ($totalSales > 0 && $recipe->sales > 0) ?($recipe->sales / $totalSales) * $recipe->costPercent : 0;
        }
        $categoryProfitability[$category] = ($totalCostPercentage / $recipeCount) * 100;
        $r_recipe[$category] = $totalSalesSuma * 100;
    }
    $models = array_merge($recipes, $combos);

    if ($bundle->save()) {
        foreach ($models as $model) {
            // Determinar la rentabilidad teórica basada en el tipo de modelo
            $rentabilidadTeorica = 0;
            $rentabilidadReal = 0;
            
            if ($model instanceof StandardRecipe) {
                // Para recetas estándar, usar la rentabilidad por categoría
                $categoryKey = (string)$model->type_of_recipe;
                
                if (isset($categoryProfitability[$categoryKey])) {
                    $rentabilidadTeorica = $categoryProfitability[$categoryKey];
                    $rentabilidadReal = $r_recipe[$categoryKey];

                }
            } elseif ($model instanceof Menu) {
                $categoryKey = (string)RecipeCategory::findOne($model->category_id)->name;
                
                if (isset($categoryProfitabilityCombo[$categoryKey])) {
                    $rentabilidadTeorica = $categoryProfitabilityCombo[$categoryKey];
                    $rentabilidadReal = $r_combo[$categoryKey];
                }
            }
            $link = new MenuBundleProduct([
                'entity_id' => $model->id,
                'entity_type' => get_class($model),
                'bundle_id' => $bundle->id,
                'rentabilidad_teorica' => $rentabilidadTeorica,
                'categoria' => $categoryKey,  // Añadir la rentabilidad teórica
                'rentabilidad_real' => $rentabilidadReal, // Añadir la rentabilidad real

            ]);

            if (!$link->save()) {
                Yii::$app->session->setFlash('warning', 'Error al guardar producto: ' . json_encode($link->errors));
                return $this->asJson(['success' => false]);
            }
        }
        
        Yii::$app->session->setFlash('success', 'Menú guardado correctamente con datos de rentabilidad');
    } else if ($bundle->hasErrors()) {
        Yii::$app->session->setFlash('danger', json_encode($bundle->errors));
        return $this->asJson(['success' => false]);
    }

    return $this->asJson(['success' => true]);
}

public function actionSavedMenus()
{
    $business = Business::findOne(['user_id' => Yii::$app->user->identity->id]);
    $menuBundleProducts = MenuBundleProduct::find()
        ->all();
    $bundles = MenuBundle::find()
        ->where(['business_id' => $business->id]);

    $dataProvider = new ActiveDataProvider([
        'query' => $bundles,
    ]);
    
    $typeOfRecipe = []; // Para recetas estándar agrupadas por categoría
    $typeOfRecipeReal = []; // Para rentabilidad real (importante inicializarla)
    
    foreach ($menuBundleProducts as $mbp) {
        if ($mbp->entity_type === StandardRecipe::class) {
            $recipe = StandardRecipe::findOne($mbp->entity_id);
            if ($recipe) {
                // Agrupar por categoría para recetas estándar
                if (!isset($typeOfRecipe[$mbp->bundle_id])) {
                    $typeOfRecipe[$mbp->bundle_id] = [];
                }
                $typeOfRecipe[$mbp->bundle_id][$mbp->categoria] = $mbp->rentabilidad_teorica;
                $typeOfRecipeReal[$mbp->bundle_id][$mbp->categoria] = $mbp->rentabilidad_real;
            }
        } elseif ($mbp->entity_type === Menu::class) {
            $combo = Menu::findOne($mbp->entity_id);
            if ($combo) {
                // Almacenar rentabilidad para combos
                if (!isset($typeOfRecipe[$mbp->bundle_id])) {
                    $typeOfRecipe[$mbp->bundle_id] = [];
                }
                $typeOfRecipe[$mbp->bundle_id][$mbp->categoria] = $mbp->rentabilidad_teorica;
                $typeOfRecipeReal[$mbp->bundle_id][$mbp->categoria] = $mbp->rentabilidad_real;
            }
        }
    }
    
    // Verificar si es una solicitud AJAX para comparación de menús
    if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $menuIds = Yii::$app->request->post('menuIds', []);
        
        if (empty($menuIds) || count($menuIds) > 2) {
            return [
                'success' => false,
                'message' => 'Se requieren 1 o 2 menús para comparar'
            ];
        }
        
        // Obtener los menús seleccionados
        $selectedMenus = MenuBundle::find()
            ->where(['id' => $menuIds, 'business_id' => $business->id])
            ->all();
        
        if (count($selectedMenus) !== count($menuIds)) {
            return [
                'success' => false,
                'message' => 'No se encontraron todos los menús solicitados'
            ];
        }
        
        // Filtrar los datos de rentabilidad solo para los menús seleccionados
        $selectedMenuData = [];
        $selectedMenuDataReal = [];
        
        foreach ($menuIds as $menuId) {
            if (isset($typeOfRecipe[$menuId])) {
                $selectedMenuData[$menuId] = $typeOfRecipe[$menuId];
            }
            
            if (isset($typeOfRecipeReal[$menuId])) {
                $selectedMenuDataReal[$menuId] = $typeOfRecipeReal[$menuId];
            }
        }
        
        // Renderizar vista parcial de comparación
        $html = $this->renderPartial('_compare_menus', [
            'menus' => $selectedMenus,
            'menuBundleProducts' => $selectedMenuData,
            'rentabilidadReal' => $selectedMenuDataReal
        ]);
        
        return [
            'success' => true,
            'html' => $html
        ];
    }
    
    // Renderizado normal de la lista de menús
    return $this->render('saved_menus', [
        'dataProvider' => $dataProvider,
        'menuBundleProducts' => $typeOfRecipe,
        'rentabilidadReal' => $typeOfRecipeReal,
    ]);
}

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
