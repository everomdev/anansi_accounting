<?php
/** @var $this \yii\web\View */

$this->title = "Insumos por proveedores"
?>

<?= \yii\bootstrap5\Html::dropDownList(
    'provider',
    $provider,
    \yii\helpers\ArrayHelper::map($providers, 'business_name', 'business_name'),
    [
        'class' => 'form-control mb-3',
        'prompt' => 'Todos',
        'id' => 'provider',
        'data-url' => \yii\helpers\Url::to(['provider/ingredients'])
    ]
) ?>

<?= \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => \yii\grid\SerialColumn::class],
        'ingredient.key',
        ['attribute' => 'ingredient.category.name', 'label' => "Familia"],
        'ingredient.ingredient',
        [
            'attribute' => 'provider',
            'label' => 'Proveedor',
            'value' => function($model) {
                if (empty($model->providers)) {
                    return 'Sin proveedor';
                }
                $prov = \common\models\Provider::findOne(['business_name' => $model->provider]);
                if ($prov) {
                    return $prov->business_name ?: $prov->name;
                }
                return $model->provider;
            }
        ]
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
