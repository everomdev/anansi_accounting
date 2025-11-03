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
                'rules' => [                    [
                        'actions' => [
                            'index',
                            'view',
                            'download-template',
                            'export-movements',
                            'import-movements',
                            'balance',
                            'get-provider-payment-types',
                            'convert-to-entry'
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
                            'update',
                        ],
                        'allow' => true,
                        'roles' => [
                            'manage_account',
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
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
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
            return;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            // Mostrar errores específicos al usuario
            foreach ($model->getFirstErrors() as $field => $error) {
                Yii::$app->session->addFlash('error', ucfirst($field) . ': ' . $error);
            }
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
            return;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            // Mostrar errores específicos al usuario
            foreach ($model->getFirstErrors() as $field => $error) {
                Yii::$app->session->addFlash('error', ucfirst($field) . ': ' . $error);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Convierte una orden en una entrada
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionConvertToEntry($id)
    {
        $originalOrder = $this->findModel($id);
        
        // Verificar que sea una orden
        if ($originalOrder->type !== Movement::TYPE_ORDER) {
            Yii::$app->session->addFlash('error', 'Solo se pueden convertir órdenes en entradas.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        // Crear nuevo movimiento basado en la orden
        $newEntry = new Movement();
        $newEntry->type = Movement::TYPE_INPUT;
        $newEntry->business_id = $originalOrder->business_id;
        $newEntry->ingredient_id = $originalOrder->ingredient_id;
        $newEntry->provider = $originalOrder->provider;
        $newEntry->payment_type = $originalOrder->payment_type;
        $newEntry->invoice = $originalOrder->invoice;
        $newEntry->quantity = $originalOrder->quantity;
        $newEntry->um = $originalOrder->um;
        $newEntry->amount = $originalOrder->amount;
        $newEntry->tax = $originalOrder->tax;
        $newEntry->retention = $originalOrder->retention;
        $newEntry->unit_price = $originalOrder->unit_price;
        $newEntry->total = $originalOrder->total;
        $newEntry->observations = $originalOrder->observations . ' (Convertido de orden #' . $originalOrder->id . ')';
        $newEntry->created_at = date('Y-m-d H:i:s'); // Fecha actual para la nueva entrada
        
        if ($newEntry->save()) {
            Yii::$app->session->addFlash('success', 'La orden se ha convertido exitosamente en una entrada.');
            return $this->redirect(['index']);
        } else {
            // Mostrar errores específicos
            foreach ($newEntry->getFirstErrors() as $field => $error) {
                Yii::$app->session->addFlash('error', ucfirst($field) . ': ' . $error);
            }
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    public function actionDownloadTemplate()
    {
        $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $businessId = $businessData['id'];
        ExcelHelper::generateMovementTemplate($businessId);
    }

    public function actionImportMovements()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);

        $file = UploadedFile::getInstanceByName('movement-file');

        if ($file) {
            try {
                $result = ExcelHelper::importMovements($business, $file->tempName);
                
                if ($result['success']) {
                    //Yii::$app->session->setFlash('success', 
                     //   "Importación completada. Se guardaron {$result['saved_count']} movimientos.");
                } else {
                    Yii::$app->session->setFlash('error', 'Error en la importación.');
                }
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error durante la importación: ' . $e->getMessage());
            }
        } else {
            Yii::$app->session->setFlash('error', 'No se seleccionó ningún archivo.');
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
        }        return $this->asJson(['success' => false, 'errors' => ["Algo falló"]]);
    }    /**
     * Obtiene los tipos de pago disponibles para un proveedor específico
     */
    public function actionGetProviderPaymentTypes($providerId = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {            // Si no se proporciona providerId, devolver todos los tipos de pago específicos
            if (empty($providerId)) {
                return [
                    'success' => true,
                    'paymentTypes' => \common\models\Movement::getFormattedPaymentMethods()
                ];
            }
            
            // Buscar el proveedor por ID
            $provider = \common\models\Provider::findOne($providerId);
              if (!$provider) {
                return [
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ];
            }
              // Obtener los métodos de pago del proveedor
            $providerPaymentMethods = $provider->payment_method;            if (empty($providerPaymentMethods)) {
                // Si el proveedor no tiene métodos de pago específicos, devolver todos
                return [
                    'success' => true,
                    'paymentTypes' => \common\models\Movement::getFormattedPaymentMethods()
                ];
            }
            
            // Asegurar que payment_method es un array
            if (!is_array($providerPaymentMethods)) {
                // Si es string, convertir a array
                if (is_string($providerPaymentMethods)) {
                    $providerPaymentMethods = explode(',', $providerPaymentMethods);
                    $providerPaymentMethods = array_map('trim', $providerPaymentMethods);                } else {
                    // Si no es array ni string, devolver todos los tipos específicos
                    return [
                        'success' => true,
                        'paymentTypes' => \common\models\Movement::getFormattedPaymentMethods()
                    ];
                }}            // Obtener los tipos de pago específicos del proveedor
            // En lugar de mapear a los tipos genéricos, mostrar los tipos específicos
            $providerPaymentTypes = \common\models\Movement::getFormattedPaymentMethods();
            
            // Filtrar solo los tipos de pago que acepta el proveedor
            $filteredPaymentTypes = [];
            
            foreach ($providerPaymentMethods as $method) {
                $method = trim($method); // Limpiar espacios
                
                if (isset($providerPaymentTypes[$method])) {
                    $filteredPaymentTypes[$method] = $providerPaymentTypes[$method];
                }
            }              // Si no se encontró ningún tipo de pago válido, devolver todos los tipos específicos
            if (empty($filteredPaymentTypes)) {
                return [
                    'success' => true,
                    'paymentTypes' => \common\models\Movement::getFormattedPaymentMethods()
                ];
            }
            
            return [
                'success' => true,
                'paymentTypes' => $filteredPaymentTypes
            ];
              } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los tipos de pago: ' . $e->getMessage()
            ];
        }
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
