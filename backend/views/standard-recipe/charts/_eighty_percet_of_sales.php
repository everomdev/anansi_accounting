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

$totalSalesAmount = array_sum(\yii\helpers\ArrayHelper::getColumn($sortBySales, 'salesAmount'));

usort($sortBySales, function ($itemA, $itemB) use ($totalSalesAmount) {
    return $itemB->getSalesAmountPercent($totalSalesAmount) - $itemA->getSalesAmountPercent($totalSalesAmount);
});

$labels = \yii\helpers\ArrayHelper::getColumn($business->recipeCategories, 'name');
$data = array_fill(0, count($labels), 0);
$sum = 0;
foreach ($sortBySales as $item) {
    $salesAmount = $item->getSalesAmountPercent($totalSalesAmount) * 100;
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
$this->registerJsVar('dataEightyPercentSales', $data);
$this->registerJsVar('labelsEightyPercentSales', $labels);

?>

<div class="card">
    <div class="card-header"><h5><?= Yii::t("app", "80% of sales") ?></h5></div>
    <div class="card-body">
        <canvas id="eightyPercentSales"></canvas>
    </div>
</div>
