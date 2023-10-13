<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $families \common\models\RecipeCategory[] */

?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Rentabilidad Teórica</strong></span>
        </div>
        <div class="card-body">
            <h1 class="text-primary"><?= $business->formatter->asPercent($business->getTheoreticalYield()['totalCost'], 2) ?></h1>
        </div>
    </div>
</div>
