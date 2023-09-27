<?php
/** @var $this \yii\web\View */

$this->title = "Ingredientes por proveedores"
?>

<?= \yii\bootstrap5\Html::dropDownList('provider', $provider, \yii\helpers\ArrayHelper::map($providers, 'name', 'name'), ['class' => 'form-control mb-3', 'prompt' => 'Todos', 'id' => 'provider', 'data-url' => \yii\helpers\Url::to(['provider/ingredients'])]) ?>

<?= \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => \yii\grid\SerialColumn::class],
        'ingredient.key',
        ['attribute' => 'ingredient.category.name', 'label' => "Familia"],
        'ingredient.ingredient',
        'provider'
    ]
]) ?>


<?php
$js = <<< JS
$(document).on('change', "#provider", function(event){
    event.preventDefault();
    const _this = $(this);
    let url = _this.data('url');
    let value = _this.val();
    if(value.length > 0){
        url += "?provider=" + value;
    }else{
        url += "?provider=all";
    }
    window.location.href = url;
    return false;
});
JS;
$this->registerJs($js);
?>
