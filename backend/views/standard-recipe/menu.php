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

// Registrar los estilos CSS para los botones de limpiar filtros
$this->registerCss('
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
');

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
                    'class' => 'yii\grid\DataColumn',
                    'header' => '#',
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'contentOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model, $key, $index, $column) use ($pagination) {
                        // Usar directamente el offset del objeto de paginación
                        // Esto es más confiable que calcular basado en el parámetro de página
                        return $index + 1 + $pagination->offset;
                    }
                ],
                [
                    'attribute' => 'title',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return get_class($model) == \common\models\StandardRecipe::class ? $model->title : $model->name;
                    },
                    'header' => getSortableHeader('Nombre de la receta', 'title', $sort, $order),
                    'filter' => '<div style="position: relative;">' . 
                        \yii\bootstrap5\Html::textInput('title', Yii::$app->request->get('title'), [
                            'class' => 'form-control',
                            'placeholder' => 'Buscar receta...',
                            'id' => 'title-filter',
                            'style' => 'padding-right: 30px;'
                        ]) . 
                        \yii\bootstrap5\Html::button('×', [
                            'class' => 'btn btn-sm',
                            'id' => 'clear-title-btn',
                            'onclick' => 'clearTitleFilter()',
                            'style' => 'position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; font-size: 16px; line-height: 1; padding: 0; width: 20px; height: 20px; display: ' . (empty(Yii::$app->request->get('title')) ? 'none' : 'block') . '; z-index: 10; cursor: pointer;',
                            'title' => 'Limpiar filtro'
                        ]) . 
                        '</div>'
                ],                [
                    'attribute' => 'cost',
                    'value' => function ($model) {
                        $cost = get_class($model) == \common\models\StandardRecipe::class ? $model->lastPrice : $model->cost;
                        return formatCost($cost);
                    },
                    'header' => getSortableHeader('Costo', 'cost', $sort, $order),
                    'contentOptions' => ['style' => 'text-align: center;'],
                    'headerOptions' => ['style' => 'text-align: center;'],
                ],
                [
                    'attribute' => 'costPercent',
                    'value' => function ($model) {
                        return formatPercentage($model->costPercent*100);
                    },
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
// Definir las funciones globales para limpiar filtros al principio del archivo
$this->registerJs("
// Funciones globales para limpiar filtros
window.clearTitleFilter = function() {
    const titleInput = document.getElementById('title-filter');
    const clearTitleBtn = document.getElementById('clear-title-btn');
    
    if (titleInput) {
        titleInput.value = '';
    }
    
    if (clearTitleBtn) {
        clearTitleBtn.style.display = 'none';
    }
    
    // Construir URL con filtros actuales, excluyendo el título
    let url = new URL(window.location);
    url.searchParams.delete('title');
    
    // Recargar la página con la nueva URL
    window.location.href = url.toString();
};

// Handlers para los eventos
window.titleInputHandler = function(event) {
    const clearTitleBtn = document.getElementById('clear-title-btn');
    if (clearTitleBtn) {
        // Usar event.target.value en lugar de this.value para mayor seguridad
        const value = event && event.target ? event.target.value : '';
        clearTitleBtn.style.display = value ? 'block' : 'none';
    }
};

// Función global para configurar botones de filtros
window.setupFilterButtons = function() {
    const titleInput = document.getElementById('title-filter');
    const clearTitleBtn = document.getElementById('clear-title-btn');
    
    // Verificar si cada elemento existe antes de intentar manipularlo
    if (titleInput && clearTitleBtn) {
        // Mostrar/ocultar botón según el estado actual
        clearTitleBtn.style.display = titleInput.value ? 'block' : 'none';
        
        try {
            // Remover listeners anteriores y agregar nuevo
            titleInput.removeEventListener('input', window.titleInputHandler);
            titleInput.addEventListener('input', window.titleInputHandler);
        } catch (e) {
            console.log('Error al configurar event listener para title-filter:', e);
        }
    }
};
", \yii\web\View::POS_HEAD);

// Código para inicializar los filtros al cargar la página
$this->registerJs("
// Configurar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    if(typeof window.setupFilterButtons === 'function') {
        try {
            window.setupFilterButtons();
        } catch(e) {
            console.error('Error al configurar filtros en DOMContentLoaded:', e);
        }
    }
});
", \yii\web\View::POS_READY);

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
    'title' => Yii::t('app', "Guardar menú")
]);

echo \kartik\date\DatePicker::widget([
    'id' => "menu-date",
    'name' => "menu-date",
    'options' => ['placeholder' => 'Seleccionar fecha...'],
    'pluginOptions' => [
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,  // Cierra automáticamente al seleccionar fecha
        'todayHighlight' => true,  // Resalta la fecha actual
        'clearBtn' => true,  // Muestra botón para limpiar
    ],
    'pluginEvents' => [
        // Opcional: cerrar también al presionar Enter
        'keyup' => "function(e) { if (e.keyCode === 13) { $(this).datepicker('hide'); } }"
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

$(document).on('click', '#btn-save-menu', function(event) {
    event.preventDefault();
    
    // Referencia al botón
    var button = $(this);
    
    // Deshabilitar y mostrar estado de procesamiento
    button.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    
    // Obtener datos del formulario
    let url = button.data("url");
    let date = $("#menu-date").val();
    
    // Realizar petición AJAX
    $.ajax({
        url: url,
        type: 'POST',
        data: { date: date },
        success: function(response) {
            console.log(response);
            // Manejar respuesta exitosa
            if(response.success) {
                button.prop('disabled', false)
                   .html('Guardar este menú');
                   $("#modal-save-menu").modal('hide');
                // Recargar o actualizar la vista si es necesario
                //$.pjax.reload({ container: '#tu-contenedor-pjax' });
            } else {
                toastr.error(response.message || 'Error al guardar el menú');
            }
        },
        error: function(xhr) {
            // Manejar error
            toastr.error(xhr.responseJSON?.message || 'Error en el servidor');
        },
        complete: function() {
            // Restaurar el botón siempre al finalizar
            button.prop('disabled', false)
                   .html('Guardar este menú');
        }
    });
    
    return false;
});
JS;
$this->registerJs($js);
?>

<!-- Los estilos han sido movidos al registerCss() al inicio del archivo -->
