<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Business;
use backend\models\StandardRecipeIngredientForm;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\StandardRecipe;
use common\models\StandardRecipeSearch;
use common\models\Convoy;
use common\models\ConvoyIngredient;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StandardRecipeController implements the CRUD actions for StandardRecipe model.
 */
class SubStandardRecipeController extends Controller
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
                        'roles' => ['subrecipe_list', 'subrecipe_view']
                    ],
                    [
                        'actions' => [
                            'create',
                            'select-ingredients',
                            'unselect-ingredient',
                            'duplicate-recipes',
                        ],
                        'allow' => true,
                        'roles' => ['subrecipe_create']
                    ],
                    [
                        'actions' => [
                            'update',
                            'select-ingredients',
                            'unselect-ingredient',
                        ],
                        'allow' => true,
                        'roles' => ['subrecipe_update']
                    ],
                    [
                        'actions' => [
                            'delete',
                            'delete-sub-recipe',
                            'check-links'
                        ],
                        'allow' => true,
                        'roles' => ['subrecipe_delete']
                    ],
                ],
            ],
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }

    /**
     * Lists all StandardRecipe models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new StandardRecipeSearch();
        // Obtener el valor de la cookie si existe
        $savedPageSize = (int)Yii::$app->request->cookies->getValue('sub_recipe_page_size', 10);
        
        // Personalizar elementos por página - solo si viene en la URL
        $perPage = Yii::$app->request->get('per-page');
        
        // Si perPage no viene en la URL o no es válido, usar el valor guardado en la cookie
        if (!$perPage || !in_array((int)$perPage, [10, 25, 50, 100])) {
            $perPage = $savedPageSize;
        } else {
            // Solo guardar una nueva cookie si el valor es diferente al que ya tenemos
            if ((int)$perPage !== $savedPageSize) {
                $cookie = new \yii\web\Cookie([
                    'name' => 'sub_recipe_page_size',
                    'value' => (int)$perPage,
                    'expire' => time() + 86400 * 30,
                ]);
                Yii::$app->response->cookies->add($cookie);
            }
        }
        
        // Usar perPage como la cantidad de elementos por página
        $pageSize = (int)$perPage;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = $pageSize;
        $dataProvider->query->andWhere([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ]);
        $ingredientCount = [];
        $recipes = $dataProvider->getModels();
        foreach ($recipes as $recipe) {
            $ingredientCount[$recipe->id] = [
                'ingredientCount' => $recipe->getIngredientRelationsSub()->count(),
                'subRecipeCount' => $recipe->getSubRecipeCount()['sub'],
                'RecipeCount' => $recipe->getSubRecipeCount()['main'],
            ];
        }
        //die(var_dump($ingredientCount));
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'ingredientCount' => $ingredientCount,
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
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new StandardRecipe([
            'business_id' => $business['id'],
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['sub-standard-recipe/select-ingredients', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionSelectIngredients($id)
    {
        $model = $this->findModel($id);
        $form = new StandardRecipeIngredientForm();
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$form])->validate();
        }
        if (Yii::$app->request->isPost && $form->load($post) && $form->validate()) {
            if (!empty($form->ingredientId)) {
                $model->addUpdateIngredient($form->ingredientId, $form->quantity);
            } else {
                $model->addUpdateSubRecipe($form->subRecipeId, $form->quantity);
            }
        }


        return $this->render('create/_ingredients_selection', [
            'model' => $model
        ]);
    }

    public function actionUnselectIngredient($id, $ingredientId)
    {
        $model = $this->findModel($id);

        $model->removeIngredient($ingredientId);

        return $this->redirect(['sub-standard-recipe/select-ingredients', 'id' => $id]);
    }

    public function actionFinishRecipeCreation($id)
    {
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

    /**
     * Updates an existing StandardRecipe model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['sub-standard-recipe/index']);
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
        $model = $this->findModel($id);

        // Remove any convoy ingredients that reference this subrecipe within the same business
        try {
            $convoyIds = Convoy::find()->select('id')->where(['business_id' => $model->business_id])->column();
            if (!empty($convoyIds)) {
                ConvoyIngredient::deleteAll([
                    'convoy_id' => $convoyIds,
                    'entity_class' => StandardRecipe::class,
                    'entity_id' => $model->id,
                ]);
            }
        } catch (\Throwable $e) {
            Yii::warning('Failed to clean convoy ingredients for subrecipe delete: ' . $e->getMessage(), __METHOD__);
        }

        $model->delete();

        return $this->redirect(['index']);
    }
    public function actionDeleteSubRecipe()
    {
        if (Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('keys'); // Recibir los IDs enviados desde el frontend
    
            if ($ids === 'all') {
                // Delete all recipes
                $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
                $business = Business::findOne(['id' => $businessData['id']]);
                // gather subrecipe ids for this business
                $subIds = StandardRecipe::find()
                    ->select('id')
                    ->where(['business_id' => $business->id, 'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB])
                    ->column();

                // gather convoy ids for this business
                $convoyIds = Convoy::find()->select('id')->where(['business_id' => $business->id])->column();

                if (!empty($subIds) && !empty($convoyIds)) {
                    ConvoyIngredient::deleteAll([
                        'convoy_id' => $convoyIds,
                        'entity_class' => StandardRecipe::class,
                        'entity_id' => $subIds,
                    ]);
                }

                StandardRecipe::deleteAll(['business_id' => $business->id, 'type'=> StandardRecipe::STANDARD_RECIPE_TYPE_SUB]);
                return $this->asJson(['success' => true]);
            } else if (!empty($ids)) {
                // Delete selected recipes
                // restrict convoy removals to convoys belonging to this business
                $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
                $business = Business::findOne(['id' => $businessData['id']]);
                $convoyIds = Convoy::find()->select('id')->where(['business_id' => $business->id])->column();

                if (!empty($convoyIds)) {
                    ConvoyIngredient::deleteAll([
                        'convoy_id' => $convoyIds,
                        'entity_class' => StandardRecipe::class,
                        'entity_id' => $ids,
                    ]);
                }

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

    /**
     * Verifica los vínculos de las subrecetas seleccionadas
     * Retorna la cantidad de recetas y subrecetas vinculadas
     */
    public function actionCheckLinks()
    {
        if (Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('keys');
            
            if (empty($ids) || $ids === 'all') {
                return $this->asJson(['hasLinks' => false]);
            }
            
            $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
            $business = Business::findOne(['id' => $businessData['id']]);
            
            // Obtener todos los convoys del negocio
            $convoyIds = Convoy::find()
                ->select('id')
                ->where(['business_id' => $business->id])
                ->column();
            
            if (empty($convoyIds)) {
                return $this->asJson(['hasLinks' => false]);
            }
            
            // Contar vínculos para todas las subrecetas seleccionadas
            $totalRecipeLinks = 0;
            $totalSubRecipeLinks = 0;
            
            foreach ($ids as $subRecipeId) {
                $subRecipe = StandardRecipe::findOne($subRecipeId);
                if (!$subRecipe) {
                    continue;
                }
                
                $counts = $subRecipe->getSubRecipeCount();
                $totalRecipeLinks += $counts['main'] ?? 0;
                $totalSubRecipeLinks += $counts['sub'] ?? 0;
            }
            
            $hasLinks = ($totalRecipeLinks > 0 || $totalSubRecipeLinks > 0);
            
            return $this->asJson([
                'hasLinks' => $hasLinks,
                'recipeCount' => $totalRecipeLinks,
                'subRecipeCount' => $totalSubRecipeLinks
            ]);
        }
        
        return $this->asJson(['hasLinks' => false]);
    }

    public function actionDuplicateRecipes()
    {
        $post = Yii::$app->request->post();

        $recipes = StandardRecipe::find()->where(['id' => $post['recipes']])->all();

        foreach($recipes as $recipe){
            $recipe->duplicate();
        }

        return $this->redirect(['index']);
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
