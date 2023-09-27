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
    ->asArray()
    ->all();

$data = \yii\helpers\ArrayHelper::getColumn($ingredients, 'totalQuantity');
$labels = \yii\helpers\ArrayHelper::getColumn($ingredients, 'ingredient');

$this->registerJsVar('dataMoreFrequent', $data);
$this->registerJsVar('labelsMoreFrequent', $labels);

?>

<div class="card">
    <div class="card-header"><h5><?= Yii::t("app", "20 frequently purchased ingredients") ?></h5></div>
    <div class="card-body">
        <canvas id="moreFrequent"></canvas>
    </div>
</div>
