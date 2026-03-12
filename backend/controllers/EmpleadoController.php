<?php

namespace backend\controllers;

use Yii;
use common\models\Empleado;
use common\models\EmpleadoSearch;
use common\models\DocumentoEmpleado;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use backend\helpers\RedisKeys;

/**
 * EmpleadoController implements the CRUD actions for Empleado model.
 */
class EmpleadoController extends Controller
{
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
                    'delete-documento' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Empleado models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getBusiness();
        $searchModel = new EmpleadoSearch();
        $searchModel->business_id = $business->id;
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Estadísticas de documentos
        $estadisticasDocumentos = $this->getEstadisticasDocumentos($business->id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'estadisticasDocumentos' => $estadisticasDocumentos,
        ]);
    }

    /**
     * Displays a single Empleado model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Empleado model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getBusiness();
        $model = new Empleado();
        $model->business_id = $business->id;
        $model->estado = Empleado::ESTADO_ACTIVO;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Empleado creado exitosamente.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Empleado model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Empleado actualizado exitosamente.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Empleado model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Empleado eliminado exitosamente.');
        return $this->redirect(['index']);
    }

    /**
     * Sube un documento para un empleado
     * @param integer $id
     * @return mixed
     */
    public function actionUploadDocumento($id)
    {
        $empleado = $this->findModel($id);
        $model = new DocumentoEmpleado();
        $model->empleado_id = $empleado->id;

        if ($model->load(Yii::$app->request->post())) {
            $model->archivoFile = UploadedFile::getInstance($model, 'archivoFile');
            
            // Primero subir el archivo y luego guardar
            if ($model->archivoFile && $model->upload()) {
                // Ahora guardamos sin validar el archivo nuevamente
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Documento subido exitosamente.');
                    return $this->redirect(['view', 'id' => $empleado->id]);
                }
            } else {
                // Si no hay archivo o falló la subida
                if (!$model->archivoFile) {
                    $model->addError('archivoFile', 'Debe seleccionar un archivo.');
                } else {
                    $model->addError('archivoFile', 'Error al subir el archivo.');
                }
            }
        }

        return $this->render('upload-documento', [
            'model' => $model,
            'empleado' => $empleado,
        ]);
    }

    /**
     * Elimina un documento
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteDocumento($id)
    {
        $documento = DocumentoEmpleado::findOne($id);
        
        if (!$documento) {
            throw new NotFoundHttpException('Documento no encontrado.');
        }

        $empleadoId = $documento->empleado_id;
        $documento->delete();

        Yii::$app->session->setFlash('success', 'Documento eliminado exitosamente.');
        return $this->redirect(['view', 'id' => $empleadoId]);
    }

    /**
     * Obtiene estadísticas de documentos
     * @param integer $businessId
     * @return array
     */
    protected function getEstadisticasDocumentos($businessId)
    {
        $tiposDocumentos = Empleado::getTiposDocumentosLabels();
        $estadisticas = [];

        $totalEmpleados = Empleado::find()
            ->where(['business_id' => $businessId, 'estado' => Empleado::ESTADO_ACTIVO])
            ->count();

        foreach ($tiposDocumentos as $tipo => $label) {
            $count = DocumentoEmpleado::find()
                ->joinWith('empleado')
                ->where([
                    'tipo_documento' => $tipo,
                    'empleados.business_id' => $businessId,
                    'empleados.estado' => Empleado::ESTADO_ACTIVO,
                ])
                ->count();

            $estadisticas[$tipo] = [
                'label' => $label,
                'count' => $count,
                'total' => $totalEmpleados,
                'porcentaje' => $totalEmpleados > 0 ? round(($count / $totalEmpleados) * 100, 2) : 0,
                'faltantes' => $totalEmpleados - $count,
                'clasificacion' => Empleado::getClasificacionDocumento($tipo),
            ];
        }

        return $estadisticas;
    }

    /**
     * Vista de estadísticas de documentos
     * @return mixed
     */
    public function actionEstadisticas()
    {
        $business = RedisKeys::getBusiness();
        $estadisticasDocumentos = $this->getEstadisticasDocumentos($business->id);

        // Estadísticas de semáforo
        $empleados = Empleado::find()
            ->where(['business_id' => $business->id, 'estado' => Empleado::ESTADO_ACTIVO])
            ->all();

        $semaforoStats = [
            Empleado::SEMAFORO_VERDE => 0,
            Empleado::SEMAFORO_AMARILLO => 0,
            Empleado::SEMAFORO_ROJO => 0,
        ];

        foreach ($empleados as $empleado) {
            $semaforo = $empleado->getSemaforoExpediente();
            $semaforoStats[$semaforo]++;
        }

        return $this->render('estadisticas', [
            'estadisticasDocumentos' => $estadisticasDocumentos,
            'semaforoStats' => $semaforoStats,
            'totalEmpleados' => count($empleados),
        ]);
    }

    /**
     * Finds the Empleado model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Empleado the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Empleado::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El empleado solicitado no existe.');
    }
}
