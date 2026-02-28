<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ExpenseCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Categorías de Gastos';
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

<div class="expense-category-index">
    
    <div class="d-flex flex-wrap">
        <div class="p-2">
            <?= Html::a('Nueva Categoría', ['create'], ['class' => 'btn btn-warning']) ?>
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
                <span class="input-group-text bg-light">categorías por página</span>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'expense-category-pjax']); ?>

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
                    'label' => 'Nombre de la Categoría',
                    'filter' => \yii\helpers\Html::activeTextInput($searchModel, 'name', [
                        'class' => 'form-control form-control-sm',
                        'style' => 'padding-right: 30px; background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 10px center;'
                    ]),
                ],
                
                [
                    'attribute' => 'description',
                    'label' => 'Descripción',
                    'format' => 'ntext',
                ],
                
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Acciones',
                    'template' => '{view} {update} {delete}',
                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                    
                ],
            ],
        ]); ?>
    </div>

    <?php Pjax::end(); ?>

</div>

<script>
document.getElementById('per-page-selector').addEventListener('change', function() {
    var url = new URL(window.location);
    url.searchParams.set('per-page', this.value);
    window.location = url.toString();
});
</script>
