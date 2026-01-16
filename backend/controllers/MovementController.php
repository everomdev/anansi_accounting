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
                    'delete-by-id' => ['POST'],
                    'delete-by-fecha' => ['POST'],
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
                            'convert-to-entry',
                            'convert-to-output',
                            'check-stock-availability'
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
                            'create-requisition',
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
                            'admin',
                            'administrator',
                        ],
                    ],
                    [
                        'actions' => [
                            'delete',
                            'delete-by-id',
                            'delete-by-fecha',
                        ],
                        'allow' => true,
                        'roles' => [
                            'movements_list',
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
        
        // Si el usuario tiene el rol consumption_requester, solo mostrar requisiciones
        if (Yii::$app->user->can('consumption_requester')) {
            // Forzar el filtro de tipo a requisiciones
            if (!isset($params['MovementSearch'])) {
                $params['MovementSearch'] = [];
            }
            $params['MovementSearch']['type'] = Movement::TYPE_REQUISITION;
        }
        
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
        // Si el tipo es REQUISITION, usar vista especial
        if ($type === Movement::TYPE_REQUISITION) {
            return $this->actionCreateRequisition();
        }
        
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
     * Crear requisición con múltiples insumos
     * @return mixed
     */
    public function actionCreateRequisition()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $post = Yii::$app->request->post();
        
        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                // Obtener datos del formulario
                $consumptionCenterId = $post['consumption_center_id'] ?? null;
                $requiredDate = $post['required_date'] ?? null;
                $observations = $post['observations'] ?? '';
                $clientTimezone = $post['client_timezone'] ?? 'UTC';
                $items = $post['items'] ?? [];
                $isExtemporaneous = ($post['is_extemporaneous'] ?? '0') === '1';
                $extemporaneousReason = $post['extemporaneous_reason_hidden'] ?? null;
                
                // Obtener fecha/hora actual del cliente (no del servidor)
                $clientCurrentDateTime = $post['client_current_datetime'] ?? date('Y-m-d H:i:s');
                
                // Validar que haya items
                if (empty($items)) {
                    throw new \Exception('Debe agregar al menos un insumo a la requisición');
                }
                
                // Validar centro de consumo
                if (empty($consumptionCenterId)) {
                    throw new \Exception('El centro de consumo es requerido');
                }
                
                // Obtener reglas de requisición del centro de consumo
                $centerRules = \common\models\ConsumptionCenterRequisitionRules::getForConsumptionCenter(
                    $consumptionCenterId,
                    $business['id']
                );
                
                // Validar si la requisición puede ser creada (solo para información)
                $requisitionCheck = $centerRules->canCreateRequisitionNow();
                $isOutOfTime = !$requisitionCheck['allowed']; // Fuera de días/horarios
                
                // Determinar el estado de tiempo de la requisición
                $timeStatus = \common\models\Movement::TIME_STATUS_ON_TIME;
                
                if ($isOutOfTime) {
                    // Si está fuera de tiempo, verificar si hay motivo
                    if (!empty($extemporaneousReason) && $centerRules->allow_extemporaneous_requisitions) {
                        // Tiene motivo Y se permiten extemporáneas = EXTEMPORÁNEA
                        $timeStatus = \common\models\Movement::TIME_STATUS_EXTEMPORANEOUS;
                        $isExtemporaneous = true;
                    } else {
                        // No tiene motivo O no se permiten extemporáneas = FUERA DE TIEMPO
                        $timeStatus = \common\models\Movement::TIME_STATUS_OUT_OF_TIME;
                        $isExtemporaneous = false;
                    }
                }
                
                // Crear el movimiento de requisición (sin afectar inventario)
                $movement = new Movement([
                    'business_id' => $business['id'],
                    'type' => Movement::TYPE_REQUISITION,
                    'consumption_center_id' => $consumptionCenterId,
                    'required_date' => $requiredDate,
                    'observations' => $observations,
                    'client_timezone' => $clientTimezone,
                    'requested_by_user_id' => Yii::$app->user->id,
                    'status' => 'pending',
                    'created_at' => $clientCurrentDateTime, // Usar fecha/hora del cliente
                    'is_extemporaneous' => $isExtemporaneous,
                    'extemporaneous_reason' => !empty($extemporaneousReason) ? $extemporaneousReason : null,
                    'requisition_time_status' => $timeStatus,
                ]);
                
                if (!$movement->save()) {
                    $errors = implode(', ', $movement->getFirstErrors());
                    throw new \Exception('Error al crear requisición: ' . $errors);
                }
                
                // Crear los items de la requisición
                $savedCount = 0;
                foreach ($items as $item) {
                    if (empty($item['ingredient_id']) || empty($item['quantity'])) {
                        continue;
                    }
                    
                    $requisitionItem = new \common\models\RequisitionItem([
                        'requisition_id' => $movement->id,
                        'ingredient_id' => $item['ingredient_id'],
                        'quantity_requested' => $item['quantity'],
                    ]);
                    
                    if (!$requisitionItem->save()) {
                        $errors = implode(', ', $requisitionItem->getFirstErrors());
                        throw new \Exception('Error al guardar insumo: ' . $errors);
                    }
                    
                    $savedCount++;
                }
                
                if ($savedCount == 0) {
                    throw new \Exception('No se guardó ningún insumo válido');
                }
                
                $transaction->commit();
                
                Yii::$app->session->addFlash('success', 
                    "Requisición {$movement->requisition_number} creada exitosamente con {$savedCount} insumo(s).");
                return $this->redirect(['index']);
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->addFlash('error', 'Error al crear requisición: ' . $e->getMessage());
            }
        }
        
        return $this->render('create_requisition');
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

    /**
     * Convierte una requisición en una salida
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionConvertToOutput($id)
    {
        $originalRequisition = $this->findModel($id);
        
        // Verificar que sea una requisición
        if ($originalRequisition->type !== Movement::TYPE_REQUISITION) {
            Yii::$app->session->addFlash('error', 'Solo se pueden convertir requisiciones en salidas.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        // Verificar que la requisición tenga items
        $items = $originalRequisition->requisitionItems;
        if (empty($items)) {
            Yii::$app->session->addFlash('error', 'La requisición no tiene insumos.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $outputsCreated = 0;
            $insufficientStock = [];
            
            // Crear una salida por cada item de la requisición
            foreach ($items as $item) {
                $ingredient = $item->ingredient;
                
                if (!$ingredient) {
                    continue;
                }
                
                // Verificar disponibilidad de stock
                $availableQuantity = $ingredient->quantity ?? 0;
                
                if ($availableQuantity < $item->quantity_requested) {
                    $insufficientStock[] = [
                        'ingredient' => $ingredient->ingredient,
                        'requested' => $item->quantity_requested,
                        'available' => $availableQuantity,
                    ];
                    continue;
                }
                
                // Crear movimiento de salida
                $newOutput = new Movement();
                $newOutput->type = Movement::TYPE_OUTPUT;
                $newOutput->business_id = $originalRequisition->business_id;
                $newOutput->ingredient_id = $item->ingredient_id;
                $newOutput->consumption_center_id = $originalRequisition->consumption_center_id;
                $newOutput->quantity = $item->quantity_requested;
                $newOutput->um = $ingredient->portion_um ?? $ingredient->um;
                $newOutput->observations = "Salida de requisición {$originalRequisition->requisition_number}";
                $newOutput->parent_requisition_id = $originalRequisition->id;
                $newOutput->created_at = date('Y-m-d H:i:s');
                
                if (!$newOutput->save()) {
                    $errors = implode(', ', $newOutput->getFirstErrors());
                    throw new \Exception("Error al crear salida para {$ingredient->ingredient}: {$errors}");
                }
                
                // Actualizar cantidad surtida en el item
                $item->quantity_fulfilled = $item->quantity_requested;
                $item->save(false);
                
                $outputsCreated++;
            }
            
            // Actualizar estado de la requisición
            if ($outputsCreated > 0) {
                if (empty($insufficientStock)) {
                    $originalRequisition->status = 'fulfilled';
                } else {
                    $originalRequisition->status = 'partially_fulfilled';
                }
                $originalRequisition->fulfilled_by_user_id = Yii::$app->user->id;
                $originalRequisition->save(false);
            }
            
            $transaction->commit();
            
            // Mostrar resultados
            if ($outputsCreated > 0) {
                Yii::$app->session->addFlash('success', 
                    "Se crearon {$outputsCreated} salida(s) exitosamente.");
            }
            
            if (!empty($insufficientStock)) {
                $message = "No se pudo surtir todo. Stock insuficiente para:\n";
                foreach ($insufficientStock as $stock) {
                    $message .= "- {$stock['ingredient']}: solicitado {$stock['requested']}, disponible {$stock['available']}\n";
                }
                Yii::$app->session->addFlash('warning', $message);
            }
            
            if ($outputsCreated == 0) {
                Yii::$app->session->addFlash('error', 'No se pudo crear ninguna salida debido a stock insuficiente.');
            }
            
            return $this->redirect(['index']);
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->addFlash('error', 'Error al convertir requisición: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $id]);
        }
    }
    
    /**
     * Verifica la disponibilidad de stock para una requisición
     * @return mixed JSON response
     */
    public function actionCheckStockAvailability()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $ingredientId = Yii::$app->request->post('ingredient_id');
        $quantity = Yii::$app->request->post('quantity');
        
        if (empty($ingredientId) || empty($quantity)) {
            return [
                'success' => false,
                'message' => 'Parámetros inválidos'
            ];
        }
        
        $ingredient = \common\models\IngredientStock::findOne($ingredientId);
        
        if (!$ingredient) {
            return [
                'success' => false,
                'message' => 'Insumo no encontrado'
            ];
        }
        
        $availableQuantity = $ingredient->quantity ?? 0;
        $available = $availableQuantity >= $quantity;
        
        return [
            'success' => true,
            'available' => $available,
            'availableQuantity' => $availableQuantity,
            'requestedQuantity' => $quantity,
            'insufficientQuantity' => max(0, $quantity - $availableQuantity),
            'ingredientName' => $ingredient->ingredient,
            'um' => $ingredient->portion_um
        ];
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

        // Obtener los IDs seleccionados del parámetro GET
        $ids = Yii::$app->request->get('ids');
        
        // Si no hay IDs, exportar todos los movimientos del negocio
        if (empty($ids)) {
            ExcelHelper::exportMovements($business);
        } else {
            // Convertir los IDs de string a array
            $idsArray = is_array($ids) ? $ids : explode(',', $ids);
            ExcelHelper::exportMovements($business, $idsArray);
        }
    }

    /**
     * Deletes an existing Movement model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('keys'); // Recibir los IDs enviados desde el frontend
    
            try {
                if ($ids === 'all') {
                    // Delete all movements for the current business
                    $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
                    $business = Business::findOne(['id' => $businessData['id']]);
                    Movement::deleteAll([
                        'business_id' => $business->id
                    ]);
                    return $this->asJson(['success' => true]);
                } else if (!empty($ids)) {
                    // Delete selected movements
                    foreach ($ids as $id) {
                        $model = $this->findModel($id);
                        if ($model) {
                            $model->delete(); 
                        }
                    }
                    return $this->asJson(['success' => true]);
                }
            } catch (\Exception $e) {
                Yii::error('Error deleting movements: ' . $e->getMessage(), __METHOD__);
                return $this->asJson([
                    'success' => false, 
                    'message' => 'Error al eliminar los movimientos: ' . $e->getMessage()
                ]);
            }
        }
        
        return $this->asJson([
            'success' => false, 
            'message' => 'Solicitud inválida'
        ]);
    }

    /**
     * Deletes an existing Movement model by ID (single deletion).
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteById($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Deletes existing Movement models by date.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $fecha
     * @return mixed
     */
    public function actionDeleteByFecha($fecha)
    {
        $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $businessId = $businessData['id'] ?? null;
        
        // Buscar todos los movimientos para esta fecha y negocio
        $movements = Movement::find()->where(['fecha' => $fecha, 'business_id' => $businessId])->all();
        
        if (empty($movements)) {
            Yii::$app->session->setFlash('error', 'No se encontraron movimientos para la fecha especificada.');
            return $this->redirect(['index']);
        }
        
        $deletedCount = 0;
        
        // Eliminar cada movimiento
        foreach ($movements as $movement) {
            $movement->delete();
            $deletedCount++;
        }
        
        Yii::$app->session->setFlash('success', "Se eliminaron {$deletedCount} movimientos de la fecha {$fecha}.");
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
