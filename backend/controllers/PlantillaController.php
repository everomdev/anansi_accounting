<?php

namespace backend\controllers;

use Yii;
use common\models\AreaTrabajo;
use common\models\PlantillaPuesto;
use common\models\Empleado;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\helpers\RedisKeys;

/**
 * PlantillaController gestiona las áreas de trabajo y plantilla de puestos
 */
class PlantillaController extends Controller
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
                    'delete-area' => ['POST'],
                    'delete-puesto' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Dashboard principal - Plantilla vs Realidad
     */
    public function actionIndex()
    {
        $business = RedisKeys::getBusiness();
        $businessId = $business->id;

        $areas = AreaTrabajo::find()
            ->where(['business_id' => $businessId, 'estado' => AreaTrabajo::ESTADO_ACTIVO])
            ->orderBy(['orden' => SORT_ASC, 'nombre' => SORT_ASC])
            ->all();

        // Calcular estadísticas generales
        $totalPuestosRequeridos = PlantillaPuesto::find()
            ->where(['business_id' => $businessId, 'estado' => PlantillaPuesto::ESTADO_ACTIVO])
            ->sum('cantidad_ideal') ?: 0;

        $totalEmpleadosActuales = Empleado::find()
            ->where(['business_id' => $businessId, 'estado' => Empleado::ESTADO_ACTIVO])
            ->count();

        return $this->render('index', [
            'areas' => $areas,
            'totalPuestosRequeridos' => $totalPuestosRequeridos,
            'totalEmpleadosActuales' => $totalEmpleadosActuales,
        ]);
    }

    /**
     * Gestión de áreas de trabajo
     */
    public function actionAreas()
    {
        $business = RedisKeys::getBusiness();
        $businessId = $business->id;

        $areas = AreaTrabajo::find()
            ->where(['business_id' => $businessId])
            ->orderBy(['orden' => SORT_ASC, 'nombre' => SORT_ASC])
            ->all();

        return $this->render('areas', [
            'areas' => $areas,
        ]);
    }

    /**
     * Crear área de trabajo
     */
    public function actionCreateArea()
    {
        $business = RedisKeys::getBusiness();
        $model = new AreaTrabajo();
        $model->business_id = $business->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Área de trabajo creada exitosamente.');
            return $this->redirect(['areas']);
        }

        return $this->render('create-area', [
            'model' => $model,
        ]);
    }

    /**
     * Actualizar área de trabajo
     */
    public function actionUpdateArea($id)
    {
        $model = $this->findAreaModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Área de trabajo actualizada exitosamente.');
            return $this->redirect(['areas']);
        }

        return $this->render('update-area', [
            'model' => $model,
        ]);
    }

    /**
     * Eliminar área de trabajo
     */
    public function actionDeleteArea($id)
    {
        $this->findAreaModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Área de trabajo eliminada exitosamente.');
        return $this->redirect(['areas']);
    }

    /**
     * Ver puestos de un área
     */
    public function actionPuestos($area_id)
    {
        $area = $this->findAreaModel($area_id);

        $puestos = PlantillaPuesto::find()
            ->where(['area_trabajo_id' => $area_id])
            ->orderBy(['nombre_puesto' => SORT_ASC])
            ->all();

        return $this->render('puestos', [
            'area' => $area,
            'puestos' => $puestos,
        ]);
    }

    /**
     * Crear puesto
     */
    public function actionCreatePuesto($area_id)
    {
        $area = $this->findAreaModel($area_id);
        $business = RedisKeys::getBusiness();
        
        $model = new PlantillaPuesto();
        $model->business_id = $business->id;
        $model->area_trabajo_id = $area->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Puesto creado exitosamente.');
            return $this->redirect(['puestos', 'area_id' => $area->id]);
        }

        return $this->render('create-puesto', [
            'model' => $model,
            'area' => $area,
        ]);
    }

    /**
     * Actualizar puesto
     */
    public function actionUpdatePuesto($id)
    {
        $model = $this->findPuestoModel($id);
        $area = $model->areaTrabajo;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Puesto actualizado exitosamente.');
            return $this->redirect(['puestos', 'area_id' => $area->id]);
        }

        return $this->render('update-puesto', [
            'model' => $model,
            'area' => $area,
        ]);
    }

    /**
     * Eliminar puesto
     */
    public function actionDeletePuesto($id)
    {
        $model = $this->findPuestoModel($id);
        $areaId = $model->area_trabajo_id;
        $model->delete();
        
        Yii::$app->session->setFlash('success', 'Puesto eliminado exitosamente.');
        return $this->redirect(['puestos', 'area_id' => $areaId]);
    }

    /**
     * Finds the AreaTrabajo model based on its primary key value.
     */
    protected function findAreaModel($id)
    {
        $business = RedisKeys::getBusiness();
        
        if (($model = AreaTrabajo::findOne(['id' => $id, 'business_id' => $business->id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El área solicitada no existe.');
    }

    /**
     * Finds the PlantillaPuesto model based on its primary key value.
     */
    protected function findPuestoModel($id)
    {
        $business = RedisKeys::getBusiness();
        
        if (($model = PlantillaPuesto::findOne(['id' => $id, 'business_id' => $business->id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El puesto solicitado no existe.');
    }
}
