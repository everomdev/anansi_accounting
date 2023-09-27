<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */

$business = \backend\helpers\RedisKeys::getBusiness();
$total = count($data);

$this->title = Yii::t('app', "Menu Analysis")
?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                <?= \yii\bootstrap5\Html::dropDownList('', $family, \yii\helpers\ArrayHelper::map($business->recipeCategories, 'name', 'name'), [
                    'prompt' => Yii::t('app', 'All'),
                    'class' => 'form-control',
                    'data-url' => \yii\helpers\Url::to(['standard-recipe/analytics']),
                    'id' => 'family-selector'
                ]) ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <th><?= Yii::t('app', "Recipes") ?></th>
                <th><?= Yii::t('app', "Cost percent") ?></th>
                <th><?= Yii::t('app', "Position") ?></th>
                <th><?= Yii::t('app', "Popularity") ?></th>
                <th><?= Yii::t('app', "Position") ?></th>
                <th><?= Yii::t('app', "Sales") ?></th>
                <th><?= Yii::t('app', "Position") ?></th>
                </thead>
                <tbody>
                <?php foreach ($data as $item): ?>
                    <?php
                    $costPercentPosition = array_search(sprintf("%s_%s", get_class($item), $item->id), $sortByCostPercent) + 1;
                    $popularityPosition = array_search(sprintf("%s_%s", get_class($item), $item->id), $sortByPopularity) + 1;
                    $salesPosition = array_search(sprintf("%s_%s", get_class($item), $item->id), $sortBySales) + 1;
                    $colorCostPercent = "rgb(" . min([255, ($costPercentPosition * (255 / $total))]) . ", 180, 0)";
                    $colorPopularity = "rgb(" . min([255, ($popularityPosition * (255 / $total))]) . ", 180, 0)";
                    $colorSales = "rgb(" . min([255, ($salesPosition * (255 / $total))]) . ", 180, 0)";
                    ?>
                    <tr>
                        <td><?= $item->name ?></td>
                        <td><?= $business->getFormatter()->asPercent($item->costPercent) ?></td>
                        <td style="background-color: <?= $colorCostPercent ?>"><strong
                                    class="text-white"><?= $costPercentPosition ?></strong></td>
                        <td><?= $item->sales ?></td>
                        <td style="background-color: <?= $colorPopularity ?>"><strong
                                    class="text-white"><?= $popularityPosition ?></strong></td>
                        <td><?= $business->getFormatter()->asCurrency($item->price * $item->sales) ?></td>
                        <td style="background-color: <?= $colorSales ?>"><strong
                                    class="text-white"><?= $salesPosition ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(document).on('change', "#family-selector", function(event){
    event.preventDefault();
    let val = $(this).val();
    let url = $(this).data('url');
    if(val.length === 0){
        val = 'all';
    }
    url += '?family=' + val;
    window.location.href = url;
    return false;
})
JS;

$this->registerJs($js);
?>
