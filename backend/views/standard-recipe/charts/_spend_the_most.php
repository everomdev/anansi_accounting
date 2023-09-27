<?php
/** @var $this \yii\web\View */
/** @var $categories \common\models\RecipeCategory[] */
/** @var $totalSales float */
$business = \backend\helpers\RedisKeys::getBusiness();

$ingredients = \common\models\IngredientStock::find()
    ->where(['business_id' => $business->id])
    ->all();

$rawData = [];
foreach ($ingredients as $ingredient) {
    $recipes = \common\models\StandardRecipe::find()
        ->select(['sum(sales*quantity) as totalSales'])
        ->innerJoin('ingredient_standard_recipe isp', 'isp.standard_recipe_id=standard_recipe.id')
        ->where(['isp.ingredient_id' => $ingredient->id])
        ->asArray(true)
        ->one();
    $rawData[] = ['label' => $ingredient->ingredient, 'sales' => round($recipes['totalSales'] * $ingredient->lastPrice, 2)];
}

usort($rawData, function($a, $b){
    return $b['sales'] - $a['sales'];
});
$data = [];
$labels = [];
foreach ($rawData as $rawDatum){
    $data[] = $rawDatum['sales'];
    $labels[] = $rawDatum['label'];
}

$this->registerJsVar('dataSpendTheMost', $data);
$this->registerJsVar('labelsSpendTheMost', $labels);
?>
<div class="card">
    <div class="card-header"><h5><?= Yii::t("app", "20 ingredients on which you spend the most") ?></h5></div>
    <div class="card-body">
        <canvas id="spendTheMost"></canvas>
    </div>
</div>
