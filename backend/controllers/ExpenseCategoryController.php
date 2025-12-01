<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\ExpenseCategory;
use common\models\ExpenseCategorySearch;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ExpenseCategoryController implements the CRUD actions for ExpenseCategory model.
 */
class ExpenseCategoryController extends Controller
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'create',
                            'update',
                            'delete',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all ExpenseCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new ExpenseCategorySearch(['business_id' => $business['id']]);

        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExpenseCategory model.
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
     * Creates a new ExpenseCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new ExpenseCategory([
            'business_id' => $business['id'],
        ]);

        $post = Yii::$app->request->post();

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
            return;
        }

        if ($model->load($post) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Categoría de gasto creada exitosamente.');
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            foreach ($model->getFirstErrors() as $field => $error) {
                Yii::$app->session->addFlash('error', ucfirst($field) . ': ' . $error);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ExpenseCategory model.
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
            return;
        }

        if ($model->load($post) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Categoría de gasto actualizada exitosamente.');
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            foreach ($model->getFirstErrors() as $field => $error) {
                Yii::$app->session->addFlash('error', ucfirst($field) . ': ' . $error);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ExpenseCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Verificar si hay gastos asociados
        $expenseCount = $model->getExpenses()->count();
        
        if ($expenseCount > 0) {
            Yii::$app->session->setFlash('error', "No se puede eliminar la categoría porque tiene {$expenseCount} gasto(s) asociado(s).");
            return $this->redirect(['index']);
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Categoría de gasto eliminada exitosamente.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the ExpenseCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExpenseCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExpenseCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
