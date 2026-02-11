<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\ExpenseCategory;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ExpenseSubcategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Subcategorías de Gastos (Tipos de Gasto)';
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
    
    .inventoriable-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        background-color: #fff3cd;
        color: #856404;
    }
    
    .category-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        background-color: #e7f3ff;
        color: #0066cc;
    }
');
?>

<div class="expense-subcategory-index">
    
    <div class="alert alert-info">
        <strong><i class="fas fa-info-circle"></i> Información:</strong>
        <p class="mb-0">Las subcategorías representan los tipos específicos de gastos. Al crear un gasto, el usuario selecciona la subcategoría y automáticamente se asigna a su categoría principal correspondiente.</p>
        <p class="mb-0 mt-2">📦 Las subcategorías marcadas con este ícono requieren <strong>control de inventario</strong> (entradas y salidas físicas).</p>
    </div>

    <div class="d-flex flex-wrap">
        <div class="p-2">
            <?= Html::a('<i class="fas fa-plus"></i> Nueva Subcategoría', ['create'], ['class' => 'btn btn-warning']) ?>
        </div>
        <div class="p-2">
            <?= Html::a('<i class="fas fa-list"></i> Ver Categorías Principales', ['expense-category/index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'expense-subcategory-pjax', 'enablePushState' => true]); ?>

    <div class="row mb-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">Mostrar</span>
                <select name="per-page" id="per-page-selector" class="form-select" style="width:78px;">
                    <?php foreach ([20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>" <?= ($dataProvider->pagination->pageSize == $size) ? 'selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-group-text bg-light">subcategorías por página</span>
            </div>
        </div>
    </div>

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
                    'attribute' => 'category_name',
                    'label' => 'Categoría Principal',
                    'format' => 'raw',
                    'value' => function($model) {
                        return '<span class="category-badge">' . Html::encode($model->category->name ?? 'Sin Categoría') . '</span>';
                    },
                    'filter' => Html::activeDropDownList(
                        $searchModel,
                        'category_id',
                        \yii\helpers\ArrayHelper::map(
                            ExpenseCategory::find()
                                ->where(['business_id' => \backend\helpers\RedisKeys::getBusiness()->id])
                                ->orderBy(['sort_order' => SORT_ASC])
                                ->all(),
                            'id',
                            'name'
                        ),
                        ['prompt' => 'Todas', 'class' => 'form-control form-control-sm']
                    ),
                    'headerOptions' => ['style' => 'width: 250px;'],
                ],
                
                [
                    'attribute' => 'name',
                    'label' => 'Nombre de la Subcategoría',
                    'format' => 'raw',
                    'value' => function($model) {
                        $html = Html::encode($model->name);
                        if ($model->is_inventoriable) {
                            $html .= ' <span class="inventoriable-badge">📦 Inventariable</span>';
                        }
                        return $html;
                    },
                ],
                
                [
                    'attribute' => 'description',
                    'label' => 'Descripción',
                    'format' => 'ntext',
                    'value' => function($model) {
                        return $model->description ?: '-';
                    },
                ],
                
                [
                    'attribute' => 'sort_order',
                    'label' => 'Orden',
                    'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
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
// Función para guardar elementos por página en localStorage
function savePerPageToStorage(pageSize) {
    localStorage.setItem('expense-subcategory-per-page', pageSize);
}

// Función para obtener elementos por página del localStorage
function getPerPageFromStorage() {
    const saved = localStorage.getItem('expense-subcategory-per-page');
    return saved || '20'; // Default 20 si no hay valor guardado
}

// Aplicar configuración guardada al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelector = document.getElementById('per-page-selector');
    const savedPerPage = getPerPageFromStorage();
    
    // Establecer el valor guardado en el selector
    if (perPageSelector) {
        perPageSelector.value = savedPerPage;
    }
});

// Detector de cambio en elementos por página
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    
    // Guardar en localStorage
    savePerPageToStorage(pageSize);
    
    // Crear URL con nuevo tamaño de página
    let url = new URL(window.location);
    url.searchParams.set('per-page', pageSize);
    url.searchParams.delete('page'); // Resetear a página 1
    
    // Recargar con el nuevo tamaño de página
    window.location.href = url.toString();
});
</script>
