<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use backend\models\UpdateAccountForm;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Business;
use common\models\BusinessSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BusinessController implements the CRUD actions for Business model.
 */
class BusinessController extends Controller
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
            
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }

    /**
     * Lists all Business models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BusinessSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Business model.
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
     * Creates a new Business model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Business();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Business model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionUpdateSales()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = $this->findModel($businessData['id']);

        $model->monthly_plate_sales = Yii::$app->request->post('plate_sales');

        $model->save(false);

        return $this->asJson(true);
    }

    public function actionMyBusiness()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = $this->findModel($businessData['id']);
        $user = Yii::$app->user->identity;
        $model = new UpdateAccountForm([
            'businessId' => $business->id,
            'businessName' => $business->name,
            'userId' => $user->getId(),
            'name' => $user->profile->name,
            'currency_code' => $business->currency_code,
            'decimal_separator' => $business->decimal_separator,
            'thousands_separator' => $business->thousands_separator,
            'timezone' => $business->timezone,
            'locale' => $business->locale,
            'requisition_allowed_days' => $business->getRequisitionAllowedDaysArray(),
            'requisition_start_time' => $business->requisition_start_time ?: '00:00',
            'requisition_end_time' => $business->requisition_end_time ?: '23:59',
            'allow_extemporaneous_requisitions' => $business->allow_extemporaneous_requisitions ?? true,
            'require_extemporaneous_reason' => $business->require_extemporaneous_reason ?? true,
        ]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if (!$model->load($post) or !$model->save()) {
            foreach ($model->errors as $error) {
                foreach (array_values($error) as $msg) {
                    Yii::$app->session->setFlash('error', $msg);
                }
            }
        } else {
            $model->password = '';
            Yii::$app->session->setFlash('success', Yii::t('app', "Changes applies!"));
        }

        return $this->render('my_business', [
            'model' => $model
        ]);
    }
    
    /**
     * Página independiente para gestionar reglas de requisición
     */
    public function actionRequisitionRules()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);
        
        if (!$business) {
            throw new NotFoundHttpException('El negocio no fue encontrado.');
        }
        
        return $this->render('requisition-rules', [
            'business' => $business,
        ]);
    }

    /**
     * Cargar reglas de requisición para un centro de consumo
     */
    public function actionLoadConsumptionCenterRules()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $centerId = Yii::$app->request->post('consumption_center_id');
        $businessId = Yii::$app->request->post('business_id');
        
        if (!$centerId || !$businessId) {
            return [
                'success' => false,
                'message' => 'Parámetros inválidos'
            ];
        }
        
        $rules = \common\models\ConsumptionCenterRequisitionRules::getForConsumptionCenter($centerId, $businessId);
        
        // Cargar horarios individuales por día
        $schedules = \common\models\ConsumptionCenterSchedule::find()
            ->where(['consumption_center_id' => $centerId])
            ->orderBy(['day_of_week' => SORT_ASC])
            ->all();
        
        // Convertir schedules a un objeto indexado por día
        $daySchedules = [];
        foreach ($schedules as $schedule) {
            $daySchedules[$schedule->day_of_week] = [
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ];
        }
        
        return [
            'success' => true,
            'rules' => [
                'requisition_allowed_days' => $rules->getRequisitionAllowedDaysArray(),
                'requisition_start_time' => $rules->requisition_start_time,
                'requisition_end_time' => $rules->requisition_end_time,
                'allow_extemporaneous_requisitions' => $rules->allow_extemporaneous_requisitions,
                'require_extemporaneous_reason' => $rules->require_extemporaneous_reason,
                'day_schedules' => $daySchedules, // Nuevo campo con horarios por día
            ]
        ];
    }
    
    /**
     * Guardar reglas de requisición para un centro de consumo
     */
    public function actionSaveConsumptionCenterRules()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $centerId = Yii::$app->request->post('consumption_center_id');
        $businessId = Yii::$app->request->post('business_id');
        
        if (!$centerId || !$businessId) {
            return [
                'success' => false,
                'message' => 'Parámetros inválidos'
            ];
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $rules = \common\models\ConsumptionCenterRequisitionRules::getForConsumptionCenter($centerId, $businessId);
            
            // Actualizar datos básicos
            $days = Yii::$app->request->post('requisition_allowed_days', []);
            $rules->setRequisitionAllowedDaysArray($days);
            $rules->requisition_start_time = Yii::$app->request->post('requisition_start_time', '00:00');
            $rules->requisition_end_time = Yii::$app->request->post('requisition_end_time', '23:59');
            
            // Los checkboxes solo envían valor cuando están marcados
            $rules->allow_extemporaneous_requisitions = Yii::$app->request->post('allow_extemporaneous_requisitions', 0) ? 1 : 0;
            $rules->require_extemporaneous_reason = Yii::$app->request->post('require_extemporaneous_reason', 0) ? 1 : 0;
            
            if (!$rules->save()) {
                throw new \Exception('Error al guardar reglas: ' . json_encode($rules->errors));
            }
            
            // Obtener horarios por día
            $dayStartTimes = Yii::$app->request->post('day_start_time', []);
            $dayEndTimes = Yii::$app->request->post('day_end_time', []);
            
            // Eliminar horarios existentes para este centro
            \common\models\ConsumptionCenterSchedule::deleteAll(['consumption_center_id' => $centerId]);
            
            // Guardar nuevos horarios solo para los días seleccionados
            foreach ($days as $day) {
                $startTime = $dayStartTimes[$day] ?? '00:00';
                $endTime = $dayEndTimes[$day] ?? '23:59';
                
                $schedule = new \common\models\ConsumptionCenterSchedule();
                $schedule->consumption_center_id = $centerId;
                $schedule->day_of_week = (int)$day;
                $schedule->start_time = $startTime;
                $schedule->end_time = $endTime;
                $schedule->is_active = true;
                
                if (!$schedule->save()) {
                    throw new \Exception('Error al guardar horario del día ' . $day . ': ' . json_encode($schedule->errors));
                }
            }
            
            $transaction->commit();
            
            return [
                'success' => true,
                'message' => 'Configuración guardada exitosamente'
            ];
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Deletes an existing Business model.
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
     * Finds the Business model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Business the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Business::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
