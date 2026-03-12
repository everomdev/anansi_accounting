<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Empleado;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EmpleadoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $estadisticasDocumentos array */

$this->title = 'Empleados';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="empleado-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="bx bx-plus"></i> Crear Empleado', ['create'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="bx bx-bar-chart"></i> Estadísticas', ['estadisticas'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <!-- Resumen de semáforo -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-success mb-0">
                <div class="card-body text-center py-2 px-3">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bx bx-check-circle text-success" style="font-size: 2.5rem;"></i>
                        <h1 class="text-success mb-0 ms-2" style="font-weight: bold;">
                            <?php
                            $verde = Empleado::find()
                                ->where(['business_id' => $searchModel->business_id, 'estado' => Empleado::ESTADO_ACTIVO])
                                ->all();
                            $countVerde = 0;
                            foreach ($verde as $e) {
                                if ($e->getSemaforoExpediente() === Empleado::SEMAFORO_VERDE) $countVerde++;
                            }
                            echo $countVerde;
                            ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning mb-0">
                <div class="card-body text-center py-2 px-3">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bx bx-error-circle text-warning" style="font-size: 2.5rem;"></i>
                        <h1 class="text-warning mb-0 ms-2" style="font-weight: bold;">
                            <?php
                            $countAmarillo = 0;
                            foreach ($verde as $e) {
                                if ($e->getSemaforoExpediente() === Empleado::SEMAFORO_AMARILLO) $countAmarillo++;
                            }
                            echo $countAmarillo;
                            ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger mb-0">
                <div class="card-body text-center py-2 px-3">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bx bx-x-circle text-danger" style="font-size: 2.5rem;"></i>
                        <h1 class="text-danger mb-0 ms-2" style="font-weight: bold;">
                            <?php
                            $countRojo = 0;
                            foreach ($verde as $e) {
                                if ($e->getSemaforoExpediente() === Empleado::SEMAFORO_ROJO) $countRojo++;
                            }
                            echo $countRojo;
                            ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'nombre',
                'value' => function ($model) {
                    return $model->getNombreCompleto();
                },
                'label' => 'Nombre Completo',
            ],
            'puesto',
            'area',
            [
                'attribute' => 'fecha_ingreso',
                'format' => ['date', 'php:d/m/Y'],
            ],
            [
                'attribute' => 'estado',
                'filter' => [
                    Empleado::ESTADO_ACTIVO => 'Activo',
                    Empleado::ESTADO_INACTIVO => 'Inactivo',
                ],
                'value' => function ($model) {
                    return $model->estado === Empleado::ESTADO_ACTIVO 
                        ? '<span class="badge bg-success">Activo</span>' 
                        : '<span class="badge bg-secondary">Inactivo</span>';
                },
                'format' => 'raw',
            ],
            [
                'label' => 'Expediente',
                'attribute' => 'semaforo_filter',
                'filter' => [
                    Empleado::SEMAFORO_VERDE => 'Completo',
                    Empleado::SEMAFORO_AMARILLO => 'Incompleto',
                    Empleado::SEMAFORO_ROJO => 'Crítico',
                ],
                'value' => function ($model) {
                    $semaforo = $model->getSemaforoExpediente();
                    $badgeClass = $model->getSemaforoBadgeClass();
                    $texto = $model->getSemaforoTexto();
                    $porcentaje = $model->getPorcentajeCompletitud();
                    
                    return '<span class="badge ' . $badgeClass . '">' . $texto . ' (' . $porcentaje . '%)</span>';
                },
                'format' => 'raw',
            ],

            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
