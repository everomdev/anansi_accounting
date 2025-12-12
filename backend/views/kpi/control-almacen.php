<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Control de Almacén';
$this->params['breadcrumbs'][] = ['label' => 'KPI\'s y Control', 'url' => ['#']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="kpi-control-almacen">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-warehouse"></i>
                        <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Información del reporte -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Control de Stock en Almacén:</strong> Este reporte compara el inventario actual en almacén contra los límites configurados (Mínimos y Máximos).
                        <br>
                        <small><strong>Total de ingredientes:</strong> <?= count($dataProvider->allModels) ?></small>
                    </div>
                    
                    <!-- Cálculo de estadísticas -->
                    <?php
                    $totalInsumos = count($dataProvider->allModels);
                    $stockCritico = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'critico';
                    }));
                    $stockBajo = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'bajo';
                    }));
                    $stockNormal = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'normal';
                    }));
                    $stockAlto = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'alto';
                    }));
                    $stockExcesivo = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'excesivo';
                    }));
                    $sinConfigurar = count(array_filter($dataProvider->allModels, function($item) {
                        return isset($item['alerta_stock']) && $item['alerta_stock'] === 'sin_configurar';
                    }));
                    ?>
                    
                    <!-- Resumen de Alertas de Stock -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5><i class="fas fa-exclamation-triangle"></i> Resumen de Alertas</h5>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Crítico</span>
                                    <span class="info-box-number"><?= $stockCritico ?></span>
                                    <small>Debajo del mínimo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Bajo</span>
                                    <span class="info-box-number"><?= $stockBajo ?></span>
                                    <small>Cerca del mínimo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Normal</span>
                                    <span class="info-box-number"><?= $stockNormal ?></span>
                                    <small>Dentro del rango</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Alto</span>
                                    <span class="info-box-number"><?= $stockAlto ?></span>
                                    <small>Cerca del máximo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-arrow-circle-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Excesivo</span>
                                    <span class="info-box-number"><?= $stockExcesivo ?></span>
                                    <small>Sobre el máximo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-question-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sin Config.</span>
                                    <span class="info-box-number"><?= $sinConfigurar ?></span>
                                    <small>Sin límites</small>
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
                                    $tooltip = 'Sin configurar';
                                    
                                    if ($model['alerta_stock'] === 'critico') {
                                        $bgClass = 'bg-danger';
                                        $icon = '<i class="fas fa-exclamation-circle"></i>';
                                        $tooltip = 'Stock CRÍTICO';
                                    } elseif ($model['alerta_stock'] === 'bajo') {
                                        $bgClass = 'bg-warning';
                                        $icon = '<i class="fas fa-exclamation-triangle"></i>';
                                        $tooltip = 'Stock BAJO';
                                    } elseif ($model['alerta_stock'] === 'normal') {
                                        $bgClass = 'bg-success';
                                        $icon = '<i class="fas fa-check-circle"></i>';
                                        $tooltip = 'Stock NORMAL';
                                    } elseif ($model['alerta_stock'] === 'alto') {
                                        $bgClass = 'bg-info';
                                        $icon = '<i class="fas fa-arrow-up"></i>';
                                        $tooltip = 'Stock ALTO';
                                    } elseif ($model['alerta_stock'] === 'excesivo') {
                                        $bgClass = 'bg-primary';
                                        $icon = '<i class="fas fa-arrow-circle-up"></i>';
                                        $tooltip = 'Stock EXCESIVO';
                                    }
                                    
                                    return '<div class="ingredient-name-container ' . $bgClass . '" title="' . $tooltip . '">' 
                                           . $icon . ' ' . Html::encode($model['nombre']) 
                                           . '</div>';
                                },
                                'headerOptions' => ['style' => 'width: 250px;'],
                            ],
                            [
                                'attribute' => 'inventario',
                                'label' => 'Inventario Actual',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '<strong>' . number_format($model['inventario'], 2) . ' ' . $model['unidad'] . '</strong>';
                                },
                                'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'attribute' => 'min_stock',
                                'label' => 'Mínimo',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model['min_stock']) {
                                        return number_format($model['min_stock'], 2) . ' ' . $model['unidad'];
                                    }
                                    return '<span class="text-muted">-</span>';
                                },
                                'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'attribute' => 'max_stock',
                                'label' => 'Máximo',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model['max_stock']) {
                                        return number_format($model['max_stock'], 2) . ' ' . $model['unidad'];
                                    }
                                    return '<span class="text-muted">-</span>';
                                },
                                'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'attribute' => 'alerta_stock',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $badges = [
                                        'critico' => '<span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> CRÍTICO</span>',
                                        'bajo' => '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> BAJO</span>',
                                        'normal' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> NORMAL</span>',
                                        'alto' => '<span class="badge badge-info"><i class="fas fa-arrow-up"></i> ALTO</span>',
                                        'excesivo' => '<span class="badge badge-primary"><i class="fas fa-arrow-circle-up"></i> EXCESIVO</span>',
                                        'sin_configurar' => '<span class="badge badge-secondary"><i class="fas fa-question-circle"></i> Sin configurar</span>',
                                    ];
                                    return $badges[$model['alerta_stock']] ?? '';
                                },
                                'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'label' => 'Mensaje',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '<small>' . Html::encode($model['mensaje_alerta']) . '</small>';
                                },
                            ],
                        ],
                    ]); ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
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
    width: 70px;
    text-align: center;
    font-size: 35px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 8px;
    margin-left: 70px;
}

.info-box-text {
    text-transform: uppercase;
    font-weight: bold;
    font-size: 11px;
    line-height: 1.1;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 16px;
    line-height: 1;
}

.info-box small {
    font-size: 9px;
    line-height: 1;
}

.bg-info { background-color: #17a2b8!important; color: white; }
.bg-success { background-color: #28a745!important; color: white; }
.bg-danger { background-color: #dc3545!important; color: white; }
.bg-warning { background-color: #ffc107!important; color: black; }
.bg-secondary { background-color: #6c757d!important; color: white; }
.bg-primary { background-color: #007bff!important; color: white; }

.badge {
    font-size: 0.75em;
    padding: 0.375em 0.5em;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
    animation: pulse-danger 2s infinite;
}

.badge-warning { background-color: #ffc107; color: #212529; }
.badge-success { background-color: #28a745; color: white; }
.badge-info { background-color: #17a2b8; color: white; }
.badge-primary { background-color: #007bff; color: white; }
.badge-secondary { background-color: #6c757d; color: white; }

@keyframes pulse-danger {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

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
    cursor: help;
}

.ingredient-name-container:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.ingredient-name-container.bg-danger {
    animation: pulse-critical 2s infinite;
    border: 2px solid #fff;
}

@keyframes pulse-critical {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.ingredient-name-container.bg-warning {
    color: #212529 !important;
    font-weight: 700;
}

.ingredient-name-container.bg-secondary {
    opacity: 0.8;
}
</style>
