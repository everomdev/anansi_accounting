<?php

namespace backend\controllers;

use backend\helpers\ExcelHelper;
use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\Category;
use common\models\StockPrice;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use http\Url;
use Yii;
use common\models\IngredientStock;
use common\models\IngredientStockSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * IngredientStockController implements the CRUD actions for IngredientStock model.
 */
class IngredientStockController extends Controller
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
                    'import-ingredients' => ['POST'],
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
                        'actions' => [
                            'create',
                            'generate-key'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_create'],
                    ],
                    [
                        'actions' => [
                            'update',
                            'generate-key'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_update'],
                    ],
                    [
                        'actions' => [
                            'create',
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_delete'],
                    ],
                    [
                        'actions' => [
                            'index',
                            'download-references',
                            'download-template',
                            'export',
                            'import-ingredients',
                            'view'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_list','ingredients_view'],
                    ],
                    [
                        'actions' => [
                            'delete',
                            'bulk-remove'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_delete'],
                    ],
                    [
                        'actions' => [
                            'price-trend',
                        ],
                        'allow' => true,
                        'roles' => ['price_trend_view'],
                    ],
                    [
                        'actions' => [
                            'storage',
                        ],
                        'allow' => true,
                        'roles' => ['storage', 'storage_list', 'storage_view'],
                    ],

                ],
            ],
        ];
    }

    /**
     * Lists all IngredientStock models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new IngredientStockSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere([
            'business_id' => $business['id']
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionStorage()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new IngredientStockSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere([
            'business_id' => $business['id']
        ]);

        return $this->render('storage', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGenerateKey($categoryId)
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        return $this->asJson(IngredientStock::keyGenerator($categoryId, $business['id']));
    }

    /**
     * Displays a single IngredientStock model.
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
     * Creates a new IngredientStock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new IngredientStock([
            'business_id' => $business['id']
        ]);
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $url = \yii\helpers\Url::previous('register-movement');
            Yii::$app->session->setFlash('success', Yii::t('app', "Ingredient has been added to storage"));
            return empty($url) ? $this->redirect(['index']) : $this->redirect($url);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing IngredientStock model.
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
        } elseif ($model->hasErrors()) {
            var_dump($model->errors);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing IngredientStock model.
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

    public function actionPriceTrend($ingredientId = null, $categoryId = null, $from = null, $to = null)
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        if (empty($from) && empty($to)) {
            $to = (new \DateTime())->format("Y-m-d");
            $from = (new \DateTime())->modify("-3 months")->format('Y-m-d');
        }
        $category = null;
        $model = IngredientStock::find()->where(['business_id' => $business['id']])->orderBy("RAND()")->one();
        if (empty($ingredientId) && empty($categoryId) && !empty($model)) {
            $ingredientId = $model->id;
        } elseif (empty($categoryId)) {
            $model = IngredientStock::findOne(['id' => $ingredientId]);
        } else {
            $category = Category::find()
                ->where([
                    'or',
                    ['business_id' => $business['id']],
                    ['business_id' => null],
                ])->andWhere(['id' => $categoryId])
                ->one();

        }
        $prices = [];
        if (!empty($model)) {

            $prices = $model->getStockPrices()
                ->andWhere([
                    'and',
                    ['>=', 'date', $from],
                    ['<=', 'date', $to],
                ])
                ->orderBy(['date' => SORT_ASC])
                ->all();
        } elseif (!empty($category)) {
            $prices = StockPrice::find()
                ->innerJoin('ingredient_stock is', 'is.id=stock_price.stock_id')
                ->where(['is.category_id' => $category->id])
                ->andWhere([
                    'and',
                    ['>=', 'date', $from],
                    ['<=', 'date', $to],
                ])
                ->orderBy(['date' => SORT_ASC])
                ->all();
        }

        return $this->render('price_trend', [
            'prices' => $prices,
            'from' => $from,
            'to' => $to,
            'categoryId' => $categoryId,
            'ingredientId' => $ingredientId
        ]);
    }

    public function actionDownloadReferences($id)
    {
        $business = Business::findOne(['id' => $id]);

        ExcelHelper::generateReferenceTemplate($business);
    }

    public function actionDownloadTemplate($id)
    {
        ExcelHelper::generateIngredientsTemplate($id);
    }

    public function actionImportIngredients($id)
    {
        $business = Business::findOne(['id' => $id]);

        $file = UploadedFile::getInstanceByName('ingredient-file');

        if ($file) {
            try {
                ExcelHelper::importIngredients($business, $file->tempName);
            }catch (\Exception $e) {
                $errors = json_decode($e->getMessage(), true);
                foreach ($errors as $field => $fieldErrors) {
                    Yii::$app->session->setFlash('error', implode("\n", $fieldErrors));
                }
            }
        }

        return $this->redirect(['ingredient-stock/index']);
    }

    public function actionExport()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);

        ExcelHelper::exportIngredients($business);
    }

    public function actionBulkRemove()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);
        $ids = Yii::$app->request->post('keys');
        if (!empty($ids)) {
            IngredientStock::deleteAll(['id' => $ids, 'business_id' => $business->id]);
        }

        return $this->asJson(['success' => true]);
    }

    /**
     * Finds the IngredientStock model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return IngredientStock the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IngredientStock::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
