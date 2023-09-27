<?php
/** @var $this \yii\web\View */
/** @var $categories \common\models\RecipeCategory[] */
/** @var $totalSales float */
$data = [];
$labels = [];
foreach ($categories as $category) {
    if (empty($category->getSalesPercent($totalSales))) {
        continue;
    }
    $data[] = $category->getSalesPercent($totalSales) * 100;
    $labels[] = $category->name;
}

$this->registerJsVar('dataSalesByFamily', $data);
$this->registerJsVar('labelsSalesByFamily', $labels);
?>
<div class="card">
    <div class="card-header"><h5><?= Yii::t("app", "Sales by family") ?></h5></div>
    <div class="card-body">
        <canvas id="salesByFamily"></canvas>
    </div>
</div>
