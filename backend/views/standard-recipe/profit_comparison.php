<?php
/** @var $this \yii\web\View */
$this->title = Yii::t('app', "Profits from improving the profitability of the menu");

$savingsMonth = ($theoreticalCost - $desiredCost) * $totalSales;
$savingsYear = $savingsMonth * 12;

// Generar opciones de meses
$months = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Generar opciones de años (desde 2020 hasta el año actual + 1)
$currentYear = (int)date('Y');
$years = [];
for ($year = 2020; $year <= $currentYear + 1; $year++) {
    $years[$year] = $year;
}
?>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><?= Yii::t('app', "Filtro por mes y año") ?></h4>
    </div>
    <div class="card-body">
        <form method="get" class="form-inline">
            <div class="form-group mr-3">
                <label for="month" class="mr-2">Mes:</label>
                <select name="month" id="month" class="form-control">
                    <?php foreach ($months as $monthNum => $monthName): ?>
                        <option value="<?= $monthNum ?>" <?= $monthNum == $selectedMonth ? 'selected' : '' ?>>
                            <?= $monthName ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="year" class="mr-2">Año:</label>
                <select name="year" id="year" class="form-control">
                    <?php foreach ($years as $yearNum => $yearName): ?>
                        <option value="<?= $yearNum ?>" <?= $yearNum == $selectedYear ? 'selected' : '' ?>>
                            <?= $yearName ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h5 class="text-info">
            <?= Yii::t('app', "Análisis para: {month} {year}", [
                'month' => $months[$selectedMonth],
                'year' => $selectedYear
            ]) ?>
        </h5>
        <hr>
        <h5><?= Yii::t('app', "Sales: <strong>{sales}</strong>", ['sales' => formatPrice($totalSales)]) ?></h5>
        <h5><?= Yii::t('app', "Current monthly A and B Cost Percentage: <strong>{theoreticalCost}</strong>", ['theoreticalCost' => formatPercentage($theoreticalCost, 2)]) ?></h5>
        <h5><?= Yii::t('app', "Desired monthly A and B Cost Percentage: <strong>{desiredCost}</strong>", ['desiredCost' => formatPercentage($desiredCost, 2)]) ?></h5>
        <h5><?= Yii::t('app', "Savings achieved (month): <strong>{savingsMonth}</strong>", ['savingsMonth' => formatPrice($savingsMonth)]) ?></h5>
        <h5><?= Yii::t('app', "Savings achieved (year): <strong>{savingsYear}</strong>", ['savingsYear' => formatPrice($savingsYear)]) ?></h5>
    </div>
</div>
