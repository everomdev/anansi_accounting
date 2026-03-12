<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $area common\models\AreaTrabajo */
/* @var $puestos common\models\PlantillaPuesto[] */

$this->title = 'Puestos de ' . $area->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Plantilla vs Realidad', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Áreas', 'url' => ['areas']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plantilla-puestos">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="bx bx-arrow-back"></i> Volver', ['areas'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('<i class="bx bx-plus"></i> Crear Puesto', ['create-puesto', 'area_id' => $area->id], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php if ($area->descripcion): ?>
        <div class="alert alert-info">
            <i class="bx bx-info-circle"></i>
            <?= Html::encode($area->descripcion) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($puestos)): ?>
                <div class="alert alert-warning">
                    <i class="bx bx-error"></i>
                    No hay puestos definidos para esta área.
                    <?= Html::a('Crear primer puesto', ['create-puesto', 'area_id' => $area->id], ['class' => 'alert-link']) ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Puesto</th>
                                <th>Descripción</th>
                                <th class="text-center">Mínimo</th>
                                <th class="text-center">Ideal</th>
                                <th class="text-center">Actual</th>
                                <th class="text-center">Semáforo</th>
                                <th>Salario Est.</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($puestos as $puesto): ?>
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
                                        <span class="badge <?= $puesto->getSemaforoBadgeClass() ?>">
                                            <?= $puesto->getSemaforoTexto() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $puesto->salario_estimado ? Yii::$app->formatter->asCurrency($puesto->salario_estimado) : '-' ?>
                                    </td>
                                    <td>
                                        <?php if ($puesto->estado === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= Html::a('<i class="bx bx-edit"></i>', ['update-puesto', 'id' => $puesto->id], [
                                            'class' => 'btn btn-sm btn-primary',
                                            'title' => 'Editar'
                                        ]) ?>
                                        <?= Html::a('<i class="bx bx-trash"></i>', ['delete-puesto', 'id' => $puesto->id], [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'Eliminar',
                                            'data' => [
                                                'confirm' => '¿Está seguro de eliminar este puesto?',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
