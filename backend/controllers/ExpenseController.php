<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Provider;
use Yii;
use common\models\Expense;
use common\models\ExpenseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ExpenseController implements the CRUD actions for Expense model.
 */
class ExpenseController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * Lists all Expense models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExpenseSearch();
        
        // Personalizar elementos por página
        $savedPageSize = (int)Yii::$app->request->cookies->getValue('expense_page_size', 20);
        $perPage = Yii::$app->request->get('per-page');
        
        if (!$perPage || !in_array((int)$perPage, [10, 20, 50, 100])) {
            $perPage = $savedPageSize;
        } else {
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'expense_page_size',
                'value' => (int)$perPage,
                'expire' => time() + (86400 * 365),
            ]));
        }
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = (int)$perPage;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Displays a single Expense model.
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
     * Creates a new Expense model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getBusiness();
        $model = new Expense();
        $model->business_id = $business->id;
        $model->frequency = 'unico';
        $model->expense_date = date('Y-m-d');
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Gasto creado exitosamente.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Expense model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Gasto actualizado exitosamente.');
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', 'Error al actualizar el gasto.');
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Expense model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Gasto eliminado exitosamente.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al eliminar el gasto.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Bulk remove expenses
     */
    public function actionBulkRemove()
    {
        $request = Yii::$app->request;
        $business = RedisKeys::getBusiness();
        
        if ($request->isPost) {
            $selection = $request->post('selection', []);
            $deleteAll = $request->post('deleteAll', false);
            
            if ($deleteAll) {
                $deleted = Expense::deleteAll(['business_id' => $business->id]);
            } else {
                $deleted = Expense::deleteAll(['and', ['business_id' => $business->id], ['in', 'id', $selection]]);
            }
            
            return $this->asJson([
                'success' => true,
                'message' => "Se eliminaron $deleted gastos exitosamente.",
                'deleted' => $deleted
            ]);
        }
        
        return $this->asJson(['success' => false, 'message' => 'Método no permitido']);
    }

    /**
     * Returns a list of providers for Select2 widget
     */
    public function actionProviderList($q = null)
    {
        $business = RedisKeys::getBusiness();
        $query = Provider::find()
            ->where(['business_id' => $business->id]);

        if ($q) {
            $query->andWhere(['like', 'business_name', $q]);
        }

        $providers = $query->limit(20)->all();
        
        $results = [];
        foreach ($providers as $provider) {
            $results[] = [
                'id' => $provider->id,
                'text' => $provider->business_name,
            ];
        }

        return $this->asJson([
            'results' => $results,
            'pagination' => ['more' => false]
        ]);
    }

    /**
     * Finds the Expense model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Expense the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $business = RedisKeys::getBusiness();
        if (($model = Expense::findOne(['id' => $id, 'business_id' => $business->id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El gasto solicitado no existe.');
    }
}
