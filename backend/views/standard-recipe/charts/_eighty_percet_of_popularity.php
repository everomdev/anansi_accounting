<?php

use backend\helpers\RedisKeys;
use common\models\Menu;
use common\models\StandardRecipe;

$business = RedisKeys::getBusiness();

$recipes = StandardRecipe::find()
    ->where([
        'business_id' => $business->id,
        'in_menu' => true,
        'in_construction' => 0,
        'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN
    ]);
$combos = Menu::find()
    ->innerJoin('recipe_category', 'recipe_category.id=menu.category_id')
    ->where([
        'menu.business_id' => $business->id,
        'in_menu' => true,
    ]);

$recipes = $recipes->all();
$combos = $combos->all();

$rawData = array_merge($recipes, $combos);

$sortBySales = $rawData;

$totalSales = array_sum(\yii\helpers\ArrayHelper::getColumn($sortBySales, 'sales'));

usort($sortBySales, function ($itemA, $itemB) use ($totalSales) {
    return $itemB->getSalesPercent($totalSales) - $itemA->getSalesPercent($totalSales);
});

$labels = \yii\helpers\ArrayHelper::getColumn($business->recipeCategories, 'name');
$data = array_fill(0, count($labels), 0);
$sum = 0;
foreach ($sortBySales as $item) {
    $salesAmount = $item->getSalesPercent($totalSales) * 100;
    $indexOfLabel = array_search($item->category->name, $labels);
    $sum += $salesAmount;
    $data[$indexOfLabel] += $salesAmount;
    if($sum >= 80){
        break;
    }
}
$count = count($data);
for($i = 0; $i < $count; $i++){
    if(empty($data[$i])){
        unset($data[$i]);
        unset($labels[$i]);
    }
}
$data = array_values($data);
$labels = array_values($labels);
$this->registerJsVar('dataEightyPercentPopularity', $data);
$this->registerJsVar('labelsEightyPercentPopularity', $labels);

?>

<div class="card">
    <div class="card-header"><h5><?= Yii::t("app", "80% of popularity") ?></h5></div>
    <div class="card-body">
        <canvas id="eightyPercentPopularity"></canvas>
    </div>
</div>
