<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\ExpenseMovement;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ExpenseMovementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Movimientos de Gastos';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(Yii::getAlias("@web/js/expense-movement/index.js"), [
    'depends' => \yii\web\YiiAsset::class,
    'position' => $this::POS_END
]);

$this->registerCss('
    .grid-view th a {
        color: #333;
        text-decoration: none;
    }
    .grid-view th a.asc:after {
        content: " ▲";
        color: #007bff;
    }
    .grid-view th a.desc:after {
        content: " ▼";
        color: #007bff;
    }
    .grid-view th a:hover {
        color: #007bff;
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
    
    .type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .type-payment { background-color: #e8f5e9; color: #2e7d32; }
    .type-adjustment { background-color: #fff3e0; color: #ef6c00; }
');
?>

<div class="expense-movement-index">
    
    <div class="d-flex flex-wrap">
        <div class="p-2">
            <?= Html::a('Nuevo Movimiento', ['create'], ['class' => 'btn btn-warning']) ?>
        </div>
        <!-- <div class="p-2">
            <?= Html::a('Exportar Movimientos', ['export'], ['class' => 'btn btn-warning']) ?>
        </div> -->
    </div>

    <!-- Selector de elementos por página -->
    <div class="row mb-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">Mostrar</span>
                <select name="per-page" id="per-page-selector" class="form-select" style="width:80px;">
                    <?php foreach ([10, 20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= ($dataProvider->pagination->pageSize == $size) ? 'selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-group-text bg-light">movimientos por página</span>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'expense-movement-pjax']); ?>

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
                    'attribute' => 'expense_name',
                    'label' => 'Gasto',
                    'value' => function($model) {
                        return $model->expense ? $model->expense->name : '-';
                    },
                    'headerOptions' => ['style' => 'width: 200px;'],
                ],
                
                [
                    'attribute' => 'type',
                    'label' => 'Tipo',
                    'format' => 'raw',
                    'value' => function($model) {
                        $cssClass = 'type-' . $model->type;
                        return '<span class="type-badge ' . $cssClass . '">' . $model->formattedType . '</span>';
                    },
                    'filter' => Html::activeDropDownList(
                        $searchModel,
                        'type',
                        ExpenseMovement::getFormattedTypes(),
                        [
                            'class' => 'form-control',
                            'prompt' => 'Todos los tipos',
                        ]
                    ),
                    'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
                
                [
                    'attribute' => 'amount',
                    'label' => 'Monto',
                    'format' => 'currency',
                    'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                ],
                
                [
                    'attribute' => 'payment_type',
                    'label' => 'Tipo de Pago',
                    'value' => function($model) {
                        return $model->formattedPaymentType;
                    },
                    'filter' => Html::activeDropDownList(
                        $searchModel,
                        'payment_type',
                        ExpenseMovement::getPaymentTypes(),
                        [
                            'class' => 'form-control',
                            'prompt' => 'Todos',
                        ]
                    ),
                    'headerOptions' => ['style' => 'width: 140px;'],
                ],
                
                [
                    'attribute' => 'invoice',
                    'label' => 'Factura',
                    'headerOptions' => ['style' => 'width: 120px;'],
                ],
                
                [
                    'attribute' => 'movement_date',
                    'label' => 'Fecha del Movimiento',
                    'format' => 'date',
                    'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
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

<!-- Modal para ver detalles -->
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-details-expense-movement'
]);
?>
<div id="container-modal-details-expense-movement"></div>
<?php \yii\bootstrap5\Modal::end(); ?>

<?php
$this->registerJs("
// Detector de cambio en elementos por página
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    let url = new URL(window.location);
    url.searchParams.set('per-page', pageSize);
    url.searchParams.delete('page');
    window.location.href = url.toString();
});

// Abrir modal con detalles
$(document).on('click', '.expense-movement-details', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    $.get(url, function(data) {
        $('#container-modal-details-expense-movement').html(data);
        $('#modal-details-expense-movement').modal('show');
    });
});
");
?>
