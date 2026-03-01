<?php

namespace backend\controllers;

use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\RecipeCategory;
use common\models\RecipeCategorySearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RecipeCategoryController implements the CRUD actions for RecipeCategory model.
 */
class RecipeCategoryController extends Controller
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'view', 'autocomplete'],
                        'allow' => true,
                        'roles' => ['recipe_category_list'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['recipe_category_create'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['recipe_category_update'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['recipe_category_delete'],
                    ],
                ],
            ],
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }

    /**
     * Lists all RecipeCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $searchModel = new RecipeCategorySearch();
        $perPage = (int)Yii::$app->request->get('per-page');
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10; // Valor predeterminado
        }
        if (!Yii::$app->user->can('admin')) {
            $searchModel->business_id = $business['id'];
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = $perPage;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single RecipeCategory model.
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
     * Creates a new RecipeCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $model = new RecipeCategory(['business_id' => $business['id']]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->renderAjax('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing RecipeCategory model.
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

        return $this->renderAjax('update', [
            'model' => $model,
        ]);
    }

    /**
     * Provides autocomplete suggestions for recipe categories
     * Used to prevent duplicate category creation
     * @return array JSON response with category suggestions
     */
    public function actionAutocomplete()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $term = \Yii::$app->request->get('term', '');
        $type = \Yii::$app->request->get('type', '');
        
        if (empty($term)) {
            return [];
        }
        
        $business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        
        $query = RecipeCategory::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['like', 'name', $term]);
            
        if (!empty($type)) {
            $query->andWhere(['type' => $type]);
        }
        
        $categories = $query->limit(10)->all();
        
        $suggestions = [];
        foreach ($categories as $category) {
            $suggestions[] = [
                'id' => $category->id,
                'label' => $category->name,
                'value' => $category->name,
                'type' => $category->type
            ];
        }
        
        return $suggestions;
    }

    /**
     * Deletes an existing RecipeCategory model.
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

    /**
     * Finds the RecipeCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RecipeCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RecipeCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
