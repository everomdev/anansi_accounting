<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\Category;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Expense;
use common\models\ExpenseSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ExpenseController implements the CRUD actions for Expense model.
 */
class ExpenseController extends Controller
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
        ];
    }

    /**
     * Lists all Expense models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new ExpenseSearch();
        
        // Obtener el valor de la cookie si existe
        $savedPageSize = (int)Yii::$app->request->cookies->getValue('expense_page_size', 10);
        
        // Personalizar elementos por página - solo si viene en la URL
        $perPage = Yii::$app->request->get('per-page');
        
        // Si perPage no viene en la URL o no es válido, usar el valor guardado en la cookie
        if (!$perPage || !in_array((int)$perPage, [10, 25, 50, 100])) {
            $perPage = $savedPageSize;
        } else {
            // Guardar la nueva preferencia en una cookie
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'expense_page_size',
                'value' => (int)$perPage,
                'expire' => time() + (86400 * 365), // 1 año
            ]));
        }
        
        // Usar perPage como la cantidad de elementos por página
        $pageSize = (int)$perPage;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = $pageSize;
        $dataProvider->query->andWhere([
            'business_id' => $business['id']
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'perPage' => $perPage,
        ]);
    }

    public function actionGenerateKey($categoryId)
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        return $this->asJson(Expense::keyGenerator($categoryId, $business['id']));
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
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new Expense([
            'business_id' => $business['id']
        ]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $validator = $this->getContainer()->get(AjaxRequestModelValidator::class);
            return $validator->validate($model);
        }

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
            $validator = $this->getContainer()->get(AjaxRequestModelValidator::class);
            return $validator->validate($model);
        }

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
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        
        if ($request->isPost) {
            $selection = $request->post('selection', []);
            $deleteAll = $request->post('deleteAll', false);
            
            if ($deleteAll) {
                // Eliminar todos los gastos del negocio
                $deleted = Expense::deleteAll(['business_id' => $business['id']]);
            } else {
                // Eliminar solo los seleccionados
                $deleted = Expense::deleteAll(['and', ['business_id' => $business['id']], ['in', 'id', $selection]]);
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
     * Finds the Expense model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Expense the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Expense::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
