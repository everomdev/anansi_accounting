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
                        'roles' => ['kpi_access'],
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
                    'consumido_real',
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
        
        // 8. Analizar alertas de stock basado en min_stock y max_stock
        $alertaStock = $this->analizarAlertasStock($ingrediente, $inventario);
        
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
            'min_stock' => $ingrediente->min_stock,
            'max_stock' => $ingrediente->max_stock,
            'alerta_stock' => $alertaStock['tipo'],
            'nivel_critico' => $alertaStock['critico'],
            'porcentaje_stock' => $alertaStock['porcentaje'],
            'mensaje_alerta' => $alertaStock['mensaje'],
        ];
    }
    
    /**
     * Calcula el consumo por ventas usando las recetas y filtros de período
     * Incluye el consumo de ingredientes en subrecetas
     */
    private function calcularConsumoVentas($ingredienteId, $selectedMonth, $selectedYear, $debug = false)
    {
        $business = RedisKeys::getBusiness();
        $consumoTotal = 0;
        
        try {
            // PASO 1: Buscar consumo directo (ingredientes directos en recetas vendidas)
            $consumoDirecto = $this->calcularConsumoDirecto($ingredienteId, $selectedMonth, $selectedYear, $debug);
            $consumoTotal += $consumoDirecto;
            
            // PASO 2: Buscar consumo indirecto (ingredientes en subrecetas de recetas vendidas)
            $consumoIndirecto = $this->calcularConsumoIndirecto($ingredienteId, $selectedMonth, $selectedYear, $debug);
            $consumoTotal += $consumoIndirecto;
            
            if ($debug) {
                Yii::info("=== RESUMEN CONSUMO INGREDIENTE {$ingredienteId} ===", 'control-insumos');
                Yii::info("Consumo directo: {$consumoDirecto}", 'control-insumos');
                Yii::info("Consumo indirecto (subrecetas): {$consumoIndirecto}", 'control-insumos');
                Yii::info("CONSUMO TOTAL: {$consumoTotal}", 'control-insumos');
            }
            
            return $consumoTotal;
            
        } catch (\Exception $e) {
            Yii::error("Error calculando consumo de ventas para ingrediente {$ingredienteId}: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula el consumo directo (ingredientes directos en recetas)
     */
    private function calcularConsumoDirecto($ingredienteId, $selectedMonth, $selectedYear, $debug = false)
    {
        $business = RedisKeys::getBusiness();
        $consumoTotal = 0;
        
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
        
        if ($debug && !empty($ventasRecetas)) {
            Yii::info("=== CONSUMO DIRECTO INGREDIENTE {$ingredienteId} ===", 'control-insumos');
            Yii::info("Recetas con ingrediente directo: " . count($ventasRecetas), 'control-insumos');
        }
        
        foreach ($ventasRecetas as $venta) {
            $cantidadVendida = floatval($venta['sales'] ?: 0);
            $cantidadIngrediente = floatval($venta['quantity'] ?: 0);
            $consumoPorVenta = $cantidadVendida * $cantidadIngrediente;
            $consumoTotal += $consumoPorVenta;
            
            if ($debug) {
                Yii::info("- Receta: {$venta['receta_nombre']}, Ventas: {$cantidadVendida}, Cantidad: {$cantidadIngrediente}, Consumo: {$consumoPorVenta}", 'control-insumos');
            }
        }
        
        return $consumoTotal;
    }
    
    /**
     * Calcula el consumo indirecto (ingredientes en subrecetas de recetas vendidas)
     */
    private function calcularConsumoIndirecto($ingredienteId, $selectedMonth, $selectedYear, $debug = false)
    {
        $business = RedisKeys::getBusiness();
        $consumoTotal = 0;
        
        // PASO 1: Buscar recetas vendidas que tienen subrecetas
        $whereConditions = [
            'ms.model_type' => 'standard_recipe',
            'sr.business_id' => $business->id
        ];
        
        if ($selectedYear != 0) {
            $whereConditions['ms.year'] = $selectedYear;
        }
        if ($selectedMonth != 0) {
            $whereConditions['ms.month'] = $selectedMonth;
        }
        
        $queryRecetasConSubrecetas = new Query();
        $queryRecetasConSubrecetas->select([
            'ms.sales',
            'ms.model_id as receta_principal_id',
            'sr.title as receta_principal_nombre',
            'srssr.sub_standard_recipe_id',
            'srssr.quantity as cantidad_subreceta',
            'sr_sub.title as subreceta_nombre'
        ])
        ->from('monthly_sales ms')
        ->innerJoin('standard_recipe sr', 'sr.id = ms.model_id')
        ->innerJoin('standard_recipe_sub_standard_recipe srssr', 'srssr.standard_recipe_id = ms.model_id')
        ->innerJoin('standard_recipe sr_sub', 'sr_sub.id = srssr.sub_standard_recipe_id')
        ->where($whereConditions);
        
        $recetasConSubrecetas = $queryRecetasConSubrecetas->all();
        
        if ($debug && !empty($recetasConSubrecetas)) {
            Yii::info("=== CONSUMO INDIRECTO INGREDIENTE {$ingredienteId} ===", 'control-insumos');
            Yii::info("Recetas con subrecetas encontradas: " . count($recetasConSubrecetas), 'control-insumos');
        }
        
        // PASO 2: Para cada subreceta, verificar si contiene nuestro ingrediente
        foreach ($recetasConSubrecetas as $recetaConSub) {
            $ventasRecetaPrincipal = floatval($recetaConSub['sales'] ?: 0);
            $cantidadSubreceta = floatval($recetaConSub['cantidad_subreceta'] ?: 0);
            $subrecetaId = $recetaConSub['sub_standard_recipe_id'];
            
            // Buscar si la subreceta contiene nuestro ingrediente
            $queryIngredienteEnSubreceta = new Query();
            $queryIngredienteEnSubreceta->select(['quantity'])
                ->from('ingredient_standard_recipe')
                ->where([
                    'standard_recipe_id' => $subrecetaId,
                    'ingredient_id' => $ingredienteId
                ]);
            
            $ingredienteEnSubreceta = $queryIngredienteEnSubreceta->one();
            
            if ($ingredienteEnSubreceta) {
                $cantidadIngredienteEnSubreceta = floatval($ingredienteEnSubreceta['quantity'] ?: 0);
                
                // Calcular consumo: ventas_receta_principal × cantidad_subreceta × cantidad_ingrediente_en_subreceta
                $consumoIndirectoPorVenta = $ventasRecetaPrincipal * $cantidadSubreceta * $cantidadIngredienteEnSubreceta;
                $consumoTotal += $consumoIndirectoPorVenta;
                
                if ($debug) {
                    Yii::info("- Receta: {$recetaConSub['receta_principal_nombre']} → Subreceta: {$recetaConSub['subreceta_nombre']}", 'control-insumos');
                    Yii::info("  Ventas: {$ventasRecetaPrincipal} × Cant.Sub: {$cantidadSubreceta} × Cant.Ing: {$cantidadIngredienteEnSubreceta} = {$consumoIndirectoPorVenta}", 'control-insumos');
                }
            }
        }
        
        return $consumoTotal;
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
     * Analiza las alertas de stock basado en min_stock y max_stock
     */
    private function analizarAlertasStock($ingrediente, $inventarioActual)
    {
        $minStock = $ingrediente->min_stock;
        $maxStock = $ingrediente->max_stock;
        
        // Si no hay límites configurados
        if (!$minStock && !$maxStock) {
            return [
                'tipo' => 'sin_configurar',
                'critico' => false,
                'porcentaje' => null,
                'mensaje' => 'Sin límites configurados'
            ];
        }
        
        // Calcular porcentaje basado en el rango min-max
        $porcentaje = null;
        if ($minStock && $maxStock && $maxStock > $minStock) {
            $rango = $maxStock - $minStock;
            $posicionEnRango = $inventarioActual - $minStock;
            $porcentaje = ($posicionEnRango / $rango) * 100;
            $porcentaje = max(0, min(100, $porcentaje)); // Limitar entre 0-100%
        }
        
        // Stock crítico (por debajo del mínimo)
        if ($minStock && $inventarioActual < $minStock) {
            $deficit = $minStock - $inventarioActual;
            return [
                'tipo' => 'critico',
                'critico' => true,
                'porcentaje' => $porcentaje,
                'mensaje' => "Stock crítico: {$deficit} unidades por debajo del mínimo"
            ];
        }
        
        // Stock bajo (entre mínimo y 20% del rango)
        if ($minStock && $maxStock && $inventarioActual >= $minStock) {
            $umbralBajo = $minStock + (($maxStock - $minStock) * 0.2); // 20% del rango
            if ($inventarioActual <= $umbralBajo) {
                return [
                    'tipo' => 'bajo',
                    'critico' => false,
                    'porcentaje' => $porcentaje,
                    'mensaje' => 'Stock bajo - Considerar reabastecimiento pronto'
                ];
            }
        }
        
        // Stock excesivo (por encima del máximo)
        if ($maxStock && $inventarioActual > $maxStock) {
            $exceso = $inventarioActual - $maxStock;
            return [
                'tipo' => 'excesivo',
                'critico' => false,
                'porcentaje' => $porcentaje,
                'mensaje' => "Stock excesivo: {$exceso} unidades por encima del máximo"
            ];
        }
        
        // Stock alto (entre 80% del rango y máximo)
        if ($minStock && $maxStock && $inventarioActual <= $maxStock) {
            $umbralAlto = $minStock + (($maxStock - $minStock) * 0.8); // 80% del rango
            if ($inventarioActual >= $umbralAlto) {
                return [
                    'tipo' => 'alto',
                    'critico' => false,
                    'porcentaje' => $porcentaje,
                    'mensaje' => 'Stock alto - Nivel cerca del máximo'
                ];
            }
        }
        
        // Stock normal
        return [
            'tipo' => 'normal',
            'critico' => false,
            'porcentaje' => $porcentaje,
            'mensaje' => 'Stock en rango normal'
        ];
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
