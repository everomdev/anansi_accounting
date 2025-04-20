<?php

/* @var $this yii\web\View */
/* @var $userStats array Estadísticas de usuarios */
/* @var $planStats array Estadísticas de planes */
/* @var $ingredientStats array Estadísticas de ingredientes */
/* @var $categoryStats array Estadísticas de categorías */
/* @var $couponStats array Estadísticas de cupones */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Dashboard Administrativo';
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js');
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css');
?>

<div class="site-index">
    <div class="container-fluid px-4">
        <h1 class="mt-4 mb-4"><?= Html::encode($this->title) ?></h1>
        
        <!-- Fila 1: Métricas principales de usuarios -->
        <div class="row">
            <!-- Total de Usuarios -->
            <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0">Usuarios Totales</h5>
                    <h2 class="display-6 mb-0"><?= $userStats['total'] ?></h2>
                </div>
                <i class="bi bi-people fs-1"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-white">
                    <i class="bi bi-arrow-up me-1"></i><?= $userStats['growth'] ?>% este mes
                </div>
                </div>
            </div>
            </div>
            
            <!-- Duración promedio en el sistema -->
            <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0">Duración Promedio</h5>
                    <h2 class="display-6 mb-0"><?= $userStats['avgDuration'] ?> días</h2>
                </div>
                <i class="bi bi-calendar-check fs-1"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-white">
                    <span>Tiempo promedio de permanencia de usuarios activos</span>
                </div>
                </div>
            </div>
            </div>
            
            <!-- Tasa de conversión -->
            <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0">Tasa de Conversión</h5>
                    <h2 class="display-6 mb-0"><?= $userStats['conversionRate'] ?>%</h2>
                </div>
                <i class="bi bi-graph-up-arrow fs-1"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-white">
                    <span>Visitantes que se convierten en clientes pagos</span>
                </div>
                </div>
            </div>
            </div>
        </div>
        
        <!-- Fila 2: Gráficos principales -->
        <div class="row">
            <!-- Distribución de Planes -->
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-bar-chart-fill me-1"></i>
                        Distribución de Planes
                    </div>
                    <div class="card-body" style="height: 350px;">
                        <canvas id="planDistributionChart" width="100%" height="50"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tendencia de Usuarios en el Tiempo -->
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-line-chart me-1"></i>
                        Tendencia de Usuarios (Últimos 12 Meses)
                    </div>
                    <div class="card-body" style="height: 350px;">
                        <canvas id="userTrendChart" width="100%" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fila 3: Ingredientes y Categorías -->
        <div class="row">
            <!-- Ingredientes Más Utilizados -->
            <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                <i class="bi bi-list-ul me-1"></i>
                Top 10 Ingredientes Más Utilizados
                </div>
                <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Ingrediente</th>
                        <th>Cantidad Total</th>
                        <th>Usos en recetas</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ingredientStats['topIngredients'] as $ingredient): ?>
                    <tr>
                        <td><?= Html::encode($ingredient['ingredient']) ?></td>
                        <td><?= $ingredient['total_quantity'] ?></td>
                        <td>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?= min(100, $ingredient['usage_count'] * 5) ?>%"
                             aria-valuenow="<?= $ingredient['usage_count'] ?>"
                             aria-valuemin="0" aria-valuemax="100">
                            <?= $ingredient['usage_count'] ?>
                            </div>
                        </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
            </div>
            
            <!-- Categorías Más Utilizadas -->
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-tags-fill me-1"></i>
                        Categorías Más Utilizadas
                    </div>
                    <div class="card-body">
                        <canvas id="categoryUsageChart" width="100%" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fila 4: Estadísticas de Cupones -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-ticket-perforated-fill me-1"></i>
                        Estadísticas de Cupones de Promoción
                    </div>
                    <div class="card-body">
                        <!-- Tarjetas resumen cupones -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Cupones Totales</h5>
                                        <h2><?= $couponStats['total'] ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Cupones Activos</h5>
                                        <h2><?= $couponStats['active'] ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Cupones Caducados</h5>
                                        <h2><?= $couponStats['expired'] ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabla de cupones -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Plan Asociado</th>
                                        <th>Descuento</th>
                                        <th>Fecha Expiración</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($couponStats['coupons'] as $coupon): ?>
                                    <tr>
                                        <td><?= Html::encode($coupon['code']) ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= $coupon['plan_class'] ?>">
                                                <?= Html::encode($coupon['plan']) ?>
                                            </span>
                                        </td>
                                        <td><?= $coupon['discount'] ?>%</td>
                                        <td><?= Yii::$app->formatter->asDate($coupon['expiration']) ?></td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?= $coupon['status_class'] ?>">
                                                <?= Html::encode($coupon['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$planLabels = json_encode(array_column($planStats['distribution'], 'name'));
$planData = json_encode(array_column($planStats['distribution'], 'count'));
$planColors = json_encode(['#4e73df', '#1cc88a', '#36b9cc']);

$userTrendLabels = json_encode($userStats['trend']['labels']);
$userTrendData = json_encode($userStats['trend']['data']);

$categoryLabels = json_encode(array_column($categoryStats['topCategories'], 'name'));
$categoryData = json_encode(array_column($categoryStats['topCategories'], 'usage_count'));
$categoryColors = json_encode([
    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
    '#5a5c69', '#6f42c1', '#fd7e14', '#20c997', '#6c757d'
]);

$js = <<<JS
// Función para configuración básica de gráficos
function createChart(ctx, type, labels, datasets, options = {}) {
    return new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: datasets
        },
        options: options
    });
}

// Gráfico de distribución de planes
const planCtx = document.getElementById('planDistributionChart');
const planChart = createChart(
    planCtx,
    'bar',
    $planLabels,
    [{
        label: 'Usuarios',
        backgroundColor: $planColors,
        data: $planData,
        borderWidth: 1
    }],
    {
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        },
        responsive: true,
        maintainAspectRatio: false
    }
);

// Gráfico de tendencia de usuarios
const userTrendCtx = document.getElementById('userTrendChart');
const userTrendChart = createChart(
    userTrendCtx,
    'line',
    $userTrendLabels,
    [{
        label: 'Total Usuarios',
        data: $userTrendData,
        backgroundColor: 'rgba(78, 115, 223, 0.05)',
        borderColor: 'rgba(78, 115, 223, 1)',
        pointRadius: 3,
        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
        pointBorderColor: 'rgba(78, 115, 223, 1)',
        pointHoverRadius: 5,
        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
        pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
        pointHitRadius: 10,
        pointBorderWidth: 2,
        fill: true,
        tension: 0.3
    }],
    {
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            tooltip: {
                backgroundColor: 'rgb(255, 255, 255)',
                bodyColor: '#858796',
                titleMarginBottom: 10,
                titleColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                caretPadding: 10,
                displayColors: false
            }
        },
        responsive: true,
        maintainAspectRatio: false
    }
);

// Gráfico de categorías más utilizadas
const categoryCtx = document.getElementById('categoryUsageChart');
const categoryChart = createChart(
    categoryCtx,
    'doughnut',
    $categoryLabels,
    [{
        data: $categoryData,
        backgroundColor: $categoryColors,
        hoverBackgroundColor: $categoryColors,
        hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
    {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'right'
            },
            tooltip: {
                backgroundColor: "rgb(255,255,255)",
                bodyColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                caretPadding: 10
            }
        }
    }
);
JS;

$this->registerJs($js);
?>