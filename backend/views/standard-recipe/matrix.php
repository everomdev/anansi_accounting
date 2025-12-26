<?php
/** @var $this \yii\web\View */
/** @var $data \common\models\StandardRecipe[]|\common\models\Menu[] */
/** @var $business  \common\models\Business */
/** @var $year int */

use yii\helpers\Html;
\backend\assets\ChartJsAsset::register($this);

$this->title = Yii::t('app', "Matriz BCG");
$totalCostEffectiveness = 0;
array_walk($data, function ($item) use (&$totalCostEffectiveness) {
    $totalCostEffectiveness += ($item->sales * $item->price) - ($item->sales * $item->cost);
});
if (!empty($totalSales)) {
    $popularityAxis = 50; // Eje en 50% para BCG estándar
    $costEffectivenessAxis = round($totalCostEffectiveness / $totalSales, 2);
} else {
    $popularityAxis = 0;
    $costEffectivenessAxis = 0;
}

$chartData = \yii\helpers\ArrayHelper::getColumn($data, function ($item) use ($totalSales, $business) {
    return [
        "x" => ($item->price - $item->cost),
        "y" => $item->getSalesPercent($totalSales) * 100,
        "r" => 5
    ];
});

$chartData[] = [
    "x" => $popularityAxis,
    "y" => $costEffectivenessAxis,
    "r" => 10
];

$currencySymbol = \common\helpers\NumberFormatter::getFormatConfig()['currency_symbol'];

$this->registerJsVar('chartData', $chartData);
$this->registerJsVar('popularityAxis', $popularityAxis);
$this->registerJsVar('costEffectivenessAxis', $costEffectivenessAxis);
$this->registerJsVar('currencySymbol', \common\helpers\NumberFormatter::getFormatConfig()['currency_symbol']);
$this->registerJsVar('currencyCode', \common\helpers\NumberFormatter::getFormatConfig()['currency_code']);
$this->registerJsVar('locale', 'en-US'); // You can make this dynamic if needed

$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/matrix.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);

?>
<div class="card">
    <div class="card-header">        <div class="row">
            <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                <?= \yii\bootstrap5\Html::dropDownList(
                    'category',
                    $type,
                    \yii\helpers\ArrayHelper::map($business->recipeCategoriesMain, 'name', 'name'),
                    [
                        'class' => 'form-control',
                        'prompt' => Yii::t('app', "All"),
                        'id' => 'category',
                        'data-url' => \yii\helpers\Url::to(['standard-recipe/matrix-bcg'])
                    ]
                ) ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                <?= Html::beginForm(['matrix-bcg'], 'get', ['class' => 'd-flex gap-2']) ?>
                <?= Html::hiddenInput('type', $type) ?>
                <?php
                // Generar años para el selector (5 años atrás hasta el actual)
                $currentYear = (int)date('Y');
                $years = [];
                for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
                    $years[$i] = $i;
                }
                ?>
                <?= Html::dropDownList('year',
                    $year ?? $currentYear,
                    $years,
                    ['class' => 'form-select', 'id' => 'year-select']
                ) ?>
                <?= Html::submitButton('Filtrar año', ['class' => 'btn btn-primary']) ?>
                <?= Html::endForm() ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                <button class="btn btn-success" data-bs-target="#modal-bcg"
                        data-bs-toggle="modal"><?= Yii::t('app', "Show chart") ?></button>
            </div>
        </div>


    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <th><?= Yii::t('app', "Recipe") ?></th>
                <th><?= Yii::t('app', "Sales") ?></th>
                <th><?= Yii::t('app', "Cost") ?></th>
                <th><?= Yii::t('app', "Price") ?></th>
                <th><?= Yii::t('app', "Sales %") ?></th>
                <th><?= Yii::t('app', "Cost %") ?></th>
                <th><?= Yii::t('app', "Contribution Margin") ?></th>
                <th><?= Yii::t('app', "Total Cost") ?></th>
                <th><?= Yii::t('app', "Total Sales") ?></th>
                <th><?= Yii::t('app', "Total Contribution Margin") ?></th>
                <th><?= Yii::t('app', "Cost effectiveness") ?></th>
                <th><?= Yii::t('app', "Popularity") ?></th>
                <th><?= Yii::t('app', "BCG") ?></th>
                </thead>
                <tbody>
                <?php foreach ($data as $item): ?>                    <tr>
                        <td><?= $item->name ?></td>
                        <td><?= $item->sales ?></td>
                        <td><?= formatPrice($item->cost) ?></td>
                        <td><?= formatPrice($item->price) ?></td>
                        <td><?= formatPercentage($item->getSalesPercent($totalSales) * 100) ?></td>
                        <td><?= formatPercentage($item->costPercent) ?></td>
                        <td><?= formatPrice($item->price - $item->cost) ?></td>
                        <td><?= formatPrice($item->sales * $item->cost) ?></td>
                        <td><?= formatPrice($item->sales * $item->price) ?></td>
                        <td><?= formatPrice(($item->sales * $item->price) - ($item->sales * $item->cost)) ?></td>
                        <td><?= $item->getPopularity($popularityAxis, $totalSales) ?></td>
                        <td><?= $item->getEffectiveness($costEffectivenessAxis, $totalSales) ?></td>
                        <td><?= $item->getBcg($popularityAxis, $costEffectivenessAxis, $totalSales) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-bcg',
    'title' => "Matríz BCG",
    'size' => \yii\bootstrap5\Modal::SIZE_LARGE
]);

echo '<canvas id="bcgChart"></canvas>';

\yii\bootstrap5\Modal::end();

?>
