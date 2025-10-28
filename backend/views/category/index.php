<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Familias de insumos');
$this->params['breadcrumbs'][] = $this->title;

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);

$this->registerJsFile(Yii::getAlias("@web/js/category/index.js"), [
    'depends' => \yii\web\YiiAsset::class
]);

// Asegurar que el atributo group.name es ordenable en el modelo de búsqueda
$dataProvider->sort->attributes['group.name'] = [
    'asc' => ['category_group.name' => SORT_ASC],
    'desc' => ['category_group.name' => SORT_DESC],
];
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
        color: #333;
        text-decoration: none;
    }
        /* Estilos para encabezados fijos */
    .sticky-header-container {
        position: relative;
        overflow: auto;
        max-height: calc(90vh - 80px); /* Ajusta según tu diseño */
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
');
?>
<div class="category-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Family'), ['create'], [
            'class' => 'btn btn-success',
            'id' => 'create-category'
        ]) ?>
    </p>

    <?php Pjax::begin(['id' => 'family-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="row mb-2 align-items-center">
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
            <select id="per-page-selector" class="form-select form-select-sm" style="width: auto; max-width: 78px;">
                <?php foreach ([10, 25, 50, 100] as $value): ?>
                <option value="<?= $value ?>" <?= $dataProvider->pagination->pageSize == $value ? 'selected' : '' ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
            <span class="input-group-text bg-light"><?= Yii::t('app', 'familias por página') ?></span>
        </div>
    </div>
</div>
    <div class="table-responsive sticky-header-container">
    <div class="row"></div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'group.color',
                'format' => 'raw',
                'value' => function ($data) {
                    return "<div style='width: 30px; height: 30px; background-color: {$data->group->color}; border-radius: 30px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);'></div>";
                },
                'enableSorting' => false, // Deshabilitar ordenamiento para esta columna
            ],
            [
                'attribute' => 'group.name',
                'label' => 'Grupo',
                'value' => function ($model) {
                    return $model->group->name ?? '';
                },
                'filter' => \yii\bootstrap5\Html::activeDropDownList(
                    $searchModel,
                    'group_id',
                    \yii\helpers\ArrayHelper::map(\common\models\CategoryGroup::find()->all(), 'id', 'name'),
                    ['class' => 'form-control', 'prompt' => Yii::t('app', "All")]
                ),
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'name',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'key_prefix',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => Yii::t('app', 'Insumos'),
                'format' => 'integer',
                'value' => function($model) use ($business) {
                    return \common\models\IngredientStock::find()
                        ->where(['category_id' => $model->id, 'business_id' => $business['id']])
                        ->count();
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                'visibleButtons' => [
                    'update' => function ($model) use ($business) {
                        return $business['id'] == $model->business_id;
                    },
                    'delete' => function ($model) use ($business) {
                        return $business['id'] == $model->business_id;
                    }
                ],
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-edit'></i>",
                            $url,
                            [
                                'class' => 'update-category'
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-trash'></i>",
                            $url,
                            [
                                'class' => 'delete-category',
                                'data' => [
                                    'confirm' => Yii::t('app', "Are you sure you want to delete this category?"),
                                    'method' => 'post'
                                ]
                            ]
                        );
                    }
                ],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
    </div>
</div>

<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-form-category',
]);

echo '<div id="form-category-container"></div>';

\yii\bootstrap5\Modal::end();
?>
<script>
    // Función para guardar elementos por página en localStorage
    function savePerPageToStorage(pageSize) {
        localStorage.setItem('category-per-page', pageSize);
    }
    
    // Función para obtener elementos por página del localStorage
    function getPerPageFromStorage() {
        const saved = localStorage.getItem('category-per-page');
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
                container: '#family-pjax',
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
            container: '#family-pjax',
            url: url.toString(),
            timeout: 10000
        });
    });
</script>
