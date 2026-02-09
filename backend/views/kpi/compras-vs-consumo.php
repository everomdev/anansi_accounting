<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $selectedMonth int */
/* @var $selectedYear int */
/* @var $years array */

function getMonthName($month) {
    if ($month == 0) {
        return 'Todos los meses';
    }
    $months = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return isset($months[$month]) ? $months[$month] : '';
}

$this->title = 'Compras vs Consumo';
$this->params['breadcrumbs'][] = ['label' => 'KPI\'s y Control', 'url' => ['#']];
$this->params['breadcrumbs'][] = $this->title;

// Obtener filtro de estado desde GET
$estadoFiltro = Yii::$app->request->get('estado', '');
?>
<div class="kpi-compras-consumo">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-balance-scale"></i>
                        <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Filtros de mes y año -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <?php $form = ActiveForm::begin([
                                        'method' => 'get',
                                        'action' => ['kpi/compras-vs-consumo'],
                                        'options' => ['class' => 'form-inline', 'id' => 'filter-form']
                                    ]); ?>
                                    
                                    <div class="form-group me-3">
                                        <label class="me-2">Mes:</label>
                                        <?= Html::dropDownList('month', $selectedMonth, 
                                            array_merge(
                                                [0 => 'TODOS'],
                                                [
                                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                                ]
                                            ),
                                            ['class' => 'form-control']
                                        ) ?>
                                    </div>
                                    
                                    <div class="form-group me-3">
                                        <label class="me-2">Año:</label>
                                        <?= Html::dropDownList('year', $selectedYear, 
                                            $years,
                                            ['class' => 'form-control']
                                        ) ?>
                                    </div>
                                    
                                    <!-- Campo oculto para el filtro de estado -->
                                    <?= Html::hiddenInput('estado', $estadoFiltro, ['id' => 'estado-filter']) ?>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    
                                    <?php ActiveForm::end(); ?>
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
                            echo '<br><small>Este reporte compara (Comprado - Consumo Teórico) vs Inventario actual en Almacén.</small>';
                        } else {
                            echo '<strong>Período Seleccionado:</strong> ' . getMonthName($selectedMonth) . ' ' . $selectedYear;
                            echo '<br><small>Este reporte compara las compras y consumo teórico de ' . getMonthName($selectedMonth) . ' ' . $selectedYear . ' vs el inventario actual en Almacén.</small>';
                        }
                        ?>
                        <br>
                        <small><strong>Total de ingredientes:</strong> <?= count($dataProvider->allModels) ?></small>
                        <?php if ($estadoFiltro): ?>
                        <br>
                        <small><strong>Filtro aplicado:</strong> <?= ucfirst($estadoFiltro) ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Cálculo de estadísticas -->
                    <?php
                    $allModels = $dataProvider->allModels;
                    $totalInsumos = count($allModels);
                    $faltantes = count(array_filter($allModels, function($item) {
                        return $item['estado_diferencia'] === 'faltante';
                    }));
                    $sobrantes = count(array_filter($allModels, function($item) {
                        return $item['estado_diferencia'] === 'sobrante';
                    }));
                    $equilibrados = count(array_filter($allModels, function($item) {
                        return $item['estado_diferencia'] === 'equilibrado';
                    }));
                    
                    // Aplicar filtro de estado si existe
                    if ($estadoFiltro) {
                        $allModels = array_filter($allModels, function($item) use ($estadoFiltro) {
                            return $item['estado_diferencia'] === $estadoFiltro;
                        });
                        
                        // Actualizar el dataProvider con los modelos filtrados
                        $dataProvider->allModels = array_values($allModels);
                        $dataProvider->setTotalCount(count($allModels));
                    }
                    ?>
                    
                    <!-- Resumen de Estados -->
                    <div class="row mb-3">
                        <div class="col-md-12 d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Análisis: (Comprado - Consumido) vs Almacén</h5>
                            <?php if ($estadoFiltro): ?>
                            <button type="button" class="btn btn-secondary btn-sm" id="clear-estado-filter">
                                <i class="fas fa-times"></i> Limpiar filtro
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box estado-card <?= $estadoFiltro === 'faltante' ? 'active' : '' ?>" data-estado="faltante" style="cursor: pointer;">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Faltantes</span>
                                    <span class="info-box-number"><?= $faltantes ?></span>
                                    <small>Inventario menor a lo esperado</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box estado-card <?= $estadoFiltro === 'equilibrado' ? 'active' : '' ?>" data-estado="equilibrado" style="cursor: pointer;">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Equilibrados</span>
                                    <span class="info-box-number"><?= $equilibrados ?></span>
                                    <small>Inventario coincide</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box estado-card <?= $estadoFiltro === 'sobrante' ? 'active' : '' ?>" data-estado="sobrante" style="cursor: pointer;">
                                <span class="info-box-icon bg-warning"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sobrantes</span>
                                    <span class="info-box-number"><?= $sobrantes ?></span>
                                    <small>Inventario mayor a lo esperado</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de datos -->
                    <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped table-hover'],
                        'columns' => [
                            [
                                'attribute' => 'nombre',
                                'label' => 'Insumo',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $bgClass = 'bg-secondary';
                                    $icon = '<i class="fas fa-question-circle"></i>';
                                    
                                    if ($model['estado_diferencia'] === 'faltante') {
                                        $bgClass = 'bg-danger';
                                        $icon = '<i class="fas fa-exclamation-triangle"></i>';
                                    } elseif ($model['estado_diferencia'] === 'equilibrado') {
                                        $bgClass = 'bg-success';
                                        $icon = '<i class="fas fa-check-circle"></i>';
                                    } elseif ($model['estado_diferencia'] === 'sobrante') {
                                        $bgClass = 'bg-warning';
                                        $icon = '<i class="fas fa-arrow-up"></i>';
                                    }
                                    
                                    return '<div class="ingredient-name-container ' . $bgClass . '">' 
                                           . $icon . ' ' . Html::encode($model['nombre']) 
                                           . '</div>';
                                },
                                'headerOptions' => ['style' => 'width: 200px;'],
                            ],
                            [
                                'attribute' => 'comprado',
                                'label' => 'Comprado',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return number_format($model['comprado'], 2) . ' ' . $model['unidad'];
                                },
                                'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'attribute' => 'consumido_real',
                                'label' => 'Consumido (Teórico)',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return number_format($model['consumido_real'], 2) . ' ' . $model['unidad'];
                                },
                                'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'label' => 'Comprado - Consumido',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $diferencia = $model['comprado'] - $model['consumido_real'];
                                    $class = $diferencia >= 0 ? 'text-success' : 'text-danger';
                                    return '<strong class="' . $class . '">' . number_format($diferencia, 2) . ' ' . $model['unidad'] . '</strong>';
                                },
                                'headerOptions' => ['style' => 'width: 150px; text-align: center; background: #e3f2fd;'],
                                'contentOptions' => ['style' => 'text-align: center; background: #e3f2fd;'],
                            ],
                            [
                                'attribute' => 'inventario',
                                'label' => 'Almacén (Inventario)',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '<strong>' . number_format($model['inventario'], 2) . ' ' . $model['unidad'] . '</strong>';
                                },
                                'headerOptions' => ['style' => 'width: 150px; text-align: center; background: #fff3cd;'],
                                'contentOptions' => ['style' => 'text-align: center; background: #fff3cd;'],
                            ],
                            [
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $diferencia = ($model['comprado'] - $model['consumido_real']) - $model['inventario'];
                                    
                                    if (abs($diferencia) < 0.01) {
                                        return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> EQUILIBRADO</span>';
                                    } elseif ($diferencia < 0) {
                                        return '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> FALTANTE</span>';
                                    } else {
                                        return '<span class="badge badge-warning"><i class="fas fa-arrow-up"></i> SOBRANTE</span>';
                                    }
                                },
                                'headerOptions' => ['style' => 'width: 130px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'label' => 'Diferencia',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $diferencia = ($model['comprado'] - $model['consumido_real']) - $model['inventario'];
                                    $class = 'text-muted';
                                    $icon = '';
                                    
                                    if ($diferencia < -0.01) {
                                        $class = 'text-danger';
                                        $icon = '<i class="fas fa-arrow-down"></i> ';
                                    } elseif ($diferencia > 0.01) {
                                        $class = 'text-warning';
                                        $icon = '<i class="fas fa-arrow-up"></i> ';
                                    }
                                    
                                    return '<span class="' . $class . '">' . $icon . number_format(abs($diferencia), 2) . ' ' . $model['unidad'] . '</span>';
                                },
                                'headerOptions' => ['style' => 'width: 130px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                        ],
                    ]); ?>
                    </div>
                    
                    <!-- Leyenda -->
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-12">
                                <h6><i class="fas fa-info-circle"></i> Leyenda:</h6>
                                <ul class="list-unstyled">
                                    <li><span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> FALTANTE</span> - El inventario en almacén es MENOR a lo que debería quedar según compras y consumo</li>
                                    <li><span class="badge badge-success"><i class="fas fa-check-circle"></i> EQUILIBRADO</span> - El inventario coincide con lo esperado</li>
                                    <li><span class="badge badge-warning"><i class="fas fa-arrow-up"></i> SOBRANTE</span> - El inventario en almacén es MAYOR a lo que debería quedar</li>
                                </ul>
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
    transition: all 0.3s ease;
}

.info-box:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transform: translateY(-2px);
}

.info-box.active {
    border: 3px solid #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

.info-box-icon {
    border-top-left-radius: 2px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 2px;
    display: block;
    float: left;
    height: 90px;
    width: 70px;
    text-align: center;
    font-size: 35px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 10px;
    margin-left: 70px;
}

.info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 12px;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.info-box small {
    font-size: 11px;
}

.bg-info { background-color: #17a2b8!important; color: white; }
.bg-success { background-color: #28a745!important; color: white; }
.bg-danger { background-color: #dc3545!important; color: white; }
.bg-warning { background-color: #ffc107!important; color: black; }
.bg-secondary { background-color: #6c757d!important; color: white; }

.badge {
    font-size: 0.75em;
    padding: 0.375em 0.5em;
}

.badge-danger { background-color: #dc3545; color: white; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-success { background-color: #28a745; color: white; }

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
}

.ingredient-name-container.bg-danger {
    background-color: #dc3545!important;
    color: white;
    animation: pulse-critical 2s infinite;
}

.ingredient-name-container.bg-warning {
    background-color: #ffc107!important;
    color: #212529;
}

.ingredient-name-container.bg-success {
    background-color: #28a745!important;
    color: white;
}

@keyframes pulse-critical {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.estado-filter-dropdown {
    width: 100%;
    max-width: 150px;
    margin: 0 auto;
    font-size: 13px;
    padding: 5px;
}

.estado-filter-dropdown option {
    padding: 5px;
}
</style>

<?php
$this->registerJs(<<<JS
$(document).ready(function() {
    // Manejar click en las tarjetas de estado
    $('.estado-card').on('click', function() {
        var estado = $(this).data('estado');
        var currentEstado = $('#estado-filter').val();
        
        // Si ya está filtrado por este estado, limpiar el filtro
        if (currentEstado === estado) {
            $('#estado-filter').val('');
        } else {
            // Aplicar el filtro de este estado
            $('#estado-filter').val(estado);
        }
        
        // Enviar el formulario
        $('#filter-form').submit();
    });
    
    // Manejar botón de limpiar filtro
    $('#clear-estado-filter').on('click', function() {
        $('#estado-filter').val('');
        $('#filter-form').submit();
    });
});
JS
);
?>
