<?php
/** @var $this \yii\web\View */
$this->title = Yii::t('app', "Profits from improving the profitability of the menu");

$savingsMonth = ($theoreticalCost - $desiredCost) * $totalSales;
$savingsYear = $savingsMonth * 12;
?>

<div class="card">
    <div class="card-body">        <h5><?= Yii::t('app', "Sales: <strong>{sales}</strong>", ['sales' => formatPrice($totalSales)]) ?></h5>
        <h5><?= Yii::t('app', "Current monthly A and B Cost Percentage: <strong>{theoreticalCost}</strong>", ['theoreticalCost' => formatPercentage($theoreticalCost, 2)]) ?></h5>
        <h5><?= Yii::t('app', "Desired monthly A and B Cost Percentage: <strong>{desiredCost}</strong>", ['desiredCost' => formatPercentage($desiredCost, 2)]) ?></h5>
        <h5><?= Yii::t('app', "Savings achieved (month): <strong>{savingsMonth}</strong>", ['savingsMonth' => formatPrice($savingsMonth)]) ?></h5>
        <h5><?= Yii::t('app', "Savings achieved (year): <strong>{savingsYear}</strong>", ['savingsYear' => formatPrice($savingsYear)]) ?></h5>

    </div>
</div>
