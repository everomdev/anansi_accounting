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
        
        // Obtener parámetros de filtro
        $selectedMonth = Yii::$app->request->get('month', date('n'));
        $selectedYear = Yii::$app->request->get('year', date('Y'));
        
        // Generar array de años disponibles
        $years = [];
        for ($i = 2020; $i <= date('Y'); $i++) {
            $years[$i] = $i;
        }
        
        $debug = Yii::$app->request->get('debug', false);
        
        // Obtener todos los ingredientes activos del negocio
        $ingredientes = IngredientStock::find()
            ->where(['business_id' => $business->id])
            ->orderBy('ingredient ASC')
            ->all();
        
        $datosControl = [];
        
        foreach ($ingredientes as $ingrediente) {
            $datos = $this->calcularDatosInsumo($ingrediente, $selectedMonth, $selectedYear, $debug);
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
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'years' => $years,
        ]);
    }
    
    /**
     * Calcula los datos de control para un insumo específico
     */
    private function calcularDatosInsumo($ingrediente, $selectedMonth, $selectedYear, $debug = false)
    {
        $unidad = $ingrediente->um ?: 'und';
        $rendimiento = $ingrediente->yield ?: 1;
        $portionsPorUnidad = $ingrediente->portions_per_unit ?: 1; // Cuántas porciones de cocina por unidad de compra
        $unidadPorcion = $ingrediente->portion_um ?: 'und'; // Unidad de cocina
        
        // 1. Calcular consumo por ventas (en unidad de cocina: litros)
        $consumoVentasEnCocina = $this->calcularConsumoVentas($ingrediente->id, $selectedMonth, $selectedYear, $debug);
        
        // 2. Convertir consumo de unidad de cocina a unidad de compra
        // Si consumo = 10 litros y portions_per_unit = 12 litros/caja
        // Entonces: 10 litros ÷ 12 litros/caja = 0.83 cajas
        $consumoVentasEnCompra = $portionsPorUnidad > 0 ? $consumoVentasEnCocina / $portionsPorUnidad : $consumoVentasEnCocina;
        
        // 3. Calcular compras (movimientos de entrada en unidad de compra)
        $compras = $this->calcularCompras($ingrediente->id, $selectedMonth, $selectedYear, $debug);
        
        // 4. Obtener inventario actual (en unidad de compra)
        $inventario = $ingrediente->quantity ?: 0;
        
        // 5. Aplicar rendimiento al consumo (solo si es necesario)
        $consumoReal = $rendimiento > 0 ? $consumoVentasEnCompra / ($rendimiento/100) : $consumoVentasEnCompra;

        // 6. Calcular diferencia (comprado - consumido)
        $diferencia = $compras - $consumoReal;
        
        // 7. Determinar estado
        $estado = $this->determinarEstado($diferencia, $inventario);
        
        return [
            'id' => $ingrediente->id,
            'nombre' => $ingrediente->ingredient,
            'unidad' => $unidad,
            'unidad_cocina' => $unidadPorcion,
            'porciones_por_unidad' => $portionsPorUnidad,
            'rendimiento' => $rendimiento,
            'consumido' => $consumoVentasEnCompra, // Consumo teórico en unidad de compra
            'consumido_cocina' => $consumoVentasEnCocina, // Consumo en unidad de cocina para referencia
            'consumido_real' => $consumoReal,
            'comprado' => $compras,
            'inventario' => $inventario,
            'diferencia' => $diferencia,
            'estado' => $estado,
        ];
    }
    
    /**
     * Calcula el consumo por ventas usando las recetas y filtros de período
     */
    private function calcularConsumoVentas($ingredienteId, $selectedMonth, $selectedYear, $debug = false)
    {
        $business = RedisKeys::getBusiness();
        $consumoTotal = 0;
        
        try {
            // Construir filtros según el período seleccionado
            $whereConditions = [
                'ms.model_type' => 'standard_recipe',
                'isr.ingredient_id' => $ingredienteId,
                'sr.business_id' => $business->id
            ];
            
            // Si se selecciona "TODOS" (0), no agregar filtros de período
            if ($selectedYear != 0) {
                $whereConditions['ms.year'] = $selectedYear;
            }
            if ($selectedMonth != 0) {
                $whereConditions['ms.month'] = $selectedMonth;
            }
            
            // Buscar ventas del período específico
            $query = new Query();
            $query->select([
                'ms.sales',
                'isr.quantity',
                'sr.title as receta_nombre',
                'ms.month',
                'ms.year'
            ])
            ->from('monthly_sales ms')
            ->innerJoin('ingredient_standard_recipe isr', 'isr.standard_recipe_id = ms.model_id')
            ->innerJoin('standard_recipe sr', 'sr.id = ms.model_id')
            ->where($whereConditions);
            
            $ventasRecetas = $query->all();
            
            foreach ($ventasRecetas as $venta) {
                $cantidadVendida = floatval($venta['sales'] ?: 0);
                $cantidadIngrediente = floatval($venta['quantity'] ?: 0);
                $consumoPorVenta = $cantidadVendida * $cantidadIngrediente;
                $consumoTotal += $consumoPorVenta;
            }
            
            return $consumoTotal;
            
        } catch (\Exception $e) {
            Yii::error("Error calculando consumo de ventas para ingrediente {$ingredienteId}: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula las compras (movimientos de entrada) para el período seleccionado
     */
    private function calcularCompras($ingredienteId, $selectedMonth, $selectedYear, $debug = false)
    {
        $business = RedisKeys::getBusiness();
        
        try {
            $query = new Query();
            $query->select('SUM(quantity) as total_comprado')
                ->from('movement')
                ->where([
                    'ingredient_id' => $ingredienteId,
                    'business_id' => $business->id,
                    'type' => 'input' // Solo movimientos de entrada
                ]);
            
            // Si no es "TODOS", aplicar filtros de fecha
            if ($selectedYear != 0 && $selectedMonth != 0) {
                // Filtro específico para año y mes
                $fechaInicio = sprintf('%d-%02d-01 00:00:00', $selectedYear, $selectedMonth);
                $fechaFin = date('Y-m-t 23:59:59', strtotime($fechaInicio));
                
                $query->andWhere(['>=', 'created_at', $fechaInicio])
                      ->andWhere(['<=', 'created_at', $fechaFin]);
            } elseif ($selectedYear != 0) {
                // Solo filtro por año
                $fechaInicio = sprintf('%d-01-01 00:00:00', $selectedYear);
                $fechaFin = sprintf('%d-12-31 23:59:59', $selectedYear);
                
                $query->andWhere(['>=', 'created_at', $fechaInicio])
                      ->andWhere(['<=', 'created_at', $fechaFin]);
            }
            
            $result = $query->one();
            $totalComprado = floatval($result['total_comprado'] ?: 0);
            
            return $totalComprado;
            
        } catch (\Exception $e) {
            Yii::error("Error calculando compras para ingrediente {$ingredienteId}: " . $e->getMessage());
            return 0;
        }
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
