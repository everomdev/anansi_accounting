<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use backend\models\StandardRecipeIngredientForm;
use common\models\Menu;
use common\models\MenuBundle;
use common\models\RecipeCategory;
use common\models\RecipeStep;
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
                            'download-complete-recipe-pdf'
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
                            'download-complete-recipe-pdf'
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
                'title' => Yii::t('app', "Recipe Name"),
                'in_construction' => true,
            ]);
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

    public function actionUpdateSelectedIngredient($id, $ingredientId, $isRecipe = false)
    {
        $model = $this->findModel($id);
        $quantity = Yii::$app->request->post('quantity');
        if ($isRecipe) {
            $model->addUpdateSubRecipe($ingredientId, $quantity);
        } else {
            $model->addUpdateIngredient($ingredientId, $quantity);
        }

        return $this->asJson(true);
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
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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

            if($category){
                $recipesFilter['type_of_recipe'] = $category->name;
                $combosFilter['category_id'] = $category->id;
            }


            $totalRecipes = (int)StandardRecipe::find()->where($recipesFilter)
                ->count();
            $recipes = StandardRecipe::find()->where($recipesFilter)
                ->offset($offset == 0 ? null : $offset)
                ->limit(30)
                ->all();
            $recipesFilter['in_menu'] = false;
            $availableRecipes = StandardRecipe::find()->where($recipesFilter)->all();

            $totalCombos = (int)Menu::find()->where($combosFilter)->count();
            $combos = Menu::find()->where($combosFilter)->offset($offset == 0 ? null : $offset)
                ->limit(30)
                ->all();
            $combosFilter['in_menu'] = false;
            $availableCombos = Menu::find()->where($combosFilter)->all();

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

            if($category){
                $recipesFilter['type_of_recipe'] = $category->name;
                $combosFilter['category_id'] = $category->id;
            }
            $totalRecipes = (int)StandardRecipe::find()->where($recipesFilter)->count();
            $recipes = StandardRecipe::find()->where($recipesFilter)
                ->offset($offset == 0 ? null : $offset)
                ->limit(30)
                ->all();
            $recipesFilter['in_menu'] = false;
            $availableRecipes = StandardRecipe::find()->where($recipesFilter)->all();

            $totalCombos = (int)Menu::find()->where($combosFilter)->count();
            $combos = Menu::find()->where($combosFilter)->offset($offset == 0 ? null : $offset)
                ->limit(30)
                ->all();
            $combosFilter['in_menu'] = false;
            $availableCombos = Menu::find()->where($combosFilter)->all();
        }


        $pagination = new Pagination([
            'page' => $page - 1,
            'pageSize' => 30,
            'totalCount' => $totalRecipes + $totalCombos
        ]);

        $dataProvider = new ActiveDataProvider([
            'models' => array_merge($recipes, $combos),
//            'query' => $modelsQuery,
        ]);

        $dataProvider->setPagination($pagination);


        return $this->render('menu', [
            'dataProvider' => $dataProvider,
            'availableRecipes' => $availableRecipes,
            'availableCombos' => $availableCombos,
            'pagination' => $pagination,
            'bundle' => $bundleModel,
            'business' => $business,
            'category' => $category
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

    public function actionAnalytics($family = 'all')
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

    $html = '';

    foreach ($recipes as $recipe) {
        // Título de la receta
        $html .= '<h1>' . htmlspecialchars($recipe->title) . '</h1>';

        // Contenedor de tabla para la imagen y los datos de la receta
        $html .= '<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">';
        $html .= '<tr>';

        // Columna izquierda: Datos de la receta (70% del ancho)
        $html .= '<td width="40%" style="vertical-align: top; padding-right: 10px;">';
        $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
        $html .= '<h3><strong>Tipo de receta:</strong> ' . htmlspecialchars($recipe->type_of_recipe ?? '') . '</h2>';
        $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
        $html .= '<h3 style="margin-bottom: 15px"><strong>Tiempo de preparación:</strong> ' . htmlspecialchars($recipe->time_of_preparation ?? '') . '</h2>';
        $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
        $html .= '<h3 style="margin-bottom: 15px"><strong>Rendimiento:</strong> ' . htmlspecialchars($recipe->yield ?? '') . ' ' . htmlspecialchars($recipe->yield_um ?? '') . '</h2>';
        $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
        $html .= '<h3 style="margin-bottom: 15px"><strong>Porciones:</strong> ' . htmlspecialchars($recipe->portions ?? '') . '</h2>';
        $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
        $html .= '<h3 style="margin-bottom: 15px"><strong>Duración:</strong> ' . htmlspecialchars($recipe->lifetime ?? '') . '</h2>';

        // Si es receta principal, mostrar precios y costos
        if ($recipe->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3><strong>Precio:</strong> '.'$' . htmlspecialchars($recipe->price ?? '') . '</h2>';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3><strong>Costo:</strong> '.'$' . number_format((float)($recipe->lastPrice ?? 0), 2) . '</h2>';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
            $html .= '<h3><strong>Costo %:</strong> ' . number_format((float)($recipe->costPercent ?? 0), 2) . '</h2>';
            $html .= '<hr style="margin: 20px 0; border: 1px solid #ccc;">';
        }
        $html .= '</td>'; // Cierre de la columna izquierda

        // Columna derecha: Imagen principal (30% del ancho)
        $html .= '<td width="60%" style="vertical-align: top; padding-left: 10px;">';
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
                        $html .= '<img src="' . $imageSrc . '" style="max-width: 100%; height: auto;" />';
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

        // Agregar los ingredientes
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

        // Otras imágenes (no principales)
        foreach ($images as $image) {
            if ($image['isMain'] != 1 && $image['filePath'] !== 'placeholder.svg') {
                $imagePath = Yii::getAlias('@web') . $image->getPath(); // Ruta relativa
                $imagePath1 = '/app/backend/web/' . $imagePath; // Ruta absoluta

                if (file_exists($imagePath1)) {
                    $imageExtension = pathinfo($imagePath1, PATHINFO_EXTENSION);
                    $imageData = base64_encode(file_get_contents($imagePath1));
                    $imageSrc = "data:image/$imageExtension;base64,{$imageData}";

                    $html .= '<div style="text-align: center; margin-bottom: 20px;">';
                    $html .= '<h3>Foto del procedimiento</h3>';
                    $html .= '<img src="' . $imageSrc . '" style="max-width: 100%; height: auto;" />';
                    $html .= '</div>';
                }
            }
        }

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

    // Generar PDF
    $mpdf = new \Mpdf\Mpdf([
        'tempDir' => Yii::getAlias('@runtime/mpdf'),
        'default_font' => 'dejavusans', // Usar una fuente compatible con UTF-8
    ]);

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
}
