<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\IngredientStock;
use common\models\Movement;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Provider;
use common\models\ProviderSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\web\UploadedFile;
use yii\web\Response;

/**
 * ProviderController implements the CRUD actions for Provider model.
 */
class ProviderController extends Controller
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
                            'export-template',
                            'export-all',
                            'ingredients',
                            'import'
                        ],
                        'allow' => true,
                        'roles' => ['providers_list']
                    ],
                    [
                        'actions' => [
                            'view',

                        ],
                        'allow' => true,
                        'roles' => ['providers_view']

                    ],
                    [
                        'actions' => [
                            'create',
                        [
                            'actions' => [
                                'import'
                            ],
                            'allow' => true,
                            'roles' => ['providers_create']
                        ],

                        ],
                        'allow' => true,
                        'roles' => ['providers_create']
                    ],
                    [
                        'actions' => [
                            'update',

                        ],
                        'allow' => true,
                        'roles' => ['providers_update']
                    ],
                    [
                        'actions' => [
                            'delete',

                        ],
                        'allow' => true,
                        'roles' => ['providers_delete']
                    ],
                ],
            ],
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }

    /**
     * Lists all Provider models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        $searchModel = new ProviderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([
            'business_id' => $business['id'],
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new Provider model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        $model = new Provider(['business_id' => $business['id']]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $model->payment_method = is_array($model->payment_method) ? 
            implode(',', $model->payment_method) : '';
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Provider model.
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
            $model->payment_method = is_array($model->payment_method) ? 
            implode(',', $model->payment_method) : '';
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
     * Deletes an existing Provider model.
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

    /*public function actionIngredients($provider = 'all')
    {
        $business = RedisKeys::getBusiness();
        \Yii::$app->db->createCommand("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));")->execute();
        // Subconsulta para obtener el id del último movimiento de entrada por ingrediente
        $subQuery = (new \yii\db\Query())
            ->select(['MAX(id)'])
            ->from('movement')
            ->where([
                'business_id' => $business->id,
                'type' => 'input',
            ])
            ->groupBy('ingredient_id');

        $movements = Movement::find()
            ->where(['id' => $subQuery]);

        if ($provider != 'all') {
            $movements->andWhere(['provider' => $provider]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $movements
        ]);

        return $this->render('ingredients', [
            'dataProvider' => $dataProvider,
            'providers' => Provider::findAll(['business_id' => $business->id]),
            'provider' => $provider
        ]);

    }*/
    public function actionIngredients($provider = 'all')
    {
        $business = RedisKeys::getBusiness();
        $query = IngredientStock::find()
            ->where(['ingredient_stock.business_id' => $business->id])
            ->with(['category', 'providers']);
        if ($provider != 'all') {
            $query->joinWith('providers')->andWhere(['provider_id' => $provider]);
        } else {
            $query->joinWith('providers');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $providers = Provider::findAll(['business_id' => $business->id]);
        return $this->render('ingredients', [
            'dataProvider' => $dataProvider,
            'providers' => $providers,
            'provider' => $provider
        ]);
    }

    /**
     * Descargar plantilla para importar proveedores (cabeceras y formato)
     */
    public function actionExportTemplate()
    {
    $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
    // Usar la versión simplificada para diagnosticar problemas de apertura en Excel
    \backend\helpers\ExcelHelper::generateProvidersTemplate(new \common\models\Business($business));
    }

    /**
     * Exportar todos los proveedores usando la misma plantilla
     */
    public function actionExportAll()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = \common\models\Business::findOne($businessData['id']);
        $providers = Provider::find()->where(['business_id' => $businessData['id']])->all();
        \backend\helpers\ExcelHelper::generateProvidersTemplate($business, $providers);
    }

    /**
     * Importar proveedores desde archivo Excel usando las mismas reglas del modelo Provider
     */
    public function actionImport()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $uploadedFile = UploadedFile::getInstanceByName('providers_file');
        if (!$uploadedFile) {
            Yii::$app->session->setFlash('danger', 'No se encontró archivo para importar.');
            return $this->redirect(['index']);
        }

        try {
            $result = \backend\helpers\ExcelHelper::importProviders(new \common\models\Business($business), $uploadedFile->tempName);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('danger', 'Error al procesar el archivo: ' . $e->getMessage());
            return $this->redirect(['index']);
        }

        $created = $result['created'] ?? 0;
        $errors = $result['errors'] ?? [];

        if ($created > 0) {
            Yii::$app->session->setFlash('success', "Se importaron $created proveedores correctamente.");
        }
        if (!empty($errors)) {
            Yii::$app->session->setFlash('warning', 'Algunas filas no se importaron. Revisa los detalles.');
            Yii::$app->session->setFlash('import_errors', json_encode($errors));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Provider model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Provider the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Provider::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
