<?php
/** @var $this \yii\web\View */

$this->title = "Insumos por proveedores"
?>

<?= \yii\bootstrap5\Html::dropDownList(
    'provider',
    $provider,
    \yii\helpers\ArrayHelper::map($providers, 'id', 'business_name'),
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
        'key',
        ['attribute' => 'category.name', 'label' => "Familia"],
        [
            'attribute' => 'ingredient',
            'label' => 'Insumo',
            'value' => function($model) {
                $parts = [$model->ingredient];
                if (!empty($model->brand)) {
                    $parts[] = $model->brand;
                }
                if (!empty($model->presentation)) {
                    $parts[] = $model->presentation;
                }
                return implode(' - ', $parts);
            }
        ],
        [
            'attribute' => 'providers',
            'label' => 'Proveedores',
            'format' => 'raw',
            'value' => function($model) {
                // Si ya están los objetos cargados
                if (isset($model->relatedRecords['providers']) && is_array($model->relatedRecords['providers']) && count($model->relatedRecords['providers'])) {
                    return implode(', ', array_map(function($provider) {
                        return $provider->business_name;
                    }, $model->relatedRecords['providers']));
                }
                // Si solo hay IDs, buscar los nombres (fallback)
                if (!empty($model->providers) && is_array($model->providers)) {
                    $providers = \common\models\Provider::find()->where(['id' => $model->providers])->all();
                    return implode(', ', array_map(function($provider) {
                        return $provider->business_name;
                    }, $providers));
                }
                return '(Sin proveedor)';
            },
        ],
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
