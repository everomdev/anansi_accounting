<?php
/** @var $this \yii\web\View */
/** @var $date \common\models\StandardRecipe[]|\common\models\Menu[] */
/** @var $business  \common\models\Business */

\backend\assets\ChartJsAsset::register($this);

$this->title = Yii::t('app', "Matriz BCG");
$totalCostEffectiveness = 0;
array_walk($data, function ($item) use (&$totalCostEffectiveness) {
    $totalCostEffectiveness += ($item->sales * $item->price) - ($item->sales * $item->cost);
});
if (!empty($totalSales)) {
    $popularityAxis = round(0.7 * (100 / $totalSales) * 100, 2);
    $costEffectivenessAxis = round($totalCostEffectiveness / $totalSales, 2);
} else {
    $popularityAxis = 0;
    $costEffectivenessAxis = 0;
}

$chartData = \yii\helpers\ArrayHelper::getColumn($data, function ($item) use ($totalSales, $business) {
    return [
        "x" => ($item->sales - $item->cost),
        "y" => $item->getSalesPercent($totalSales) * 100,
        "r" => 5
    ];
});

$chartData[] = [
    "x" => $popularityAxis,
    "y" => $costEffectivenessAxis,
    "r" => 10
];

$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($business->currency_code));

$this->registerJsVar('chartData', $chartData);
$this->registerJsVar('popularityAxis', $popularityAxis);
$this->registerJsVar('costEffectivenessAxis', $costEffectivenessAxis);
$this->registerJsVar('currencySymbol', $business->getFormatter()->currencyCode);
$this->registerJsVar('locale', str_replace('_', '-', $business->getFormatter()->locale));

$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/matrix.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);

?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                <?= \yii\bootstrap5\Html::dropDownList(
                    'category',
                    $type,
                    \yii\helpers\ArrayHelper::map($business->recipeCategories, 'name', 'name'),
                    [
                        'class' => 'form-control',
                        'prompt' => Yii::t('app', "All"),
                        'id' => 'category',
                        'data-url' => \yii\helpers\Url::to(['standard-recipe/matrix-bcg'])
                    ]
                ) ?>
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
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td><?= $item->name ?></td>
                        <td><?= $item->sales ?></td>
                        <td><?= $business->getFormatter()->asCurrency($item->cost) ?></td>
                        <td><?= $business->getFormatter()->asCurrency($item->price) ?></td>
                        <td><?= $business->getFormatter()->asPercent($item->getSalesPercent($totalSales)) ?></td>
                        <td><?= $business->getFormatter()->asPercent($item->costPercent) ?></td>
                        <td><?= $business->getFormatter()->asCurrency($item->price - $item->cost) ?></td>
                        <td><?= $business->getFormatter()->asCurrency($item->sales * $item->cost) ?></td>
                        <td><?= $business->getFormatter()->asCurrency($item->sales * $item->price) ?></td>
                        <td><?= $business->getFormatter()->asCurrency(($item->sales * $item->price) - ($item->sales * $item->cost)) ?></td>
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
