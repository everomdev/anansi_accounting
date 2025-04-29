<?php

$business = \backend\helpers\RedisKeys::getBusiness();
$ingredients = \common\models\IngredientStock::find()
    ->innerJoin("movement", "movement.ingredient_id=ingredient_stock.id")
    ->select([
        'ingredient_stock.id',
        "concat(ingredient_stock.ingredient, ' (', ingredient_stock.um, ')') as ingredient",
        'sum(movement.quantity) as totalQuantity',
    ])
    ->where([
        'ingredient_stock.business_id' => $business->id
    ])
    ->groupBy(['ingredient_stock.id', 'movement.ingredient_id'])
    ->orderBy(['totalQuantity' => SORT_ASC])
    ->limit(20)
    ->asArray()
    ->all();

$data = \yii\helpers\ArrayHelper::getColumn($ingredients, 'totalQuantity');
$labels = \yii\helpers\ArrayHelper::getColumn($ingredients, 'ingredient');

$this->registerJsVar('dataMoreFrequent', $data);
$this->registerJsVar('labelsMoreFrequent', $labels);

?>

<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><?= Yii::t("app", "20 frequently purchased ingredients") ?></h5>
        <button type="button" class="btn btn-sm btn-outline-primary fullscreen-btn" data-chart-id="moreFrequent">
            <i class="fas fa-expand"></i>
        </button>
    </div>
    <div class="card-body">
        <canvas id="moreFrequent"></canvas>
    </div>
</div>
