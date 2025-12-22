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
                    'bulk-remove' => ['POST'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'bulk-remove', 'provider-list', 'abc-analysis'],
                        'allow' => true,
                        'roles' => ['@'], // Cualquier usuario autenticado
                    ],
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
        $model->is_recurring = 0; // Por defecto no es recurrente
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post())) {
            // Si no es recurrente, limpiar campos relacionados
            if (!$model->is_recurring) {
                $model->amount = null;
                $model->frequency = null;
                $model->expense_date = null;
            } else {
                // Si es recurrente, establecer frecuencia por defecto si no se especificó
                if (empty($model->frequency)) {
                    $model->frequency = 'mensual';
                }
                if (empty($model->expense_date)) {
                    $model->expense_date = date('Y-m-d');
                }
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Gasto creado exitosamente.');
                return $this->redirect(['index']);
            }
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

        if ($model->load(Yii::$app->request->post())) {
            // Si no es recurrente, limpiar campos relacionados
            if (!$model->is_recurring) {
                $model->amount = null;
                $model->frequency = null;
                $model->expense_date = null;
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Gasto actualizado exitosamente.');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el gasto.');
            }
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
     * ABC Analysis for expenses
     */
    public function actionAbcAnalysis()
    {
        $business = RedisKeys::getBusiness();
        
        // Get all active expenses for the business
        $expenses = Expense::find()
            ->where(['business_id' => $business->id, 'is_active' => 1])
            ->all();
        
        $analysis = $this->performAbcAnalysis($expenses);
        
        return $this->render('abc-analysis', [
            'analysis' => $analysis,
        ]);
    }

    /**
     * Performs ABC analysis on expenses
     */
    private function performAbcAnalysis($expenses)
    {
        $expenseData = [];
        $totalMonthlyCost = 0;
        
        // Calculate monthly amounts and total
        foreach ($expenses as $expense) {
            $monthlyAmount = $expense->getMonthlyAmount();
            $expenseData[] = [
                'id' => $expense->id,
                'name' => $expense->name,
                'monthly_amount' => $monthlyAmount,
                'category' => $expense->category ? $expense->category->name : 'Sin categoría',
                'frequency' => $expense->frequency,
                'is_recurring' => $expense->is_recurring,
            ];
            $totalMonthlyCost += $monthlyAmount;
        }
        
        // Sort by monthly amount descending
        usort($expenseData, function($a, $b) {
            return $b['monthly_amount'] <=> $a['monthly_amount'];
        });
        
        // Perform ABC analysis with 2 categories (A and B)
        $cumulativeCost = 0;
        $analysis = [
            'A' => ['items' => [], 'total_cost' => 0, 'percentage' => 0, 'count' => 0, 'recommendations' => []],
            'B' => ['items' => [], 'total_cost' => 0, 'percentage' => 0, 'count' => 0, 'recommendations' => []],
        ];
        
        foreach ($expenseData as $expense) {
            $cumulativeCost += $expense['monthly_amount'];
            $cumulativePercentage = ($cumulativeCost / $totalMonthlyCost) * 100;
            
            if ($cumulativePercentage <= 80) { // A items: first 80% of cumulative cost
                $category = 'A';
            } else { // B items: remaining 20%
                $category = 'B';
            }
            
            $analysis[$category]['items'][] = $expense;
            $analysis[$category]['total_cost'] += $expense['monthly_amount'];
            $analysis[$category]['count']++;
        }
        
        // Calculate percentages
        foreach ($analysis as $category => &$data) {
            $data['percentage'] = $totalMonthlyCost > 0 ? ($data['total_cost'] / $totalMonthlyCost) * 100 : 0;
            $data['recommendations'] = $this->generateRecommendations($category, $data['items']);
        }
        
        return [
            'categories' => $analysis,
            'total_monthly_cost' => $totalMonthlyCost,
            'total_expenses' => count($expenseData),
        ];
    }
    
    /**
     * Generate practical recommendations for expense reduction
     */
    private function generateRecommendations($category, $expenses)
    {
        $recommendations = [];
        
        if ($category === 'A') {
            // High impact expenses - focus on optimization and reduction
            $recommendations[] = "Revisar contratos y buscar proveedores alternativos para reducir costos.";
            $recommendations[] = "Implementar controles de consumo más estrictos.";
            $recommendations[] = "Evaluar si algunos gastos pueden ser eliminados o reducidos sin afectar operaciones.";
            $recommendations[] = "Negociar mejores condiciones de pago o descuentos por volumen.";
            
            // Category-specific recommendations
            $categories = array_unique(array_column($expenses, 'category'));
            foreach ($categories as $cat) {
                if ($cat === 'Servicios Públicos') {
                    $recommendations[] = "Implementar medidas de ahorro energético (apagar equipos, usar LED, etc.).";
                } elseif ($cat === 'Alquiler') {
                    $recommendations[] = "Considerar renegociar contrato de alquiler o buscar espacios más pequeños.";
                } elseif (strpos($cat, 'Personal') !== false) {
                    $recommendations[] = "Optimizar horarios de trabajo y evaluar productividad del personal.";
                }
            }
            
        } elseif ($category === 'B') {
            // Medium impact expenses - focus on monitoring and gradual reduction
            $recommendations[] = "Establecer presupuestos mensuales y alertas de gasto.";
            $recommendations[] = "Buscar alternativas más económicas para suministros.";
            $recommendations[] = "Implementar compras centralizadas para obtener mejores precios.";
            $recommendations[] = "Revisar frecuencias de gastos recurrentes y ajustar según necesidad.";
            
            // Check for high-frequency expenses
            $highFrequency = array_filter($expenses, function($expense) {
                return in_array($expense['frequency'], ['diario', 'semanal']);
            });
            if (count($highFrequency) > 0) {
                $recommendations[] = "Evaluar si algunos gastos de alta frecuencia pueden convertirse a mensual o trimestral.";
            }
        }
        
        return $recommendations;
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
