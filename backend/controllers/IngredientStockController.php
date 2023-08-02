<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Category;
use common\models\StockPrice;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use http\Url;
use Yii;
use common\models\IngredientStock;
use common\models\IngredientStockSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
        $model = null;
        if (empty($ingredientId) && empty($categoryId)) {
            $model = IngredientStock::find()->where(['business_id' => $business['id']])->orderBy("RAND()")->one();
            $ingredientId = $model->id;
        } elseif (empty($categoryId)) {
            $model = $this->findModel($ingredientId);
        } else {
            $category = Category::find()
                ->where([
                    'or',
                    ['business_id' => $business['id']],
                    ['business_id' => null],
                ])->andWhere(['id' => $categoryId])
                ->one();

        }

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
