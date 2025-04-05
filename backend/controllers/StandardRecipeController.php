<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Business;
use backend\models\StandardRecipeIngredientForm;
use common\models\Menu;
use common\models\Convoy;
use common\models\MenuBundle;
use common\models\RecipeCategory;
use common\models\IngredientStock;
use common\models\RecipeStep;
use backend\helpers\ExcelHelper;
use yii\web\UploadedFile;
use common\models\UnitOfMeasurement;
use common\models\Category;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use rico\yii2images\models\Image;
use Symfony\Component\Yaml\Yaml;
use Yii;
use common\models\StandardRecipe;
use common\models\StandardRecipeSearch;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StandardRecipeController implements the CRUD actions for StandardRecipe model.
 */
class StandardRecipeController extends Controller
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
                            'index'
                        ],
                        'allow' => true,
                        'roles' => ['recipe_list', 'recipe_view'],
                    ],
                    [
                        'actions' => [
                            'add-step',
                            'remove-step',
                            'create',
                            'delete-image',
                            'finish-recipe-creation',
                            'form-select-ingredient',
                            'select-ingredients',
                            'unselect-ingredient',
                            'duplicate-recipes',
                            'ckeck-title',
                            'download-recipes',
                            'download-recipes-pdf',
                            'download-complete-recipe-pdf',
                            'export-recipes-to-excel',
                            'export-recipes-plantilla',
                            'import-recipes',
                            'edit-step',
                            'move-step',
                            'get-available-ingredients',
                            'get-sub-standard-recipes'
                            


                        ],
                        'allow' => true,
                        'roles' => [
                            'recipe_create'
                        ],
                    ],
                    [
                        'actions' => [
                            'add-step',
                            'remove-step',
                            'update',
                            'delete-image',
                            'finish-recipe-creation',
                            'form-select-ingredient',
                            'select-ingredients',
                            'unselect-ingredient',
                            'update-selected-ingredient',
                            'check-title',
                            'download-recipes',
                            'download-recipes-pdf',
                            'download-complete-recipe-pdf',
                            'export-recipes-to-excel',
                            'export-recipes-plantilla',
                            'import-recipes',
                            'move-step',
                            'edit-step',
                            'get-available-ingredients',
                            'get-sub-standard-recipes'

                        ],
                        'allow' => true,
                        'roles' => [
                            'recipe_update'
                        ],
                    ],
                    [
                        'actions' => [
                            'delete',
                        ],
                        'allow' => true,
                        'roles' => [
                            'recipe_delete'
                        ],
                    ],
                    [
                        'actions' => [
                            'analytics',
                        ],
                        'allow' => true,
                        'roles' => [
                            'menu_analysis_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'charts',
                        ],
                        'allow' => true,
                        'roles' => [
                            'charts_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'charts',
                        ],
                        'allow' => true,
                        'roles' => [
                            'charts_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'matrix_bcg',
                        ],
                        'allow' => true,
                        'roles' => [
                            'matrix_bcg',
                        ],
                    ],
                    [
                        'actions' => [
                            'menu-improvement',
                        ],
                        'allow' => true,
                        'roles' => [
                            'menu_improvements_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'menu-recipes',
                        ],
                        'allow' => true,
                        'roles' => [
                            'menu_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'profit-comparison',
                        ],
                        'allow' => true,
                        'roles' => [
                            'profitability_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'real-yield',
                        ],
                        'allow' => true,
                        'roles' => [
                            'real_profitability_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'theoretical-yield',
                        ],
                        'allow' => true,
                        'roles' => [
                            'theoretical_profitability_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'sales',
                        ],
                        'allow' => true,
                        'roles' => [
                            'sales_view',
                        ],
                    ],
                    [
                        'actions' => [
                            'save-sales',
                        ],
                        'allow' => true,
                        'roles' => [
                            'sales_update',
                        ],
                    ],
                    [
                        'actions' => [
                            'select-unselect-for-menu',
                        ],
                        'allow' => true,
                        'roles' => [
                            'menu_add_item',
                            'menu_remove_item',
                        ],
                    ],
                    [
                        'actions' => [
                            'matrix-bcg',
                        ],
                        'allow' => true,
                        'roles' => [
                            'matrix_bcg',
                        ],
                    ],

                ],
            ],
        ];
    }

    /**
     * Lists all StandardRecipe models.
     * @return mixed
     */
    public function actionIndex($type = StandardRecipe::STANDARD_RECIPE_TYPE_MAIN)
    {
        $page = (int)Yii::$app->request->get('page', 1);
        Url::remember(['standard-recipe/index', 'type' => $type, 'page' => $page], 'index-recipe');
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new StandardRecipeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => $type
        ]);
        $ingredientCount = [];
        $recipes = $dataProvider->getModels();
        foreach ($recipes as $recipe) {
            $ingredientCount[$recipe->id] = [
                'ingredientCount' => $recipe->getIngredientRelations()->count(),
                'sub_recipe' => $recipe->getSubStandardRecipes()->count(),
            ];
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'ingredientCount' => $ingredientCount
        ]);
    }

    public function actionTheoreticalYield()
    {
        /** @var $business \common\models\Business */
        $business = RedisKeys::getBusiness();

        return $this->render('theoretical_yield', $business->getTheoreticalYield());
    }

    public function actionRealYield()
    {
        $business = RedisKeys::getBusiness();


        return $this->render('real_yield', $business->getRealYield());
    }


    /**
     * Displays a single StandardRecipe model.
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
     * Creates a new StandardRecipe model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($type = StandardRecipe::STANDARD_RECIPE_TYPE_MAIN)
    {
        $user = Yii::$app->user->identity;
        if ($type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN && $user->hasRestrictions('recipes')) {
            Yii::$app->session->setFlash('warning', "Haz alcanzado el límite de recetas");
            return $this->redirect(['standard-recipe/index']);
        }
        if ($type == StandardRecipe::STANDARD_RECIPE_TYPE_SUB && $user->hasRestrictions('subrecipes')) {
            Yii::$app->session->setFlash('warning', "Haz alcanzado el límite de subrecetas");
            return $this->redirect(['sub-standard-recipe/index']);
        }
        Url::remember(['standard-recipe/create', 'type' => $type], 'create-recipe');
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = StandardRecipe::findOne(['business_id' => $business['id'], 'in_construction' => true, 'type' => $type]);
        if (empty($model)) {
            $model = new StandardRecipe([
                'business_id' => $business['id'],
                'type' => $type,
                'title' => '',
                'in_construction' => true,
            ]);
        } elseif($model->in_construction){
            $model->title = ''; 
        }
//        elseif($model->in_construction){
//            Yii::$app->db->createCommand()
//                ->delete("ingredient_standard_recipe", ['standard_recipe_id' => $model->id])
//                ->execute();
//            Yii::$app->db->createCommand()
//                ->delete("standard_recipe_sub_standard_recipe", ['standard_recipe_id' => $model->id])
//                ->execute();
//        }

        if (Yii::$app->request->isGet) {
            $model->save(false);
        }

        $post = Yii::$app->request->post();
        if (!empty($post)) {
            $post['StandardRecipe']['in_construction'] = false;
        }

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }
        if ($model->load($post) && $model->save()) {
                // Procesar excludeFromCost y costPercentage
            $excludeFromCost = Yii::$app->request->post('excludeFromCost', []);
            $costPercentage = Yii::$app->request->post('costPercentage', []);
            
            // Actualizar relaciones de ingredientes
            foreach ($model->ingredientRelations as $relation) {
                $ingredientId = $relation->ingredient_id;
                
                // Actualizar exclude_from_cost
                $relation->exclude_from_cost = isset($excludeFromCost[$ingredientId]);
                
                // Actualizar cost_percentage
                if (isset($costPercentage[$ingredientId])) {
                    $relation->cost_percentage = intval($costPercentage[$ingredientId]);
                } else {
                    $relation->cost_percentage = 0; // Valor por defecto
                }
                
                $relation->save(false); // Guardar sin validación
            }
            
            // También procesar subrecetas si es necesario
            /*foreach ($model->getSubStandardRecipesRelation()->all() as $relation) {
                $subRecipeId = $relation->sub_recipe_id;
                
                // Actualizar exclude_from_cost
                $relation->exclude_from_cost = isset($excludeFromCost[$subRecipeId]);
                
                // Actualizar cost_percentage
                if (isset($costPercentage[$subRecipeId])) {
                    $relation->cost_percentage = intval($costPercentage[$subRecipeId]);
                } else {
                    $relation->cost_percentage = 100;
                }
                
                $relation->save(false);
            }*/
            return $this->redirect(Url::previous('index-recipe'));
            if ($model->type == $model::STANDARD_RECIPE_TYPE_MAIN) {
                return $this->redirect(['standard-recipe/index', 'type' => $model->type]);
            } else {
                return $this->redirect(['sub-standard-recipe/index']);
            }
        } elseif ($model->hasErrors()) {
            foreach ($model->errors as $field => $error) {
                Yii::$app->session->setFlash('error', implode('\n', $error));
            }

        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionSelectIngredients($id)
    {
        $this->layout = 'blank';
        $model = $this->findModel($id);
        $form = new StandardRecipeIngredientForm();

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$form])->validate();
        }
        if ($form->load($post) && $form->validate()) {
            if (empty($form->subRecipeId)) {
                $model->addUpdateIngredient($form->ingredientId, $form->quantity);
            } else {
                $model->addUpdateSubRecipe($form->subRecipeId, $form->quantity);
            }
        }

        return $this->asJson(true);
    }

    public function actionFormSelectIngredient($id = null)
    {
        $recipe = StandardRecipe::findOne(['id' => $id]);
        return $this->renderAjax('create/_form_ingredient', [
            'model' => new \backend\models\StandardRecipeIngredientForm(),
            'recipe' => $recipe
        ]);
    }

    public function actionImportRecipes($id)
    {
        $business = Business::findOne(['id' => $id]);

        $file = UploadedFile::getInstanceByName('ingredient-file');//

        if ($file) {
            try {
                ExcelHelper::importRecipe($business, $file->tempName);
            }catch (\Exception $e) {
                $errors = json_decode($e->getMessage(), true);
                foreach ($errors as $field => $fieldErrors) {
                    Yii::$app->session->setFlash('error', implode("\n", $fieldErrors));
                }
            }
        }

        return $this->redirect(['standard-recipe/index']);
    }

    public function actionUpdateSelectedIngredient($id, $ingredientId, $isRecipe = false)
{
    $model = $this->findModel($id);
    $quantity = Yii::$app->request->post('quantity');
    $newItemId = Yii::$app->request->post('newItemId', $ingredientId);
    
    // Si el ingrediente/subreceta ha cambiado
    if ($newItemId != $ingredientId) {
        // Eliminar el ingrediente/subreceta actual
        if ($isRecipe) {
            $model->removeSubRecipe($ingredientId);
            // Agregar el nuevo con la cantidad proporcionada
            $model->addUpdateSubRecipe($newItemId, $quantity);
        } else {
            $model->removeIngredient($ingredientId);
            // Agregar el nuevo con la cantidad proporcionada
            $model->addUpdateIngredient($newItemId, $quantity);
        }
    } else {
        // Actualizar solo la cantidad
        if ($isRecipe) {
            $model->addUpdateSubRecipe($ingredientId, $quantity);
        } else {
            $model->addUpdateIngredient($ingredientId, $quantity);
        }
    }

    return $this->asJson(['success' => true]);
}
    public function actionGetAvailableIngredients()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $business = RedisKeys::getBusiness();
    
    $ingredients = IngredientStock::find()
        ->select(['id', 'ingredient as name', 'portion_um as um'])
        ->where(['business_id' => $business->id])
        ->asArray()
        ->all();
    
    return [
        'success' => true,
        'ingredients' => $ingredients
    ];
}
public function actionGetSubStandardRecipes()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
    $business = Business::findOne(['id' => $businessData['id']]);
    $subrecipes = StandardRecipe::find()
        ->where([
            'business_id' => $business->id, 
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ])
        ->select(['id', 'title', 'um'])
        ->asArray()
        ->all();
    
    return $subrecipes;
    
}

    public function actionUnselectIngredient($id, $ingredientId, $isRecipe = false)
    {
        $model = $this->findModel($id);

        if ($isRecipe) {
            $model->removeSubRecipe($ingredientId);
        } else {
            $model->removeIngredient($ingredientId);
        }

        return $this->asJson(true);
    }

    public function actionFinishRecipeCreation($id)
    {
        $this->layout = 'blank';
        $model = $this->findModel($id);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save(true, ['flowchart', 'equipment', 'steps', 'allergies'])) {
            return $this->redirect(['index']);
        }

        return $this->render('create/_finish_creation', [
            'model' => $model
        ]);
    }

    public function actionAddStep($id)
    {
        $model = $this->findModel($id);
        $step = new RecipeStep([
            'recipe_id' => $model->id,
        ]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$step])->validate();
        }

        $step->load($post);
        $step->save();

        return $this->asJson(true);
    }

    public function actionRemoveStep($recipeId, $id)
    {
        $model = $this->findModel($recipeId);
        $step = RecipeStep::findOne(['id' => $id]);

        if (!empty($step)) {
            $step->delete();
        }

        $previous = '';
        if ($model->in_construction) {
            $previous = 'create-recipe';
        } else {
            $previous = 'update-recipe';
        }
        return $this->asJson(true);
    }

    /**
     * Updates an existing StandardRecipe model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Url::remember(['standard-recipe/update', 'id' => $id], 'update-recipe');
        $model = $this->findModel($id);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
                // Procesar excludeFromCost y costPercentage
                $excludeFromCost = Yii::$app->request->post('excludeFromCost', []);
                $costPercentage = Yii::$app->request->post('costPercentage', []);
                
                // Actualizar relaciones de ingredientes
                foreach ($model->ingredientRelations as $relation) {
                    $ingredientId = $relation->ingredient_id;
                    
                    // Actualizar exclude_from_cost
                    $relation->exclude_from_cost = isset($excludeFromCost[$ingredientId]);
                    
                    // Actualizar cost_percentage
                    if (isset($costPercentage[$ingredientId])) {
                        $relation->cost_percentage = intval($costPercentage[$ingredientId]);
                    } else {
                        $relation->cost_percentage = 0; // Valor por defecto
                    }
                    
                    $relation->save(false); // Guardar sin validación
                }
            if ($model->type == $model::STANDARD_RECIPE_TYPE_SUB) {
                return $this->redirect(['sub-standard-recipe/index']);
            }

            return $this->redirect(Url::previous('index-recipe'));
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing StandardRecipe model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('keys'); // Recibir los IDs enviados desde el frontend
    
            if ($ids === 'all') {
                // Delete all recipes
                $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
                $business = Business::findOne(['id' => $businessData['id']]);
                StandardRecipe::deleteAll(['business_id' => $business->id]);
                return $this->asJson(['success' => true]);
            } else if (!empty($ids)) {
                // Delete selected recipes
                foreach ($ids as $id) {
                    $model = $this->findModel($id);
                    if ($model) {
                        $model->delete(); 
                    }
                }
                return $this->asJson(['success' => true]);
            }
        }
    }

    public function actionDeleteImage($id)
    {
        $post = Yii::$app->request->post();
        if (isset($post['key'])) {
            $imageId = $post['key'];
            $model = $this->findModel($id);

            $images = $model->getImages();
            foreach ($images as $image) {
                if ($image->getPrimaryKey() == $imageId) {
                    $model->removeImage($image);
                    break;
                }
            }

            return $this->asJson([]);
        }

        return $this->asJson(['error' => true, 'data' => $post]);
    }

    public function actionSales()
    {
        $business = RedisKeys::getBusinessData();
        $recipes = StandardRecipe::find()->where([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
            'in_menu' => true
        ])->all();

        $combos = Menu::find()->where([
            'business_id' => $business['id'],
            'in_menu' => true
        ])->all();

        $dataProvider = new ActiveDataProvider([
            'models' => array_merge($recipes, $combos)
        ]);

        return $this->render('sales', [
            'dataProvider' => $dataProvider
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

    public function actionMenuRecipes($bundle = null)
    {
        $categoryId = Yii::$app->request->get('categoryId', null);
        $sort = Yii::$app->request->get('sort', null);
        $order = Yii::$app->request->get('order', 'desc');
    
        $business = RedisKeys::getBusinessData();
    
        $bundleModel = null;
        if ($bundle !== null) {
            $bundleModel = MenuBundle::findOne(['id' => $bundle, 'business_id' => $business['id']]);
        }
    
        $category = null;
        if (!empty($categoryId)) {
            $category = RecipeCategory::find()
                ->where([
                    'business_id' => $business['id'],
                    'id' => $categoryId
                ])->one();
        }
        
        $title = Yii::$app->request->get('title');
    
        $page = (int)Yii::$app->request->get('page', 1);
        $offset = ($page - 1) * 30;
    
        Url::remember(['standard-recipe/menu-recipes', 'page' => $page, 'bundle' => $bundle], 'menu-recipes');
    
        if ($bundleModel) {
            $bundleRecipesIds = $bundleModel->getStandardRecipes(true);
            $bundleCombosIds = $bundleModel->getCombos(true);
            $recipesFilter = [
                'business_id' => $business['id'],
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
                'id' => $bundleRecipesIds,
            ];
    
            $combosFilter = [
                'business_id' => $business['id'],
                'in_menu' => true,
                'id' => $bundleCombosIds,
            ];
        } else {
            $recipesFilter = [
                'business_id' => $business['id'],
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
            ];
    
            $combosFilter = [
                'business_id' => $business['id'],
                'in_menu' => true,
            ];
        }
    
        if($category){
            $recipesFilter['type_of_recipe'] = $category->name;
            $combosFilter['category_id'] = $category->id;
        }
        if (!empty($title)) {
            $recipesFilter = ['like', 'title', "%$title%", false];
            $combosFilter = ['like', 'name', "%$title%", false];
        }
        
        // Obtener datos sin aplicar ordenación en la consulta
        $totalRecipes = (int)StandardRecipe::find()->where($recipesFilter)->count();
        $recipes = StandardRecipe::find()->where($recipesFilter)->all();
        
        $totalCombos = (int)Menu::find()->where($combosFilter)->count();
        $combos = Menu::find()->where($combosFilter)->all();
        
        // Obtener los modelos para la lista de disponibles
        $recipesFilter['in_menu'] = false;
        $availableRecipes = StandardRecipe::find()->where($recipesFilter)->all();
        
        $combosFilter['in_menu'] = false;
        $availableCombos = Menu::find()->where($combosFilter)->all();
    
        // Combinar las recetas y los combos
        $models = array_merge($recipes, $combos);
        
        // Aplicar ordenación si es necesario
        if (!empty($sort)) {
            if ($sort == 'title') {
                usort($models, function($a, $b) use ($order) {
                    $aTitle = strtolower($a instanceof StandardRecipe ? $a->title : $a->name);
                    $bTitle = strtolower($b instanceof StandardRecipe ? $b->title : $b->name);
                    
                    if ($order == 'asc') {
                        return strcmp($aTitle, $bTitle);
                    } else {
                        return strcmp($bTitle, $aTitle);
                    }
                });
            } elseif ($sort == 'cost') {
                usort($models, function($a, $b) use ($order) {
                    $aCost = $a instanceof StandardRecipe ? $a->lastPrice : $a->cost;
                    $bCost = $b instanceof StandardRecipe ? $b->lastPrice : $b->cost;
                    
                    if ($order == 'asc') {
                        return $aCost <=> $bCost;
                    } else {
                        return $bCost <=> $aCost;
                    }
                });
            } elseif ($sort == 'costPercent') {
                usort($models, function($a, $b) use ($order) {
                    $aPercent = $a->getCostPercent();
                    $bPercent = $b->getCostPercent();
                    
                    if ($order == 'asc') {
                        return $aPercent <=> $bPercent;
                    } else {
                        return $bPercent <=> $aPercent;
                    }
                });
            }
        }
        
        // Paginación manual
        $pagination = new Pagination([
            'page' => $page - 1,
            'pageSize' => 30,
            'totalCount' => count($models)
        ]);
        
        // Obtener solo los modelos para la página actual
        $paginatedModels = array_slice($models, $pagination->offset, $pagination->limit);
        
        $dataProvider = new ActiveDataProvider([
            'models' => $paginatedModels,
            'pagination' => false // La paginación se maneja manualmente
        ]);
        
        return $this->render('menu', [
            'dataProvider' => $dataProvider,
            'availableRecipes' => $availableRecipes,
            'availableCombos' => $availableCombos,
            'pagination' => $pagination,
            'bundle' => $bundleModel,
            'business' => $business,
            'category' => $category,
            'sort' => $sort,          // Pasar el parámetro de ordenación a la vista
            'order' => $order         // Pasar el orden actual a la vista
        ]);
    }

    public function actionSelectUnselectForMenu($id, $type = 'recipe')
    {
        $business = RedisKeys::getBusinessData();
        /** @var $model StandardRecipe|Menu */
        if ($type == 'recipe') {
            $model = StandardRecipe::findOne(['business_id' => $business['id'], 'id' => $id]);
        } else {
            $model = Menu::findOne(['business_id' => $business['id'], 'id' => $id]);
        }

        Yii::$app->db->createCommand()
            ->update(
                $model::tableName(),
                ['in_menu' => !$model->in_menu],
                ['id' => $model->id]
            )->execute();

        return $this->redirect(['standard-recipe/menu-recipes']);
    }

    /*public function actionAnalytics($family = 'all')
    {
        $business = RedisKeys::getBusiness();

        $recipes = StandardRecipe::find()
            ->where([
                'business_id' => $business->id,
                'in_menu' => true,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN
            ]);
        $combos = Menu::find()
            ->innerJoin('recipe_category', 'recipe_category.id=menu.category_id')
            ->where([
                'menu.business_id' => $business->id,
                'in_menu' => true,
            ]);
        if ($family != 'all') {
            $recipes->andWhere(['type_of_recipe' => $family]);
            $combos->andWhere(['recipe_category.name' => $family]);
        }

        $recipes = $recipes->all();
        $combos = $combos->all();

        $data = array_merge($recipes, $combos);
        $sortByCostPercent = $data;
        $sortByPopularity = $data;
        $sortBySales = $data;

        usort($sortByCostPercent, function ($itemA, $itemB) {
            return ($itemA->costPercent * 100) - ($itemB->costPercent * 100);
        });
        usort($sortByPopularity, function ($itemA, $itemB) {
            return $itemB->sales - $itemA->sales;
        });
        die(var_dump($sortByPopularity));
        usort($sortBySales, function ($itemA, $itemB) {
            return ($itemB->sales * $itemB->price) - ($itemA->sales * $itemA->price);
        });

        $sortByCostPercent = ArrayHelper::getColumn($sortByCostPercent, function ($item) {
            return sprintf("%s_%s", get_class($item), $item->id);
        });
        $sortByPopularity = ArrayHelper::getColumn($sortByPopularity, function ($item) {
            return sprintf("%s_%s", get_class($item), $item->id);
        });
        $sortBySales = ArrayHelper::getColumn($sortBySales, function ($item) {
            return sprintf("%s_%s", get_class($item), $item->id);
        });

        return $this->render('analytics', [
            'data' => $data,
            'sortByCostPercent' => $sortByCostPercent,
            'sortByPopularity' => $sortByPopularity,
            'sortBySales' => $sortBySales,
            'family' => $family
        ]);

    }*/
    public function actionAnalytics($family = 'all', $sort = null, $direction = 'asc')
{
    $business = RedisKeys::getBusiness();

    // Obtener recetas y combos
    $recipes = StandardRecipe::find()
        ->where([
            'business_id' => $business->id,
            'in_menu' => true,
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN
        ]);
    
    $combos = Menu::find()
        ->innerJoin('recipe_category', 'recipe_category.id=menu.category_id')
        ->where([
            'menu.business_id' => $business->id,
            'in_menu' => true,
        ]);

    // Filtrar por familia si se especifica
    if ($family != 'all') {
        $recipes->andWhere(['type_of_recipe' => $family]);
        $combos->andWhere(['recipe_category.name' => $family]);
    }

    $recipes = $recipes->all();
    $combos = $combos->all();

    // Combinar recetas y combos
    $data = array_merge($recipes, $combos);
    
    // Aplicar ordenamiento según parámetros
    if ($sort) {
        usort($data, function ($a, $b) use ($sort, $direction) {
            $compare = 0;
            
            switch ($sort) {
                case 'name':
                    $compare = strcasecmp($a->name, $b->name);
                    break;
                    
                case 'cost-percent':
                    $compare = ($a->costPercent <=> $b->costPercent);
                    break;
                    
                case 'popularity':
                    $compare = ($a->sales <=> $b->sales);
                    break;
                    
                case 'sales':
                    $salesA = $a->price * $a->sales;
                    $salesB = $b->price * $b->sales;
                    $compare = ($salesA <=> $salesB);
                    break;
            }
            
            return ($direction === 'desc') ? -$compare : $compare;
        });
    }
    
    // Calcular posiciones para cada tipo de ordenamiento
    $sortByCostPercent = $data;
    usort($sortByCostPercent, function ($a, $b) {
        return ($a->costPercent <=> $b->costPercent);
    });
    
    $sortByPopularity = $data;
    usort($sortByPopularity, function ($a, $b) {
        return ($b->sales <=> $a->sales);
    });
    
    $sortBySales = $data;
    usort($sortBySales, function ($a, $b) {
        return (($b->price * $b->sales) <=> ($a->price * $a->sales));
    });
    
    // Convertir a strings para la vista
    $sortByCostPercent = ArrayHelper::getColumn($sortByCostPercent, function ($item) {
        return sprintf("%s_%s", get_class($item), $item->id);
    });
    
    $sortByPopularity = ArrayHelper::getColumn($sortByPopularity, function ($item) {
        return sprintf("%s_%s", get_class($item), $item->id);
    });
    
    $sortBySales = ArrayHelper::getColumn($sortBySales, function ($item) {
        return sprintf("%s_%s", get_class($item), $item->id);
    });

    // Calcular Pareto (como en tu versión original)
    $totalSales = array_sum(array_map(function($item) { 
        return $item->sales; 
    }, $data));
    
    $paretoItems = $data;
    usort($paretoItems, function ($a, $b) {
        return ($b->sales <=> $a->sales);
    });
    
    $accumulatedPercentage = 0;
    $paretoCategories = [];
    
    foreach ($paretoItems as $item) {
        $itemKey = sprintf("%s_%s", get_class($item), $item->id);
        
        if ($totalSales > 0) {
            $itemPercentage = ($item->sales / $totalSales) * 100;
            $accumulatedPercentage += $itemPercentage;
            
            if ($accumulatedPercentage <= 80) {
                $paretoCategories[$itemKey] = 'verde';
            } elseif ($accumulatedPercentage <= 95) {
                $paretoCategories[$itemKey] = 'amarillo';
            } else {
                $paretoCategories[$itemKey] = 'rojo';
            }
        } else {
            $paretoCategories[$itemKey] = 'gris';
        }
    }

    return $this->render('analytics', [
        'data' => $data,
        'sortByCostPercent' => $sortByCostPercent,
        'sortByPopularity' => $sortByPopularity,
        'sortBySales' => $sortBySales,
        'family' => $family,
        'paretoCategories' => $paretoCategories,
        'totalSales' => $totalSales,
        'currentSort' => $sort,       // Para mostrar el orden actual
        'currentDirection' => $direction // Para mostrar la dirección
    ]);
}
    public function actionMenuImprovement()
    {
        $business = RedisKeys::getBusiness();
        $recipes = StandardRecipe::find()
            ->where([
                'business_id' => $business->id,
                'in_menu' => true,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN
            ])->all();
        $combos = Menu::find()
            ->innerJoin('recipe_category', 'recipe_category.id=menu.category_id')
            ->where([
                'menu.business_id' => $business->id,
                'in_menu' => true,
            ])->all();

        return $this->render('menu_improvement', [
            'data' => array_merge($recipes, $combos)
        ]);
    }

    public function actionProfitComparison()
    {
        $business = RedisKeys::getBusiness();

        $recipes = StandardRecipe::find()
            ->where([
                'business_id' => $business->id,
                'in_menu' => true,
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN
            ])->all();
        $combos = Menu::find()
            ->innerJoin('recipe_category', 'recipe_category.id=menu.category_id')
            ->where([
                'menu.business_id' => $business->id,
                'in_menu' => true,
            ])->all();

        $data = array_merge($recipes, $combos);
        $total = count($data);
        $theoreticalCost = 0;
        $desiredCost = 0;
        $totalSales = 0;
        array_walk($data, function ($item) use (&$theoreticalCost, &$totalSales, &$desiredCost) {
            $theoreticalCost += $item->getCostPercent(false);
            $desiredCost += $item->getCostPercent(true);
            $totalSales += ($item->price * $item->sales);
        });

        if ($total != 0) {
            $theoreticalCost = $theoreticalCost / $total;
            $desiredCost = $desiredCost / $total;
        } else {
            $theoreticalCost = 0;
            $desiredCost = 0;
        }

        return $this->render('profit_comparison', [
            'theoreticalCost' => $theoreticalCost,
            'desiredCost' => $desiredCost,
            'totalSales' => $totalSales,
            'business' => $business
        ]);
    }

    public function actionMatrixBcg($type = 'all')
    {

        $business = RedisKeys::getBusiness();


        return $this->render('matrix', $business->getBcgData($type));
    }

    public function actionCharts()
    {
        $business = RedisKeys::getBusiness();
        $categories = RecipeCategory::find()
            ->where([
                'business_id' => $business['id']
            ])->all();

        $totalSales = array_sum(array_map(function ($category) {
            return $category->totalSales;
        }, $categories));


        return $this->render('charts', [
            'totalSales' => $totalSales,
            'categories' => $categories
        ]);
    }

    public function actionDuplicateRecipes()
    {
        $post = Yii::$app->request->post();

        $recipes = StandardRecipe::find()->where(['id' => $post['recipes']])->all();

        foreach ($recipes as $recipe) {
            $recipe->duplicate();
        }

        return $this->redirect(Url::previous('index-recipe'));
    }

    /**
     * Finds the StandardRecipe model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return StandardRecipe the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StandardRecipe::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionCheckTitle()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $title = Yii::$app->request->post('title');
        $businessId = Yii::$app->request->post('business_id');
        $type = Yii::$app->request->post('type');
        $exists = StandardRecipe::find()
            ->where(['title' => $title, 'business_id' => $businessId, 'type' => $type])
            ->exists();

        return ['exists' => $exists];
    }
    public function actionDownloadRecipes($type)
    {
        $id = Yii::$app->request->get();
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        if (count($id) > 1) {
            $recipe_id = explode(',' ,$id['id']);
            $recipes = StandardRecipe::find()
                ->where([
                    'business_id' => $business['id'],
                    'in_construction' => 0,
                    'type' => $type,
                    'id' => $recipe_id
                ])
                ->all();
        } else {
        $recipes = StandardRecipe::find()
            ->where([
                'business_id' => $business['id'],
                'in_construction' => 0,
                'type' => $type
            ])
            ->all();
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
            $sheet->setCellValue('A1', 'Nombre');
            $sheet->setCellValue('B1', 'Costo');
            $sheet->setCellValue('C1', 'Precio de venta');
            $sheet->setCellValue('D1', 'Porcentaje de costo');
            $sheet->setCellValue('E1', 'Cantidad de ingredientes');
            $sheet->setCellValue('F1', 'Cantidad de Sub-recetas');
        } else {
            $sheet->setCellValue('A1', 'Nombre');
            $sheet->setCellValue('B1', 'Costo');
            $sheet->setCellValue('C1', 'Cantidad de ingredientes');
            $sheet->setCellValue('D1', 'Cantidad de Recetas');
        }

        $row = 2;
        foreach ($recipes as $recipe) {
            $sheet->setCellValue('A' . $row, $recipe->title);
            $sheet->setCellValue('B' . $row, '$' . number_format($recipe->recipeLastPrice, 2));
            if ($type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
                $sheet->setCellValue('C' . $row, '$' . $recipe->price);
                $sheet->setCellValue('D' . $row, $recipe->costPercent*100 . '%');
                $sheet->setCellValue('E' . $row, $recipe->getIngredientRelations()->count());
                $sheet->setCellValue('F' . $row, $recipe->getSubStandardRecipes()->count());
            } else {
                $sheet->setCellValue('C' . $row, $recipe->getIngredientRelations()->count());
                $sheet->setCellValue('D' . $row, $recipe->getSubRecipeCount()->count());
            }
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = $type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN ? 'Recetas.xlsx':'Sub-Recetas.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $fileName);
    }

    public function actionDownloadRecipesPdf($type)
    {
        $id = Yii::$app->request->get();
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        if (count($id) > 1) {
            $recipe_id = explode(',' ,$id['id']);
            $recipes = StandardRecipe::find()
                ->where([
                    'business_id' => $business['id'],
                    'in_construction' => 0,
                    'type' => $type,
                    'id' => $recipe_id
                ])
                ->all();
        } else {
        $recipes = StandardRecipe::find()
            ->where([
                'business_id' => $business['id'],
                'in_construction' => 0,
                'type' => $type
            ])
            ->all();
        }
        // die(var_dump($recipes));
        $html = '<h1>' . ($type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN ? 'Recetas' : 'Sub Recetas') . '</h1>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><th>Nombre</th><th>Costo</th>';
        if ($type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
            $html .= '<th>Precio de venta</th><th>Porcentaje de costo</th>';
        }
        $html .= '<th>Cantidad de ingredientes</th>';
        $type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN ? $html .= '<th>Cantidad de Sub-recetas</th>' : $html .= '<th>Cantidad de Recetas</th>';
        $html .= '</tr>';

        foreach ($recipes as $recipe) {
            $html .= '<tr>';
            $html .= '<td>' . $recipe->title . '</td>';
            $html .= '<td style="text-align: center;">$' . number_format($recipe->recipeLastPrice, 2) . '</td>';
            if ($type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
            $html .= '<td style="text-align: center;">$' . $recipe->price . '</td>';
            $html .= '<td style="text-align: center;">' . ($recipe->costPercent)*100 . '%</td>';
            }
            $html .= '<td style="text-align: center;">' . $recipe->getIngredientRelations()->count() . '</td>';
            $type == StandardRecipe::STANDARD_RECIPE_TYPE_MAIN ? $html .= '<td style="text-align: center;">' . $recipe->getSubStandardRecipes()->count() . '</td>': $html .= '<td style="text-align: center;">' . $recipe->getSubRecipeCount()->count() . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
       // die(var_dump($post));

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => Yii::getAlias('@runtime/mpdf')
        ]);
        $mpdf->WriteHTML($html);
        $fileName = $type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN ? 'Recetas.pdf':'Sub-Recetas.pdf';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $mpdf->Output($tempFile, \Mpdf\Output\Destination::FILE);

        return Yii::$app->response->sendFile($tempFile, $fileName);
    }
    public function actionDownloadCompleteRecipePdf()
    {
        $get = Yii::$app->request->get();
        $selectedRecipes = isset($get['id']) ? explode(',', $get['id']) : []; // Obtener los IDs de las recetas seleccionadas
        $business = \backend\helpers\RedisKeys::getBusiness();
        // Buscar todas las recetas seleccionadas
        $recipes = StandardRecipe::find()->where(['id' => $selectedRecipes])->all();
        if (empty($recipes)) {
            throw new \yii\web\NotFoundHttpException('No se encontraron recetas seleccionadas.');
        }
        // Generar PDF
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => Yii::getAlias('@runtime/mpdf'),
            'default_font' => 'dejavusans', // Usar una fuente compatible con UTF-8
        ]);
    
        $html = '';
    
        foreach ($recipes as $recipe) {
            // Título de la receta
            $html .= '<h1>' . htmlspecialchars($recipe->title) . '</h1>';
    
            // Contenedor de tabla para la imagen y los datos de la receta
            $html .= '<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">';
            $html .= '<tr>';
    
            // Columna izquierda: Datos de la receta (50% del ancho)
            $html .= '<td width="60%" style="vertical-align: top; padding-right: 10px;">';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3><strong>Tipo de receta:</strong> ' . htmlspecialchars($recipe->type_of_recipe ?? '') . '</h3>';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3 style="margin-bottom: 15px"><strong>Tiempo de preparación:</strong> ' . htmlspecialchars($recipe->time_of_preparation ?? '') . '</h3>';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3 style="margin-bottom: 15px"><strong>Rendimiento:</strong> ' . htmlspecialchars($recipe->yield ?? '') . ' ' . htmlspecialchars($recipe->yield_um ?? '') . '</h3>';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3 style="margin-bottom: 15px"><strong>Porciones:</strong> ' . htmlspecialchars($recipe->portions ?? '') . '</h3>';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3 style="margin-bottom: 15px"><strong>Duración:</strong> ' . htmlspecialchars($recipe->lifetime ?? '') . '</h3>';
    
            // Si es receta principal, mostrar precios y costos
            if ($recipe->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
                $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
                $html .= '<h3><strong>Precio:</strong> $' . htmlspecialchars($recipe->price ?? '') . '</h3>';
                $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
                $html .= '<h3><strong>Costo:</strong> $' . number_format((float)($recipe->lastPrice ?? 0), 2) . '</h3>';
                $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
                $html .= '<h3><strong>Costo %:</strong> ' . number_format((float)($recipe->costPercent ?? 0), 2) . '</h3>';
                $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            }
            $html .= '</td>'; // Cierre de la columna izquierda
    
            // Columna derecha: Imagen principal (50% del ancho)
            $html .= '<td width="40%" style="vertical-align: top; padding-left: 10px; height: 550px;">';
            $images = $recipe->getImages();
            $mainImageFound = false;
            if (!empty($images)) {
                foreach ($images as $image) {
                    if ($image['isMain'] == 1 && $image['filePath'] !== 'placeholder.svg') {
                        $imagePath = Yii::getAlias('@web') . $image->getPath(); // Ruta relativa
                        $imagePath1 = '/app/backend/web/' . $imagePath; // Ruta absoluta
    
                        if (file_exists($imagePath1)) {
                            // Obtener la extensión real de la imagen
                            $imageExtension = pathinfo($imagePath1, PATHINFO_EXTENSION);
                            $imageData = base64_encode(file_get_contents($imagePath1));
                            $imageSrc = "data:image/$imageExtension;base64,{$imageData}";
    
                            // Agregar la imagen principal (a la derecha)
                            $html .= '<div style="height: 100%; display: flex; align-items: center; justify-content: center;">';
                            $html .= '<img src="' . $imageSrc . '" style="max-width: 100%; max-height: 550px; height: auto;" />';
                            $html .= '</div>';
                            $mainImageFound = true;
                            break; // Solo necesitamos la imagen principal
                        }
                    }
                }
            }
            if (!$mainImageFound) {
                // Agregar un contenedor vacío para la imagen principal
                $html .= '<div style="width: 100%; height: 200px; border: 1px solid #ccc;"></div>';
            }
            $html .= '</td>'; // Cierre de la columna derecha
    
            $html .= '</tr>';
            $html .= '</table>'; // Cierre de la tabla
            //die(var_dump($html));
            $mpdf->WriteHTML($html);
            // Agregar los ingredientes
            $html = '';
            $html .= '<h2>Ingredientes</h2>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
            $html .= '<tr><th>INGREDIENTE</th><th>CANTIDAD</th><th>COSTO</th></tr>';
            foreach ($recipe->ingredientRelations as $index => $ingredientStandardRecipe) {
                $cost = $ingredientStandardRecipe->lastUnitPrice * $ingredientStandardRecipe->quantity;
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($ingredientStandardRecipe->ingredient->ingredient) . '</td>';
                $html .= '<td>' . htmlspecialchars($ingredientStandardRecipe->quantity . ' ' . $ingredientStandardRecipe->ingredient->portion_um) . '</td>';
                $html .= '<td>' . htmlspecialchars($business->formatter->asCurrency($cost)) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
    
            // Separar los pasos en procedimientos y cuidados especiales
            $steps = RecipeStep::find()->where(['recipe_id' => $recipe->id])->all();
            $procedureSteps = [];
            $specialSteps = [];
            foreach ($steps as $step) {
                if ($step->type === 'procedure') {
                    $procedureSteps[] = $step;
                } elseif ($step->type === 'special') {
                    $specialSteps[] = $step;
                }
            }
    
            // Procedimiento
            $html .= '<h2>Procedimiento</h2>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
            $html .= '<tr><th>#</th><th>ACTIVIDAD</th><th>TIEMPO</th><th>INDICADOR</th></tr>';
            foreach ($procedureSteps as $step) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($step->number) . '</td>';
                $html .= '<td>' . htmlspecialchars($step->activity) . '</td>';
                $html .= '<td>' . htmlspecialchars($step->time ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($step->indicator ?? '') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            $mpdf->WriteHTML($html);
            $html = '';
            $html .= '<h3>Foto del procedimiento</h3>';
            // Otras imágenes (no principales)
            foreach ($images as $image) {
                if ($image['isMain'] != 1 && $image['filePath'] !== 'placeholder.svg') {
                    $imagePath = Yii::getAlias('@web') . $image->getPath('300x300'); // Ruta relativa
                    $imagePath1 = '/app/backend/web/' . $imagePath; // Ruta absoluta
    
                    if (file_exists($imagePath1)) {
                        $imageExtension = pathinfo($imagePath1, PATHINFO_EXTENSION);
                        $imageData = base64_encode(file_get_contents($imagePath1));
                        $imageSrc = "data:image/$imageExtension;base64,{$imageData}";
    
                        $html .= '<div style="text-align: center; margin-bottom: 20px;">';
                        $html .= '<img src="' . $imageSrc . '" style="max-width: 100%; height: auto;" />';
                        $html .= '</div>';
                        $mpdf->WriteHTML($html);
                        $html = '';
                    }
                }
            }
            $mpdf->WriteHTML($html);
            $html = '';
            // Cuidados y medidas especiales
            $html .= '<h2>Cuidados y medidas especiales</h2>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
            $html .= '<tr><th>#</th><th>ACTIVIDAD</th><th>TIEMPO</th><th>INDICADOR</th></tr>';
            foreach ($specialSteps as $step) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($step->number) . '</td>';
                $html .= '<td>' . htmlspecialchars($step->activity) . '</td>';
                $html .= '<td>' . htmlspecialchars($step->time ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($step->indicator ?? '') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
    
            // Alergies
            $allergies = Yii::$app->params['allergies'];
            $selectedAllergies = [];
            $html .= '<h2>Alérgenos</h2>';
    
            if (!empty($recipe->allergies)) {
                $selectedAllergies = array_values(explode(";", trim($recipe->allergies)));
                $allergies = array_unique(array_values(array_merge($allergies, $selectedAllergies)));
            }
            $html .= '<table style="width: 100%; border-collapse: collapse;">';
            $html .= '<tr>'; // Fila de la tabla
    
            foreach ($allergies as $index => $allergy) {
                // Verificar si el alérgeno está seleccionado
                $isChecked = in_array($allergy, $selectedAllergies);
    
                // Usar un símbolo de checkbox marcado o desmarcado
                $checkbox = $isChecked ? '☑' : '☐'; // ☑ = Marcado, ☐ = Desmarcado
    
                // Mostrar el alérgeno con el checkbox en una celda de la tabla
                $html .= '<td style="padding: 5px; border: 1px solid #ccc;">' . $checkbox . ' ' . htmlspecialchars($allergy) . '</td>';
    
                // Si hay 4 alérgenos en una fila, cerrar la fila y abrir una nueva
                if (($index + 1) % 4 === 0) {
                    $html .= '</tr><tr>'; // Cerrar fila y abrir una nueva
                }
            }
            if (count($allergies) % 5 !== 0) {
                $html .= '</tr>';
            }
    
            $html .= '</table>'; // Cierre de la tabla
            $html .= '<h2>Equipo</h2>';
            $html .=  $recipe->equipment;
    
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
        }
    
        // Añadir CSS personalizado para asegurar que el diseño se mantenga
        $stylesheet = '
        img {
            max-width: 100%;
            height: auto;
        }
    ';
        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html);
    
        $fileName = 'Complete_Recipes.pdf';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $mpdf->Output($tempFile, \Mpdf\Output\Destination::FILE);
    
        return Yii::$app->response->sendFile($tempFile, $fileName);
    }

 public function actionExportRecipesToExcel()
 {
     $get = Yii::$app->request->get();
     $selectedRecipes = isset($get['id']) ? explode(',', $get['id']) : []; // Obtener los IDs de las recetas seleccionadas
     $business = \backend\helpers\RedisKeys::getBusiness();
 
     // Buscar todas las recetas seleccionadas
     $recipes = StandardRecipe::find()->where(['id' => $selectedRecipes])->all();
     if (empty($recipes)) {
         throw new \yii\web\NotFoundHttpException('No se encontraron recetas seleccionadas.');
     }
 
     $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
 
     // Crear hojas principales
     $recipesSheet = $spreadsheet->getActiveSheet();
     $recipesSheet->setTitle('Recetas');
 
     $ingredientsSheet = $spreadsheet->createSheet();
     $ingredientsSheet->setTitle('Ingredients');
 
     $insumosSheet = $spreadsheet->createSheet();
     $insumosSheet->setTitle('Insumos');
    
     $convoySheet = $spreadsheet->createSheet();
     $convoySheet->setTitle('Convoy');
 
 
     // Definir las cabeceras para cada hoja
     $recipesSheet->setCellValue('A1', 'Nombre');
     $recipesSheet->setCellValue('B1', 'Tipo de Receta');
     $recipesSheet->setCellValue('C1', 'Tiempo de preparación');
     $recipesSheet->setCellValue('D1', 'Rendimiento');
     $recipesSheet->setCellValue('E1', 'Rendimiento UM');
     $recipesSheet->setCellValue('F1', 'Porciones');
     $recipesSheet->setCellValue('G1', 'Duración');
     $recipesSheet->setCellValue('H1', 'Precio');
     $recipesSheet->setCellValue('I1', 'Costo');
     $recipesSheet->setCellValue('J1', 'Porcentaje de costo');
     $recipesSheet->setCellValue('K1', 'Alimento o Bebida');
     $recipesSheet->setCellValue('L1', 'Convoy');
 
     $ingredientsSheet->setCellValue('A1', 'Receta');
     $ingredientsSheet->setCellValue('B1', 'Insumo');
     $ingredientsSheet->setCellValue('C1', 'Cantidad');
     $ingredientsSheet->setCellValue('D1', 'UM');
     $ingredientsSheet->setCellValue('E1', 'Costo');
 
     $insumosSheet->setCellValue('A1', 'Insumo');
     $insumosSheet->setCellValue('B1', 'Cantidad');
     $insumosSheet->setCellValue('C1', 'UM');
     $insumosSheet->setCellValue('D1', 'Costo');

     $convoySheet->setCellValue('A1', 'ID Convoy');
     $convoySheet->setCellValue('B1', 'Nombre Convoy');
 
     // Ajustar automáticamente el tamaño de las columnas en todas las hojas
     foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'] as $column) {
         $recipesSheet->getColumnDimension($column)->setAutoSize(true);
     }
 
     foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
         $ingredientsSheet->getColumnDimension($column)->setAutoSize(true);
         $insumosSheet->getColumnDimension($column)->setAutoSize(true);
     }
 
     // Centrar los valores en todas las celdas
     $centerStyle = [
         'alignment' => [
             'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
             'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
         ],
     ];
 
     $recipesRow = 2; // Initialize the variable
     $ingredientsRow = 2; // Initialize the variable
     $insumosRow = 2; // Initialize the variable
 
     /** @var UnitOfMeasurement[] $unitOfMeasurements */
     $unitOfMeasurements = UnitOfMeasurement::find()
         ->select('name')
         ->groupBy('name')
         ->all();
 
     $categories = RecipeCategory::find()
         ->where([
             'business_id' => $business['id']
         ])->all();
 
     $ingredientStock = IngredientStock::find()
         ->where([
             'business_id' => $business['id']
         ])->all();
     $convoy = Convoy::find()
         ->where([
             'business_id' => $business['id']
         ])->all();
 
     // Crear hojas para listas de validación
     $umSheet = $spreadsheet->createSheet();
     $umSheet->setTitle('UMs');
     $umSheet->setCellValue('A1', 'Unidad de Medida');
 
     $categorySheet = $spreadsheet->createSheet();
     $categorySheet->setTitle('Categorias');
     $categorySheet->setCellValue('A1', 'Categoría');
 
     // Llenar la hoja de unidades de medida
     $row = 2;
     foreach ($unitOfMeasurements as $um) {
         $umSheet->setCellValue("A$row", $um->name);
         $row++;
     }
 
     // Llenar la hoja de categorías
     $row = 2;
     foreach ($categories as $category) {
         $categorySheet->setCellValue("A$row", sprintf("%s", $category->name));
         $row++;
     }
     $rowConvoy = 2;
     foreach ($convoy as $convoy) {
         $convoySheet->setCellValue("A$rowConvoy", sprintf("%s", $convoy->id));
         $convoySheet->setCellValue("B$rowConvoy", sprintf("%s", $convoy->name));
         $rowConvoy++;
     }
 
     // Llenar la hoja de insumos
     foreach ($ingredientStock as $ingredient) {
         $insumosSheet->setCellValue('A' . $insumosRow, $ingredient->ingredient);
         $insumosSheet->setCellValue('B' . $insumosRow, $ingredient->quantity);
         $insumosSheet->setCellValue('C' . $insumosRow, $ingredient->um);
         $insumosSheet->setCellValue('D' . $insumosRow, $ingredient->lastPrice);
         $insumosRow++;
     }
 
     // Crear rangos nombrados para las listas de validación
     $spreadsheet->addNamedRange(
         new \PhpOffice\PhpSpreadsheet\NamedRange(
             'UMs',
             $umSheet,
             'A2:A' . ($row - 1) // Rango de celdas con las unidades de medida
         )
     );
 
     $spreadsheet->addNamedRange(
         new \PhpOffice\PhpSpreadsheet\NamedRange(
             'Categorias',
             $categorySheet,
             'A2:A' . ($row - 1) // Rango de celdas con las categorías
         )
     );

     $dataValidationFoodOrDrink = $recipesSheet->getCell('K2')->getDataValidation();
     $dataValidationFoodOrDrink->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationFoodOrDrink->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationFoodOrDrink->setAllowBlank(false);
     $dataValidationFoodOrDrink->setShowInputMessage(true);
     $dataValidationFoodOrDrink->setShowErrorMessage(true);
     $dataValidationFoodOrDrink->setShowDropDown(true);
     $dataValidationFoodOrDrink->setErrorTitle('Error de entrada');
     $dataValidationFoodOrDrink->setError('Este valor no es admitido');
     $dataValidationFoodOrDrink->setPromptTitle('Selecciona una opción');
     $dataValidationFoodOrDrink->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationFoodOrDrink->setFormula1('"Alimento,Bebida"'); // Lista de opciones
 
     // Aplicar la validación a todas las celdas de la columna K (Alimento o Bebida) en la hoja de recetas
     for ($i = 2; $i <= 50; $i++) {
         $recipesSheet->getCell("K$i")->setDataValidation(clone $dataValidationFoodOrDrink);
     }
     
     // Aplicar validación de datos a la columna de unidades de medida (UM) en la hoja de ingredientes
     $dataValidationUM = $ingredientsSheet->getCell('D2')->getDataValidation();
     $dataValidationUM->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationUM->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationUM->setAllowBlank(false);
     $dataValidationUM->setShowInputMessage(true);
     $dataValidationUM->setShowErrorMessage(true);
     $dataValidationUM->setShowDropDown(true);
     $dataValidationUM->setErrorTitle('Error de entrada');
     $dataValidationUM->setError('Este valor no es admitido');
     $dataValidationUM->setPromptTitle('Selecciona una unidad de medida');
     $dataValidationUM->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationUM->setFormula1('=UMs!$A$2:$A$' . ($row - 1)); // Referencia al rango nombrado

     // Aplicar validación de datos a la columna de unidades de medida (UM) en la hoja de recetas
     $dataValidationUMRecipes = $recipesSheet->getCell('E2')->getDataValidation();
     $dataValidationUMRecipes->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationUMRecipes->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationUMRecipes->setAllowBlank(false);
     $dataValidationUMRecipes->setShowInputMessage(true);
     $dataValidationUMRecipes->setShowErrorMessage(true);
     $dataValidationUMRecipes->setShowDropDown(true);
     $dataValidationUMRecipes->setErrorTitle('Error de entrada');
     $dataValidationUMRecipes->setError('Este valor no es admitido');
     $dataValidationUMRecipes->setPromptTitle('Selecciona una unidad de medida');
     $dataValidationUMRecipes->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationUMRecipes->setFormula1('=UMs!$A$2:$A$' . ($row - 1)); // Referencia al rango nombrado
 
     // Aplicar validación de datos a la columna de categorías (Categoría) en la hoja de recetas
     $dataValidationCategory = $recipesSheet->getCell('B2')->getDataValidation();
     $dataValidationCategory->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationCategory->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationCategory->setAllowBlank(false);
     $dataValidationCategory->setShowInputMessage(true);
     $dataValidationCategory->setShowErrorMessage(true);
     $dataValidationCategory->setShowDropDown(true);
     $dataValidationCategory->setErrorTitle('Error de entrada');
     $dataValidationCategory->setError('Este valor no es admitido');
     $dataValidationCategory->setPromptTitle('Selecciona una categoría');
     $dataValidationCategory->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationCategory->setFormula1('=Categorias!$A$2:$A$' . ($row - 1)); // Referencia al rango nombrado

      // Aplicar validación de datos a la columna de convoy (Convoy) en la hoja de recetas
    $dataValidationConvoy = $recipesSheet->getCell('L2')->getDataValidation();
    $dataValidationConvoy->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $dataValidationConvoy->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $dataValidationConvoy->setAllowBlank(false);
    $dataValidationConvoy->setShowInputMessage(true);
    $dataValidationConvoy->setShowErrorMessage(true);
    $dataValidationConvoy->setShowDropDown(true);
    $dataValidationConvoy->setErrorTitle('Error de entrada');
    $dataValidationConvoy->setError('Este valor no es admitido');
    $dataValidationConvoy->setPromptTitle('Selecciona un convoy');
    $dataValidationConvoy->setPrompt('Por favor, selecciona un valor del desplegable.');
    $dataValidationConvoy->setFormula1('=Convoy!$B$2:$B$' . ($row - 1)); // Referencia al rango nombrado
 
 
     // Aplicar la validación a todas las celdas de la columna D (UM) en la hoja de ingredientes
     for ($i = 2; $i <= 50; $i++) {
         $ingredientsSheet->getCell("C$i")->setDataValidation(clone $dataValidationUM);
     }
 
     // Aplicar la validación a todas las celdas de la columna B (Categoría) en la hoja de recetas
     for ($i = 2; $i <= 50; $i++) {
         $recipesSheet->getCell("B$i")->setDataValidation(clone $dataValidationCategory);
     }
       // Aplicar la validación a todas las celdas de la columna E (Categoría) en la hoja de recetas
       for ($i = 2; $i <= 50; $i++) {
        $recipesSheet->getCell("E$i")->setDataValidation(clone $dataValidationUMRecipes);
    }
     // Aplicar la validación a todas las celdas de la columna J (Convoy) en la hoja de recetas
     for ($i = 2; $i <= 50; $i++) {
        $recipesSheet->getCell("L$i")->setDataValidation(clone $dataValidationConvoy);
    }

    
     // Llenar los datos de las recetas
     $recipesRow = 2;
     $ingredientsRow = 2;
 
     foreach ($recipes as $recipe) {
         // Hoja de Recetas
         $recipesSheet->setCellValue('A' . $recipesRow, $recipe->title);
         $recipesSheet->setCellValue('B' . $recipesRow, $recipe->type_of_recipe);
         $recipesSheet->setCellValue('C' . $recipesRow, $recipe->time_of_preparation);
         $recipesSheet->setCellValue('D' . $recipesRow, $recipe->yield);
         $recipesSheet->setCellValue('E' . $recipesRow, $recipe->yield_um);
         $recipesSheet->setCellValue('F' . $recipesRow, $recipe->portions);
         $recipesSheet->setCellValue('G' . $recipesRow, $recipe->lifetime);
         $recipesSheet->setCellValue('H' . $recipesRow, $recipe->price);
         $recipesSheet->setCellValue('I' . $recipesRow, $recipe->lastPrice);
         $recipesSheet->setCellValue('J' . $recipesRow, $recipe->costPercent);
         $recipesSheet->setCellValue('K' . $recipesRow, $recipe->is_food ? 'Alimento' : 'Bebida');
         $recipesSheet->setCellValue('L' . $recipesRow, Convoy::find()
         ->where([
             'id' => $recipe->convoy_id
         ])->one()->name ?? '');
 
         // Hoja de Ingredientes
         foreach ($recipe->ingredientRelations as $ingredientRelation) {
             $ingredientsSheet->setCellValue('A' . $ingredientsRow, $recipe->title);
             $ingredientsSheet->setCellValue('B' . $ingredientsRow, $ingredientRelation->ingredient->ingredient);
             $ingredientsSheet->setCellValue('C' . $ingredientsRow, $ingredientRelation->quantity);
             $ingredientsSheet->setCellValue('D' . $ingredientsRow, $ingredientRelation->ingredient->portion_um);
             $ingredientsSheet->setCellValue('E' . $ingredientsRow, $ingredientRelation->lastPrice);
             $ingredientsRow++;
         }
 
         $recipesRow++;
     }
 
    // Aplicar validación de datos a la columna de insumos en la hoja de ingredientes
    $dataValidationInsumos = $ingredientsSheet->getCell('B2')->getDataValidation();
    $dataValidationInsumos->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $dataValidationInsumos->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $dataValidationInsumos->setAllowBlank(false);
    $dataValidationInsumos->setShowInputMessage(true);
    $dataValidationInsumos->setShowErrorMessage(true);
    $dataValidationInsumos->setShowDropDown(true);
    $dataValidationInsumos->setErrorTitle('Error de entrada');
    $dataValidationInsumos->setError('Este valor no es admitido');
    $dataValidationInsumos->setPromptTitle('Selecciona un insumo');
    $dataValidationInsumos->setPrompt('Por favor, selecciona un valor del desplegable.');
    $dataValidationInsumos->setFormula1('=Insumos!$A$2:$A$' . ($insumosRow - 1)); // Referencia al rango nombrado

    // Aplicar la validación a todas las celdas de la columna B (Insumo) en la hoja de ingredientes
    for ($i = 2; $i <= 100; $i++) {
        $ingredientsSheet->getCell("B$i")->setDataValidation(clone $dataValidationInsumos);
    }

    // Agregar fórmulas para calcular automáticamente el costo y cargar la unidad de medida
    for ($i = 2; $i <= 100; $i++) {
        $ingredientsSheet->setCellValue("D$i", "=IFERROR(VLOOKUP(B$i, Insumos!A:D, 3, FALSE), \"\")");
        $ingredientsSheet->setCellValue("E$i", "=IFERROR(C$i * VLOOKUP(B$i, Insumos!A:D, 4, FALSE), \"\")");
    }
    // Aplicar el estilo centrado a todas las celdas en cada hoja
    $recipesSheet->getStyle('A1:Q' . ($recipesRow - 1))->applyFromArray($centerStyle);
    $ingredientsSheet->getStyle('A1:E' . ($ingredientsRow - 1))->applyFromArray($centerStyle);
    $insumosSheet->getStyle('A1:D' . ($insumosRow - 1))->applyFromArray($centerStyle);
 
     // Crear el archivo Excel
     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
     $fileName = 'Recetas.xlsx';
 
     // Configurar las cabeceras para forzar la descarga
     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
     header('Content-Disposition: attachment;filename="' . $fileName . '"');
     header('Cache-Control: max-age=0');
 
     // Enviar el archivo al navegador
     $writer->save('php://output');
     exit;
 }

 public function actionExportRecipesPlantilla()
 {
     $business = \backend\helpers\RedisKeys::getBusiness();
 
     $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
 
     // Crear hojas principales
     $recipesSheet = $spreadsheet->getActiveSheet();
     $recipesSheet->setTitle('Plantilla para importar recetas');
 
     $ingredientsSheet = $spreadsheet->createSheet();
     $ingredientsSheet->setTitle('Ingredients');
 
     $insumosSheet = $spreadsheet->createSheet();
     $insumosSheet->setTitle('Insumos');
    
     $convoySheet = $spreadsheet->createSheet();
     $convoySheet->setTitle('Convoy');
 
     // Definir las cabeceras para cada hoja
     $recipesSheet->setCellValue('A1', 'Nombre');
     $recipesSheet->setCellValue('B1', 'Tipo de Receta');
     $recipesSheet->setCellValue('C1', 'Tiempo de preparación');
     $recipesSheet->setCellValue('D1', 'Rendimiento');
     $recipesSheet->setCellValue('E1', 'Rendimiento UM');
     $recipesSheet->setCellValue('F1', 'Porciones');
     $recipesSheet->setCellValue('G1', 'Duración');
     $recipesSheet->setCellValue('H1', 'Precio');
     $recipesSheet->setCellValue('I1', 'Alimento o Bebida');
     $recipesSheet->setCellValue('J1', 'Convoy');
 
     $ingredientsSheet->setCellValue('A1', 'Receta');
     $ingredientsSheet->setCellValue('B1', 'Insumo');
     $ingredientsSheet->setCellValue('C1', 'Cantidad');
     $ingredientsSheet->setCellValue('D1', 'UM');
     $ingredientsSheet->setCellValue('E1', 'Costo');
 
     $insumosSheet->setCellValue('A1', 'Insumo');
     $insumosSheet->setCellValue('B1', 'Cantidad');
     $insumosSheet->setCellValue('C1', 'UM');
     $insumosSheet->setCellValue('D1', 'Costo');

     $convoySheet->setCellValue('A1', 'ID Convoy');
     $convoySheet->setCellValue('B1', 'Nombre Convoy');
 
     // Ajustar automáticamente el tamaño de las columnas en todas las hojas
     foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'] as $column) {
         $recipesSheet->getColumnDimension($column)->setAutoSize(true);
     }
 
     foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
         $ingredientsSheet->getColumnDimension($column)->setAutoSize(true);
         $insumosSheet->getColumnDimension($column)->setAutoSize(true);
     }
 
     // Centrar los valores en todas las celdas
     $centerStyle = [
         'alignment' => [
             'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
             'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
         ],
     ];
 
     $recipesRow = 2; // Initialize the variable
     $ingredientsRow = 2; // Initialize the variable
     $insumosRow = 2; // Initialize the variable
 
     /** @var UnitOfMeasurement[] $unitOfMeasurements */
     $unitOfMeasurements = UnitOfMeasurement::find()
         ->select('name')
         ->groupBy('name')
         ->all();
 
     $categories = RecipeCategory::find()
         ->where([
             'business_id' => $business['id']
         ])->all();
 
     $ingredientStock = IngredientStock::find()
         ->where([
             'business_id' => $business['id']
         ])->all();
     $convoy = Convoy::find()
         ->where([
             'business_id' => $business['id']
         ])->all();
     // Crear hojas para listas de validación
     $umSheet = $spreadsheet->createSheet();
     $umSheet->setTitle('UMs');
     $umSheet->setCellValue('A1', 'Unidad de Medida');
 
     $categorySheet = $spreadsheet->createSheet();
     $categorySheet->setTitle('Categorias');
     $categorySheet->setCellValue('A1', 'Categoría');
 
     // Llenar la hoja de unidades de medida
     $row = 2;
     foreach ($unitOfMeasurements as $um) {
         $umSheet->setCellValue("A$row", $um->name);
         $row++;
     }
 
     // Llenar la hoja de categorías
     $row = 2;
     foreach ($categories as $category) {
         $categorySheet->setCellValue("A$row", sprintf("%s", $category->name));
         $row++;
     }
    $rowConvoy = 2;
     foreach ($convoy as $convoy) {
         $convoySheet->setCellValue("A$rowConvoy", sprintf("%s", $convoy->id));
         $convoySheet->setCellValue("B$rowConvoy", sprintf("%s", $convoy->name));
         $rowConvoy++;
     }
 
     // Llenar la hoja de insumos
     foreach ($ingredientStock as $ingredient) {
         $insumosSheet->setCellValue('A' . $insumosRow, $ingredient->ingredient);
         $insumosSheet->setCellValue('B' . $insumosRow, $ingredient->quantity);
         $insumosSheet->setCellValue('C' . $insumosRow, $ingredient->um);
         $insumosSheet->setCellValue('D' . $insumosRow, $ingredient->lastPrice);
         $insumosRow++;
     }
 
     // Crear rangos nombrados para las listas de validación
     $spreadsheet->addNamedRange(
         new \PhpOffice\PhpSpreadsheet\NamedRange(
             'UMs',
             $umSheet,
             'A2:A' . ($row - 1) // Rango de celdas con las unidades de medida
         )
     );
 
     $spreadsheet->addNamedRange(
         new \PhpOffice\PhpSpreadsheet\NamedRange(
             'Categorias',
             $categorySheet,
             'A2:A' . ($row - 1) // Rango de celdas con las categorías
         )
     );
     

    $dataValidationFoodOrDrink = $recipesSheet->getCell('I2')->getDataValidation();
    $dataValidationFoodOrDrink->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $dataValidationFoodOrDrink->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $dataValidationFoodOrDrink->setAllowBlank(false);
    $dataValidationFoodOrDrink->setShowInputMessage(true);
    $dataValidationFoodOrDrink->setShowErrorMessage(true);
    $dataValidationFoodOrDrink->setShowDropDown(true);
    $dataValidationFoodOrDrink->setErrorTitle('Error de entrada');
    $dataValidationFoodOrDrink->setError('Este valor no es admitido');
    $dataValidationFoodOrDrink->setPromptTitle('Selecciona una opción');
    $dataValidationFoodOrDrink->setPrompt('Por favor, selecciona un valor del desplegable.');
    $dataValidationFoodOrDrink->setFormula1('"Alimento,Bebida"'); // Lista de opciones

    // Aplicar la validación a todas las celdas de la columna K (Alimento o Bebida) en la hoja de recetas
    for ($i = 2; $i <= 50; $i++) {
        $recipesSheet->getCell("I$i")->setDataValidation(clone $dataValidationFoodOrDrink);
    }
    
     // Aplicar validación de datos a la columna de unidades de medida (UM) en la hoja de ingredientes
     $dataValidationUM = $ingredientsSheet->getCell('D2')->getDataValidation();
     $dataValidationUM->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationUM->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationUM->setAllowBlank(false);
     $dataValidationUM->setShowInputMessage(true);
     $dataValidationUM->setShowErrorMessage(true);
     $dataValidationUM->setShowDropDown(true);
     $dataValidationUM->setErrorTitle('Error de entrada');
     $dataValidationUM->setError('Este valor no es admitido');
     $dataValidationUM->setPromptTitle('Selecciona una unidad de medida');
     $dataValidationUM->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationUM->setFormula1('=UMs!$A$2:$A$' . ($row - 1)); // Referencia al rango nombrado
 
     // Aplicar validación de datos a la columna de unidades de medida (UM) en la hoja de recetas
     $dataValidationUMRecipes = $recipesSheet->getCell('E2')->getDataValidation();
     $dataValidationUMRecipes->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationUMRecipes->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationUMRecipes->setAllowBlank(false);
     $dataValidationUMRecipes->setShowInputMessage(true);
     $dataValidationUMRecipes->setShowErrorMessage(true);
     $dataValidationUMRecipes->setShowDropDown(true);
     $dataValidationUMRecipes->setErrorTitle('Error de entrada');
     $dataValidationUMRecipes->setError('Este valor no es admitido');
     $dataValidationUMRecipes->setPromptTitle('Selecciona una unidad de medida');
     $dataValidationUMRecipes->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationUMRecipes->setFormula1('=UMs!$A$2:$A$' . ($row - 1)); // Referencia al rango nombrado
 
     // Aplicar validación de datos a la columna de categorías (Categoría) en la hoja de recetas
     $dataValidationCategory = $recipesSheet->getCell('B2')->getDataValidation();
     $dataValidationCategory->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationCategory->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationCategory->setAllowBlank(false);
     $dataValidationCategory->setShowInputMessage(true);
     $dataValidationCategory->setShowErrorMessage(true);
     $dataValidationCategory->setShowDropDown(true);
     $dataValidationCategory->setErrorTitle('Error de entrada');
     $dataValidationCategory->setError('Este valor no es admitido');
     $dataValidationCategory->setPromptTitle('Selecciona una categoría');
     $dataValidationCategory->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationCategory->setFormula1('=Categorias!$A$2:$A$' . ($row - 1)); // Referencia al rango nombrado

     // Aplicar validación de datos a la columna de convoy (Convoy) en la hoja de recetas
    $dataValidationConvoy = $recipesSheet->getCell('J2')->getDataValidation();
    $dataValidationConvoy->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $dataValidationConvoy->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $dataValidationConvoy->setAllowBlank(false);
    $dataValidationConvoy->setShowInputMessage(true);
    $dataValidationConvoy->setShowErrorMessage(true);
    $dataValidationConvoy->setShowDropDown(true);
    $dataValidationConvoy->setErrorTitle('Error de entrada');
    $dataValidationConvoy->setError('Este valor no es admitido');
    $dataValidationConvoy->setPromptTitle('Selecciona un convoy');
    $dataValidationConvoy->setPrompt('Por favor, selecciona un valor del desplegable.');
    $dataValidationConvoy->setFormula1('=Convoy!$B$2:$B$' . ($row - 1)); // Referencia al rango nombrado
 
     // Aplicar la validación a todas las celdas de la columna D (UM) en la hoja de ingredientes
     for ($i = 2; $i <= 50; $i++) {
         $ingredientsSheet->getCell("D$i")->setDataValidation(clone $dataValidationUM);
     }
 
     // Aplicar la validación a todas las celdas de la columna B (Categoría) en la hoja de recetas
     for ($i = 2; $i <= 50; $i++) {
         $recipesSheet->getCell("B$i")->setDataValidation(clone $dataValidationCategory);
     }
     // Aplicar la validación a todas las celdas de la columna E (Categoría) en la hoja de recetas
     for ($i = 2; $i <= 50; $i++) {
         $recipesSheet->getCell("E$i")->setDataValidation(clone $dataValidationUMRecipes);
     }

     // Aplicar la validación a todas las celdas de la columna J (Convoy) en la hoja de recetas
    for ($i = 2; $i <= 50; $i++) {
        $recipesSheet->getCell("J$i")->setDataValidation(clone $dataValidationConvoy);
    }
     // Aplicar el estilo centrado a todas las celdas en cada hoja
     $recipesSheet->getStyle('A1:Q' . ($recipesRow - 1))->applyFromArray($centerStyle);
     $ingredientsSheet->getStyle('A1:E' . ($ingredientsRow - 1))->applyFromArray($centerStyle);
     $insumosSheet->getStyle('A1:D' . ($insumosRow - 1))->applyFromArray($centerStyle);
 
     // Aplicar validación de datos a la columna de insumos en la hoja de ingredientes
     $dataValidationInsumos = $ingredientsSheet->getCell('B2')->getDataValidation();
     $dataValidationInsumos->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationInsumos->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationInsumos->setAllowBlank(false);
     $dataValidationInsumos->setShowInputMessage(true);
     $dataValidationInsumos->setShowErrorMessage(true);
     $dataValidationInsumos->setShowDropDown(true);
     $dataValidationInsumos->setErrorTitle('Error de entrada');
     $dataValidationInsumos->setError('Este valor no es admitido');
     $dataValidationInsumos->setPromptTitle('Selecciona un insumo');
     $dataValidationInsumos->setPrompt('Por favor, selecciona un valor del desplegable.');
     $dataValidationInsumos->setFormula1('=Insumos!$A$2:$A$' . ($insumosRow - 1)); // Referencia al rango nombrado
 
     // Aplicar la validación a todas las celdas de la columna B (Insumo) en la hoja de ingredientes
     for ($i = 2; $i <= 100; $i++) {
         $ingredientsSheet->getCell("B$i")->setDataValidation(clone $dataValidationInsumos);
     }
 
     // Agregar fórmulas para calcular automáticamente el costo y cargar la unidad de medida
     for ($i = 2; $i <= 100; $i++) {
         $ingredientsSheet->setCellValue("D$i", "=IFERROR(VLOOKUP(B$i, Insumos!A:D, 3, FALSE), \"\")");
         $ingredientsSheet->setCellValue("E$i", "=IFERROR(C$i * VLOOKUP(B$i, Insumos!A:D, 4, FALSE), \"\")");
     }
 
     // Crear el archivo Excel
     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
     $fileName = 'Plantilla para importar recetas.xlsx';
 
     // Configurar las cabeceras para forzar la descarga
     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
     header('Content-Disposition: attachment;filename="' . $fileName . '"');
     header('Cache-Control: max-age=0');
 
     // Enviar el archivo al navegador
     $writer->save('php://output');
     exit;
 }
 public function actionEditStep()
{
    $id = Yii::$app->request->post('id');
    $step = RecipeStep::findOne($id);

    if ($step) {
        $step->activity = Yii::$app->request->post('activity');
        $step->time = Yii::$app->request->post('time');
        $step->indicator = Yii::$app->request->post('indicator');
        if ($step->save()) {
            return $this->asJson(['success' => true]);
        }
    }

    return $this->asJson(['success' => false]);
}
}
