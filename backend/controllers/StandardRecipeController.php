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
                            'import-sub-recipes',
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
                            'import-sub-recipes',
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
        // Obtener el valor de la cookie si existe
        $savedPageSize = (int)Yii::$app->request->cookies->getValue('recipe_page_size', 10);
        
        // Personalizar elementos por página - solo si viene en la URL
        $perPage = Yii::$app->request->get('per-page');
        
        // Si perPage no viene en la URL o no es válido, usar el valor guardado en la cookie
        if (!$perPage || !in_array((int)$perPage, [10, 25, 50, 100])) {
            $perPage = $savedPageSize;
        } else {
            // Solo guardar una nueva cookie si el valor es diferente al que ya tenemos
            if ((int)$perPage !== $savedPageSize) {
                $cookie = new \yii\web\Cookie([
                    'name' => 'recipe_page_size',
                    'value' => (int)$perPage,
                    'expire' => time() + 86400 * 30,
                ]);
                Yii::$app->response->cookies->add($cookie);
            }
        }
        
        // Usar perPage como la cantidad de elementos por página
        $pageSize = (int)$perPage;
        
        // Incluir siempre el per-page en la URL recordada
        Url::remember(['standard-recipe/index', 'type' => $type, 'page' => $page, 'per-page' => $pageSize], 'index-recipe');
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new StandardRecipeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = $pageSize;
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
    public function actionImportSubRecipes($id)
    {
        $business = Business::findOne(['id' => $id]);

        $file = UploadedFile::getInstanceByName('ingredient-file');//

        if ($file) {
            try {
                ExcelHelper::importSubRecipe($business, $file->tempName);
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
    /*public function actionAnalytics($family = 'all', $sort = null, $direction = 'asc')
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

    // Guardar datos en la sesión para usarlos en menu_improvement
    Yii::$app->session->set('menuAnalysisData', $data);
    Yii::$app->session->set('sortByCostPercent', $sortByCostPercent);
    Yii::$app->session->set('sortByPopularity', $sortByPopularity);
    Yii::$app->session->set('sortBySales', $sortBySales);
    Yii::$app->session->set('paretoCategories', $paretoCategories);
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
}*/
public function actionAnalytics($family = 'all', $sort = null, $direction = 'asc')
{
    $business = RedisKeys::getBusiness();

    // Obtener recetas y combos en una sola consulta optimizada
    $query = (new \yii\db\Query())
        ->select([
            'id' => 'sr.id',
            'name' => 'sr.title',
            'type' => new \yii\db\Expression("'recipe'"),
            'type_of_recipe' => 'sr.type_of_recipe',
            'price' => 'sr.price',
            'cost' => 'sr.custom_cost',
            'sales' => 'sr.sales',
            'cost_percent' => new \yii\db\Expression('(sr.custom_cost / NULLIF(sr.price, 0)) * 100'),
            'sales_value' => new \yii\db\Expression('sr.price * sr.sales')
        ])
        ->from(['sr' => 'standard_recipe'])
        ->where([
            'sr.business_id' => $business->id,
            'sr.in_menu' => true,
            'sr.in_construction' => 0,
            'sr.type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN
        ]);

    if ($family != 'all') {
        $query->andWhere(['sr.type_of_recipe' => $family]);
    }

    $combosQuery = (new \yii\db\Query())
        ->select([
            'id' => 'm.id',
            'name' => 'm.name',
            'type' => new \yii\db\Expression("'combo'"),
            'type_of_recipe' => 'rc.name',
            'price' => 'm.total_price',
            'cost' => 'm.total_cost',
            'sales' => 'm.sales',
            'cost_percent' => new \yii\db\Expression('(m.total_cost / NULLIF(m.total_price, 0)) * 100'),
            'sales_value' => new \yii\db\Expression('m.total_price * m.sales')
        ])
        ->from(['m' => 'menu'])
        ->innerJoin(['rc' => 'recipe_category'], 'rc.id = m.category_id')
        ->where([
            'm.business_id' => $business->id,
            'm.in_menu' => true,
        ]);

    if ($family != 'all') {
        $combosQuery->andWhere(['rc.name' => $family]);
    }

    // Unir ambas consultas y ejecutar una sola query
    $combinedQuery = (new \yii\db\Query())
        ->from(['combined' => $query->union($combosQuery)]);

    // Obtener todos los datos en un solo fetch
    $data = $combinedQuery->all();

    // Calcular total de ventas para Pareto
    $totalSales = array_sum(array_column($data, 'sales'));

    // Función para ordenar según parámetros
    $sortFunction = function ($a, $b) use ($sort, $direction) {
        $compare = 0;
        
        switch ($sort) {
            case 'name':
                $compare = strcasecmp($a['name'], $b['name']);
                break;
                
            case 'cost-percent':
                $compare = ($a['cost_percent'] <=> $b['cost_percent']);
                break;
                
            case 'popularity':
                $compare = ($a['sales'] <=> $b['sales']);
                break;
                
            case 'sales':
                $compare = ($a['sales_value'] <=> $b['sales_value']);
                break;
        }
        
        return ($direction === 'desc') ? -$compare : $compare;
    };

    // Ordenar datos principales si es necesario
    if ($sort) {
        usort($data, $sortFunction);
    }

    // Generar versiones ordenadas una sola vez
    $sortedVersions = [
        'costPercent' => $data,
        'popularity' => $data,
        'sales' => $data
    ];

    usort($sortedVersions['costPercent'], fn($a, $b) => $a['cost_percent'] <=> $b['cost_percent']);
    usort($sortedVersions['popularity'], fn($a, $b) => $b['sales'] <=> $a['sales']);
    usort($sortedVersions['sales'], fn($a, $b) => $b['sales_value'] <=> $a['sales_value']);

    // Generar claves únicas y categorías Pareto
    $paretoItems = $sortedVersions['popularity']; // Ya está ordenado por popularidad
    $paretoCategories = [];
    $accumulatedPercentage = 0;

    foreach ($paretoItems as $item) {
        $itemKey = "{$item['type']}_{$item['id']}";
        
        if ($totalSales > 0) {
            $itemPercentage = ($item['sales'] / $totalSales) * 100;
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

    // Preparar datos para la vista
    $prepareForView = function($items) {
        return array_map(fn($item) => "{$item['type']}_{$item['id']}", $items);
    };

    // Guardar en sesión
    Yii::$app->session->set('menuAnalysisData', $data);
    Yii::$app->session->set('sortByCostPercent', $prepareForView($sortedVersions['costPercent']));
    Yii::$app->session->set('sortByPopularity', $prepareForView($sortedVersions['popularity']));
    Yii::$app->session->set('sortBySales', $prepareForView($sortedVersions['sales']));
    Yii::$app->session->set('paretoCategories', $paretoCategories);

    return $this->render('analytics', [
        'data' => $data,
        'sortByCostPercent' => $prepareForView($sortedVersions['costPercent']),
        'sortByPopularity' => $prepareForView($sortedVersions['popularity']),
        'sortBySales' => $prepareForView($sortedVersions['sales']),
        'family' => $family,
        'paretoCategories' => $paretoCategories,
        'totalSales' => $totalSales,
        'currentSort' => $sort,
        'currentDirection' => $direction
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
            'data' => array_merge($recipes, $combos),
            'formatter' => $business->getFormatter()
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
        // $type = Yii::$app->request->post('type');
        $exists = StandardRecipe::find()
            ->where(['title' => $title, 'business_id' => $businessId])
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
        $exportAll = isset($get['all']) && $get['all'] === 'true';
        $type = $get['type'];
        $business = \backend\helpers\RedisKeys::getBusiness();
        // Buscar todas las recetas seleccionadas
        $recipes = $exportAll ? StandardRecipe::find()->where(['business_id' => $business->id, 'type' => $type, 'in_construction' => 0])->all() : StandardRecipe::find()->where(['id' => $selectedRecipes])->all();
        
        if (empty($recipes)) {
            throw new \yii\web\NotFoundHttpException('No se encontraron recetas seleccionadas.');
        }
    
        $isSubrecipe = ($type === StandardRecipe::STANDARD_RECIPE_TYPE_SUB);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // 1. Configurar hojas principales con los mismos nombres que la plantilla
        $recipesSheet = $spreadsheet->getActiveSheet();
        $recipesSheet->setTitle($isSubrecipe ? 'FICHA GENERAL DE LA SUBRECETA' : 'FICHA GENERAL DE LA RECETA');
        
        $ingredientsSheet = $spreadsheet->createSheet();
        $ingredientsSheet->setTitle('INGREDIENTES');
        
        $insumosSheet = $spreadsheet->createSheet();
        $insumosSheet->setTitle('INSUMOS');
        
        $convoySheet = $spreadsheet->createSheet();
        $convoySheet->setTitle('CONVOY');
        
        $umSheet = $spreadsheet->createSheet();
        $umSheet->setTitle('UMs');
        
        $categorySheet = $spreadsheet->createSheet();
        $categorySheet->setTitle('Categorias');
    
        // 2. Configurar cabeceras para la hoja principal según el tipo
        $recipesHeaders = $isSubrecipe ? 
            ['Nombre', 'Tipo de Subreceta', 'Tiempo de preparación', 'Unidad de tiempo', 
             'Rendimiento', 'Rendimiento UM', 'Porciones', 'Duración', 'Unidad de duración', 'Unidad de medida final'] :
            ['Nombre', 'Tipo de Receta', 'Tiempo de preparación', 'Unidad de tiempo', 
             'Rendimiento', 'Rendimiento UM', 'Porciones', 'Duración', 'Unidad de duración', 
             'Precio', 'Alimento o Bebida', 'Convoy', 'Unidad de medida final'];
    
        $col = 'A';
        foreach ($recipesHeaders as $header) {
            $recipesSheet->setCellValue($col.'1', $header);
            $recipesSheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
    
        // 3. Configurar cabeceras para otras hojas
        $ingredientsSheet->setCellValue('A1', ($isSubrecipe ? 'SubReceta' : 'Receta'));
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
        
        $umSheet->setCellValue('A1', 'Unidad de Medida');
        
        $categorySheet->setCellValue('A1', 'Categoría');
    
        // 4. Ancho automático para todas las columnas
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            foreach (range('A', 'Z') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
    
        // 5. Cargar datos de referencia (UMs, Categorías, Insumos, Convoy)
        // Unidades de medida
        $unitOfMeasurements = UnitOfMeasurement::find()
            ->select('name')
            ->groupBy('name')
            ->all();
            
        $rowUM = 2;
        foreach ($unitOfMeasurements as $um) {
            $umSheet->setCellValue("A$rowUM", $um->name);
            $rowUM++;
        }
        
        // Categorías
        $categories = RecipeCategory::find()
            ->where([
                'business_id' => $business['id']
            ])->all();
            
        $rowCategory = 2;
        foreach ($categories as $category) {
            $categorySheet->setCellValue("A$rowCategory", $category->name);
            $rowCategory++;
        }
        
        // Convoy
        $convoys = Convoy::find()
            ->where([
                'business_id' => $business['id']
            ])->all();
            
        $rowConvoy = 2;
        foreach ($convoys as $convoy) {
            $convoySheet->setCellValue("A$rowConvoy", $convoy->id);
            $convoySheet->setCellValue("B$rowConvoy", $convoy->name);
            $rowConvoy++;
        }
        
        // Insumos
        $ingredientStock = IngredientStock::find()
            ->where([
                'business_id' => $business['id']
            ])->all();
            
        $insumosRow = 2;
        foreach ($ingredientStock as $ingredient) {
            $insumosSheet->setCellValue('A' . $insumosRow, $ingredient->ingredient);
            $insumosSheet->setCellValue('B' . $insumosRow, $ingredient->quantity);
            $insumosSheet->setCellValue('C' . $insumosRow, $ingredient->um);
            $insumosSheet->setCellValue('D' . $insumosRow, $ingredient->lastPrice);
            $insumosRow++;
        }
    
        // 6. Llenar los datos de recetas con el nuevo formato
        $recipesRow = 2;
        $ingredientsRow = 2;
    
        foreach ($recipes as $recipe) {
            // CORRECCIÓN: Mejorar la extracción del tiempo de preparación - más robusto
            $timeValue = '';
            $timeUnit = 'minutos'; // Valor predeterminado
    
            if (!empty($recipe->time_of_preparation)) {
                if (preg_match('/^(\d+(?:\.\d+)?)\s*(\w+.*)?$/', trim($recipe->time_of_preparation), $matches)) {
                    $timeValue = $matches[1]; // Valor numérico
                    
                    // Si hay unidad especificada, usarla; si no, usar el predeterminado
                    if (isset($matches[2]) && !empty(trim($matches[2]))) {
                        $unitText = trim(strtolower($matches[2]));
                        
                        // Normalización de unidades
                        if (strpos($unitText, 'min') !== false) {
                            $timeUnit = 'minutos';
                        } elseif (strpos($unitText, 'hor') !== false || strpos($unitText, 'hr') !== false) {
                            $timeUnit = 'horas';
                        } elseif (strpos($unitText, 'día') !== false || strpos($unitText, 'dia') !== false) {
                            $timeUnit = 'días';
                        } else {
                            $timeUnit = $unitText; // Usar tal cual si no coincide con ninguna normalización
                        }
                    }
                } else {
                    // Si no tiene formato numérico, poner el valor completo como numérico
                    $timeValue = $recipe->time_of_preparation;
                }
            }
            
            // CORRECCIÓN: Mejorar la extracción de la duración - más robusto
            $durationValue = '';
            $durationUnit = 'días'; // Valor predeterminado
    
            if (!empty($recipe->lifetime)) {
                if (preg_match('/^(\d+(?:\.\d+)?)\s*(\w+.*)?$/', trim($recipe->lifetime), $matches)) {
                    $durationValue = $matches[1]; // Valor numérico
                    
                    // Si hay unidad especificada, usarla; si no, usar el predeterminado
                    if (isset($matches[2]) && !empty(trim($matches[2]))) {
                        $unitText = trim(strtolower($matches[2]));
                        
                        // Normalización de unidades
                        if (strpos($unitText, 'min') !== false) {
                            $durationUnit = 'minutos';
                        } elseif (strpos($unitText, 'hor') !== false || strpos($unitText, 'hr') !== false) {
                            $durationUnit = 'horas';
                        } elseif (strpos($unitText, 'día') !== false || strpos($unitText, 'dia') !== false) {
                            $durationUnit = 'días';
                        } else {
                            $durationUnit = $unitText; // Usar tal cual si no coincide con ninguna normalización
                        }
                    }
                } else {
                    // Si no tiene formato numérico, poner el valor completo como numérico
                    $durationValue = $recipe->lifetime;
                }
            }
    
            // Llenar hoja de Recetas en el nuevo formato
            $col = 'A';
            $recipesSheet->setCellValue($col++.$recipesRow, $recipe->title);
            $recipesSheet->setCellValue($col++.$recipesRow, $recipe->type_of_recipe);
            $recipesSheet->setCellValue($col++.$recipesRow, $timeValue);
            $recipesSheet->setCellValue($col++.$recipesRow, $timeUnit);
            $recipesSheet->setCellValue($col++.$recipesRow, $recipe->yield);
            $recipesSheet->setCellValue($col++.$recipesRow, $recipe->yield_um);
            $recipesSheet->setCellValue($col++.$recipesRow, $recipe->portions);
            $recipesSheet->setCellValue($col++.$recipesRow, $durationValue);
            $recipesSheet->setCellValue($col++.$recipesRow, $durationUnit);
            
            if (!$isSubrecipe) {
                $recipesSheet->setCellValue($col++.$recipesRow, $recipe->price);
                $recipesSheet->setCellValue($col++.$recipesRow, $recipe->is_food ? 'Alimento' : 'Bebida');
                $recipesSheet->setCellValue($col++.$recipesRow, Convoy::find()->where(['id' => $recipe->convoy_id])->one()->name ?? '');
            }
            
            // CORRECCIÓN: Asegurarse de que la unidad de medida final se incluye
            $recipesSheet->setCellValue($col++.$recipesRow, $recipe->um ?: '');
            
            // Llenar hoja de Ingredientes
            foreach ($recipe->ingredientRelations as $ingredientRelation) {
                $ingredientsSheet->setCellValue('A'.$ingredientsRow, $recipe->title);
                $ingredientsSheet->setCellValue('B'.$ingredientsRow, $ingredientRelation->ingredient->ingredient);
                $ingredientsSheet->setCellValue('C'.$ingredientsRow, $ingredientRelation->quantity);
                $ingredientsSheet->setCellValue('D'.$ingredientsRow, $ingredientRelation->ingredient->portion_um);
                $ingredientsSheet->setCellValue('E'.$ingredientsRow, $ingredientRelation->lastPrice);
                $ingredientsRow++;
            }
            
            $recipesRow++;
        }
    
        // 8. Estilo y formato
        $centerStyle = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        
        // CORRECCIÓN: Ajustar el rango de estilo para incluir todas las columnas
        $lastCol = $isSubrecipe ? 'J' : 'M'; // Última columna según el tipo
        $recipesSheet->getStyle('A1:' . $lastCol . ($recipesRow-1))->applyFromArray($centerStyle);
        $ingredientsSheet->getStyle('A1:E' . ($ingredientsRow-1))->applyFromArray($centerStyle);
        
        // Formato de moneda para precio
        if (!$isSubrecipe) {
            $recipesSheet->getStyle('J2:J' . ($recipesRow-1))->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        }
        
        // Formato de moneda para costos en la hoja de ingredientes
        $ingredientsSheet->getStyle('E2:E' . ($ingredientsRow-1))->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
    
        // 9. Agregar fórmulas para insumos similares a la plantilla
        for ($i = 2; $i <= 100; $i++) {
            $ingredientsSheet->setCellValue("D$i", "=IFERROR(VLOOKUP(B$i, INSUMOS!A:D, 3, FALSE), \"\")");
            $ingredientsSheet->setCellValue("E$i", "=IF(IFERROR(C$i * VLOOKUP(B$i, INSUMOS!A:D, 4, FALSE), \"\")=\"\",\"\",ROUND(C$i * VLOOKUP(B$i, INSUMOS!A:D, 4, FALSE), 2))");
        }
    
        // 10. Añadir nota informativa similar a la plantilla
        $ingredientsSheet->setCellValue('G1', 'NOTA: El nombre de la receta debe existir primero en la hoja "FICHA GENERAL DE LA RECETA"');
        $ingredientsSheet->mergeCells('G1:J1');
        $ingredientsSheet->getStyle('G1')->getFont()
            ->setItalic(true)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED));
        $spreadsheet->setActiveSheetIndex(0); // Esto establece la primera hoja como activa
        // Generar el archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = $isSubrecipe ? 'Subrecetas_Exportadas.xlsx' : 'Recetas_Exportadas.xlsx';
    
        // Configurar las cabeceras para forzar la descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
    
        // Enviar el archivo al navegador
        $writer->save('php://output');
        exit;
    }

 public function actionExportRecipesPlantilla($type)
 {
     $business = \backend\helpers\RedisKeys::getBusiness();
     //die(var_dump($type));
     // Configuración para mejorar rendimiento
     set_time_limit(300);
     ini_set('memory_limit', '512M');
     // Configurar títulos según el tipo
    $mainTitle = ($type === 'sub') ? 'FICHA GENERAL DE LA SUBRECETA' : 'FICHA GENERAL DE LA RECETA';
    $ingredientsTitle = ($type === 'sub') ? 'INGREDIENTES PARA SUBRECETA' : 'INGREDIENTES';
     $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
     
     // 1. Configurar hojas principales
     $recipesSheet = $spreadsheet->getActiveSheet();
     $recipesSheet->setTitle($mainTitle);
     
     // 2. Modificar cabeceras de recetas (separar duración y tiempo de preparación)
     // Configurar headers según el tipo
    $recipesHeaders = ($type === 'sub') 
    ? [
        'Nombre*', 
        'Tipo de Subreceta*', 
        'Tiempo de preparación*', 
        'Unidad de tiempo*', 
        'Rendimiento*', 
        'Rendimiento UM*', 
        'Porciones*', 
        'Duración*', 
        'Unidad de duración*',
        'Unidad de medida final*'
      ]
    : [
        'Nombre*', 
        'Tipo de Receta*', 
        'Tiempo de preparación*', 
        'Unidad de tiempo*', 
        'Rendimiento*', 
        'Rendimiento UM*', 
        'Porciones*', 
        'Duración*', 
        'Unidad de duración*', 
        'Precio*', 
        'Alimento o Bebida*', 
        'Convoy',
        'Unidad de medida final*'
      ];
     
     $col = 'A';
     foreach ($recipesHeaders as $header) {
         $recipesSheet->setCellValue($col.'1', $header);
         $recipesSheet->getColumnDimension($col)->setAutoSize(true);
         $col++;
     }
     
     // 3. Crear otras hojas necesarias
     $ingredientsSheet = $spreadsheet->createSheet();
     $ingredientsSheet->setTitle('INGREDIENTES');
     
     $insumosSheet = $spreadsheet->createSheet();
     $insumosSheet->setTitle('INSUMOS');
     
     $convoySheet = $spreadsheet->createSheet();
     $convoySheet->setTitle('CONVOY');
     
     $umSheet = $spreadsheet->createSheet();
     $umSheet->setTitle('UMs');
     
     $categorySheet = $spreadsheet->createSheet();
     $categorySheet->setTitle('Categorias');
     
     // 4. Configurar cabeceras de todas las hojas
     $headers = [
         'INGREDIENTES' => [($type === 'sub' ? 'SubReceta' : 'Receta'), 'Insumo', 'Cantidad', 'UM', 'Costo'],
         'INSUMOS' => ['Insumo', 'Cantidad', 'UM', 'Costo'],
         'CONVOY' => ['ID Convoy', 'Nombre Convoy'],
         'UMs' => ['Unidad de Medida'],
         'Categorias' => ['Categoría']
     ];
     
     foreach ($headers as $sheetName => $sheetHeaders) {
         $sheet = $spreadsheet->getSheetByName($sheetName);
         $col = 'A';
         foreach ($sheetHeaders as $header) {
             $sheet->setCellValue($col.'1', $header);
             $sheet->getColumnDimension($col)->setAutoSize(true)->setWidth(50);
             $sheet->getStyle($col)->getAlignment()->setWrapText(true);
             $col++;
         }
     }
     
     // Configuración especial para columnas
     $ingredientsSheet->getColumnDimension('B')->setAutoSize(true)->setWidth(30);
     $ingredientsSheet->getStyle('B2:B500')->getAlignment()->setWrapText(true);
     
     $ingredientsSheet->getColumnDimension('A')->setAutoSize(true)->setWidth(30);
     $ingredientsSheet->getStyle('A2:A500')->getAlignment()->setWrapText(true);
     
     $recipesSheet->getColumnDimension('A')->setAutoSize(true)->setWidth(30);
     $recipesSheet->getStyle('A2:A500')->getAlignment()->setWrapText(true);
     
     // 5. Cargar datos con límite para la plantilla
     $batchSize = 100;
     
     $unitOfMeasurements = UnitOfMeasurement::find()
         ->select('name')
         ->groupBy('name')
         ->limit($batchSize)
         ->all();
     
     $categories = RecipeCategory::find()
         ->where(['business_id' => $business['id']])
         ->andWhere(['type' => $type])
         ->limit($batchSize)
         ->all();
     
     $ingredientStock = IngredientStock::find()
         ->where(['business_id' => $business['id']])
         ->limit($batchSize)
         ->all();
     
     $convoy = Convoy::find()
         ->where(['business_id' => $business['id']])
         ->limit($batchSize)
         ->all();
     
     // 6. Llenar hojas de referencia
     $rowUM = 2;
     foreach ($unitOfMeasurements as $um) {
         $umSheet->setCellValue("A$rowUM", $um->name);
         $rowUM++;
     }
     
     $rowCategory = 2;
     foreach ($categories as $category) {
         $categorySheet->setCellValue("A$rowCategory", $category->name);
         $rowCategory++;
     }
     
     $rowConvoy = 2;
     foreach ($convoy as $conv) {
         $convoySheet->setCellValue("B$rowConvoy", $conv->name);
         $rowConvoy++;
     }
     
     $insumosRow = 2;
     foreach ($ingredientStock as $ingredient) {
         $insumosSheet->setCellValue('A'.$insumosRow, $ingredient->ingredient);
         $insumosSheet->setCellValue('B'.$insumosRow, $ingredient->quantity);
         $insumosSheet->setCellValue('C'.$insumosRow, $ingredient->um);
         $insumosSheet->setCellValue('D'.$insumosRow, $ingredient->lastPrice);
         $insumosRow++;
     }
     
     // 7. Configurar estilos
     $centerStyle = [
         'alignment' => [
             'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
             'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
         ],
     ];
     
     $recipesSheet->getStyle('A1:L100')->applyFromArray($centerStyle);
     $ingredientsSheet->getStyle('A1:E500')->applyFromArray($centerStyle);
     $insumosSheet->getStyle('A1:D100')->applyFromArray($centerStyle);
     $convoySheet->getStyle('A1:B100')->applyFromArray($centerStyle);
     $umSheet->getStyle('A1:A100')->applyFromArray($centerStyle);
     $categorySheet->getStyle('A1:A100')->applyFromArray($centerStyle);
     $recipesSheet->getColumnDimension('J')->setWidth(15);
     
     // 8. Configurar TODAS las validaciones optimizadas
     $colFinalUM = ($type === 'sub') ? 'J' : 'M'; // Ajusta estas letras según tu estructura de columnas

$dataValidationFinalUM = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
$dataValidationFinalUM->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$dataValidationFinalUM->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
$dataValidationFinalUM->setAllowBlank(false);
$dataValidationFinalUM->setShowInputMessage(true);
$dataValidationFinalUM->setShowErrorMessage(true);
$dataValidationFinalUM->setShowDropDown(true);
$dataValidationFinalUM->setErrorTitle('Error de entrada');
$dataValidationFinalUM->setError('Seleccione una unidad de medida válida');
$dataValidationFinalUM->setPromptTitle('Unidad de medida final');
$dataValidationFinalUM->setPrompt('Seleccione la unidad de medida final para esta receta');
$dataValidationFinalUM->setFormula1('=UMs!$A$2:$A$'.($rowUM-1)); // Ajusta el rango según tus datos

// 2. Aplicar a todas las filas
for ($i = 2; $i <= 500; $i++) {
    $recipesSheet->getCell($colFinalUM.$i)->setDataValidation(clone $dataValidationFinalUM);
}

// 3. Ajustar ancho de columna si es necesario
$recipesSheet->getColumnDimension($colFinalUM)->setWidth(20);
     // a) Validación para nombres de recetas en INGREDIENTES
     $validation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
     $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $validation->setAllowBlank(false);
     $validation->setShowInputMessage(true);
     $validation->setShowErrorMessage(true);
     $validation->setShowDropDown(true);
     $validation->setErrorTitle('Error de entrada');
     $validation->setError('Debe seleccionar una receta existente');
     $validation->setPromptTitle('Seleccionar receta');
     $validation->setPrompt('Seleccione una receta de la lista');
    
        
    // Usar una fórmula dinámica que se actualice automáticamente cuando se añaden elementos
    if ($type === 'sub') {
        $sheetName = 'FICHA GENERAL DE LA SUBRECETA';
    } else {
        $sheetName = 'FICHA GENERAL DE LA RECETA';
    }

    // Fórmula mejorada que funciona incluso cuando no hay datos inicialmente
    $dynamicFormula = "=OFFSET('$sheetName'!A$2,0,0,COUNTA('$sheetName'!A:A)-1,1)";
    $validation->setFormula1($dynamicFormula);
     
     // Aplicar a 500 filas para permitir múltiples ingredientes por receta
     for ($row = 2; $row <= 500; $row++) {
         $ingredientsSheet->getCell('A'.$row)->setDataValidation(clone $validation);
     }
     
     // b) Validación para Alimento o Bebida
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
     $dataValidationFoodOrDrink->setFormula1('"Alimento,Bebida"');
     
     // c) Validación para unidades de medida (UM)
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
     $dataValidationUM->setFormula1('=UMs!$A$2:$A$' . ($rowUM - 1));
     
     // d) Validación para categorías
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
     $dataValidationCategory->setFormula1('=Categorias!$A$2:$A$' . ($rowCategory - 1));
     
     // e) Validación para rendimiento (sólo números)
     $dataValidationYield = $recipesSheet->getCell('E2')->getDataValidation();
     $dataValidationYield->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
     $dataValidationYield->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationYield->setAllowBlank(false);
     $dataValidationYield->setShowInputMessage(true);
     $dataValidationYield->setShowErrorMessage(true);
     $dataValidationYield->setErrorTitle('Error de entrada');
     $dataValidationYield->setError('Este campo solo acepta valores numéricos');
     $dataValidationYield->setPromptTitle('Ingrese el rendimiento');
     $dataValidationYield->setPrompt('Por favor, ingrese un valor numérico para el rendimiento.');
     $dataValidationYield->setFormula1(0);
     $dataValidationYield->setFormula2(999999);
     
     // f) Validación para convoy
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
    if ($rowConvoy > 2) {
        // If there are convoy items, use them for validation
        $dataValidationConvoy->setFormula1('=CONVOY!$B$2:$B$' . ($rowConvoy - 1));
    } else {
        // If no convoy items, use an empty list
        $dataValidationConvoy->setFormula1('""');
    }
     
     // g) Validación para insumos
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
     $dataValidationInsumos->setFormula1('=INSUMOS!$A$2:$A$' . ($insumosRow - 1));
     
     // h) Validación para unidades de tiempo (duración y tiempo de preparación)
     $dataValidationTimeUnits = $recipesSheet->getCell('D2')->getDataValidation();
     $dataValidationTimeUnits->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
     $dataValidationTimeUnits->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationTimeUnits->setAllowBlank(false);
     $dataValidationTimeUnits->setShowInputMessage(true);
     $dataValidationTimeUnits->setShowErrorMessage(true);
     $dataValidationTimeUnits->setShowDropDown(true);
     $dataValidationTimeUnits->setErrorTitle('Error de entrada');
     $dataValidationTimeUnits->setError('Este valor no es admitido');
     $dataValidationTimeUnits->setPromptTitle('Selecciona una unidad de tiempo');
     $dataValidationTimeUnits->setPrompt('Por favor, selecciona una unidad de tiempo.');
     $dataValidationTimeUnits->setFormula1('"minutos,horas,días"');
     
     // Validación para valores numéricos en tiempo de preparación
     $dataValidationTimeValue = $recipesSheet->getCell('C2')->getDataValidation();
     $dataValidationTimeValue->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
     $dataValidationTimeValue->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationTimeValue->setAllowBlank(false);
     $dataValidationTimeValue->setShowInputMessage(true);
     $dataValidationTimeValue->setShowErrorMessage(true);
     $dataValidationTimeValue->setErrorTitle('Error de entrada');
     $dataValidationTimeValue->setError('Este campo solo acepta valores numéricos');
     $dataValidationTimeValue->setPromptTitle('Ingrese el valor');
     $dataValidationTimeValue->setPrompt('Por favor, ingrese un valor numérico.');
     $dataValidationTimeValue->setFormula1(0);
     $dataValidationTimeValue->setFormula2(999999);
     
     // Validación para valores numéricos en duración
     $dataValidationDurationValue = $recipesSheet->getCell('H2')->getDataValidation();
     $dataValidationDurationValue->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
     $dataValidationDurationValue->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationDurationValue->setAllowBlank(false);
     $dataValidationDurationValue->setShowInputMessage(true);
     $dataValidationDurationValue->setShowErrorMessage(true);
     $dataValidationDurationValue->setErrorTitle('Error de entrada');
     $dataValidationDurationValue->setError('Este campo solo acepta valores numéricos');
     $dataValidationDurationValue->setPromptTitle('Ingrese el valor');
     $dataValidationDurationValue->setPrompt('Por favor, ingrese un valor numérico.');
     $dataValidationDurationValue->setFormula1(0);
     $dataValidationDurationValue->setFormula2(999999);
    if ($type !== 'sub'){
     // Modificar la validación para permitir valores más grandes
     $dataValidationPrice = $recipesSheet->getCell('J2')->getDataValidation();
     $dataValidationPrice->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
     $dataValidationPrice->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
     $dataValidationPrice->setAllowBlank(true);
     $dataValidationPrice->setShowInputMessage(true);
     $dataValidationPrice->setShowErrorMessage(true);
     $dataValidationPrice->setErrorTitle('Error de entrada');
     $dataValidationPrice->setError('Este campo solo acepta valores numéricos');
     $dataValidationPrice->setPromptTitle('Ingrese el precio');
     $dataValidationPrice->setPrompt('Por favor, ingrese un valor numérico para el precio (ej: 1,254,525.25)');
     $dataValidationPrice->setFormula1(0);
     $dataValidationPrice->setFormula2(99999999); // Aumentar el límite máximo

     $recipesSheet->getStyle('J2:J100')->getNumberFormat()->setFormatCode('$#,##0.00');
    }
     // Aplicar todas las validaciones a las celdas correspondientes
     for ($i = 2; $i <= 500; $i++) {
         // Hoja INGREDIENTES
         $ingredientsSheet->getCell("D$i")->setDataValidation(clone $dataValidationUM);
         $ingredientsSheet->getCell("B$i")->setDataValidation(clone $dataValidationInsumos);
         
         // Hoja RECETAS
         $recipesSheet->getCell("B$i")->setDataValidation(clone $dataValidationCategory);
         $recipesSheet->getCell("F$i")->setDataValidation(clone $dataValidationUM);
         $recipesSheet->getCell("E$i")->setDataValidation(clone $dataValidationYield);
         $recipesSheet->getCell("L$i")->setDataValidation(clone $dataValidationConvoy);
         $recipesSheet->getCell("K$i")->setDataValidation(clone $dataValidationFoodOrDrink);
         $recipesSheet->getCell("D$i")->setDataValidation(clone $dataValidationTimeUnits);
         $recipesSheet->getCell("I$i")->setDataValidation(clone $dataValidationTimeUnits);
         $recipesSheet->getCell("C$i")->setDataValidation(clone $dataValidationTimeValue);
         $recipesSheet->getCell("H$i")->setDataValidation(clone $dataValidationDurationValue);
         if ($type !== 'sub') {
             $recipesSheet->getCell("J$i")->setDataValidation(clone $dataValidationPrice);
         }
     }
     
     // 9. Configurar fórmulas
     
     // Para el cálculo automático del costo y UM en INGREDIENTES (limitando a 2 decimales)
     for ($i = 2; $i <= 500; $i++) {
         $ingredientsSheet->setCellValue("D$i", "=IFERROR(VLOOKUP(B$i, INSUMOS!A:D, 3, FALSE), \"\")");
         $ingredientsSheet->setCellValue("E$i", "=IF(IFERROR(C$i * VLOOKUP(B$i, INSUMOS!A:D, 4, FALSE), \"\")=\"\",\"\",ROUND(C$i * VLOOKUP(B$i, INSUMOS!A:D, 4, FALSE), 2))");
         
         // Dar formato de moneda a la columna de costo
         $ingredientsSheet->getStyle("E$i")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
     }
     
     // 11. Añadir fórmula para bloquear el campo de porciones cuando se seleccionan ciertos rendimientos
     for ($i = 2; $i <= 500; $i++) {
         // Si el rendimiento UM es porción, pieza o rebanada, poner un 1 fijo en porciones
         $formulaLockPortions = "=IF(OR(F$i=\"porción\",F$i=\"pieza\",F$i=\"rebanada\",F$i=\"porcion\",F$i=\"Porción\",F$i=\"Pieza\",F$i=\"Rebanada\"),1,\"\")";
         $recipesSheet->setCellValue("G$i", $formulaLockPortions);
         
         // Validación dinámica para el campo Porciones
         $portionsValidation = $recipesSheet->getCell("G$i")->getDataValidation();
         $portionsValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_CUSTOM);
         $portionsValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
         $portionsValidation->setAllowBlank(false);
         $portionsValidation->setShowInputMessage(true);
         $portionsValidation->setShowErrorMessage(true);
         $portionsValidation->setErrorTitle('Campo bloqueado');
         $portionsValidation->setError('Este campo está bloqueado cuando la unidad de rendimiento es porción, pieza o rebanada');
         $portionsValidation->setPromptTitle('Porciones');
         $portionsValidation->setPrompt('Ingrese el número de porciones si la unidad no es porción, pieza o rebanada');
         $portionsValidation->setFormula1("=IF(OR(F$i=\"porción\",F$i=\"pieza\",F$i=\"rebanada\",F$i=\"porcion\",F$i=\"Porción\",F$i=\"Pieza\",F$i=\"Rebanada\"),FALSE,TRUE)");
         
         $recipesSheet->getCell("G$i")->setDataValidation($portionsValidation);
     }
     
     // 12. Nota informativa
    $noteText = ($type === 'sub') 
        ? 'NOTA: El nombre de la subreceta debe existir primero en la hoja "FICHA GENERAL DE LA SUBRECETA"' 
        : 'NOTA: El nombre de la receta debe existir primero en la hoja "FICHA GENERAL DE LA RECETA"';
    $ingredientsSheet->setCellValue('G1', $noteText);
     $ingredientsSheet->mergeCells('G1:Q1');
     $ingredientsSheet->getStyle('G1')->getFont()
         ->setItalic(true)
         ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED));
     
     // 13. Nota sobre tiempo de preparación y duración
     /*$recipesSheet->setCellValue('C1:D1', 'Ingrese tiempo y seleccione unidad');
     $recipesSheet->getStyle('C1:D1')->getFont()
         ->setItalic(true)
         ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE));
     
     $recipesSheet->setCellValue('H1:I1', 'Ingrese duración y seleccione unidad');
     $recipesSheet->getStyle('H1:I1')->getFont()
         ->setItalic(true)
         ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE));
     */
     // 14. Ajustar anchos de columna
     foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
         $worksheet->calculateColumnWidths();
     }
     
     // 15. Definir la primera hoja como activa al abrir el archivo
     $spreadsheet->setActiveSheetIndex(0);
     
     // 16. Generar el archivo
     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
     $writer->setPreCalculateFormulas(true); // Calcular fórmulas antes de guardar
     
     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . ($type === 'sub' ? 'Plantilla para importar Subrecetas.xlsx' : 'Plantilla para importar Recetas.xlsx') . '"');
     header('Cache-Control: max-age=0');
     
     // Guardar el archivo directamente a la salida
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
