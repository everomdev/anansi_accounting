<?php
/** @var $this \yii\web\View */
/** @var float $totalSales */
/** @var $categories \common\models\RecipeCategory[] */
\backend\assets\ChartJsAsset::register($this);
$business = \backend\helpers\RedisKeys::getBusiness();
$this->title = Yii::t('app', "Charts");

$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/charts.js"), [
    'depends' => [\yii\web\YiiAsset::class],
    'position' => $this::POS_END
]);
$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($business->currency_code));
$this->registerJsVar('currencySymbol', $business->getFormatter()->currencyCode);
$this->registerJsVar('locale', str_replace('_', '-', $business->getFormatter()->locale));
?>

<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-3">
        <?= $this->render('charts/_sales_by_family', [
            'totalSales' => $totalSales,
            'categories' => $categories
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-3">
        <?= $this->render('charts/_eighty_percet_of_sales.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-3">
        <?= $this->render('charts/_eighty_percet_of_popularity.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-3">
        <?= $this->render('charts/_more_profitable.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-3">
        <?= $this->render('charts/_spend_the_most.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 mb-3">
        <?= $this->render('charts/_frequent_ingredients.php') ?>
    </div>




</div>


