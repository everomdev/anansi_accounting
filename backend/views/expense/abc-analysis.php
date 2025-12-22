<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $analysis array */

$this->title = 'Análisis ABC de Gastos';
$this->params['breadcrumbs'][] = ['label' => 'Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
.abc-summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.category-card-a {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(255,107,107,0.2);
}

.category-card-b {
    background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
    color: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(254,202,87,0.2);
}

.metric-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid #f1f3f4;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.expense-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.recommendations-card {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    border: none;
    border-radius: 15px;
    color: #2c3e50;
}

.priority-badge {
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 20px;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(255,107,107,0.3);
}

.progress-bar-custom {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    overflow: hidden;
}

.progress-fill-a {
    background: linear-gradient(90deg, #ff6b6b, #ee5a24);
    height: 100%;
    border-radius: 4px;
    transition: width 1s ease;
}

.progress-fill-b {
    background: linear-gradient(90deg, #feca57, #ff9ff3);
    height: 100%;
    border-radius: 4px;
    transition: width 1s ease;
}

.glass-effect {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}
');
?>

<div class="expense-abc-analysis">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="abc-summary-card p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">
                            <i class="fas fa-chart-line me-3"></i>
                            Análisis ABC de Gastos
                        </h2>
                        <p class="mb-0 opacity-75">
                            Optimización inteligente de costos operativos mediante análisis de Pareto
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="glass-effect p-3 rounded-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-light opacity-75">Total Mensual</small>
                                    <h4 class="mb-0 text-warning">$<?= number_format($analysis['total_monthly_cost'], 2) ?></h4>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="metric-card p-3 text-center">
                <i class="fas fa-receipt fa-2x text-primary mb-2"></i>
                <h5 class="text-muted">Total Gastos</h5>
                <h3 class="text-primary mb-0"><?= $analysis['total_expenses'] ?></h3>
                <small class="text-muted">activos</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card p-3 text-center">
                <i class="fas fa-fire fa-2x text-danger mb-2"></i>
                <h5 class="text-muted">Categoría A</h5>
                <h3 class="text-danger mb-0"><?= $analysis['categories']['A']['count'] ?? 0 ?></h3>
                <small class="text-muted">alto impacto</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card p-3 text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                <h5 class="text-muted">Categoría B</h5>
                <h3 class="text-warning mb-0"><?= $analysis['categories']['B']['count'] ?? 0 ?></h3>
                <small class="text-muted">impacto medio</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card p-3 text-center">
                <i class="fas fa-chart-pie fa-2x text-info mb-2"></i>
                <h5 class="text-muted">Distribución</h5>
                <h3 class="text-info mb-0">80/20</h3>
                <small class="text-muted">principio Pareto</small>
            </div>
        </div>
    </div>

    <!-- Distribution Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="metric-card p-4">
                <h5 class="mb-3">
                    <i class="fas fa-chart-bar me-2"></i>
                    Distribución de Costos por Categoría
                </h5>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="progress-bar-custom mb-2">
                            <div class="progress-fill-a" style="width: <?= $analysis['categories']['A']['percentage'] ?? 0 ?>%"></div>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill-b" style="width: <?= $analysis['categories']['B']['percentage'] ?? 0 ?>%"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-square text-danger me-2"></i>Categoría A</span>
                            <strong><?= number_format($analysis['categories']['A']['percentage'] ?? 0, 1) ?>%</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-square text-warning me-2"></i>Categoría B</span>
                            <strong><?= number_format($analysis['categories']['B']['percentage'] ?? 0, 1) ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Analysis -->
    <?php foreach (['A', 'B'] as $category): ?>
        <?php $data = $analysis['categories'][$category]; ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="category-card-<?= strtolower($category) ?> p-4">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="fas fa-<?= $category === 'A' ? 'exclamation-circle' : 'info-circle' ?> me-3"></i>
                                Categoría <?= $category ?> - <?= $category === 'A' ? 'Alto Impacto' : 'Impacto Medio' ?>
                            </h4>
                            <p class="mb-0 opacity-75">
                                <?= $category === 'A' ? 'Gastos críticos que requieren atención inmediata' : 'Gastos moderados con potencial de optimización' ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if ($category === 'A'): ?>
                                <div class="priority-badge">
                                    <i class="fas fa-star me-1"></i>
                                    PRIORIDAD ALTA
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="glass-effect p-3 rounded-3 text-center">
                                <i class="fas fa-dollar-sign fa-2x mb-2 opacity-75"></i>
                                <h5 class="mb-1">Costo Mensual</h5>
                                <h3 class="mb-0">$<?= number_format($data['total_cost'], 2) ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="glass-effect p-3 rounded-3 text-center">
                                <i class="fas fa-list fa-2x mb-2 opacity-75"></i>
                                <h5 class="mb-1">Cantidad</h5>
                                <h3 class="mb-0"><?= $data['count'] ?></h3>
                                <small>gastos</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="glass-effect p-3 rounded-3 text-center">
                                <i class="fas fa-percentage fa-2x mb-2 opacity-75"></i>
                                <h5 class="mb-1">Participación</h5>
                                <h3 class="mb-0"><?= number_format($data['percentage'], 1) ?>%</h3>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($data['items'])): ?>
                        <div class="expense-table">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fas fa-tag me-2"></i>Nombre del Gasto</th>
                                            <th><i class="fas fa-folder me-2"></i>Categoría</th>
                                            <th><i class="fas fa-calendar me-2"></i>Frecuencia</th>
                                            <th class="text-end"><i class="fas fa-dollar-sign me-2"></i>Costo Mensual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($data['items'], 0, 10) as $expense): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= Html::encode($expense['name']) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?= Html::encode($expense['category']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= Html::encode(ucfirst($expense['frequency'])) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="text-<?= $category === 'A' ? 'danger' : 'warning' ?>">
                                                        $<?= number_format($expense['monthly_amount'], 2) ?>
                                                    </strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (count($data['items']) > 10): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">
                                                    <em>... y <?= count($data['items']) - 10 ?> gastos más</em>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Recommendations Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="recommendations-card p-4">
                <h4 class="mb-4">
                    <i class="fas fa-lightbulb text-warning me-3"></i>
                    Recomendaciones Estratégicas de Ahorro
                </h4>

                <div class="row">
                    <?php foreach (['A', 'B'] as $category): ?>
                        <?php $data = $analysis['categories'][$category]; ?>
                        <div class="col-md-6 mb-4">
                            <div class="bg-white rounded-3 p-3 h-100">
                                <h5 class="text-<?= $category === 'A' ? 'danger' : 'warning' ?> mb-3">
                                    <i class="fas fa-<?= $category === 'A' ? 'exclamation-triangle' : 'info-circle' ?> me-2"></i>
                                    Categoría <?= $category ?> - <?= $category === 'A' ? 'Alto Impacto' : 'Impacto Medio' ?>
                                </h5>

                                <div class="show" id="recs-<?= $category ?>">
                                    <?php if (!empty($data['recommendations'])): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($data['recommendations'] as $recommendation): ?>
                                                <div class="list-group-item border-0 px-0 py-2">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <?= Html::encode($recommendation) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No hay recomendaciones específicas para esta categoría.
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- General Tips -->
    <div class="row">
        <div class="col-12">
            <div class="metric-card p-4">
                <h5 class="text-success mb-3">
                    <i class="fas fa-leaf me-2"></i>
                    Consejos Generales para una Gestión Eficiente de Gastos
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Revisa regularmente tus gastos y compara con presupuestos</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Negocia con proveedores para obtener mejores condiciones</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Implementa controles de aprobación para gastos grandes</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Considera alternativas digitales para reducir costos</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Realiza mantenimiento preventivo</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Monitorea el ROI de cada gasto operativo</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
