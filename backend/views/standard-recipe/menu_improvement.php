<?php
/** @var $this \yii\web\View */

use common\models\StandardRecipe;

$this->title = Yii::t('app', "Menu improvements");
$business = \backend\helpers\RedisKeys::getBusiness();


?>
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-menu-improvement']); ?>
<?php
$sum = array_sum(\yii\helpers\ArrayHelper::getColumn($data, function ($item) {
    return $item->getCostPercent(true);
}));

$yield = round($sum / count($data), 2);
?>
<div class="card">
    <div class="card-header">
        <h4>
            <?= Yii::t('app', "New profitability of menu: {yield}", [
                'yield' => $business->getFormatter()->asPercent($yield, 2)
            ]) ?>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <th><?= Yii::t('app', "Recipe") ?></th>
                <th><?= Yii::t('app', "Cost") ?></th>
                <th><?= Yii::t('app', "Price") ?></th>
                <th><?= Yii::t('app', "Cost percent") ?></th>
                </thead>
                <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td><?= $item->name ?></td>
                        <td><?= \yii\bootstrap5\Html::activeInput('number', $item, 'custom_cost', ['class' => 'form-control modify-custom-field', 'data-url' => get_class($item) == StandardRecipe::class ? \yii\helpers\Url::to(['standard-recipe/save-sales', 'id' => $item->id]) : \yii\helpers\Url::to(['menu/save-sales', 'id' => $item->id])]) ?></td>
                        <td><?= \yii\bootstrap5\Html::activeInput('number', $item, 'custom_price', ['class' => 'form-control modify-custom-field', 'data-url' => get_class($item) == StandardRecipe::class ? \yii\helpers\Url::to(['standard-recipe/save-sales', 'id' => $item->id]) : \yii\helpers\Url::to(['menu/save-sales', 'id' => $item->id])]) ?></td>
                        <td><?= $business->getFormatter()->asPercent($item->getCostPercent(true), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php \yii\widgets\Pjax::end(); ?>
<?php
$js = <<< JS
$(document).on('change', ".modify-custom-field", function(event){
    event.preventDefault();
    let _this = $(this);
    let value = _this.val();
    let url = _this.data('url');
    let fieldName = _this.attr('name');
    let data = {};
    data[fieldName] = value;
    $.ajax({
        url: url,
        type: 'post',
        data: data
    }).done(function(response){
        $.pjax.reload({container: "#pjax-menu-improvement"});
    })
    return false;
})
JS;
$this->registerJs($js);
?>
