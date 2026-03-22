<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\ExpenseSubcategory;
use common\models\ExpenseSubcategorySearch;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ExpenseSubcategoryController implements the CRUD actions for ExpenseSubcategory model.
 */
class ExpenseSubcategoryController extends Controller
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
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expensecategory_list');
                        }
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expensecategory_view');
                        }
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expensecategory_create');
                        }
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expensecategory_update');
                        }
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('expensecategory_delete');
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all ExpenseSubcategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new ExpenseSubcategorySearch(['business_id' => $business['id']]);
        
        // Obtener el valor de la cookie si existe
        $savedPageSize = (int)Yii::$app->request->cookies->getValue('expense_subcategory_page_size', 20);
        
        // Personalizar elementos por página - solo si viene en la URL
        $perPage = Yii::$app->request->get('per-page');
        
        // Si perPage no viene en la URL o no es válido, usar el valor guardado en la cookie
        if (!$perPage || !in_array((int)$perPage, [20, 50, 100])) {
            $perPage = $savedPageSize;
        } else {
            // Solo guardar una nueva cookie si el valor es diferente al que ya tenemos
            if ((int)$perPage !== $savedPageSize) {
                $cookie = new \yii\web\Cookie([
                    'name' => 'expense_subcategory_page_size',
                    'value' => $perPage,
                    'expire' => time() + 86400 * 30,
                ]);
                Yii::$app->response->cookies->add($cookie);
            }
        }
        
        // Usar perPage como la cantidad de elementos por página
        $pageSize = (int)$perPage;
        
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = $pageSize;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExpenseSubcategory model.
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
     * Creates a new ExpenseSubcategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new ExpenseSubcategory([
            'business_id' => $business['id'],
        ]);

        $post = Yii::$app->request->post();

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
            return;
        }

        if ($model->load($post) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Subcategoría de gasto creada exitosamente.');
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            foreach ($model->getFirstErrors() as $field => $error) {
                Yii::$app->session->addFlash('error', ucfirst($field) . ': ' . $error);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ExpenseSubcategory model.
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

        if ($model->load($post) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Subcategoría de gasto actualizada exitosamente.');
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            foreach ($model->getFirstErrors() as $field => $error) {
                Yii::$app->session->addFlash('error', ucfirst($field) . ': ' . $error);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ExpenseSubcategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Verificar si hay gastos asociados
        $expenseCount = $model->getExpenses()->count();
        
        if ($expenseCount > 0) {
            Yii::$app->session->setFlash('error', "No se puede eliminar la subcategoría porque tiene {$expenseCount} gasto(s) asociado(s).");
            return $this->redirect(['index']);
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Subcategoría de gasto eliminada exitosamente.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the ExpenseSubcategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExpenseSubcategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExpenseSubcategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
