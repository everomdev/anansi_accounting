<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\ExpenseUnitMeasurement;
use common\models\ExpenseUnitMeasurementSearch;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ExpenseUnitMeasurementController implements the CRUD actions for ExpenseUnitMeasurement model.
 */
class ExpenseUnitMeasurementController extends Controller
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
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expenseunitmeasurement_list');
                        }
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expenseunitmeasurement_view');
                        }
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expenseunitmeasurement_create');
                        }
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expenseunitmeasurement_update');
                        }
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expenseunitmeasurement_delete');
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all ExpenseUnitMeasurement models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new ExpenseUnitMeasurementSearch(['business_id' => $business['id']]);

        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExpenseUnitMeasurement model.
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
     * Creates a new ExpenseUnitMeasurement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new ExpenseUnitMeasurement([
            'business_id' => $business['id'],
        ]);

        $post = Yii::$app->request->post();

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
            return;
        }

        if ($model->load($post) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Unidad de medida creada exitosamente.');
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
     * Updates an existing ExpenseUnitMeasurement model.
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
            Yii::$app->session->setFlash('success', 'Unidad de medida actualizada exitosamente.');
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
     * Deletes an existing ExpenseUnitMeasurement model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Unidad de medida eliminada exitosamente.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the ExpenseUnitMeasurement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExpenseUnitMeasurement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExpenseUnitMeasurement::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
