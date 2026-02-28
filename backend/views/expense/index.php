<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Expense;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ExpenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Catálogo de Gastos';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .grid-view th a {
        color: #333;
        text-decoration: none;
    }
    .grid-view th a.asc:after {
        content: " ▲";
    }
    .grid-view th a.desc:after {
        content: " ▼";
    }
    .grid-view th a:hover {
        color: #fca311;
    }
        
    /* Estilos para encabezados fijos */
    .sticky-header-container {
        position: relative;
        overflow: auto;
        max-height: calc(90vh - 180px);
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    
    .sticky-header-table {
        margin-bottom: 0;
    }
    
    .sticky-header-table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        white-space: normal;
        vertical-align: middle;
    }
    
    .frequency-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .frequency-unico { background-color: #e3f2fd; color: #1565c0; }
    .frequency-diario { background-color: #f3e5f5; color: #7b1fa2; }
    .frequency-semanal { background-color: #e8f5e8; color: #2e7d32; }
    .frequency-mensual { background-color: #fff3e0; color: #ef6c00; }
    .frequency-anual { background-color: #ffebee; color: #c62828; }
');
?>

<div class="expense-index">
    
    <div class="d-flex flex-wrap">
        <div class="p-2">
            <?= Html::a('Nuevo Gasto', ['create'], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= Html::a('<i class="fas fa-chart-pie"></i> Análisis ABC', ['abc-analysis'], ['class' => 'btn btn-warning']) ?>
        </div>
        <!-- <div class="p-2">
            <?= Html::a('<i class="fas fa-file-export"></i> Exportar Gastos', ['export'], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= Html::a('<i class="fas fa-file-import"></i> Importar Gastos', '#', [
                'class' => 'btn btn-warning',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modal-import-expenses'
            ]) ?>
        </div> -->
    </div>

    <!-- Selector de elementos por página -->
    <div class="row mb-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">Mostrar</span>
                <select name="per-page" id="per-page-selector" class="form-select" style="width:80px;">
                    <?php foreach ([10, 20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= ($perPage == $size) ? 'selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-group-text bg-light">gastos por página</span>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'expense-pjax']); ?>

    <div class="table-responsive sticky-header-container">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
            'options' => ['class' => 'grid-view sticky-header-grid'],
            'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;']
                ],
                
                [
                    'attribute' => 'key',
                    'label' => 'Clave',
                    'headerOptions' => ['style' => 'width: 220px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center; font-family: monospace; font-weight: bold; font-size: 12px;'],
                ],
                
                [
                    'attribute' => 'name',
                    'label' => 'Nombre del Gasto',
                    'format' => 'raw',
                    'headerOptions' => ['style' => 'min-width: 250px; width: 25%;'],
                    'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'name', [
                        'class' => 'form-control form-control-sm',
                        'style' => 'padding-right: 30px; background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 10px center;'
                    ]),
                    'value' => function($model) {
                        $html = '<strong>' . Html::encode($model->name) . '</strong>';
                        
                        // Badge para indicar si es recurrente
                        if ($model->is_recurring) {
                            $html .= ' <span class="badge bg-info" style="font-size: 10px;"><i class="fas fa-repeat"></i> Frecuente</span>';
                        }
                        
                        if ($model->description) {
                            $html .= '<br><small class="text-muted">' . Html::encode(substr($model->description, 0, 60)) . 
                                     (strlen($model->description) > 60 ? '...' : '') . '</small>';
                        }
                        return $html;
                    },
                ],
                
                [
                    'attribute' => 'amount',
                    'label' => 'Monto',
                    'format' => 'raw',
                    'value' => function($model) {
                        if ($model->is_recurring && $model->amount) {
                            return '<span class="text-primary fw-bold">$' . number_format($model->amount, 2) . '</span>';
                        }
                        return '<span class="text-muted">-</span>';
                    },
                    'filter' => false,
                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: right;'],
                ],
                
                [
                    'attribute' => 'unit_measurement_id',
                    'label' => 'Unidad',
                    'value' => function($model) {
                        return $model->unitMeasurement ? $model->unitMeasurement->name : '-';
                    },
                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
                
                [
                    'attribute' => 'category_id',
                    'label' => 'Categoría',
                    'value' => function($model) {
                        return $model->category ? $model->category->name : '-';
                    },
                    'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
                
                [
                    'attribute' => 'frequency',
                    'label' => 'Frecuencia',
                    'format' => 'raw',
                    'value' => function($model) {
                        if (!$model->is_recurring) {
                            return '<span class="text-muted">-</span>';
                        }
                        $frequencies = $model::getFrequencyOptions();
                        $frequency = $frequencies[$model->frequency] ?? $model->frequency;
                        $cssClass = 'frequency-' . $model->frequency;
                        return '<span class="frequency-badge ' . $cssClass . '">' . $frequency . '</span>';
                    },
                    'filter' => Html::activeDropDownList(
                        $searchModel,
                        'frequency',
                        Expense::getFrequencyOptions(),
                        [
                            'class' => 'form-control',
                            'prompt' => 'Todas las frecuencias',
                            'onchange' => '$.pjax.reload({timeout: 10000});'
                        ]
                    ),
                    'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
                
                [
                    'attribute' => 'expense_date',
                    'label' => 'Fecha del Gasto',
                    'format' => 'raw',
                    'value' => function($model) {
                        if ($model->is_recurring && $model->expense_date) {
                            return Yii::$app->formatter->asDate($model->expense_date);
                        }
                        return '<span class="text-muted">-</span>';
                    },
                    'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
                
                [
                    'attribute' => 'provider_name',
                    'label' => 'Proveedor',
                    'value' => function($model) {
                        return $model->provider ? $model->provider->business_name : '-';
                    },
                    'headerOptions' => ['style' => 'width: 150px;'],
                ],
                
                [
                    'label' => 'Monto Mensual',
                    'format' => 'raw',
                    'value' => function($model) {
                        if ($model->is_recurring && $model->amount) {
                            $monthly = $model->getMonthlyAmount();
                            return '<span class="text-primary font-weight-bold">$' . number_format($monthly, 2) . '</span>';
                        }
                        return '<span class="text-muted">-</span>';
                    },
                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: right;'],
                ],
                
                [
                    'attribute' => 'is_active',
                    'label' => 'Estado',
                    'format' => 'raw',
                    'value' => function($model) {
                        if ($model->is_active) {
                            return '<span class="badge bg-success">Activo</span>';
                        } else {
                            return '<span class="badge bg-secondary">Inactivo</span>';
                        }
                    },
                    'filter' => Html::activeDropDownList(
                        $searchModel,
                        'is_active',
                        [1 => 'Activo', 0 => 'Inactivo'],
                        [
                            'class' => 'form-control',
                            'prompt' => 'Todos los estados',
                            'onchange' => '$.pjax.reload({timeout: 10000});'
                        ]
                    ),
                    'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                    'template' => '{view} {update} {delete}',
                    
                ],
            ],
        ]); ?>
    </div>

    <?php Pjax::end(); ?>
</div>

<script>
// Detector de cambio en elementos por página
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    let url = new URL(window.location);
    url.searchParams.set('per-page', pageSize);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
});
</script>
