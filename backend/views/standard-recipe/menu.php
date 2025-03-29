<?php
/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $searchModel \common\models\StandardRecipeSearch */
/** @var $pagination \yii\data\Pagination */
/* @var $availableRecipes array|\common\models\StandardRecipe[]|\yii\db\ActiveRecord[] */
/* @var $availableCombos array|\common\models\Menu[]|\yii\db\ActiveRecord[] */
/* @var $currentSort string */
/* @var $currentOrder string */

/* @var $bundle \common\models\MenuBundle|null */
use common\models\RecipeCategory;
use yii\helpers\Html;
use yii\helpers\Url;
// Obtener los parámetros de ordenación actuales
$sort = Yii::$app->request->get('sort', '');
$order = Yii::$app->request->get('order', 'desc');

// Función para crear encabezados de columna ordenables
function getSortableHeader($label, $attribute, $currentSort, $currentOrder) {
    $icon = '';
    $nextOrder = 'desc'; // Orden predeterminado al hacer clic
    
    if ($currentSort === $attribute) {
        // Si ya está ordenado por esta columna, mostrar un icono y configurar el siguiente orden
        $icon = $currentOrder === 'asc' ? ' ↑' : ' ↓';
        $nextOrder = $currentOrder === 'asc' ? 'desc' : 'asc';
    }
    
    return Html::a($label . $icon, Url::current(['sort' => $attribute, 'order' => $nextOrder]), [
        'class' => 'sort-link ' . ($currentSort === $attribute ? 'active' : '')
    ]);
}



$this->title = empty($bundle) ? "Menú" : "Menú del " . Yii::$app->formatter->asDate($bundle->date);

$urlBulkRemove = \yii\helpers\Url::to(['menu/remove-from-menu-in-bulk']);
$this->registerJsVar('urlBulkRemove', $urlBulkRemove);
$this->registerJsFile(Yii::getAlias("@web/js/menu/index.js"), [
    'depends' => \yii\web\YiiAsset::class,
    'position' => $this::POS_END
]);

$categories = RecipeCategory::find()
    ->where([
        'business_id' => $business['id'],
    ])
    ->orderBy(['name' => SORT_ASC])
    ->all();
?>

<p class="pb-3">
    <?= \yii\bootstrap5\Html::a(Yii::t('app', "Add recipe"), '#', [
        'class' => 'btn btn-success',
        'data-bs-target' => '#modal-add-recipe',
        'data-bs-toggle' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::a(Yii::t('app', "Add combo"), '#', [
        'class' => 'btn btn-success',
        'data-bs-target' => '#modal-add-combo',
        'data-bs-toggle' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Guardar este menú', [
        'icon' => ""
    ]), '', ['class' => 'btn btn-success', 'id' => 'save-menu']) ?>
    <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Menús guardados', [
        'icon' => ""
    ]), '/menu/saved-menus', ['class' => 'btn btn-success',]) ?>

    <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Quitar Seleccionados', [
        'icon' => ""
    ]), '', ['class' => 'btn btn-danger', 'id' => 'bulk-remove']) ?>

</p>
<div class="card">
    <div class="card-body">
        <?= \yii\grid\GridView::widget([
            'id' => 'menu-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => (new \yii\base\Model()),
            'layout' => "{items}",
            'columns' => [
                [
                    'class' => \yii\grid\CheckboxColumn::class,
                    'checkboxOptions' => function ($model) {
                        return ['data-model-id' => $model->id, 'data-type' => get_class($model)];
                    },
                ],
                [
                    'class' => \yii\grid\SerialColumn::class,
                    'header' => '#'
                ],
                [
                    'attribute' => 'title',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return get_class($model) == \common\models\StandardRecipe::class ? $model->title : $model->name;
                    },
                    'header' => getSortableHeader('Nombre de la receta', 'title', $sort, $order),
                    'filter' => \yii\bootstrap5\Html::textInput('title', Yii::$app->request->get('title'), [
                        'class' => 'form-control',
                        'placeholder' => 'Buscar por título...'
                    ])
                ],
                [
                    'attribute' => 'cost',
                    'format' => 'currency',
                    'value' => function ($model) {
                        return get_class($model) == \common\models\StandardRecipe::class ? $model->lastPrice : $model->cost;
                    },
                    'header' => getSortableHeader('Costo', 'cost', $sort, $order),
                    'contentOptions' => ['style' => 'text-align: center;'],
                    'headerOptions' => ['style' => 'text-align: center;'],
                ],
                [
                    'attribute' => 'costPercent',
                    'format' => 'percent',
                    'header' => getSortableHeader('Porcentaje de costo', 'costPercent', $sort, $order),
                    'contentOptions' => ['style' => 'text-align: center;'],
                    'headerOptions' => ['style' => 'text-align: center;'],
                ],
                [
                    'label' => "Categoría",
                    'value' => function ($model) {
                        return get_class($model) == \common\models\StandardRecipe::class ? $model->type_of_recipe : $model->category->name;
                    },
                    'filter' => \yii\bootstrap5\Html::dropDownList('categoryId', $category ? $category->id : null, \yii\helpers\ArrayHelper::map($categories, 'id', 'name'), [
                        'prompt' => "Seleccione una categoría",
                        'class' => 'form-control'
                    ])
                ],
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'template' => '{remove-from-menu}',
                    'buttons' => [
                        'remove-from-menu' => function ($url, $model, $key) use ($bundle) {
                            return \yii\bootstrap5\Html::a('<i class="bx bx-x"></i>', ['menu/remove-from-menu', 'id' => $model->id, 'type' => get_class($model), 'bundle' => $bundle == null ? null : $bundle->id], [
                                'class' => '',
                                'data' => [
                                    'confirm' => get_class($model) == \common\models\StandardRecipe::class ? "¿Estás seguro de que deseas eliminar esta receta del menú?" : "¿Estás seguro de que deseas eliminar este combo del menú?",
                                ]
                            ]);
                        }
                    ]
                ]
            ],
            'sorter' => [
                'attributes' => [
                    'title',
                    'cost',
                    'costPercent',
                ],
            ],
        ]) ?>
    </div>
    <div class="card-footer">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $pagination,
            'options' => ['class' => 'pagination pagination-sm m-0 float-right'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link', 'data-pjax-scrollto' => '1'],
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
            'firstPageLabel' => "Página inicial",
            'lastPageLabel' => "Última página",
        ]) ?>
    </div>
</div>


<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-add-recipe',
    'title' => Yii::t('app', "Include recipe in menu")
]);
echo \kartik\select2\Select2::widget([
    'data' => \yii\helpers\ArrayHelper::map($availableRecipes, 'id', 'name'),
    'name' => 'recipe-id',
    'id' => 'recipe-id',
    'pluginOptions' => [
        'dropdownParent' => '#modal-add-recipe'
    ]
]);

echo \yii\bootstrap5\Html::button(Yii::t('app', "Add"), [
    'class' => 'btn btn-success mt-3',
    'id' => 'btn-add-recipe',
    'data-url' => \yii\helpers\Url::to(['standard-recipe/select-unselect-for-menu', 'type' => 'recipe'])
]);
\yii\bootstrap5\Modal::end();

// Modal add combo to menu
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-add-combo',
    'title' => Yii::t('app', "Include combo in menu")
]);
echo \kartik\select2\Select2::widget([
    'data' => \yii\helpers\ArrayHelper::map($availableCombos, 'id', 'name'),
    'name' => 'combo-id',
    'id' => 'combo-id',
    'pluginOptions' => [
        'dropdownParent' => '#modal-add-combo'
    ]
]);

echo \yii\bootstrap5\Html::button(Yii::t('app', "Add"), [
    'class' => 'btn btn-success mt-3',
    'id' => 'btn-add-combo',
    'data-url' => \yii\helpers\Url::to(['standard-recipe/select-unselect-for-menu', 'type' => 'combo'])
]);
\yii\bootstrap5\Modal::end();

\yii\bootstrap5\Modal::begin([
    'id' => 'modal-save-menu',
    'title' => Yii::t('app', "Save menu")
]);

echo \kartik\date\DatePicker::widget([
    'id' => "menu-date",
    'name' => "menu-date",
    'pluginOptions' => [
        'format' => "yyyy-mm-dd",
    ]
]);

echo \yii\bootstrap5\Html::button(Yii::t('app', "Save"), [
    'class' => 'btn btn-success mt-3',
    'id' => 'btn-save-menu',
    'data-url' => \yii\helpers\Url::to(['menu/save-menu'])
]);

\yii\bootstrap5\Modal::end();

$js = <<< JS
$(document).on('hidden.bs.modal', "#modal-add-recipe, #modal-add-combo", (event) => {
    $('body').attr('style', '');
});

$(document).on('click', '#btn-add-recipe', function(event){
    event.preventDefault();
    let _this = $(this);
    let url = _this.data("url");
    let val = $("#recipe-id").val()
    url += "&id=" + val;
    
    $.ajax({
        url,
        type: 'get'
    })
    
    return false;
});
$(document).on('click', '#btn-add-combo', function(event){
    event.preventDefault();
    let _this = $(this);
    let url = _this.data("url");
    let val = $("#combo-id").val()
    url += "&id=" + val;
    
    $.ajax({
        url,
        type: 'get'
    })
    
    return false;
});

$(document).on('click', '#save-menu', function(event){
    event.preventDefault();
    $("#modal-save-menu").modal('show');
    return false;
});

$(document).on('click', '#btn-save-menu', function(event){
    event.preventDefault();
    let _this = $(this);
    let url = _this.data("url");
    let date = $("#menu-date").val();
    
    $.ajax({
        url,
        type: 'post',
        data: {
            date
        }
    })
    
    return false;
});
JS;
$this->registerJs($js);
?>

<style>
.sort-link {
    display: block;
    color: #333;
    text-decoration: none;
}

.sort-link:hover {
    text-decoration: none;
    color: #23527c;
}

.sort-link.active {
    font-weight: bold;
}
</style>
