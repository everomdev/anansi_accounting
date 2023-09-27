<?php

namespace backend\controllers;

use backend\helpers\ExcelHelper;
use backend\helpers\RedisKeys;
use common\models\Balance;
use common\models\Business;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Movement;
use common\models\MovementSearch;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * MovementController implements the CRUD actions for Movement model.
 */
class MovementController extends Controller
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
                            'index',
                            'view',
                            'download-template',
                            'export-movements',
                            'import-movements',
                            'balance'
                        ],
                        'allow' => true,
                        'roles' => [
                            'movements_view',
                            'movements_list'
                        ],
                    ],
                    [
                        'actions' => [
                            'create',
                        ],
                        'allow' => true,
                        'roles' => [
                            'movements_view',
                            'movements_list'
                        ],
                    ],
                    [
                        'actions' => [
                            'balance',
                            'register-balance',
                        ],
                        'allow' => true,
                        'roles' => [
                            'movements_manage_balance',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Movement models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new MovementSearch(['business_id' => $business['id']]);

        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Movement model.
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
     * Creates a new Movement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($type)
    {
        Url::remember(['movement/create'], 'register-movement');
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new Movement([
            'business_id' => $business['id'],
            'type' => $type
        ]);
        $post = Yii::$app->request->post();

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            var_dump($model->errors);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Movement model.
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

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDownloadTemplate()
    {
        ExcelHelper::generateMovementTemplate();
    }

    public function actionImportMovements()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);

        $file = UploadedFile::getInstanceByName('movement-file');

        if ($file) {
            ExcelHelper::importMovements($business, $file->tempName);
        }

        return $this->redirect(['movement/index']);
    }

    public function actionExportMovements()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);

        ExcelHelper::exportMovements($business);
    }

    /**
     * Deletes an existing Movement model.
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

    public function actionBalance()
    {
        $business = RedisKeys::getBusinessData();

        $balances = Balance::find()->where(['business_id' => $business['id']])->orderBy(['date' => SORT_ASC])->all();

        return $this->renderAjax('balance', [
            'balances' => $balances
        ]);
    }

    public function actionRegisterBalance()
    {
        $business = RedisKeys::getBusinessData();

        $balance = new Balance([
            'business_id' => $business['id'],
            'created_by' => Yii::$app->user->id,
            'date' => date('Y-m-d')
        ]);

        $post = Yii::$app->request->post();

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$balance])->validate();
        }

        if ($balance->load($post) && $balance->save()) {
            return $this->asJson(['success' => true]);
        } elseif ($balance->hasErrors()) {
            return $this->asJson(['success' => false, 'errors' => array_values(array_values($balance->errors))]);
        }

        return $this->asJson(['success' => false, 'errors' => ["Algo falló"]]);
    }

    /**
     * Finds the Movement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Movement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Movement::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
