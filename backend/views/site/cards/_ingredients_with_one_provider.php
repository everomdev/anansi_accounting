<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $ingredients \common\models\IngredientStock[] */

$ingredients = $business->getIngredientStocks()->all();

$ingredients = array_filter($ingredients, function($ingredient){
    return $ingredient->getProviders()->count() == 1;
});

$css = <<< CSS
.list .item:not(:last-child)::after{
    content: "|";
    margin-left: 16px;
}
CSS;
$this->registerCss($css);
?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Insumos con un proveedor</strong></span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap list">
            <?php foreach ($ingredients as $ingredient): ?>
                <div class="p-2 item"><?= $ingredient->ingredient ?></div>
            <?php endforeach; ?>
                <?php foreach ($ingredients as $ingredient): ?>
                    <div class="p-2 item"><?= $ingredient->ingredient ?></div>
                <?php endforeach; ?>
                <?php foreach ($ingredients as $ingredient): ?>
                    <div class="p-2 item"><?= $ingredient->ingredient ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
