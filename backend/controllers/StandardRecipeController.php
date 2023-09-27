<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use backend\models\StandardRecipeIngredientForm;
use common\models\Menu;
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
                            'unselect-ingredient'
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
                            'unselect-ingredient'
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
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new StandardRecipeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => $type
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTheoreticalYield()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        $categories = RecipeCategory::find()
            ->where([
                'business_id' => $business['id']
            ])->all();

        $totalSales = 0;
        $total = 0;
        $data = [];
        foreach ($categories as $category) {
            $recipes = StandardRecipe::find()->where([
                'business_id' => $business['id'],
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
                'type_of_recipe' => $category->name
            ])->all();

            $combos = Menu::find()->where([
                'business_id' => $business['id'],
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

        return $this->render('theoretical_yield', [
            'data' => $data,
            'totalSales' => $totalSales,
            'totalPcr' => $totalPcr,
            'totalCost' => $totalCost
        ]);
    }

    public function actionRealYield()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        $categories = RecipeCategory::find()
            ->where([
                'business_id' => $business['id']
            ])->all();

        $totalSales = 0;
        $data = [];
        foreach ($categories as $category) {
            $recipes = StandardRecipe::find()->where([
                'business_id' => $business['id'],
                'in_construction' => 0,
                'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
                'in_menu' => true,
                'type_of_recipe' => $category->name
            ])->all();

            $combos = Menu::find()->where([
                'business_id' => $business['id'],
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


        return $this->render('real_yield', [
            'data' => $data,
            'totalSales' => $totalSales,
            'totalPcr' => $totalPcr
        ]);
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
                'title' => Yii::t('app', "A good recipe"),
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
            $model->save();
        }

        $post = Yii::$app->request->post();
        if (!empty($post)) {
            $post['StandardRecipe']['in_construction'] = false;
        }

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
            if ($model->type == $model::STANDARD_RECIPE_TYPE_MAIN) {
                return $this->redirect(['standard-recipe/index', 'type' => $model->type]);
            } else {
                return $this->redirect(['sub-standard-recipe/index']);
            }
        } else {
            Yii::$app->session->setFlash('error', json_encode(array_values(array_values($model->errors))));
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

    public function actionFormSelectIngredient($id)
    {
        $recipe = $this->findModel($id);
        return $this->renderAjax('create/_form_ingredient', [
            'model' => new \backend\models\StandardRecipeIngredientForm(),
            'recipe' => $recipe
        ]);
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
            return $this->redirect(['index']);
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

    public function actionMenuRecipes()
    {
        $business = RedisKeys::getBusinessData();

        $recipes = StandardRecipe::find()->where([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
            'in_menu' => true
        ])->all();

        $availableRecipes = StandardRecipe::find()->where([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
            'in_menu' => false
        ])->all();

        $combos = Menu::find()->where([
            'business_id' => $business['id'],
            'in_menu' => true
        ])->all();

        $availableCombos = Menu::find()->where([
            'business_id' => $business['id'],
            'in_menu' => false
        ])->all();

        $dataProvider = new ActiveDataProvider([
            'models' => array_merge($recipes, $combos)
        ]);

        return $this->render('menu', [
            'dataProvider' => $dataProvider,
            'availableRecipes' => $availableRecipes,
            'availableCombos' => $availableCombos
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
        }else{
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

        if ($type != 'all') {
            $recipes->andWhere(['type_of_recipe' => $type]);
            $combos->andWhere(['recipe_category.name' => $type]);
        }

        $recipes = $recipes->all();
        $combos = $combos->all();

        $data = array_merge($recipes, $combos);

        $totalSales = array_sum(ArrayHelper::getColumn($data, 'sales'));

        return $this->render('matrix', [
            "data" => $data,
            'business' => $business,
            'totalSales' => $totalSales,
            'type' => $type
        ]);
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
}
