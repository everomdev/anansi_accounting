<?php
/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = Yii::t('app', "Sales");

?>
<?php \yii\widgets\Pjax::begin([
    'id' => 'pjax-sales'
]) ?>

<?= \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => \yii\grid\SerialColumn::class],
        'title',
        [
            'attribute' => 'cost',
            'format' => 'currency',
            'label' => "Costo"
        ],
        'costPercent:percent',
        [
            'label' => Yii::t('app', "Sales"),
            'format' => 'raw',
            'value' => function ($data) {
                $class = get_class($data);
                return \yii\bootstrap5\Html::activeInput('number', $data, 'sales', [
                    'class' => 'form-control sales-input',
                    'data-url' => $class == \common\models\StandardRecipe::class
                        ? \yii\helpers\Url::to(['standard-recipe/save-sales', 'id' => $data->id])
                        : \yii\helpers\Url::to(['menu/save-sales', 'id' => $data->id])
                ]);
            },
        ]
    ]
]) ?>
<?php \yii\widgets\Pjax::end(); ?>
<?php
$js = <<< JS
$(document).on('change', '.sales-input', function(event){
    event.preventDefault();
    let _this = $(this);
    let value = _this.val();
    let url = _this.data('url');
    let inputName = _this.attr('name');
    let data = {};
    data[inputName] = value;
    $.ajax({
        data,
        url,
        type: 'post'
    }).done(function(response){
        $.pjax.reload({container: "#pjax-sales"});
    })
    
    return false;
})
JS;
$this->registerJs($js);
?>
