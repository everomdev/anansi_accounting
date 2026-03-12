<?php

use yii\helpers\Html;
use common\models\PlantillaPuesto;

/* @var $this yii\web\View */
/* @var $areas common\models\AreaTrabajo[] */
/* @var $totalPuestosRequeridos int */
/* @var $totalEmpleadosActuales int */

$this->title = 'Plantilla vs Realidad';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plantilla-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="bx bx-cog"></i> Gestionar Áreas', ['areas'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-primary mb-0" style="border-width: 2px;">
                <div class="card-body text-center py-3 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-group text-primary" style="font-size: 2.5rem;"></i>
                            <div class="ms-3 text-start">
                                <h5 class="mb-0 text-muted">Personal Actual</h5>
                                <h2 class="mb-0 text-primary" style="font-weight: bold;"><?= $totalEmpleadosActuales ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-success mb-0" style="border-width: 2px;">
                <div class="card-body text-center py-3 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-target-lock text-success" style="font-size: 2.5rem;"></i>
                            <div class="ms-3 text-start">
                                <h5 class="mb-0 text-muted">Plantilla Ideal</h5>
                                <h2 class="mb-0 text-success" style="font-weight: bold;"><?= $totalPuestosRequeridos ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Áreas y Puestos -->
    <?php if (empty($areas)): ?>
        <div class="alert alert-info">
            <i class="bx bx-info-circle"></i>
            No hay áreas de trabajo configuradas. 
            <?= Html::a('Crear primera área', ['create-area'], ['class' => 'alert-link']) ?>
        </div>
    <?php else: ?>
        <?php foreach ($areas as $area): ?>
            <div class="card mb-4">
                <div class="card-header bg-light border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark">
                            <i class="bx bx-buildings"></i>
                            <?= Html::encode($area->nombre) ?>
                            <span class="badge bg-secondary ms-2">
                                <?= $area->getEmpleadosCount() ?> / <?= $area->getTotalRequerido() ?> personas
                            </span>
                        </h5>
                        <div>
                            <?= Html::a('<i class="bx bx-plus"></i> Agregar Puesto', ['create-puesto', 'area_id' => $area->id], ['class' => 'btn btn-sm btn-success']) ?>
                            <?= Html::a('<i class="bx bx-cog"></i>', ['puestos', 'area_id' => $area->id], ['class' => 'btn btn-sm btn-secondary', 'title' => 'Gestionar puestos']) ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($area->puestos)): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bx bx-error"></i>
                            No hay puestos definidos para esta área.
                            <?= Html::a('Agregar puesto', ['create-puesto', 'area_id' => $area->id], ['class' => 'alert-link']) ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Puesto</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Mín</th>
                                        <th class="text-center">Ideal</th>
                                        <th class="text-center">Actual</th>
                                        <th class="text-center">Estado</th>
                                        <th width="150">Progreso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($area->puestos as $puesto): ?>
                                        <?php $actual = $puesto->getEmpleadosActualesCount(); ?>
                                        <tr>
                                            <td><strong><?= Html::encode($puesto->nombre_puesto) ?></strong></td>
                                            <td><?= Html::encode($puesto->descripcion) ?: '-' ?></td>
                                            <td class="text-center"><?= $puesto->cantidad_minima ?></td>
                                            <td class="text-center"><strong><?= $puesto->cantidad_ideal ?></strong></td>
                                            <td class="text-center">
                                                <span class="badge <?= $puesto->getSemaforoBadgeClass() ?>">
                                                    <?= $actual ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($actual >= $puesto->cantidad_ideal): ?>
                                                    <i class="bx bx-check-circle text-success" style="font-size: 1.5rem;"></i>
                                                <?php elseif ($actual >= $puesto->cantidad_minima): ?>
                                                    <i class="bx bx-error-circle text-warning" style="font-size: 1.5rem;"></i>
                                                <?php else: ?>
                                                    <i class="bx bx-x-circle text-danger" style="font-size: 1.5rem;"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $porcentaje = $puesto->cantidad_ideal > 0 ? round(($actual / $puesto->cantidad_ideal) * 100) : 0;
                                                $porcentaje = min($porcentaje, 100);
                                                ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar <?= $puesto->getSemaforoBadgeClass() ?>" 
                                                         role="progressbar" 
                                                         style="width: <?= $porcentaje ?>%;" 
                                                         aria-valuenow="<?= $porcentaje ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?= $porcentaje ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
