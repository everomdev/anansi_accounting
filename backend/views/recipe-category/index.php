<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\RecipeCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Recipe Categories');
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile(Yii::getAlias("@web/js/recipe-category/index.js"), [
    'depends' => \yii\web\YiiAsset::class
]);
$this->registerCss('
    .grid-view th a {
        color: #333;
        text-decoration: none;
        position: relative;
        display: block;
    }
    .grid-view th a.asc:after {
        content: " ▲";
        font-size: 12px;
    }
    .grid-view th a.desc:after {
        content: " ▼";
        font-size: 12px;
    }
    .grid-view th a:hover {
        color: #fca311;
        text-decoration: none;
    }
        /* Estilos para encabezados fijos */
    .sticky-header-container {
        position: relative;
        overflow: auto;
        max-height: calc(90vh - 80px); /* Ajusta según tu diseño */
        margin-bottom: 10px;
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
    }
    
   
    
    /* Mejorar la apariencia de las columnas ordenables */
    .sortable-column {
        background-color: rgba(0,0,0,0.01);
    }
    
    /* Asegurar que el texto de los encabezados no se corte */
    .sticky-header-table th {
        white-space: normal;
        vertical-align: middle;
    }
    
    /* Estilos para botones de limpiar filtros */
    .filter-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .clear-filter-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #999;
        font-size: 16px;
        line-height: 1;
        padding: 0;
        width: 20px;
        height: 20px;
        cursor: pointer;
        z-index: 10;
    }
    
    .clear-filter-btn:hover {
        color: #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
        border-radius: 50%;
    }
');
?>
<div class="recipe-category-index">

    <p>
        <?php if (Yii::$app->user->can('recipe_category_create')): ?>
            <?= Html::a(Yii::t('app', 'Create Category'), ['create'], [
                'class' => 'btn btn-success',
                'id' => 'create-recipe-category'
            ]) ?>
        <?php endif; ?>
    </p>
<!-- Selector de elementos por página y filtros mejorados -->
<div class="row mb-2 align-items-center">
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
            <select id="per-page-selector" class="form-select form-select-sm" style="width: auto; max-width: 78px;">
                <?php foreach ([10, 25, 50, 100] as $value): ?>
                <option value="<?= $value ?>" <?= $dataProvider->pagination->pageSize == $value ? 'selected' : '' ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
            <span class="input-group-text bg-light"><?= Yii::t('app', 'categorías por página') ?></span>
        </div>
    </div>
</div>
    <?php Pjax::begin(['id' => 'category-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="table-responsive sticky-header-container">
    <div class="row"></div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'name',
                'filter' => '<div style="position: relative;">' . 
                    Html::textInput('RecipeCategorySearch[name]', $searchModel->name, [
                        'class' => 'form-control',
                        'placeholder' => 'Buscar por nombre...',
                        'id' => 'name-filter',
                        'style' => ' background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KPHBhdGggZD0iTSAxMC41IDEgQyA4LjAyNzI3MjcgMSA2IDMuMDI3MjcyIDYgNS41IEMgNiA2LjU1NDE0NTkgNi40MjI3OTM2IDcuNDg2MTgxIDcuMDM3MTA5NCA4LjI1NTg1OTQgTCAyLjA0Njg3NSAxMy4yNDYwOTQgTCAyLjc1MzkwNjIgMTMuOTUzMTI1IEwgNy43NDQxNDA2IDguOTYyODkwNiBDIDguNTEzODE4NSA5LjU3NzIwNjQgOS40NDU4NTQxIDEwIDEwLjUgMTAgQyAxMi45NzI3MjcgMTAgMTUgNy45NzI3MjcgMTUgNS41IEMgMTUgMy4wMjcyNzMgMTIuOTcyNzMgMSAxMC41IDEgeiBNIDEwLjUgMiBDIDEyLjQyNzI3MyAyIDE0IDMuNTcyNzI3MyAxNCA1LjUgQyAxNCA3LjQyNzI3MyAxMi40MjcyNzMgOSAxMC41IDkgQyA4LjU3MjcyNyA5IDcgNy40MjcyNzMgNyA1LjUgQyA3IDMuNTcyNzI3MyA4LjU3MjcyNyAyIDEwLjUgMiB6Ij48L3BhdGg+Cjwvc3ZnPgo=); background-repeat: no-repeat; background-position: right 5px center;'

                    ]) . 
                    Html::button('×', [
                        'class' => 'btn btn-sm',
                        'id' => 'clear-name-btn',
                        'onclick' => 'clearNameFilter()',
                        'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (empty($searchModel->name) ? 'none' : 'block') . '; z-index: 10; cursor: pointer;',
                        'title' => 'Limpiar filtro'
                    ]) . 
                    '</div>',
            ],
            [
                'attribute' => 'type',
                'value' => function ($data) {
                    return \common\models\RecipeCategory::getFormattedTypes()[$data->type];
                },
                'filter' => '<div style="position: relative;">' .
                    \yii\bootstrap5\Html::activeDropDownList($searchModel, 'type', \common\models\RecipeCategory::getFormattedTypes(), [
                        'class' => 'form-control', 
                        'prompt' => Yii::t('app', "All"),
                        'id' => 'type-filter',
                        'style' => 'padding-right: 30px;'
                    ]) .
                    Html::button('×', [
                        'class' => 'btn btn-sm',
                        'id' => 'clear-type-btn',
                        'onclick' => 'clearTypeFilter()',
                        'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (empty($searchModel->type) ? 'none' : 'block') . '; z-index: 10; cursor: pointer;',
                        'title' => 'Limpiar filtro'
                    ]) .
                    '</div>'
            ],
            [
                'attribute' => 'custom',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->custom == 1) {
                        return '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Personalizada</span>';
                    } else {
                        return '<span class="badge bg-success"><i class="fas fa-check"></i> Estándar</span>';
                    }
                },
                'filter' => [
                    0 => 'Estándar',
                    1 => 'Personalizada'
                ],
                'headerOptions' => ['style' => 'width: 150px;'],
            ],
            [
                'attribute' => 'is_food',
                'label' => 'Alimentos/Bebidas',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->type !== \common\models\RecipeCategory::TYPE_MAIN) {
                        return '<span class="text-muted">—</span>';
                    }
                    if ($model->is_food === null) {
                        return '<span class="badge bg-danger"><i class="fas fa-exclamation-circle"></i> Sin definir</span>';
                    }
                    return $model->is_food
                        ? '<span class="badge bg-primary"><i class="fas fa-utensils"></i> Alimentos</span>'
                        : '<span class="badge bg-info text-dark"><i class="fas fa-glass-martini-alt"></i> Bebidas</span>';
                },
                'headerOptions' => ['style' => 'width: 160px;'],
            ],
            [
                'attribute' => 'subrecipes_count',
                'value' => function ($data) {
                    return $data->getSubRecipes()->count();
                },
                'label' => "Subrecetas",
                'headerOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'recipes_count',
                'value' => function ($data) {
                    return $data->getRecipes()->count();
                },
                'label' => "Recetas",
                'headerOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                'visibleButtons' => [
                    'update' => function ($model, $key, $index) {
                        return Yii::$app->user->can('recipe_category_update');
                    },
                    'delete' => function ($model, $key, $index) {
                        // Solo mostrar botón delete para categorías personalizadas Y con permiso
                        return $model->custom == 1 && Yii::$app->user->can('recipe_category_delete');
                    }
                ],
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-edit'></i>",
                            $url,
                            [
                                'class' => 'update-recipe-category text-warning',
                                'data-bs-toggle' => 'tooltip',
                                'title' => 'Editar categoría de receta'
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-trash'></i>",
                            $url,
                            [
                                'class' => 'delete-recipe-category text-danger',
                                'data' => [
                                    'confirm' => '¿Está seguro de que desea eliminar esta categoría personalizada?',
                                    'method' => 'post'
                                ],
                            ]
                        );
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
    </div>
</div>

<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-form-recipe-category',
]);

echo '<div id="form-recipe-category-container"></div>';

\yii\bootstrap5\Modal::end();
?>
<?php
// Definir las funciones globales primero
$this->registerJs("
// Funciones globales para limpiar filtros
window.clearNameFilter = function() {
    document.getElementById('name-filter').value = '';
    document.getElementById('clear-name-btn').style.display = 'none';
    
    // Construir URL con filtros actuales, excluyendo el nombre
    let url = new URL(window.location);
    url.searchParams.delete('RecipeCategorySearch[name]');
    
    // Recargar la tabla
    $.pjax.reload({
        container: '#category-pjax',
        url: url.toString(),
        timeout: 10000
    }).done(function() {
        setupFilterButtons(); // Reconfigurar botones después de la recarga
    });
};

window.clearTypeFilter = function() {
    document.getElementById('type-filter').value = '';
    document.getElementById('clear-type-btn').style.display = 'none';
    
    // Construir URL con filtros actuales, excluyendo el tipo
    let url = new URL(window.location);
    url.searchParams.delete('RecipeCategorySearch[type]');
    
    // Recargar la tabla
    $.pjax.reload({
        container: '#category-pjax',
        url: url.toString(),
        timeout: 10000
    }).done(function() {
        setupFilterButtons(); // Reconfigurar botones después de la recarga
    });
};

// Handlers para los eventos
window.nameInputHandler = function() {
    const clearNameBtn = document.getElementById('clear-name-btn');
    if (clearNameBtn) {
        clearNameBtn.style.display = this.value ? 'block' : 'none';
    }
};

window.typeSelectHandler = function() {
    const clearTypeBtn = document.getElementById('clear-type-btn');
    if (clearTypeBtn) {
        clearTypeBtn.style.display = this.value ? 'block' : 'none';
    }
};

// Función global para configurar botones de filtros
window.setupFilterButtons = function() {
    const nameInput = document.getElementById('name-filter');
    const typeSelect = document.getElementById('type-filter');
    const clearNameBtn = document.getElementById('clear-name-btn');
    const clearTypeBtn = document.getElementById('clear-type-btn');
    
    if (nameInput && clearNameBtn) {
        // Mostrar/ocultar botón según el estado actual
        clearNameBtn.style.display = nameInput.value ? 'block' : 'none';
        
        // Remover listeners anteriores y agregar nuevo
        nameInput.removeEventListener('input', nameInputHandler);
        nameInput.addEventListener('input', nameInputHandler);
    }
    
    if (typeSelect && clearTypeBtn) {
        // Mostrar/ocultar botón según el estado actual
        clearTypeBtn.style.display = typeSelect.value ? 'block' : 'none';
        
        // Remover listeners anteriores y agregar nuevo
        typeSelect.removeEventListener('change', typeSelectHandler);
        typeSelect.addEventListener('change', typeSelectHandler);
    }
};

// Configurar al cargar la página
setupFilterButtons();

// Reconfigurar después de PJAX
$(document).on('pjax:complete', '#category-pjax', function() {
    setupFilterButtons();
});
", \yii\web\View::POS_HEAD);
?>
<script>
    // Función para guardar elementos por página en localStorage
    function savePerPageToStorage(pageSize) {
        localStorage.setItem('recipe-category-per-page', pageSize);
    }
    
    // Función para obtener elementos por página del localStorage
    function getPerPageFromStorage() {
        const saved = localStorage.getItem('recipe-category-per-page');
        return saved || '10'; // Default 10 si no hay valor guardado
    }
    
    // Aplicar configuración guardada al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const perPageSelector = document.getElementById('per-page-selector');
        const savedPerPage = getPerPageFromStorage();
        
        // Establecer el valor guardado en el selector
        perPageSelector.value = savedPerPage;
        
        // Si el valor actual es diferente al guardado, aplicar el guardado
        const currentPageSize = '<?= $dataProvider->pagination->pageSize ?>';
        if (currentPageSize != savedPerPage) {
            // Crear URL con el valor guardado y recargar
            let url = new URL(window.location);
            url.searchParams.set('per-page', savedPerPage);
            
            $.pjax.reload({
                container: '#category-pjax',
                url: url.toString(),
                timeout: 10000
            });
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
        
        // Recargar con el nuevo tamaño de página
        $.pjax.reload({
            container: '#category-pjax',
            url: url.toString(),
            timeout: 10000
        });
    });
</script>