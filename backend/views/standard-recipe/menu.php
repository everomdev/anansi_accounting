<?php
/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $searchModel \common\models\StandardRecipeSearch */

$this->title = "Menú";

$urlBulkRemove = \yii\helpers\Url::to(['menu/remove-from-menu-in-bulk']);
$this->registerJsVar('urlBulkRemove', $urlBulkRemove);
$this->registerJsFile(Yii::getAlias("@web/js/menu/index.js"), [
    'depends' => \yii\web\YiiAsset::class,
    'position' => $this::POS_END
]);
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
    <?= \yii\bootstrap5\Html::a(Yii::t('app', '{icon} Quitar Seleccionados', [
        'icon' => ""
    ]), '', ['class' => 'btn btn-danger', 'id' => 'bulk-remove']) ?>
</p>
<div class="card">
    <div class="card-body">
        <?= \yii\grid\GridView::widget([
            'id' => 'menu-grid',
            'dataProvider' => $dataProvider,
            'layout' => "{items}",
            'columns' => [
                [
                    'class' => \yii\grid\CheckboxColumn::class,
                    'checkboxOptions' => function ($model) {
                        return ['data-model-id' => $model->id, 'data-type' => get_class($model)];
                    },
                ],
                ['class' => \yii\grid\SerialColumn::class],

                'title',
                [
                    'attribute' => 'cost',
                    'format' => 'currency',
                    'label' => "Costo"
                ],
                'costPercent:percent',
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'template' => '{remove-from-menu}',
                    'buttons' => [
                        'remove-from-menu' => function ($url, $model, $key) {
                            return \yii\bootstrap5\Html::a('<i class="bx bx-x"></i>', ['menu/remove-from-menu', 'id' => $model->id, 'type' => get_class($model)], [
                                'class' => '',
                                'data' => [
                                    'confirm' => get_class($model) == \common\models\StandardRecipe::class ? "¿Estás seguro de que deseas eliminar esta receta del menú?" : "¿Estás seguro de que deseas eliminar este combo del menú?",
                                ]
                            ]);
                        }
                    ]
                ]
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
JS;
$this->registerJs($js);
?>
