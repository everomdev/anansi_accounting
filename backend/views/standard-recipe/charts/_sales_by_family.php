<?php
/** @var $this \yii\web\View */
/** @var $categories \common\models\RecipeCategory[] */
/** @var $totalSales float */
/** @var $selectedMonth string */
/** @var $selectedYear int */
$data = [];
$labels = [];
foreach ($categories as $category) {
    if (empty($category->getSalesPercent($totalSales, $selectedMonth, $selectedYear))) {
        continue;
    }
    $data[] = $category->getSalesPercent($totalSales, $selectedMonth, $selectedYear) * 100;
    $labels[] = $category->name;
}

$this->registerJsVar('dataSalesByFamily', $data);
$this->registerJsVar('labelsSalesByFamily', $labels);
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><?= Yii::t('app', 'Ventas por categoría') ?></h5>
        <button type="button" class="btn btn-sm btn-outline-primary fullscreen-btn" data-chart-id="salesByFamily">
            <i class="fas fa-expand"></i>
        </button>
    </div>
    <div class="card-body">
        <canvas id="salesByFamily"></canvas>
    </div>
</div>
