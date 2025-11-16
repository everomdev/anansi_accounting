<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ExpenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = Yii::t('app', 'Catálogo de Gastos');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

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
    
    /* Mejorar la apariencia de las columnas ordenables */
    .sortable-column {
        cursor: pointer;
        user-select: none;
    }
    
    /* Asegurar que el texto de los encabezados no se corte */
    .sticky-header-table th {
        padding: 12px 8px;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    
    /* Estilos para filtros activos */
    .filter-active {
        background-color: #e3f2fd !important;
        border-color: #2196f3 !important;
    }
    
    /* Mejorar la apariencia de los campos de filtro */
    .grid-view .filters input[type="text"] {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 8px;
        font-size: 14px;
        transition: border-color 0.15s ease-in-out;
    }
    
    .grid-view .filters input[type="text"]:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
');
?>
<div class="expense-index">
    <div class="d-flex flex-wrap">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="ms-auto">
            <?= Html::a(Yii::t('app', 'Crear Gasto'), ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <!-- Selector de elementos por página y filtros mejorados -->
    <div class="row mb-2 align-items-center">
        <div class="col-md-4">
            <div class="d-inline-block">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
                    <select name="per-page" id="per-page-selector" class="form-select" style="width:80px;">
                        <?php foreach ([10, 25, 50, 100] as $size): ?>
                            <option value="<?= $size ?>" <?= ($perPage == $size) ? 'selected' : '' ?>><?= $size ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="input-group-text bg-light"><?= Yii::t('app', 'elementos') ?></span>
                </div>
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
                ['class' => 'yii\grid\SerialColumn'],
                
                [
                    'attribute' => 'key',
                    'label' => 'Clave',
                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center; font-family: monospace; font-weight: bold;'],
                ],
                
                [
                    'attribute' => 'name',
                    'label' => 'Nombre del Gasto',
                    'format' => 'raw',
                    'value' => function($model) {
                        $name = Html::encode($model->name);
                        if ($model->brand) {
                            $name .= '<br><small class="text-muted">' . Html::encode($model->brand) . '</small>';
                        }
                        if ($model->presentation) {
                            $name .= '<br><small class="text-info">' . Html::encode($model->presentation) . '</small>';
                        }
                        return $name;
                    },
                    'filter' => '<div style="position: relative;">' . 
                        Html::textInput('ExpenseSearch[name]', $searchModel->name, [
                            'class' => 'form-control',
                            'placeholder' => 'Buscar por nombre, marca o presentación...',
                            'id' => 'title-filter'
                        ]) . 
                        Html::button('×', [
                            'class' => 'btn btn-sm',
                            'id' => 'clear-title-btn',
                            'onclick' => 'clearTitleFilter()',
                            'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (!empty($searchModel->name) ? 'block' : 'none') . '; z-index: 10; cursor: pointer;',
                            'title' => 'Limpiar filtro'
                        ]) . 
                        '</div>',
                ],
                
                [
                    'attribute' => 'categoria',
                    'label' => 'Categoría',
                    'value' => function($model) {
                        return $model->category ? $model->category->name : 'Sin categoría';
                    },
                    'filter' => Html::activeDropDownList(
                        $searchModel,
                        'categoria',
                        \common\models\Category::find()
                            ->where([
                                'or',
                                ['business_id' => $business->id],
                                ['builtin' => 1]
                            ])
                            ->select(['name', 'id'])->indexBy('id')->column(),
                        [
                            'class' => 'form-control',
                            'prompt' => 'Todas las categorías',
                            'onchange' => 'typeSelectHandler(this)'
                        ]
                    ),
                ],
                
                [
                    'attribute' => 'um',
                    'label' => 'Unidad',
                    'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                ],
                
                [
                    'attribute' => 'quantity',
                    'label' => 'Cantidad',
                    'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                    'value' => function($model) {
                        return $model->quantity ? Yii::$app->formatter->asDecimal($model->quantity, 3) : '-';
                    },
                ],

                ['class' => 'yii\grid\ActionColumn'],
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

// Funciones globales para limpiar filtros
window.clearTitleFilter = function() {
    document.getElementById('title-filter').value = '';
    document.getElementById('clear-title-btn').style.display = 'none';
    
    // Construir URL con filtros actuales, excluyendo el título
    let url = new URL(window.location);
    url.searchParams.delete('ExpenseSearch[name]');
    
    // Recargar la tabla
    $.pjax.reload({
        timeout: 10000
    });
};

window.typeSelectHandler = function() {
    // Recargar automáticamente cuando cambie el filtro de categoría
    $.pjax.reload({
        timeout: 10000
    });
};

// Handler para mostrar/ocultar botón de limpiar filtro de título
document.addEventListener('input', function(e) {
    if (e.target.id === 'title-filter') {
        const clearBtn = document.getElementById('clear-title-btn');
        clearBtn.style.display = e.target.value ? 'block' : 'none';
    }
});
</script>
