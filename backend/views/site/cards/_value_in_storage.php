<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $ingredients \common\models\IngredientStock[] */

$ingredients = $business->ingredientStocks;

$total = array_sum(\yii\helpers\ArrayHelper::getColumn($ingredients, 'valueInMoney'))

?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Dinero en Almacén</strong></span>
        </div>
        <div class="card-body">
            <h1 class="text-primary"><?= $business->formatter->asCurrency($total) ?></h1>
        </div>
    </div>
</div>
