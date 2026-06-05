<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $areas common\models\AreaTrabajo[] */

$this->title = 'Gestión de Áreas de Trabajo';
$this->params['breadcrumbs'][] = ['label' => 'Plantilla vs Realidad', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="area-trabajo-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="bx bx-arrow-back"></i> Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('<i class="bx bx-plus"></i> Crear Área', ['create-area'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center">Puestos</th>
                            <th class="text-center">Colaboradores</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($areas)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay áreas registradas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($areas as $area): ?>
                                <tr>
                                    <td><strong><?= Html::encode($area->nombre) ?></strong></td>
                                    <td><?= Html::encode($area->descripcion) ?: '-' ?></td>
                                    <td class="text-center">
                                        <?= Html::a(
                                            '<span class="badge bg-primary">' . count($area->puestos) . '</span>',
                                            ['puestos', 'area_id' => $area->id]
                                        ) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?= $area->getEmpleadosCount() ?></span>
                                    </td>
                                    <td><?= $area->orden ?></td>
                                    <td>
                                        <?php if ($area->estado === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $empleadosCount = $area->getEmpleadosCount(); ?>
                                        <?= Html::a('<i class="bx bx-show"></i>', ['puestos', 'area_id' => $area->id], [
                                            'class' => 'btn btn-sm btn-success',
                                            'title' => 'Ver puestos'
                                        ]) ?>
                                        <?= Html::a('<i class="bx bx-edit"></i>', ['update-area', 'id' => $area->id], [
                                            'class' => 'btn btn-sm btn-success',
                                            'title' => 'Editar',
                                            'onclick' => $empleadosCount > 0
                                                ? 'return confirm("Esta área tiene ' . $empleadosCount . ' ' . ($empleadosCount === 1 ? 'empleado asignado' : 'empleados asignados') . '. Los cambios que realice afectarán a ' . ($empleadosCount === 1 ? 'ese empleado' : 'esos empleados') . '. ¿Desea continuar?");'
                                                : null,
                                        ]) ?>
                                        <?= Html::a('<i class="bx bx-trash"></i>', ['delete-area', 'id' => $area->id], [
                                            'class' => 'btn btn-sm btn-success',
                                            'title' => 'Eliminar',
                                            'data' => [
                                                'confirm' => $empleadosCount > 0
                                                    ? '¿Está seguro de eliminar esta área? Tiene ' . $empleadosCount . ' ' . ($empleadosCount === 1 ? 'empleado asignado que se verá afectado' : 'empleados asignados que se verán afectados') . '. Se eliminarán todos los puestos asociados.'
                                                    : '¿Está seguro de eliminar esta área? Se eliminarán todos los puestos asociados.',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
