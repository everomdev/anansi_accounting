<?php
$this->registerCss('
    .grid-view th a {
        color: #333;
        text-decoration: none;
        position: relative;
        display: block;
    }
    .grid-view th a.asc:after {
        content: " ▲";
        font-size: 12px;
    }
    .grid-view th a.desc:after {
        content: " ▼";
        font-size: 12px;
    }
    .grid-view th a:hover {
        color: #333;
        text-decoration: none;
    }
');

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $selectedMonth int */
/* @var $selectedYear int */
/* @var $years array */

/**
 * Función auxiliar que devuelve el nombre del mes en español
 * @param int $month Número del mes (1-12)
 * @return string Nombre del mes en español
 */
function getMonthName($month) {
    if ($month == 0) {
        return 'Todos los meses';
    }
    $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    return isset($months[$month]) ? $months[$month] : '';
}

$this->title = 'Control de Insumos';
$this->params['breadcrumbs'][] = ['label' => 'KPI\'s y Control', 'url' => ['#']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="kpi-control-insumos">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Pestañas -->
                    <!-- <ul class="nav nav-tabs mb-3" id="controlTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="control-tab" data-bs-toggle="tab" data-bs-target="#control-pane" type="button" role="tab" aria-controls="control-pane" aria-selected="true">
                                <i class="fas fa-chart-line"></i> Control de Insumos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inventario-tab" data-bs-toggle="tab" data-bs-target="#inventario-pane" type="button" role="tab" aria-controls="inventario-pane" aria-selected="false">
                                <i class="fas fa-clipboard-check"></i> Inventario Físico
                            </button>
                        </li>
                    </ul> -->
                    
                    <!-- Contenido de las pestañas -->
                    <div class="tab-content" id="controlTabsContent">
                        
                        <!-- Pestaña Control de Insumos -->
                        <div class="tab-pane fade show active" id="control-pane" role="tabpanel" aria-labelledby="control-tab">
                    
                    <!-- Filtros de mes y año -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-filter"></i> Filtros de Período
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?= Html::beginForm(['control-insumos'], 'get') ?>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="month-select" class="form-label">Mes</label>
                                            <?= Html::dropDownList('month',
                                                $selectedMonth ?? date('n'), 
                                                array_merge(['0' => 'TODOS'], [
                                                    '1' => 'Enero',
                                                    '2' => 'Febrero',
                                                    '3' => 'Marzo',
                                                    '4' => 'Abril',
                                                    '5' => 'Mayo',
                                                    '6' => 'Junio',
                                                    '7' => 'Julio',
                                                    '8' => 'Agosto',
                                                    '9' => 'Septiembre',
                                                    '10' => 'Octubre',
                                                    '11' => 'Noviembre',
                                                    '12' => 'Diciembre',
                                                ]),
                                                ['class' => 'form-select', 'id' => 'month-select']
                                            ) ?>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="year-select" class="form-label">Año</label>
                                            <?= Html::dropDownList('year',
                                                $selectedYear ?? date('Y'),
                                                ['0' => 'TODOS'] + $years,
                                                ['class' => 'form-select', 'id' => 'year-select']
                                            ) ?>
                                        </div>
                                        
                                        <div class="col-md-4 d-flex align-items-end">
                                            <?= Html::submitButton('<i class="fas fa-filter"></i> Filtrar', [
                                                'class' => 'btn btn-primary'
                                            ]) ?>
                                            <?= Html::a('<i class="fas fa-times"></i> Limpiar', ['control-insumos'], [
                                                'class' => 'btn btn-secondary ms-2'
                                            ]) ?>
                                        </div>
                                    </div>
                                    <?= Html::endForm() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información del período -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php
                        $showAllData = ($selectedMonth == 0 || $selectedYear == 0);
                        if ($showAllData) {
                            echo '<strong>Análisis Histórico Completo:</strong> Mostrando TODOS los datos históricos (sin filtro de fecha)';
                            echo '<br><small>Este reporte compara el consumo teórico basado en TODAS las ventas históricas vs. TODAS las compras registradas.</small>';
                        } else {
                            echo '<strong>Período Seleccionado:</strong> ' . getMonthName($selectedMonth) . ' ' . $selectedYear;
                            echo '<br><small>Este reporte compara el consumo teórico basado en las ventas de ' . getMonthName($selectedMonth) . ' ' . $selectedYear . ' vs. las compras del mismo período.</small>';
                        }
                        ?>
                        <br>
                        <small><strong>Total de ingredientes:</strong> <?= count($dataProvider->allModels) ?></small>
                    </div>
                    
                    <!-- Cálculo de estadísticas -->
                    <?php
                    $totalInsumos = count($dataProvider->allModels);
                    $sobrantes = count(array_filter($dataProvider->allModels, function($item) {
                        return $item['diferencia'] > 0;
                    }));
                    $faltantes = count(array_filter($dataProvider->allModels, function($item) {
                        return $item['diferencia'] < 0;
                    }));
                    $equilibrados = $totalInsumos - $sobrantes - $faltantes;
                    
                    // Estadísticas de alertas de stock
                    $stockCritico = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'critico';
                    }));
                    $stockBajo = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'bajo';
                    }));
                    $stockExcesivo = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'excesivo';
                    }));
                    $sinConfigurar = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'sin_configurar';
                    }));
                    $stockNormal = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'normal';
                    }));
                    $stockAlto = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'alto';
                    }));
                    ?>
                    
                    <!-- Filtros rápidos de alertas -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-filter"></i> Filtros Rápidos por Alerta de Stock
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group" aria-label="Filtros de alertas">
                                        <button type="button" class="btn btn-outline-secondary active" onclick="filtrarPorAlerta('todos')">
                                            <i class="fas fa-list"></i> Todos
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="filtrarPorAlerta('critico')">
                                            <i class="fas fa-exclamation-triangle"></i> Críticos (<?= $stockCritico ?>)
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="filtrarPorAlerta('bajo')">
                                            <i class="fas fa-exclamation"></i> Bajos (<?= $stockBajo ?>)
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="filtrarPorAlerta('normal')">
                                            <i class="fas fa-check"></i> Normales (<?= $stockNormal ?>)
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="filtrarPorAlerta('alto')">
                                            <i class="fas fa-arrow-up"></i> Altos (<?= $stockAlto ?>)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorAlerta('excesivo')">
                                            <i class="fas fa-arrow-up"></i> Excesivos (<?= $stockExcesivo ?>)
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="filtrarPorAlerta('sin_configurar')">
                                            <i class="fas fa-cog"></i> Sin Config. (<?= $sinConfigurar ?>)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Alertas de Stock -->
                    <!-- <div class="row mb-3">
                        <div class="col-md-12">
                            <h5><i class="fas fa-exclamation-triangle"></i> Alertas de Stock</h5>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Stock Crítico</span>
                                    <span class="info-box-number"><?= $stockCritico ?></span>
                                    <small class="text-muted">Por debajo del mínimo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Stock Bajo</span>
                                    <span class="info-box-number"><?= $stockBajo ?></span>
                                    <small class="text-muted">Requiere reabastecimiento</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Stock Normal</span>
                                    <span class="info-box-number"><?= $stockNormal ?></span>
                                    <small class="text-muted">En rango óptimo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Stock Alto</span>
                                    <span class="info-box-number"><?= $stockAlto ?></span>
                                    <small class="text-muted">Cerca del máximo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Stock Excesivo</span>
                                    <span class="info-box-number"><?= $stockExcesivo ?></span>
                                    <small class="text-muted">Por encima del máximo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-cog"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sin Configurar</span>
                                    <span class="info-box-number"><?= $sinConfigurar ?></span>
                                    <small class="text-muted">Sin límites definidos</small>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    
                    <!-- Resumen de Diferencias -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5><i class="fas fa-chart-bar"></i> Análisis de Diferencias (Comprado vs. Consumido)</h5>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sobrantes</span>
                                    <span class="info-box-number"><?= $sobrantes ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Faltantes</span>
                                    <span class="info-box-number"><?= $faltantes ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-balance-scale"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Equilibrados</span>
                                    <span class="info-box-number"><?= $equilibrados ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de datos -->
                    <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped'],
                        'columns' => [
                            [
                                'attribute' => 'nombre',
                                'label' => 'Insumo',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $alerta = $model['alerta_stock'] ?? 'sin_configurar';
                                    
                                    // Mapear tipos de alerta a clases CSS
                                    $backgroundClass = '';
                                    $textClass = 'text-white';
                                    
                                    switch ($alerta) {
                                        case 'critico':
                                            $backgroundClass = 'bg-danger';
                                            $textClass = 'text-white';
                                            break;
                                        case 'bajo':
                                            $backgroundClass = 'bg-warning';
                                            $textClass = 'text-dark';
                                            break;
                                        case 'normal':
                                            $backgroundClass = 'bg-success';
                                            $textClass = 'text-white';
                                            break;
                                        case 'alto':
                                            $backgroundClass = 'bg-info';
                                            $textClass = 'text-white';
                                            break;
                                        case 'excesivo':
                                            $backgroundClass = 'bg-primary';
                                            $textClass = 'text-white';
                                            break;
                                        case 'sin_configurar':
                                        default:
                                            $backgroundClass = 'bg-secondary';
                                            $textClass = 'text-white';
                                            break;
                                    }
                                    
                                    $nombre = Html::encode($model['nombre']);
                                    $mensaje = $model['mensaje_alerta'] ?? '';
                                    
                                    return '<div class="ingredient-name-container ' . $backgroundClass . ' ' . $textClass . '" title="' . 
                                           Html::encode($mensaje) . '">' .
                                           '<strong>' . $nombre . '</strong>' .
                                           '</div>';
                                },
                            ],
                            [
                                'attribute' => 'unidad',
                                'label' => 'Unidad',
                                'headerOptions' => ['style' => 'width: 80px;'],
                            ],
                            [
                                'attribute' => 'rendimiento',
                                'label' => 'Rendimiento',
                                'headerOptions' => ['style' => 'width: 100px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $rendimiento = $model['rendimiento'];
                                    $porcentaje = $rendimiento;
                                    return number_format($porcentaje) . '%';
                                },
                            ],
                            [
                                'attribute' => 'consumido',
                                'label' => 'Consumido (Teórico)',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $valor = $model['consumido'];
                                    return '<span class="badge badge-dark text-black">' . number_format($valor, 2) . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'consumido_real',
                                'label' => 'Consumido (Real)',
                                'format' => 'raw',
                                'headerOptions' => [
                                    'style' => 'width: 120px; font-weight: bold; cursor: pointer;',
                                    'class' => 'sortable-column',
                                    'data-sort-by' => 'consumido_real'
                                ],
                                'contentOptions' => ['class' => 'text-center'],
                                'enableSorting' => true,
                                'value' => function ($model) {
                                    $valor = $model['consumido_real'];
                                    return '<span class="badge badge-dark text-black">' . number_format($valor, 2) . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'comprado',
                                'label' => 'Comprado',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $valor = $model['comprado'];
                                    return '<span class="badge badge-dark text-black">' . number_format($valor, 2) . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'inventario',
                                'label' => 'Inventario (Actual)',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $valor = $model['inventario'];
                                    return '<span class="badge badge-dark text-black">' . number_format($valor, 2) . '</span>';
                                },
                            ],
                            [
                                'label' => 'Límites Stock',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 100px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $max = $model['max_stock'] ?? 0;
                                    $min = $model['min_stock'] ?? 0;
                                    if (!$min && !$max) {
                                        return '<small class="text-muted">Sin configurar</small>';
                                    }
                                    $out = '';
                                    if ($max) {
                                        $out .= 'Máx: ' . number_format($max, 1);
                                    }
                                    if ($max && $min) {
                                        $out .= '<br>';
                                    }
                                    if ($min) {
                                        $out .= 'Mín: ' . number_format($min, 1);
                                    }
                                    return '<small class="text-info">' . $out . '</small>';
                                },
                            ],
                            [
                                'label' => 'Alerta Stock',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $alerta = $model['alerta_stock'] ?? 'sin_configurar';
                                    $mensaje = $model['mensaje_alerta'] ?? '';
                                    
                                    $classMap = [
                                        'critico' => 'badge-danger',
                                        'bajo' => 'badge-warning',
                                        'alto' => 'badge-info', 
                                        'excesivo' => 'badge-primary',
                                        'normal' => 'badge-success',
                                        'sin_configurar' => 'badge-secondary'
                                    ];
                                    
                                    $iconMap = [
                                        'critico' => 'fas fa-exclamation-triangle',
                                        'bajo' => 'fas fa-exclamation',
                                        'alto' => 'fas fa-arrow-up',
                                        'excesivo' => 'fas fa-arrow-up',
                                        'normal' => 'fas fa-check',
                                        'sin_configurar' => 'fas fa-cog'
                                    ];
                                    
                                    $textMap = [
                                        'critico' => 'CRÍTICO',
                                        'bajo' => 'BAJO',
                                        'alto' => 'ALTO',
                                        'excesivo' => 'EXCESIVO',
                                        'normal' => 'NORMAL',
                                        'sin_configurar' => 'SIN CONFIG'
                                    ];
                                    
                                    $badgeClass = $classMap[$alerta] ?? 'badge-secondary';
                                    $icon = $iconMap[$alerta] ?? 'fas fa-question';
                                    $text = $textMap[$alerta] ?? 'DESCONOCIDO';
                                    
                                    $badge = '<span class="badge ' . $badgeClass . '" title="' . Html::encode($mensaje) . '">' .
                                             '<i class="' . $icon . '"></i> ' . $text .
                                             '</span>';
                                    
                                    // Agregar barra de progreso para items con límites configurados
                                    if (isset($model['porcentaje_stock']) && $model['porcentaje_stock'] !== null) {
                                        $porcentaje = max(0, min(100, $model['porcentaje_stock']));
                                        $progressClass = 'bg-success';
                                        if ($porcentaje < 20) $progressClass = 'bg-danger';
                                        elseif ($porcentaje < 40) $progressClass = 'bg-warning';
                                        elseif ($porcentaje > 80) $progressClass = 'bg-info';
                                        
                                        $badge .= '<div class="progress mt-1" style="height: 4px;">' .
                                                 '<div class="progress-bar ' . $progressClass . '" style="width: ' . $porcentaje . '%"></div>' .
                                                 '</div>';
                                    }
                                    
                                    return $badge;
                                },
                            ],
                            [
                                'attribute' => 'diferencia',
                                'label' => 'Diferencia (Comprado - Consumido)',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $diferencia = $model['diferencia'];
                                    $signo = $diferencia > 0 ? '+' : '';
                                    return '<span class="badge badge-dark text-black">' . $signo . number_format($diferencia, 2) . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'estado',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 100px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $estado = $model['estado'];
                                    return '<span class="badge badge-dark text-black">' . $estado . '</span>';
                                },
                            ],
                            /*[
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Acciones',
                                'headerOptions' => ['style' => 'width: 80px;'],
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::button('<i class="fas fa-eye"></i>', [
                                            'class' => 'btn btn-sm btn-outline-info',
                                            'title' => 'Ver detalles',
                                            'onclick' => 'mostrarDetalles(' . $model['id'] . ', "' . addslashes($model['nombre']) . '")',
                                        ]);
                                    },
                                ],
                            ],*/
                        ],
                    ]); ?>
                    </div>
                    
                    <!-- Leyenda -->
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Leyenda - Análisis de Consumo:</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-circle text-info"></i> <strong>Consumido (Teórico):</strong> Cantidad que debería haberse consumido según las ventas y recetas.</li>
                                    <li><i class="fas fa-circle text-secondary"></i> <strong>Consumido (Real):</strong> Consumo teórico dividido por el factor de rendimiento del insumo.</li>
                                    <li><i class="fas fa-circle text-primary"></i> <strong>Comprado:</strong> Total de compras registradas en el período.</li>
                                    <li><i class="fas fa-circle text-success"></i> <strong>Inventario:</strong> Stock actual registrado en el sistema.</li>
                                    <li><i class="fas fa-circle text-warning"></i> <strong>Diferencia:</strong> Comprado menos Consumido Real. Positivo = Sobrante, Negativo = Faltante.</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Leyenda - Alertas de Stock:</h5>
                                <ul class="list-unstyled">
                                    <li><span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> CRÍTICO</span> Stock por debajo del mínimo configurado</li>
                                    <li><span class="badge badge-warning"><i class="fas fa-exclamation"></i> BAJO</span> Stock bajo, requiere reabastecimiento pronto</li>
                                    <li><span class="badge badge-success"><i class="fas fa-check"></i> NORMAL</span> Stock en rango normal (entre 20%-80% del rango)</li>
                                    <li><span class="badge badge-info"><i class="fas fa-arrow-up"></i> ALTO</span> Stock alto, cerca del máximo configurado</li>
                                    <li><span class="badge badge-primary"><i class="fas fa-arrow-up"></i> EXCESIVO</span> Stock por encima del máximo configurado</li>
                                    <li><span class="badge badge-secondary"><i class="fas fa-cog"></i> SIN CONFIG</span> Sin límites de stock configurados</li>
                                </ul>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> La barra de progreso muestra la posición del stock actual dentro del rango mínimo-máximo configurado.
                                </small>
                            </div>
                            <!-- <div class="col-md-6">
                                <h5>Leyenda - Colores de Ingredientes:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ingredient-name-container bg-danger text-white mb-2" style="animation: none;">
                                            <small><strong>🔴 CRÍTICO</strong></small>
                                        </div>
                                        <div class="ingredient-name-container bg-warning text-dark mb-2">
                                            <small><strong>🟡 BAJO</strong></small>
                                        </div>
                                        <div class="ingredient-name-container bg-success text-white mb-2">
                                            <small><strong>🟢 NORMAL</strong></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ingredient-name-container bg-info text-white mb-2">
                                            <small><strong>🔵 ALTO</strong></small>
                                        </div>
                                        <div class="ingredient-name-container bg-primary text-white mb-2">
                                            <small><strong>🔵 EXCESIVO</strong></small>

                                $this->registerCss('
                                    .grid-view th a {
                                        color: #333;
                                        text-decoration: none;
                                        position: relative;
                                        display: block;
                                    }
                                    .grid-view th a.asc:after {
                                        content: " ▲";
                                        font-size: 12px;
                                    }
                                    .grid-view th a.desc:after {
                                        content: " ▼";
                                        font-size: 12px;
                                    }
                                    .grid-view th a:hover {
                                        color: #333;
                                        text-decoration: none;
                                    }
                                ');
                                        </div>
                                        <div class="ingredient-name-container bg-secondary text-white mb-2">
                                            <small><strong>⚪ SIN CONFIG</strong></small>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-palette"></i> Los nombres de ingredientes cambian de color según su estado de stock. Pasa el mouse sobre el nombre para ver detalles.
                                </small>
                            </div> -->
                        </div>
                    </div>
                    
                        </div>
                        
                        <!-- Pestaña Inventario Físico -->
                        <div class="tab-pane fade" id="inventario-pane" role="tabpanel" aria-labelledby="inventario-tab">
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> El inventario físico debe realizarse mensualmente para comparar con los datos teóricos del sistema.
                                <br>
                                <small>Esta funcionalidad permite registrar el inventario físico real y compararlo con los cálculos teóricos.</small>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Seleccionar Mes y Año:</h5>
                                    <div class="form-inline">
                                        <?= Html::dropDownList('mes_inventario', date('n'), [
                                            '1' => 'Enero',
                                            '2' => 'Febrero', 
                                            '3' => 'Marzo',
                                            '4' => 'Abril',
                                            '5' => 'Mayo',
                                            '6' => 'Junio',
                                            '7' => 'Julio',
                                            '8' => 'Agosto',
                                            '9' => 'Septiembre',
                                            '10' => 'Octubre',
                                            '11' => 'Noviembre',
                                            '12' => 'Diciembre'
                                        ], ['class' => 'form-control mr-2']) ?>
                                        
                                        <?= Html::dropDownList('año_inventario', date('Y'), array_combine(
                                            range(date('Y')-2, date('Y')+1),
                                            range(date('Y')-2, date('Y')+1)
                                        ), ['class' => 'form-control mr-2']) ?>
                                        
                                        <button type="button" class="btn btn-primary" onclick="cargarInventarioFisico()">
                                            <i class="fas fa-search"></i> Cargar Inventario
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" class="btn btn-success" onclick="exportarInventarioFisico()">
                                        <i class="fas fa-file-excel"></i> Exportar Plantilla
                                    </button>
                                    <button type="button" class="btn btn-info ml-2" onclick="importarInventarioFisico()">
                                        <i class="fas fa-file-import"></i> Importar Inventario
                                    </button>
                                </div>
                            </div>
                            
                            <div id="inventario-fisico-container">
                                <div class="text-center text-muted">
                                    <i class="fas fa-clipboard-list" style="font-size: 3em; color: #ddd;"></i>
                                    <h4>Selecciona un mes para ver el inventario físico</h4>
                                    <p>Puedes registrar el inventario físico mensual para comparar con los datos teóricos.</p>
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
.form-inline .form-group {
    margin-right: 15px;
}

.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-top-left-radius: 2px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 2px;
    display: block;
    float: left;
    height: 90px;
    width: 70px; /* Reducido para acomodar 6 columnas */
    text-align: center;
    font-size: 35px; /* Reducido */
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 8px; /* Reducido padding */
    margin-left: 70px; /* Ajustado al nuevo ancho del icono */
}

.info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 11px; /* Reducido para que quepa mejor */
    line-height: 1.1;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 16px; /* Reducido */
    line-height: 1;
}

.info-box small {
    font-size: 9px; /* Muy pequeño para que quepa */
    line-height: 1;
}

.bg-info {
    background-color: #17a2b8!important;
    color: white;
}

.bg-success {
    background-color: #28a745!important;
    color: white;
}

.bg-danger {
    background-color: #dc3545!important;
    color: white;
}

.bg-warning {
    background-color: #ffc107!important;
    color: black;
}

.bg-secondary {
    background-color: #6c757d!important;
    color: white;
}

/* Estilos específicos para alertas de stock */
.badge {
    font-size: 0.75em;
    padding: 0.375em 0.5em;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
    animation: pulse-danger 2s infinite;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-primary {
    background-color: #007bff;
    color: white;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

/* Animación para alertas críticas */
@keyframes pulse-danger {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Estilos para las barras de progreso pequeñas */
.progress {
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Estilos para tooltips de alertas */
.badge[title]:hover {
    cursor: help;
    opacity: 0.8;
}

/* Estilos para contenedor de nombres de ingredientes con colores de stock */
.ingredient-name-container {
    padding: 8px 12px;
    border-radius: 6px;
    margin: 2px 0;
    font-weight: 600;
    text-align: center;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: help;
}

.ingredient-name-container:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Animación especial para ingredientes críticos */
.ingredient-name-container.bg-danger {
    animation: pulse-critical 2s infinite;
    border: 2px solid #fff;
}

@keyframes pulse-critical {
    0% { 
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

/* Mejoras de contraste para texto */
.ingredient-name-container.bg-warning {
    color: #212529 !important;
    font-weight: 700;
}

.ingredient-name-container.bg-secondary {
    opacity: 0.8;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 768px) {
    .ingredient-name-container {
        padding: 6px 8px;
        min-height: 35px;
        font-size: 0.9em;
    }
}
</style>

<script>
function cargarInventarioFisico() {
    const mes = document.querySelector('select[name="mes_inventario"]').value;
    const año = document.querySelector('select[name="año_inventario"]').value;
    
    // Aquí puedes agregar la lógica para cargar el inventario físico
    document.getElementById('inventario-fisico-container').innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Cargando inventario físico para ${obtenerNombreMes(mes)} ${año}...</strong>
            <br>
            <small>Esta funcionalidad será implementada en una futura actualización.</small>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Inventario Sistema</th>
                        <th>Inventario Físico</th>
                        <th>Diferencia</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="fas fa-tools"></i> 
                            Funcionalidad en desarrollo
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function exportarInventarioFisico() {
    alert('Funcionalidad de exportar plantilla de inventario físico será implementada próximamente.');
}

function importarInventarioFisico() {
    alert('Funcionalidad de importar inventario físico será implementada próximamente.');
}

function obtenerNombreMes(numeroMes) {
    const meses = [
        '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    return meses[parseInt(numeroMes)];
}

function mostrarDetalles(ingredienteId, nombreInsumo) {
    // Crear un modal para mostrar detalles del insumo
    const modalHtml = `
        <div class="modal fade" id="detallesModal" tabindex="-1" role="dialog" aria-labelledby="detallesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detallesModalLabel">
                            <i class="fas fa-info-circle"></i> Detalles de: ${nombreInsumo}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Información:</strong> Aquí se mostrarán los detalles del cálculo del insumo.
                            <br><small>Esta funcionalidad será implementada en una futura actualización.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Recetas que usan este insumo:</h6>
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <i class="fas fa-utensils"></i> Próximamente...
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Historial de movimientos:</h6>
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <i class="fas fa-chart-line"></i> Próximamente...
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Eliminar modal existente si existe
    const existingModal = document.getElementById('detallesModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Mostrar modal
    $('#detallesModal').modal('show');
}

// Funciones para filtros rápidos de alertas
function filtrarPorAlerta(tipoAlerta) {
    const table = document.querySelector('.table-responsive table tbody');
    const rows = table.querySelectorAll('tr');
    const buttons = document.querySelectorAll('[onclick^="filtrarPorAlerta"]');
    
    // Actualizar estado de botones
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filtrar filas
    rows.forEach(row => {
        if (tipoAlerta === 'todos') {
            row.style.display = '';
            return;
        }
        
        // Buscar el contenedor del nombre del ingrediente para identificar el tipo de alerta
        const nameContainer = row.querySelector('.ingredient-name-container');
        if (!nameContainer) {
            row.style.display = tipoAlerta === 'sin_configurar' ? '' : 'none';
            return;
        }
        
        // Verificar el tipo de alerta basado en las clases CSS del contenedor del nombre
        let shouldShow = false;
        switch (tipoAlerta) {
            case 'critico':
                shouldShow = nameContainer.classList.contains('bg-danger');
                break;
            case 'bajo':
                shouldShow = nameContainer.classList.contains('bg-warning');
                break;
            case 'excesivo':
                shouldShow = nameContainer.classList.contains('bg-primary');
                break;
            case 'sin_configurar':
                shouldShow = nameContainer.classList.contains('bg-secondary');
                break;
            case 'normal':
                shouldShow = nameContainer.classList.contains('bg-success');
                break;
            case 'alto':
                shouldShow = nameContainer.classList.contains('bg-info');
                break;
        }
        
        row.style.display = shouldShow ? '' : 'none';
    });
    
    // Actualizar contador visible
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none').length;
    
    // Mostrar mensaje temporal con el resultado del filtro
    mostrarMensajeFiltro(tipoAlerta, visibleRows);
}

// Función para mostrar mensaje temporal del filtro aplicado
function mostrarMensajeFiltro(tipoAlerta, cantidad) {
    const filtroNames = {
        'todos': 'Todos los ingredientes',
        'critico': 'Ingredientes críticos',
        'bajo': 'Ingredientes con stock bajo',
        'normal': 'Ingredientes con stock normal',
        'alto': 'Ingredientes con stock alto',
        'excesivo': 'Ingredientes con stock excesivo',
        'sin_configurar': 'Ingredientes sin configurar'
    };
    
    const mensaje = `${filtroNames[tipoAlerta]}: ${cantidad} encontrados`;
    
    // Crear o actualizar el mensaje
    let mensajeElement = document.getElementById('filtro-mensaje');
    if (!mensajeElement) {
        mensajeElement = document.createElement('div');
        mensajeElement.id = 'filtro-mensaje';
        mensajeElement.className = 'alert alert-info alert-dismissible fade show mt-2';
        mensajeElement.style.position = 'relative';
        
        const tableContainer = document.querySelector('.table-responsive');
        tableContainer.parentNode.insertBefore(mensajeElement, tableContainer);
    }
    
    mensajeElement.innerHTML = `
        <i class="fas fa-filter"></i> <strong>Filtro aplicado:</strong> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Auto-ocultar después de 3 segundos
    setTimeout(() => {
        if (mensajeElement && mensajeElement.parentNode) {
            mensajeElement.remove();
        }
    }, 3000);
}
</script>
