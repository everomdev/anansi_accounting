<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ExpenseUnitMeasurementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Unidades de Medida para Gastos';
$this->params['breadcrumbs'][] = $this->title;

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
');
?>

<div class="expense-unit-measurement-index">
    
    <div class="d-flex flex-wrap">
        <div class="p-2">
            <?= Html::a('Nueva Unidad de Medida', ['create'], ['class' => 'btn btn-warning']) ?>
        </div>
    </div>

    <div class="row mb-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">Mostrar</span>
                <select name="per-page" id="per-page-selector" class="form-select" style="width:78px;">
                    <?php foreach ([20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= ($dataProvider->pagination->pageSize == $size) ? 'selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-group-text bg-light">unidades por página</span>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'expense-unit-measurement-pjax']); ?>

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
                    'attribute' => 'name',
                    'label' => 'Unidad de Medida',
                ],
                
                // [
                //     'attribute' => 'created_at',
                //     'label' => 'Fecha de Creación',
                //     'format' => 'datetime',
                //     'headerOptions' => ['style' => 'width: 180px; text-align: center;'],
                //     'contentOptions' => ['style' => 'text-align: center;'],
                // ],
                
                [
                    'class' => 'yii\grid\ActionColumn',
                    'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                    'template' => '{update} {delete}',
                    
                ],
            ],
        ]); ?>
    </div>

    <?php Pjax::end(); ?>
</div>

<?php
$this->registerJs("
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    let url = new URL(window.location);
    url.searchParams.set('per-page', pageSize);
    url.searchParams.delete('page');
    window.location.href = url.toString();
});
");
?>
