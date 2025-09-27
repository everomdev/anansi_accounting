<?php
namespace backend\controllers;

use Yii;
use common\models\Inventory;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class InventoryController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Inventory::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Inventory();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $fecha = $post['Inventory']['fecha'] ?? null;
            $inventarios = $post['inventario'] ?? [];
            $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
            $businessId = $businessData['id'] ?? null;
            $errors = [];
            // Obtener todos los insumos del negocio
            $allStocks = \common\models\IngredientStock::find()->where(['business_id' => $businessId])->all();
            $allIds = array_map(function($stock) { return $stock->id; }, $allStocks);
            foreach ($allIds as $insumoId) {
                $data = isset($inventarios[$insumoId]) ? $inventarios[$insumoId] : [];
                $inv = new Inventory();
                $inv->ingredient_stock_id = $insumoId;
                $inv->business_id = $businessId;
                $inv->fecha = $fecha;
                $inv->inventario_almacen = isset($data['inventario_almacen']) && $data['inventario_almacen'] !== '' ? $data['inventario_almacen'] : 0;
                $inv->inventario_cocina = isset($data['inventario_cocina']) && $data['inventario_cocina'] !== '' ? $data['inventario_cocina'] : 0;
                $inv->inventario_barra = isset($data['inventario_barra']) && $data['inventario_barra'] !== '' ? $data['inventario_barra'] : 0;
                $inv->inventario_servicio = isset($data['inventario_servicio']) && $data['inventario_servicio'] !== '' ? $data['inventario_servicio'] : 0;
                $inv->inventario_otro = isset($data['inventario_otro']) && $data['inventario_otro'] !== '' ? $data['inventario_otro'] : 0;
                if (!$inv->save()) {
                    $errors[$insumoId] = $inv->getErrors();
                }
            }
            if (empty($errors)) {
                return $this->redirect(['index']);
            } else {
                var_dump($errors);
                die();
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

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

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Inventory::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested Inventory does not exist.');
    }
    public function actionDetalle($fecha)
    {
        $searchModel = new \common\models\InventorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $fecha);
        return $this->render('detalle', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'fecha' => $fecha,
        ]);
    }
}
