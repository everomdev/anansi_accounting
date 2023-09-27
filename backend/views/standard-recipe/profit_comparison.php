<?php
/** @var $this \yii\web\View */
$this->title = Yii::t('app', "Profits from improving the profitability of the menu");

$savingsMonth = ($theoreticalCost - $desiredCost) * $totalSales;
$savingsYear = $savingsMonth * 12;
?>

<div class="card">
    <div class="card-body">

        <h5><?= Yii::t('app', "Sales: <strong>{sales}</strong>", ['sales' => $business->getFormatter()->asCurrency($totalSales)]) ?></h5>
        <h5><?= Yii::t('app', "Current monthly A and B Cost Percentage: <strong>{theoreticalCost}</strong>", ['theoreticalCost' => $business->getFormatter()->asPercent($theoreticalCost, 2)]) ?></h5>
        <h5><?= Yii::t('app', "Desired monthly A and B Cost Percentage: <strong>{desiredCost}</strong>", ['desiredCost' => $business->getFormatter()->asPercent($desiredCost, 2)]) ?></h5>
        <h5><?= Yii::t('app', "Savings achieved (month): <strong>{savingsMonth}</strong>", ['savingsMonth' => $business->getFormatter()->asCurrency($savingsMonth)]) ?></h5>
        <h5><?= Yii::t('app', "Savings achieved (year): <strong>{savingsYear}</strong>", ['savingsYear' => $business->getFormatter()->asCurrency($savingsYear)]) ?></h5>

    </div>
</div>
