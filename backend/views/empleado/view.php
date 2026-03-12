<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Empleado;

/* @var $this yii\web\View */
/* @var $model common\models\Empleado */

$this->title = $model->getNombreCompleto();
$this->params['breadcrumbs'][] = ['label' => 'Empleados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="empleado-view">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="bx bx-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="bx bx-upload"></i> Subir Documento', ['upload-documento', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="bx bx-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Está seguro de eliminar este empleado?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Semáforo del expediente -->
    <div class="card mb-4">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0 text-dark">
                <i class="bx bx-folder"></i>
                Estado del Expediente: 
                <span class="badge <?= $model->getSemaforoBadgeClass() ?>"><?= $model->getSemaforoTexto() ?></span>
                <span class="float-end"><?= $model->getPorcentajeCompletitud() ?>% Completo</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar <?= $model->getSemaforoBadgeClass() ?>" 
                     role="progressbar" 
                     style="width: <?= $model->getPorcentajeCompletitud() ?>%;" 
                     aria-valuenow="<?= $model->getPorcentajeCompletitud() ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <?= $model->getPorcentajeCompletitud() ?>%
                </div>
            </div>

            <?php if (!empty($model->getDocumentosFaltantes())): ?>
                <div class="alert alert-warning mt-3">
                    <strong>Documentos faltantes:</strong>
                    <ul class="mb-0 mt-2">
                        <?php 
                        $labels = Empleado::getTiposDocumentosLabels();
                        foreach ($model->getDocumentosFaltantes() as $documento): 
                        ?>
                            <li>
                                <?= $labels[$documento] ?? $documento ?>
                                <?php if (Empleado::esDocumentoCritico($documento)): ?>
                                    <span class="badge bg-danger">Crítico</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información del empleado -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 text-dark"><i class="bx bx-user"></i> Información Personal</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'nombre',
                            'apellido',
                            'telefono',
                            'email:email',
                            'direccion:ntext',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 text-dark"><i class="bx bx-briefcase"></i> Información Laboral</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'puesto',
                            'area',
                            [
                                'attribute' => 'fecha_ingreso',
                                'format' => ['date', 'php:d/m/Y'],
                            ],
                            [
                                'attribute' => 'fecha_salida',
                                'value' => $model->fecha_salida ? Yii::$app->formatter->asDate($model->fecha_salida, 'php:d/m/Y') : 'N/A',
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => $model->estado === Empleado::ESTADO_ACTIVO ? 'Activo' : 'Inactivo',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Documentos -->
    <div class="card mt-4">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-dark"><i class="bx bx-file"></i> Documentos (<?= count($model->documentos) ?>)</h5>
            <?= Html::a('<i class="bx bx-plus"></i> Agregar Documento', ['upload-documento', 'id' => $model->id], [
                'class' => 'btn btn-sm btn-success'
            ]) ?>
        </div>
        <div class="card-body">
            <?php if (empty($model->documentos)): ?>
                <div class="alert alert-info">
                    <i class="bx bx-info-circle"></i>
                    No hay documentos cargados para este empleado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tipo de Documento</th>
                                <th>Clasificación</th>
                                <th>Archivo</th>
                                <th>Fecha de Carga</th>
                                <th>Vencimiento</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($model->documentos as $documento): ?>
                                <tr>
                                    <td>
                                        <i class="<?= $documento->getFileIcon() ?>"></i>
                                        <?= $documento->getTipoDocumentoLabel() ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $clasificacion = Empleado::getClasificacionDocumento($documento->tipo_documento);
                                        $badgeClass = 'bg-secondary';
                                        if (strpos($clasificacion, 'Crítico') !== false) {
                                            $badgeClass = 'bg-danger';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $clasificacion ?></span>
                                    </td>
                                    <td>
                                        <?= Html::a(
                                            '<i class="bx bx-download"></i> ' . Html::encode($documento->nombre_original),
                                            $documento->getArchivoUrl(),
                                            ['target' => '_blank', 'class' => 'btn btn-sm btn-outline-primary']
                                        ) ?>
                                    </td>
                                    <td><?= Yii::$app->formatter->asDatetime($documento->fecha_carga, 'php:d/m/Y H:i') ?></td>
                                    <td>
                                        <?php if ($documento->fecha_vencimiento): ?>
                                            <?php if ($documento->estaVencido()): ?>
                                                <span class="badge bg-danger">
                                                    Vencido: <?= Yii::$app->formatter->asDate($documento->fecha_vencimiento, 'php:d/m/Y') ?>
                                                </span>
                                            <?php else: ?>
                                                <?= Yii::$app->formatter->asDate($documento->fecha_vencimiento, 'php:d/m/Y') ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Html::encode($documento->observaciones) ?? '-' ?></td>
                                    <td>
                                        <?= Html::a('<i class="bx bx-trash"></i>', ['delete-documento', 'id' => $documento->id], [
                                            'class' => 'btn btn-sm btn-danger',
                                            'data-confirm' => '¿Está seguro de eliminar este documento?',
                                            'data-method' => 'post',
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
