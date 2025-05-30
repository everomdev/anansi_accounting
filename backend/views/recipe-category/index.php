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
        color: #333;
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
');
?>
<div class="recipe-category-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Category'), ['create'], [
            'class' => 'btn btn-success',
            'id' => 'create-recipe-category'
        ]) ?>
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

            'name',
            [
                'attribute' => 'type',
                'value' => function ($data) {
                    return \common\models\RecipeCategory::getFormattedTypes()[$data->type];
                },
                'filter' => \yii\bootstrap5\Html::activeDropDownList($searchModel, 'type', \common\models\RecipeCategory::getFormattedTypes(), ['class' => 'form-control', 'prompt' => Yii::t('app', "All")])
            ],
            [
                'value' => function ($data) {
                    return $data->getRecipes($data->type)->count();
                },
                'label' => "Recetas"
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}",
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-edit'></i>",
                            $url,
                            [
                                'class' => 'update-recipe-category text-warning'
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            "<i class='bx bx-trash'></i>",
                            $url,
                            [
                                'class' => 'delete-recipe-category text-warning',
                                'data' => [
                                    'confirm' => Yii::t('app', "Are you sure you want to delete this category?"),
                                    'method' => 'post'

                                ]
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
<script>
    // Detector de cambio en elementos por página
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    
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