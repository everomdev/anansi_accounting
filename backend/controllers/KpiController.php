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
use common\models\Category;
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
                        'actions' => ['control-insumos', 'control-almacen', 'compras-vs-consumo', 'proyeccion-compras', 'comparar-insumos', 'comparacion-insumos','ajustar-existencia', 'historial-ajustes','ajustar-inventario-completo', 'ajustar-existencia-masivo'],
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
     * Displays the control de insumos page (MANTENER POR COMPATIBILIDAD)
     */
    public function actionControlInsumos()
    {
        // Redirigir al nuevo KPI de Compras vs Consumo
        return $this->redirect(['kpi/compras-vs-consumo']);
    }
    
    /**
     * KPI: Control de Almacén (Inventario vs Mínimos y Máximos)
     */
    public function actionControlAlmacen()
    {
        $business = RedisKeys::getBusiness();
        
        // Obtener todos los ingredientes activos del negocio
        $ingredientes = IngredientStock::find()
            ->where(['business_id' => $business->id])
            ->orderBy('ingredient ASC')
            ->all();
        
        $datosControl = [];
        
        foreach ($ingredientes as $ingrediente) {
            $inventario = $ingrediente->quantity ?: 0;
            $alertaStock = $this->analizarAlertasStock($ingrediente, $inventario);
            
            $datosControl[] = [
                'id' => $ingrediente->id,
                'nombre' => $ingrediente->ingredient,
                'unidad' => $ingrediente->um,
                'inventario' => round($inventario, 2),
                'min_stock' => $ingrediente->min_stock,
                'max_stock' => $ingrediente->max_stock,
                'alerta_stock' => $alertaStock['tipo'],
                'nivel_critico' => $alertaStock['critico'],
                'porcentaje_stock' => $alertaStock['porcentaje'],
                'mensaje_alerta' => $alertaStock['mensaje'],
            ];
        }
        
        // Crear el data provider
        $dataProvider = new ArrayDataProvider([
            'allModels' => $datosControl,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'nombre',
                    'inventario',
                    'min_stock',
                    'max_stock',
                    'alerta_stock',
                ],
            ],
        ]);
        
        return $this->render('control-almacen', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * KPI: Compras vs Consumo Teórico
     */
    public function actionComprasVsConsumo()
    {
        $business = RedisKeys::getBusiness();
        
        // Obtener parámetros de filtro
        $selectedMonth = Yii::$app->request->get('month', date('n'));
        $selectedYear = Yii::$app->request->get('year', date('Y'));
        
        // Validar y corregir valores
        $selectedMonth = intval($selectedMonth);
        $selectedYear = intval($selectedYear);
        
        // Si el año es menor a 2000, probablemente es un error (índice en lugar de valor)
        // En ese caso, usar el año actual por defecto
        if ($selectedYear > 0 && $selectedYear < 2000) {
            Yii::warning("Año inválido recibido: {$selectedYear}, usando año actual", 'kpi-compras');
            $selectedYear = date('Y');
        }
        
        // Validar mes (0-12)
        if ($selectedMonth < 0 || $selectedMonth > 12) {
            $selectedMonth = date('n');
        }
        
        // Generar array de años disponibles (key = value para que el dropdown envíe el año correcto)
        $years = [0 => 'TODOS'];
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
            
            // Calcular estado basado en (Comprado - Consumido) vs Inventario
            $esperado = $datos['comprado'] - $datos['consumido_real'];
            $diferencia = $esperado - $datos['inventario'];
            
            $estadoDiferencia = 'equilibrado';
            if (abs($diferencia) < 0.01) {
                $estadoDiferencia = 'equilibrado';
            } elseif ($diferencia < 0) {
                $estadoDiferencia = 'faltante'; // El inventario es MENOR a lo esperado
            } else {
                $estadoDiferencia = 'sobrante'; // El inventario es MAYOR a lo esperado
            }
            
            $datosControl[] = [
                'id' => $datos['id'],
                'nombre' => $datos['nombre'],
                'unidad' => $datos['unidad'],
                'comprado' => round($datos['comprado'], 2),
                'consumido_real' => round($datos['consumido_real'], 2),
                'inventario' => round($datos['inventario'], 2),
                'estado_diferencia' => $estadoDiferencia,
                'diferencia' => round($diferencia, 2),
            ];
        }
        
        // Crear el data provider
        $dataProvider = new ArrayDataProvider([
            'allModels' => $datosControl,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'nombre',
                    'comprado',
                    'consumido_real',
                    'inventario',
                ],
            ],
        ]);
        
        return $this->render('compras-vs-consumo', [
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
    // 1. Obtener datos base
    $unidad = $ingrediente->um;
    $unidadPorcion = $ingrediente->portion_um;
    $rendimiento = $ingrediente->yield ?: 100;
    $portionsPorUnidad = $ingrediente->portions_per_unit ?: 1;

    // 2. Obtener TODAS las subrecetas del ingrediente
    $subrecetas = $ingrediente->getSubRecipes()->all();
    $tieneSubrecetas = $ingrediente->getSubRecipes()->exists();

    // 3. CALCULAR CONSUMO TOTAL SUMANDO CADA SUBRECETA POR SEPARADO
    $consumoTotalUnidadCompra = 0;

    if ($tieneSubrecetas) {
        // PARA CADA SUBRECETA, calcular su consumo individual
        foreach ($subrecetas as $subreceta) {
            $consumoSubreceta = $this->calcularConsumoPorSubreceta($ingrediente, $subreceta->standard_recipe_id, $selectedMonth, $selectedYear, $debug);
            $consumoTotalUnidadCompra += $consumoSubreceta;
        }
    } 
    // INGREDIENTE DIRECTO (sin subrecetas)
    $consumoHojas = $this->calcularConsumoDirectoSinSubrecetas($ingrediente->id, $selectedMonth, $selectedYear, $debug);
    $consumoTotalUnidadCompra += $this->convertirUnidadCocinaACompra($ingrediente, $consumoHojas, $debug);
    

    // 4. Consumo teórico es el total calculado
    $consumoTeorico = $consumoTotalUnidadCompra;

    // 5. Aplicar rendimiento para consumo real
    $rendimientoDecimal = $rendimiento / 100;
    if ($rendimientoDecimal <= 0) $rendimientoDecimal = 1;
    $consumoReal = $consumoTeorico / $rendimientoDecimal;

    // 6. Compras e inventario
    $comprado = $this->calcularCompras($ingrediente->id, $selectedMonth, $selectedYear, false);
    $inventario = $ingrediente->quantity ?: 0;

    // 7. Diferencia y estado
    $diferencia = $comprado - $consumoReal;
    $estado = $this->determinarEstado($diferencia, $inventario);
    $alertaStock = $this->analizarAlertasStock($ingrediente, $inventario);


    return [
        'id' => $ingrediente->id,
        'nombre' => $ingrediente->ingredient,
        'unidad' => $unidad,
        'unidad_cocina' => $unidadPorcion,
        'porciones_por_unidad' => $portionsPorUnidad,
        'rendimiento' => $rendimiento,
        'consumido' => round($consumoTeorico, 2),
        'consumido_cocina' => round($this->calcularConsumoVentas($ingrediente->id, $selectedMonth, $selectedYear, false), 2),
        'consumido_real' => round($consumoReal, 2),
        'comprado' => round($comprado, 2),
        'inventario' => round($inventario, 2),
        'diferencia' => round($diferencia, 2),
        'estado' => $estado,
        'min_stock' => $ingrediente->min_stock,
        'max_stock' => $ingrediente->max_stock,
        'alerta_stock' => $alertaStock['tipo'],
        'nivel_critico' => $alertaStock['critico'],
        'porcentaje_stock' => $alertaStock['porcentaje'],
        'mensaje_alerta' => $alertaStock['mensaje'],
        'tiene_subrecetas' => $tieneSubrecetas,
        'numero_subrecetas' => count($subrecetas),
    ];
}

/**
 * Calcula el consumo para UNA subreceta específica
 */
private function calcularConsumoPorSubreceta($ingrediente, $subreceta, $selectedMonth, $selectedYear, $debug = false)
{
    $business = RedisKeys::getBusiness();
    // 1. Obtener ventas de recetas que usan ESTA subreceta
    $whereConditions = [
        'ms.model_type' => 'standard_recipe',
        'sr.business_id' => $business->id,
        'srssr.sub_standard_recipe_id' => $subreceta
    ];
    
    if ($selectedYear != 0) $whereConditions['ms.year'] = $selectedYear;
    if ($selectedMonth != 0) $whereConditions['ms.month'] = $selectedMonth;
    
    $queryRecetas = new Query();
    $queryRecetas->select([
        'ms.sales',
        'sr.title as receta_nombre',
        'srssr.quantity as cantidad_subreceta_en_receta'
    ])
    ->from('monthly_sales ms')
    ->innerJoin('standard_recipe sr', 'sr.id = ms.model_id')
    ->innerJoin('standard_recipe_sub_standard_recipe srssr', 'srssr.standard_recipe_id = ms.model_id')
    ->where($whereConditions);

    $recetas = $queryRecetas->all();
    
    $consumoTotalSubreceta = 0;
    // 2. Para cada receta que usa esta subreceta
    foreach ($recetas as $receta) {
        $ventasReceta = floatval($receta['sales'] ?: 0);
        $cantidadSubrecetaEnReceta = floatval($receta['cantidad_subreceta_en_receta'] ?: 0);
        
        // 3. Obtener cantidad del ingrediente en ESTA subreceta
        $ingredienteEnSubreceta = (new Query())
            ->select(['quantity'])
            ->from('ingredient_standard_recipe')
            ->where([
                'standard_recipe_id' => $subreceta,
                'ingredient_id' => $ingrediente->id
            ])
            ->one();
            
        if ($ingredienteEnSubreceta) {
            $cantidadIngredienteEnSubreceta = floatval($ingredienteEnSubreceta['quantity'] ?: 0);
            
            // 4. Calcular consumo en unidad de cocina de la subreceta
            $consumoUnidadCocina = $ventasReceta * $cantidadSubrecetaEnReceta * $cantidadIngredienteEnSubreceta;
            
            // 5. Convertir a unidad de compra según las características de ESTA subreceta
            $consumoUnidadCompra = $this->convertirConsumoSubreceta($ingrediente, $subreceta, $consumoUnidadCocina, $debug);
            
            $consumoTotalSubreceta += $consumoUnidadCompra;
        }
    }
    return $consumoTotalSubreceta;
}

/**
 * Convierte el consumo de una subreceta a unidad de compra
 */
private function convertirConsumoSubreceta($ingrediente, $subreceta, $consumoUnidadCocina, $debug = false)
{
    // CASO 1: Si la subreceta tiene yield (ej: 120 hojas por kg)
    $sub_recipe = StandardRecipe::findOne($subreceta);

    if ($sub_recipe->yield && $sub_recipe->yield > 0) {
        // Ejemplo: consumo en hojas → kg
        $consumoKg = $consumoUnidadCocina / $sub_recipe->portions;
        // Luego kg → piezas (si aplica)
        if ($ingrediente->portions_per_unit && $ingrediente->portions_per_unit > 0) {
            $consumoPiezas = $consumoKg / $ingrediente->portions_per_unit;
            return $consumoPiezas;
        }
        
        return $consumoKg;
    }
    
    // CASO 2: Si no hay yield, verificar si necesita conversión de unidades
    $necesitaConversion = ($subreceta->yield_um && $ingrediente->um && 
                          $subreceta->yield_um !== $ingrediente->um);
    
    if ($necesitaConversion && $ingrediente->portions_per_unit && $ingrediente->portions_per_unit > 0) {
        // Conversión directa usando portions_per_unit
        $consumoConvertido = $consumoUnidadCocina / $ingrediente->portions_per_unit;
        
        if ($debug) {
            Yii::warning("    Conversión directa: {$consumoUnidadCocina} {$subreceta->yield_um} / {$ingrediente->portions_per_unit} = {$consumoConvertido} {$ingrediente->um}", 'control-insumos');
        }
        
        return $consumoConvertido;
    }
    
    // CASO 3: Mismas unidades, no necesita conversión
    return $consumoUnidadCocina;
}

/**
 * Para ingredientes sin subrecetas - cálculo directo
 */
private function calcularConsumoDirectoSinSubrecetas($ingredienteId, $selectedMonth, $selectedYear, $debug = false)
{
    return $this->calcularConsumoDirecto($ingredienteId, $selectedMonth, $selectedYear, $debug);
}

/**
 * Conversión para ingredientes directos sin subrecetas
 */
private function convertirUnidadCocinaACompra($ingrediente, $consumoUnidadCocina, $debug = false)
{
    $necesitaConversion = ($ingrediente->portion_um && $ingrediente->um && 
                          $ingrediente->portion_um !== $ingrediente->um);
    
    if ($necesitaConversion && $ingrediente->portions_per_unit && $ingrediente->portions_per_unit > 0) {
        $consumoConvertido = $consumoUnidadCocina / $ingrediente->portions_per_unit;
        
        if ($debug) {
            Yii::warning("Conversión ingrediente directo: {$consumoUnidadCocina} {$ingrediente->portion_um} → {$consumoConvertido} {$ingrediente->um}", 'control-insumos');
        }
        
        return $consumoConvertido;
    }
    
    return $consumoUnidadCocina;
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
    
        
        foreach ($ventasRecetas as $venta) {
            $cantidadVendida = floatval($venta['sales'] ?: 0);
            $cantidadIngrediente = floatval($venta['quantity'] ?: 0);
            $consumoPorVenta = $cantidadVendida * $cantidadIngrediente;
            $consumoTotal += $consumoPorVenta;
        }
        //die(var_dump($consumoTotal,'as', $ingredienteId));
        return $consumoTotal;
    }
    
    /**
     * Calcula el consumo indirecto (ingredientes en subrecetas de recetas vendidas)
     */
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
    
    // PASO 2: Para cada subreceta, verificar si contiene nuestro ingrediente
    foreach ($recetasConSubrecetas as $recetaConSub) {
        $ventasRecetaPrincipal = floatval($recetaConSub['sales'] ?: 0);
        $cantidadSubreceta = floatval($recetaConSub['cantidad_subreceta'] ?: 0);
        $subrecetaId = $recetaConSub['sub_standard_recipe_id'];

        // FALTABA ESTA CONSULTA: Buscar si el ingrediente está en la subreceta
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

            // Calcular hojas consumidas (unidad de cocina de la subreceta)
            $unidadesCocinaConsumidas = $ventasRecetaPrincipal * $cantidadSubreceta * $cantidadIngredienteEnSubreceta;
            
            // Para consumo indirecto, siempre devolvemos las unidades de cocina (hojas)
            // La conversión a unidad de compra se hace en calcularDatosInsumo
            $consumoTotal += $unidadesCocinaConsumidas;
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
        
        // Si es "TODOS" (0,0), no aplicar filtros de fecha
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
        // Si ambos son 0 (TODOS), no se aplican filtros de fecha
        
        // Debug: Log de la consulta SQL
        if ($debug || $ingredienteId == 4027) { // 4027 = ACHIOTE
            Yii::warning("=== DEBUG COMPRAS - Ingrediente ID: {$ingredienteId} ===", 'kpi-compras');
            Yii::warning("SQL: " . $query->createCommand()->getRawSql(), 'kpi-compras');
        }
        
        $result = $query->one();
        $totalComprado = floatval($result['total_comprado'] ?: 0);
        
        // Debug: Verificar movimientos individuales
        if ($debug || $ingredienteId == 4027) {
            $movimientos = Movement::find()
                ->where([
                    'ingredient_id' => $ingredienteId,
                    'business_id' => $business->id,
                ])
                ->all();
            
            Yii::warning("Total movimientos encontrados: " . count($movimientos), 'kpi-compras');
            foreach ($movimientos as $mov) {
                Yii::warning("Movimiento ID {$mov->id}: type={$mov->type}, quantity={$mov->quantity}, created_at={$mov->created_at}", 'kpi-compras');
            }
            Yii::warning("Total comprado calculado: {$totalComprado}", 'kpi-compras');
        }
        
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
    public function actionCompararInsumos()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $business = \backend\helpers\RedisKeys::getBusiness();
        $ingredientes = \common\models\IngredientStock::find()
            ->where(['business_id' => $business->id])
            ->orderBy('ingredient ASC')
            ->all();

        $result = [];
        foreach ($ingredientes as $ingrediente) {
            // Existencia almacén
            $existencia_almacen = $ingrediente->quantity;
            // Inventario almacén (puedes ajustar si tienes el modelo Inventory)
            $inventario_almacen = isset($ingrediente->inventario_almacen) ? $ingrediente->inventario_almacen : '-';
            // Compras menos consumo real
            // Puedes usar los métodos del controlador para calcular estos valores
            $comprado = method_exists($this, 'calcularCompras') ? $this->calcularCompras($ingrediente->id, 0, 0, false) : 0;
            $consumido_real = method_exists($this, 'calcularConsumoVentas') ? $this->calcularConsumoVentas($ingrediente->id, 0, 0, false) : 0;
            $compras_menos_consumo = $comprado - $consumido_real;

            $result[] = [
                'nombre' => $ingrediente->ingredient,
                'existencia_almacen' => $existencia_almacen,
                'inventario_almacen' => $inventario_almacen,
                'compras_menos_consumo' => $compras_menos_consumo,
            ];
        }
        return $result;
    }
     /**
     * Página de comparación de insumos con paginación y filtros
     */
    /**
 * Página de comparación de insumos con paginación y filtros
 */
public function actionComparacionInsumos()
{
    $business = \backend\helpers\RedisKeys::getBusiness();
    $fecha = \Yii::$app->request->get('fecha');
    $nombre = \Yii::$app->request->get('nombre', '');
    $categoriaId = \Yii::$app->request->get('categoria', '');
    $datos = [];

    // Obtener todas las categorías con las mismas condiciones que en detalle
    $categorias = Category::find()
        ->where([
            'or',
            ['business_id' => $business->id],
            ['builtin' => 1]
        ])
        ->select(['name', 'id'])
        ->indexBy('id')
        ->column();

    // Buscar inventario por fecha
    $inventarioModels = [];
    if ($fecha) {
        $inventarioModels = \common\models\Inventory::find()
            ->where(['business_id' => $business->id, 'fecha' => $fecha])
            ->with('inventoryConsumptionCenters')
            ->all();
    }

    // Si no hay inventario para la fecha, mostrar vacío
    if (empty($inventarioModels)) {
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => [],
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('comparacion-insumos', [
            'dataProvider' => $dataProvider,
            'categorias' => $categorias,
            'fecha' => $fecha,
            'nombre' => $nombre,
            'categoria' => $categoriaId,
        ]);
    }

    // Obtener el centro de consumo "Almacén" para este business
    $almacenCenter = \common\models\ConsumptionCenter::find()
        ->where(['business_id' => $business->id, 'name' => 'Almacén'])
        ->one();

    if (!$almacenCenter) {
        // Si no hay centro "Almacén", mostrar error o usar 0
        Yii::$app->session->setFlash('error', 'No se encontró el centro de consumo "Almacén" para este negocio.');
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => [],
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('comparacion-insumos', [
            'dataProvider' => $dataProvider,
            'categorias' => $categorias,
            'fecha' => $fecha,
            'nombre' => $nombre,
            'categoria' => $categoriaId,
        ]);
    }

    // Crear mapa de inventario por ingredient_stock_id
    $inventarioMap = [];
    foreach ($inventarioModels as $inv) {
        foreach ($inv->inventoryConsumptionCenters as $icc) {
            if ($icc->consumption_center_id == $almacenCenter->id) {
                $inventarioMap[$inv->ingredient_stock_id] = $icc->quantity;
                break;
            }
        }
    }

    // Obtener los insumos del inventario de esa fecha
    foreach ($inventarioModels as $inv) {
        $ingrediente = $inv->ingredientStock;
        if (!$ingrediente) continue;
        
        // Aplicar filtros
        if ($nombre && stripos($ingrediente->ingredient, $nombre) === false) continue;
        if ($categoriaId && (!isset($ingrediente->category) || $ingrediente->category->id != $categoriaId)) continue;
        
        // REUTILIZAR EL MÉTODO EXISTENTE con período "TODOS" (0)
        $datosInsumo = $this->calcularDatosInsumo($ingrediente, 0, 0, false);
        
        $existencia_almacen = $ingrediente->quantity;
        // Usar el inventario del centro "Almacén"
        $inventario_almacen = isset($inventarioMap[$ingrediente->id]) ? $inventarioMap[$ingrediente->id] : 0;
        $comprado = $datosInsumo['comprado'];
        $consumido_real = $datosInsumo['consumido_real'];
        $compras_menos_consumo = $comprado - $consumido_real;
        
        $datos[] = [
            'ingredient_stock_id' => $ingrediente->id,
            'nombre' => $ingrediente->ingredient,
            'categoria' => isset($ingrediente->category) ? $ingrediente->category->name : '-',
            'unidad_compra' => $ingrediente->um,
            'existencia_almacen' => $existencia_almacen,
            'inventario_almacen' => $inventario_almacen,
            'compras_menos_consumo' => $compras_menos_consumo,
            'consumido_real' => $consumido_real,
            'comprado' => $comprado,
        ];
    }
    
    // Obtener el tamaño de página de la petición, por defecto 10
    $perPage = Yii::$app->request->get('per-page', 10);
    
    $dataProvider = new \yii\data\ArrayDataProvider([
        'allModels' => $datos,
        'pagination' => ['pageSize' => $perPage],
        'sort' => [
            'attributes' => ['nombre', 'categoria', 'unidad_compra', 'existencia_almacen', 'inventario_almacen', 'compras_menos_consumo'],
        ],
    ]);
    
    return $this->render('comparacion-insumos', [
        'dataProvider' => $dataProvider,
        'categorias' => $categorias,
        'fecha' => $fecha,
        'nombre' => $nombre,
        'categoria' => $categoriaId,
    ]); 
}

    /**
     * Ajusta la existencia de un insumo en el almacén y registra el log
     */
    public function actionAjustarExistencia()
    {
        if (Yii::$app->request->isPost) {
            $ingredientStockId = Yii::$app->request->post('ingredient_stock_id');
            $existenciaAnterior = Yii::$app->request->post('existencia_anterior');
            $nuevaExistencia = Yii::$app->request->post('nueva_existencia');
            $motivo = Yii::$app->request->post('motivo');
            
            if (!$ingredientStockId || !is_numeric($nuevaExistencia)) {
                return $this->asJson(['success' => false, 'message' => 'Datos inválidos']);
            }
            
            $ingredientStock = \common\models\IngredientStock::findOne($ingredientStockId);
            if (!$ingredientStock) {
                return $this->asJson(['success' => false, 'message' => 'Insumo no encontrado']);
            }
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Actualizar la existencia
                $ingredientStock->quantity = $nuevaExistencia;
                if (!$ingredientStock->save()) {
                    throw new \Exception('Error al actualizar la existencia');
                }
                
                // Crear el log
                $log = new \common\models\LogsInventario();
                $log->ingredient_stock_id = $ingredientStockId;
                $log->existencia_anterior = $existenciaAnterior;
                $log->existencia_nueva = $nuevaExistencia;
                $log->motivo = $motivo;
                $log->fecha_ajuste = date('Y-m-d H:i:s');
                
                // Asegurar que user_id y business_id estén asignados
                if (!Yii::$app->user->isGuest) {
                    $log->user_id = Yii::$app->user->id;
                }
                
                $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
                if ($businessData && isset($businessData['id'])) {
                    $log->business_id = $businessData['id'];
                }
                
                if (!$log->save()) {
                    $errors = $log->getErrors();
                    $errorMsg = 'Error al guardar el log: ' . json_encode($errors);
                    throw new \Exception($errorMsg);
                }
                
                $transaction->commit();
                return $this->asJson(['success' => true, 'message' => 'Existencia ajustada correctamente']);
                
            } catch (\Exception $e) {
                $transaction->rollback();
                return $this->asJson(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        
        return $this->asJson(['success' => false, 'message' => 'Método no permitido']);
    }

    /**
     * Ajusta la existencia de múltiples insumos de forma masiva
     */
    public function actionAjustarExistenciaMasivo()
    {
        if (Yii::$app->request->isPost) {
            $ajustes = Yii::$app->request->post('ajustes', []);
            
            if (empty($ajustes)) {
                return $this->asJson(['success' => false, 'message' => 'No hay ajustes para procesar']);
            }
            
            $transaction = Yii::$app->db->beginTransaction();
            $ajustados = 0;
            $errores = [];
            
            try {
                foreach ($ajustes as $ajuste) {
                    $ingredientStockId = $ajuste['ingredient_stock_id'] ?? null;
                    $existenciaAnterior = $ajuste['existencia_anterior'] ?? 0;
                    $nuevaExistencia = $ajuste['nueva_existencia'] ?? 0;
                    $motivo = $ajuste['motivo'] ?? 'Ajuste masivo';
                    
                    if (!$ingredientStockId || !is_numeric($nuevaExistencia)) {
                        $errores[] = "Datos inválidos para insumo ID: $ingredientStockId";
                        continue;
                    }
                    
                    $ingredientStock = \common\models\IngredientStock::findOne($ingredientStockId);
                    if (!$ingredientStock) {
                        $errores[] = "Insumo no encontrado ID: $ingredientStockId";
                        continue;
                    }
                    
                    // Actualizar la existencia
                    $ingredientStock->quantity = $nuevaExistencia;
                    if (!$ingredientStock->save()) {
                        $errores[] = "Error al actualizar insumo: " . $ingredientStock->ingredient;
                        continue;
                    }
                    
                    // Crear el log
                    $log = new \common\models\LogsInventario();
                    $log->ingredient_stock_id = $ingredientStockId;
                    $log->existencia_anterior = $existenciaAnterior;
                    $log->existencia_nueva = $nuevaExistencia;
                    $log->motivo = $motivo;
                    $log->fecha_ajuste = date('Y-m-d H:i:s');
                    
                    // Asegurar que user_id y business_id estén asignados
                    if (!Yii::$app->user->isGuest) {
                        $log->user_id = Yii::$app->user->id;
                    }
                    
                    $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
                    if ($businessData && isset($businessData['id'])) {
                        $log->business_id = $businessData['id'];
                    }
                    
                    if (!$log->save()) {
                        $errores[] = "Error al guardar log para: " . $ingredientStock->ingredient;
                        continue;
                    }
                    
                    $ajustados++;
                }
                
                $transaction->commit();
                
                $mensaje = "Se ajustaron $ajustados insumos correctamente.";
                if (!empty($errores)) {
                    $mensaje .= " Errores: " . implode(', ', $errores);
                }
                
                return $this->asJson([
                    'success' => true, 
                    'message' => $mensaje,
                    'ajustados' => $ajustados,
                    'errores' => $errores
                ]);
                
            } catch (\Exception $e) {
                $transaction->rollback();
                return $this->asJson(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        
        return $this->asJson(['success' => false, 'message' => 'Método no permitido']);
    }

    /**
     * Ajusta todo el inventario de una fecha específica al almacén
     */
    public function actionAjustarInventarioCompleto()
    {
        if (Yii::$app->request->isPost) {
            $fecha = Yii::$app->request->post('fecha');
            
            if (!$fecha) {
                return $this->asJson(['success' => false, 'message' => 'Fecha requerida']);
            }
            
            $business = \backend\helpers\RedisKeys::getBusiness();
            
            // Obtener todos los inventarios de esa fecha
            $inventarios = \common\models\Inventory::find()
                ->where(['business_id' => $business->id, 'fecha' => $fecha])
                ->with(['ingredientStock', 'inventoryConsumptionCenters'])
                ->all();
            
            if (empty($inventarios)) {
                return $this->asJson(['success' => false, 'message' => 'No se encontraron inventarios para esa fecha']);
            }

            // Obtener el centro de consumo "Almacén" para este business
            $almacenCenter = \common\models\ConsumptionCenter::find()
                ->where(['business_id' => $business->id, 'name' => 'Almacén'])
                ->one();

            if (!$almacenCenter) {
                return $this->asJson(['success' => false, 'message' => 'No se encontró el centro de consumo "Almacén" para este negocio.']);
            }
            
            $transaction = Yii::$app->db->beginTransaction();
            $ajustados = 0;
            $errores = [];
            
            try {
                foreach ($inventarios as $inventario) {
                    if (!$inventario->ingredientStock) {
                        $errores[] = "Insumo no encontrado para inventario ID: " . $inventario->id;
                        continue;
                    }
                    
                    $ingredientStock = $inventario->ingredientStock;
                    $existenciaAnterior = $ingredientStock->quantity;
                    
                    // Encontrar el inventario del centro "Almacén"
                    $nuevaExistencia = 0;
                    foreach ($inventario->inventoryConsumptionCenters as $icc) {
                        if ($icc->consumption_center_id == $almacenCenter->id) {
                            $nuevaExistencia = $icc->quantity;
                            break;
                        }
                    }
                    
                    // Solo ajustar si hay diferencia
                    if ($existenciaAnterior != $nuevaExistencia) {
                        // Actualizar la existencia
                        $ingredientStock->quantity = $nuevaExistencia;
                        if (!$ingredientStock->save()) {
                            $errores[] = "Error al actualizar: " . $ingredientStock->ingredient;
                            continue;
                        }
                        
                        // Crear el log
                        $log = new \common\models\LogsInventario();
                        $log->ingredient_stock_id = $ingredientStock->id;
                        $log->existencia_anterior = $existenciaAnterior;
                        $log->existencia_nueva = $nuevaExistencia;
                        $log->motivo = "Ajuste masivo desde inventario fecha: $fecha";
                        $log->fecha_ajuste = date('Y-m-d H:i:s');
                        
                        // Asegurar que user_id y business_id estén asignados
                        if (!Yii::$app->user->isGuest) {
                            $log->user_id = Yii::$app->user->id;
                        }
                        
                        $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
                        if ($businessData && isset($businessData['id'])) {
                            $log->business_id = $businessData['id'];
                        }
                        
                        if (!$log->save()) {
                            $errores[] = "Error al guardar log para: " . $ingredientStock->ingredient;
                            continue;
                        }
                        
                        $ajustados++;
                    }
                }
                
                $transaction->commit();
                
                $mensaje = "Se ajustaron $ajustados insumos correctamente.";
                if (!empty($errores)) {
                    $mensaje .= " Errores: " . implode(', ', $errores);
                }
                
                return $this->asJson([
                    'success' => true, 
                    'message' => $mensaje,
                    'ajustados' => $ajustados,
                    'errores' => $errores
                ]);
                
            } catch (\Exception $e) {
                $transaction->rollback();
                return $this->asJson(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        
        return $this->asJson(['success' => false, 'message' => 'Método no permitido']);
    }

    /**
     * Muestra el historial de ajustes realizados
     */
    public function actionHistorialAjustes()
    {
        $searchModel = new \common\models\LogsInventarioSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('historial-ajustes', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
