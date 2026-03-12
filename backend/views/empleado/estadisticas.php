<?php

use yii\helpers\Html;
use common\models\Empleado;

/* @var $this yii\web\View */
/* @var $estadisticasDocumentos array */
/* @var $semaforoStats array */
/* @var $totalEmpleados int */

$this->title = 'Estadísticas de Documentación';
$this->params['breadcrumbs'][] = ['label' => 'Empleados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="empleado-estadisticas">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <?= Html::a('<i class="bx bx-arrow-back"></i> Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <!-- Resumen de Semáforo -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-success mb-0" style="border-width: 2px;">
                <div class="card-body text-center py-2 px-3">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bx bx-check-circle text-success" style="font-size: 2.5rem;"></i>
                        <div class="ms-3 text-start">
                            <h5 class="mb-0 text-muted">Completos</h5>
                            <h2 class="mb-0 text-success" style="font-weight: bold;">
                                <?= $semaforoStats[Empleado::SEMAFORO_VERDE] ?>
                            </h2>
                            <small class="text-muted">
                                <?= $totalEmpleados > 0 ? round(($semaforoStats[Empleado::SEMAFORO_VERDE] / $totalEmpleados) * 100, 2) : 0 ?>%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning mb-0" style="border-width: 2px;">
                <div class="card-body text-center py-2 px-3">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bx bx-error-circle text-warning" style="font-size: 2.5rem;"></i>
                        <div class="ms-3 text-start">
                            <h5 class="mb-0 text-muted">Incompletos</h5>
                            <h2 class="mb-0 text-warning" style="font-weight: bold;">
                                <?= $semaforoStats[Empleado::SEMAFORO_AMARILLO] ?>
                            </h2>
                            <small class="text-muted">
                                <?= $totalEmpleados > 0 ? round(($semaforoStats[Empleado::SEMAFORO_AMARILLO] / $totalEmpleados) * 100, 2) : 0 ?>%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger mb-0" style="border-width: 2px;">
                <div class="card-body text-center py-2 px-3">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bx bx-x-circle text-danger" style="font-size: 2.5rem;"></i>
                        <div class="ms-3 text-start">
                            <h5 class="mb-0 text-muted">Críticos</h5>
                            <h2 class="mb-0 text-danger" style="font-weight: bold;">
                                <?= $semaforoStats[Empleado::SEMAFORO_ROJO] ?>
                            </h2>
                            <small class="text-muted">
                                <?= $totalEmpleados > 0 ? round(($semaforoStats[Empleado::SEMAFORO_ROJO] / $totalEmpleados) * 100, 2) : 0 ?>%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Documento -->
    <div class="card">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0 text-dark"><i class="bx bx-file"></i> Estadísticas por Tipo de Documento</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Clasificación</th>
                            <th class="text-center">Con Documento</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Porcentaje</th>
                            <th class="text-center">Faltantes</th>
                            <th width="200">Progreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estadisticasDocumentos as $tipo => $stats): ?>
                            <tr>
                                <td>
                                    <strong><?= Html::encode($stats['label']) ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    if ($stats['clasificacion'] === 'Crítico Legal' || $stats['clasificacion'] === 'Crítico Operativo') {
                                        $badgeClass = 'bg-danger';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $stats['clasificacion'] ?></span>
                                </td>
                                <td class="text-center">
                                    <strong><?= $stats['count'] ?></strong>
                                </td>
                                <td class="text-center"><?= $stats['total'] ?></td>
                                <td class="text-center">
                                    <?php
                                    $porcentaje = $stats['porcentaje'];
                                    $colorClass = 'text-danger';
                                    if ($porcentaje >= 80) {
                                        $colorClass = 'text-success';
                                    } elseif ($porcentaje >= 50) {
                                        $colorClass = 'text-warning';
                                    }
                                    ?>
                                    <strong class="<?= $colorClass ?>"><?= $porcentaje ?>%</strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($stats['faltantes'] > 0): ?>
                                        <span class="badge bg-danger"><?= $stats['faltantes'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <?php
                                        $progressClass = 'bg-danger';
                                        if ($porcentaje >= 80) {
                                            $progressClass = 'bg-success';
                                        } elseif ($porcentaje >= 50) {
                                            $progressClass = 'bg-warning';
                                        }
                                        ?>
                                        <div class="progress-bar <?= $progressClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= $porcentaje ?>%;" 
                                             aria-valuenow="<?= $porcentaje ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $porcentaje ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= Html::a(
                                        '<i class="bx bx-search"></i>',
                                        ['index', 'EmpleadoSearch[documento_faltante]' => $tipo],
                                        ['class' => 'btn btn-sm btn-info', 'title' => 'Ver faltantes']
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
