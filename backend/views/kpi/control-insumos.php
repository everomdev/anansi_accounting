<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $fechaDesde string */
/* @var $fechaHasta string */

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
                    
                    <!-- Filtros de fecha -->
                    <!-- <div class="row mb-3">
                        <div class="col-md-12">
                            <?php $form = ActiveForm::begin([
                                'method' => 'get',
                                'options' => ['class' => 'form-inline'],
                            ]); ?>
                            
                            <div class="form-group mr-3">
                                <?= Html::label('Fecha Desde:', null, ['class' => 'mr-2']) ?>
                                <?= Html::input('date', 'fecha_desde', $fechaDesde, [
                                    'class' => 'form-control',
                                    'id' => 'fecha_desde'
                                ]) ?>
                            </div>
                            
                            <div class="form-group mr-3">
                                <?= Html::label('Fecha Hasta:', null, ['class' => 'mr-2']) ?>
                                <?= Html::input('date', 'fecha_hasta', $fechaHasta, [
                                    'class' => 'form-control',
                                    'id' => 'fecha_hasta'
                                ]) ?>
                            </div>
                            
                            <div class="form-group">
                                <?= Html::submitButton('<i class="fas fa-filter"></i> Filtrar', [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-times"></i> Limpiar', ['control-insumos'], [
                                    'class' => 'btn btn-secondary ml-2'
                                ]) ?>
                            </div>
                            
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div> -->
                    
                    <!-- Información del período -->
                    <!-- <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Análisis Histórico Completo:</strong> Mostrando TODOS los datos históricos (sin filtro de fecha)
                        <br>
                        <small>Este reporte compara el consumo teórico basado en TODAS las ventas históricas vs. TODAS las compras registradas.</small>
                        <br>
                        <small><strong>Total de ingredientes:</strong> <?= count($dataProvider->allModels) ?></small>
                    </div> -->
                    
                    <!-- Resumen estadístico -->
                    <?php
                    $totalInsumos = count($dataProvider->allModels);
                    $sobrantes = count(array_filter($dataProvider->allModels, function($item) {
                        return $item['diferencia'] > 0;
                    }));
                    $faltantes = count(array_filter($dataProvider->allModels, function($item) {
                        return $item['diferencia'] < 0;
                    }));
                    $equilibrados = $totalInsumos - $sobrantes - $faltantes;
                    ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-boxes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Insumos</span>
                                    <span class="info-box-number"><?= $totalInsumos ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sobrantes</span>
                                    <span class="info-box-number"><?= $sobrantes ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Faltantes</span>
                                    <span class="info-box-number"><?= $faltantes ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                                    return Html::encode($model['nombre']);
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
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
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
                        <h5>Leyenda:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-circle text-info"></i> <strong>Consumido (Teórico):</strong> Cantidad que debería haberse consumido según las ventas y recetas.</li>
                            <li><i class="fas fa-circle text-secondary"></i> <strong>Consumido (Real):</strong> Consumo teórico dividido por el factor de rendimiento del insumo.</li>
                            <li><i class="fas fa-circle text-primary"></i> <strong>Comprado:</strong> Total de compras registradas en el período.</li>
                            <li><i class="fas fa-circle text-success"></i> <strong>Inventario:</strong> Stock actual registrado en el sistema.</li>
                            <li><i class="fas fa-circle text-warning"></i> <strong>Diferencia:</strong> Comprado menos Consumido Real. Positivo = Sobrante, Negativo = Faltante.</li>
                        </ul>
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
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 14px;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
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
</script>
