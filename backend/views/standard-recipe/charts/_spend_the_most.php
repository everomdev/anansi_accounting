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
        // Calculate total sales for this ingredient using MonthlySales data
        $totalSales = 0;
        $ingredientRelations = \common\models\IngredientStandardRecipe::find()
            ->where(['ingredient_id' => $ingredient->id])
            ->all();
        
        foreach ($ingredientRelations as $relation) {
            $recipeSales = \common\models\MonthlySales::getTotalSales(
                \common\models\MonthlySales::TYPE_RECIPE,
                $relation->standard_recipe_id,
                $selectedYear,
                $selectedMonth
            );
            $totalSales += $recipeSales * $relation->quantity;
        }
        
        $rawData[] = ['label' => $ingredient->ingredient, 'sales' => round($totalSales * $ingredient->lastPrice, 2)];
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
<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= Yii::t("app", "20 ingredients on which you spend the most") ?></h5>
        <button type="button" class="btn btn-sm btn-outline-primary fullscreen-btn" data-chart-id="spendTheMost">
            <i class="fas fa-expand"></i>
        </button>
    </div>
    <div class="card-body">
        <canvas id="spendTheMost"></canvas>
    </div>
</div>
