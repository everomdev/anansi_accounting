<?php

use common\models\Menu;
use common\models\StandardRecipe;

$business = \backend\helpers\RedisKeys::getBusiness();
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

$data = array_merge($recipes, $combos);
$sortByCostPercent = $data;

usort($sortByCostPercent, function ($itemA, $itemB) {
    return ($itemB->costPercent * 100) - ($itemA->costPercent * 100);
});

$data = array_slice($sortByCostPercent, 0, 20);

$labels = \yii\helpers\ArrayHelper::getColumn($data, 'name');
$data = \yii\helpers\ArrayHelper::getColumn($data, function($item){
    return $item->costPercent * 100;
});
$this->registerJsVar('dataMoreProfitable', $data);
$this->registerJsVar('labelsMoreProfitable', $labels);

?>

<div class="card">
    <div class="card-header"><h5><?= Yii::t("app", "20 recipes more profitable") ?></h5></div>
    <div class="card-body">
        <canvas id="moreProfitable"></canvas>
    </div>
</div>
