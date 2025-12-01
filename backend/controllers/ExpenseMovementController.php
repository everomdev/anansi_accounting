<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\ExpenseMovement;
use common\models\ExpenseMovementSearch;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ExpenseMovementController implements the CRUD actions for ExpenseMovement model.
 */
class ExpenseMovementController extends Controller
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
     * Lists all ExpenseMovement models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new ExpenseMovementSearch(['business_id' => $business['id']]);

        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExpenseMovement model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ExpenseMovement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new ExpenseMovement([
            'business_id' => $business['id'],
            'movement_date' => date('Y-m-d'),
        ]);

        $post = Yii::$app->request->post();

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
            return;
        }

        if ($model->load($post) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Movimiento de gasto registrado exitosamente.');
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
     * Updates an existing ExpenseMovement model.
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
            Yii::$app->session->setFlash('success', 'Movimiento de gasto actualizado exitosamente.');
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
     * Deletes an existing ExpenseMovement model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Movimiento de gasto eliminado exitosamente.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the ExpenseMovement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExpenseMovement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExpenseMovement::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
