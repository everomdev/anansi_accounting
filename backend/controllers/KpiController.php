<?php

namespace backend\controllers;

use Yii;
use backend\helpers\RedisKeys;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use common\models\IngredientStock;
use common\models\Movement;
use common\models\Recipe;
use common\models\Sales;
use common\models\MonthlySales;
use common\models\StandardRecipe;
use common\models\IngredientStandardRecipe;
use common\models\Menu;
use yii\helpers\ArrayHelper;
use yii\db\Query;

/**
 * KPI controller
 */
class KpiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['control-insumos', 'proyeccion-compras'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays the control de insumos page
     */
    public function actionControlInsumos()
    {
        $business = RedisKeys::getBusiness();
        $fechaDesde = Yii::$app->request->get('fecha_desde', date('Y-m-01')); // Primer día del mes actual
        $fechaHasta = Yii::$app->request->get('fecha_hasta', date('Y-m-t')); // Último día del mes actual
        
        // Obtener todos los ingredientes activos del negocio
        $ingredientes = IngredientStock::find()
            ->where(['business_id' => $business->id])
            ->orderBy('ingredient ASC')
            ->all();
        
        $datosControl = [];
        
        foreach ($ingredientes as $ingrediente) {
            $datos = $this->calcularDatosInsumo($ingrediente, $fechaDesde, $fechaHasta);
            $datosControl[] = $datos;
        }
        
        // Crear el data provider
        $dataProvider = new ArrayDataProvider([
            'allModels' => $datosControl,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'nombre',
                    'consumido',
                    'comprado',
                    'inventario',
                    'diferencia',
                    'estado',
                ],
            ],
        ]);
        
        return $this->render('control-insumos', [
            'dataProvider' => $dataProvider,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ]);
    }
    
    /**
     * Calcula los datos de control para un insumo específico
     */
    private function calcularDatosInsumo($ingrediente, $fechaDesde, $fechaHasta)
    {
        $unidad = $ingrediente->um ?: 'und';
        $rendimiento = $ingrediente->yield ?: 1;
        
        // 1. Calcular consumo por ventas (usando recetas)
        $consumoVentas = $this->calcularConsumoVentas($ingrediente->id, $fechaDesde, $fechaHasta);
        
        // 2. Calcular compras (movimientos de entrada)
        $compras = $this->calcularCompras($ingrediente->id, $fechaDesde, $fechaHasta);
        
        // 3. Obtener inventario actual
        $inventario = $ingrediente->quantity ?: 0;
        
        // 4. Aplicar rendimiento al consumo
        $consumoReal = $rendimiento > 0 ? $consumoVentas / ($rendimiento/100) : $consumoVentas;

        // 5. Calcular diferencia (comprado - consumido)
        $diferencia = $compras - $consumoReal;
        
        // 6. Determinar estado
        $estado = $this->determinarEstado($diferencia, $inventario);
        
        return [
            'id' => $ingrediente->id,
            'nombre' => $ingrediente->ingredient,
            'unidad' => $unidad,
            'rendimiento' => $rendimiento,
            'consumido' => $consumoVentas,
            'consumido_real' => $consumoReal,
            'comprado' => $compras,
            'inventario' => $inventario,
            'diferencia' => $diferencia,
            'estado' => $estado,
        ];
    }
    
    /**
     * Calcula el consumo por ventas usando las recetas
     */
    private function calcularConsumoVentas($ingredienteId, $fechaDesde, $fechaHasta)
    {
        $business = RedisKeys::getBusiness();
        
        $consumoTotal = 0;
        
        // Buscar TODAS las ventas históricas de recetas que usan este ingrediente (sin filtro de fecha)
        $query = new Query();
        $query->select([
            'ms.sales',
            'isr.quantity',
            'sr.title as receta_nombre'
        ])
        ->from('monthly_sales ms')
        ->innerJoin('ingredient_standard_recipe isr', 'isr.standard_recipe_id = ms.model_id')
        ->innerJoin('standard_recipe sr', 'sr.id = ms.model_id')
        ->where([
            'ms.model_type' => MonthlySales::TYPE_RECIPE,
            'isr.ingredient_id' => $ingredienteId,
            'sr.business_id' => $business->id
        ]);
        
        $ventasRecetas = $query->all();
        
        foreach ($ventasRecetas as $venta) {
            $consumoPorVenta = $venta['sales'] * $venta['quantity'];
            $consumoTotal += $consumoPorVenta;
        }
        
        // Buscar ventas de menús
        $query2 = new Query();
        $query2->select([
            'ms.sales',
            'isr.quantity',
            'm.name as menu_nombre'
        ])
        ->from('monthly_sales ms')
        ->innerJoin('menu_standard_recipe msr', 'msr.menu_id = ms.model_id')
        ->innerJoin('ingredient_standard_recipe isr', 'isr.standard_recipe_id = msr.standard_recipe_id')
        ->innerJoin('menu m', 'm.id = ms.model_id')
        ->where([
            'ms.model_type' => MonthlySales::TYPE_MENU,
            'isr.ingredient_id' => $ingredienteId,
            'm.business_id' => $business->id
        ]);
        
        $ventasMenus = $query2->all();
        
        foreach ($ventasMenus as $venta) {
            $consumoPorVenta = $venta['sales'] * $venta['quantity'];
            $consumoTotal += $consumoPorVenta;
        }
        
        return $consumoTotal;
    }
    
    /**
     * Calcula las compras (movimientos de entrada)
     */
    private function calcularCompras($ingredienteId, $fechaDesde, $fechaHasta)
    {
        $business = RedisKeys::getBusiness();
        
        // Obtener TODOS los movimientos históricos (sin filtro de fecha)
        $query = new Query();
        $query->select('SUM(quantity) as total_comprado')
            ->from('movement')
            ->where([
                'ingredient_id' => $ingredienteId,
                'business_id' => $business->id,
                'type' => 'input' // Solo movimientos de entrada
            ]);
        
        $result = $query->one();
        $totalComprado = $result['total_comprado'] ?: 0;
        
        return $totalComprado;
    }
    
    /**
     * Determina el estado basado en la diferencia e inventario
     */
    private function determinarEstado($diferencia, $inventario)
    {
        if ($diferencia > 0) {
            return 'Sobrante';
        } elseif ($diferencia < 0) {
            return 'Faltante';
        } else {
            return 'Equilibrado';
        }
    }
    
    /**
     * Displays the proyección de compras page
     */
    public function actionProyeccionCompras()
    {
        // Placeholder para futura implementación
        return $this->render('proyeccion-compras');
    }
}
