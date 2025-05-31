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
// Registrar el nuevo archivo para la funcionalidad de pantalla completa
$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/chart-fullscreen.js"), [
    'depends' => [\yii\web\YiiAsset::class, \backend\assets\ChartJsAsset::class],
    'position' => $this::POS_END
]);

// Use global number formatter configuration
$jsConfig = \common\helpers\NumberFormatter::getJsConfig();
$this->registerJsVar('currencySymbol', $jsConfig['currencySymbol']);
$this->registerJsVar('locale', 'en-US'); // You can make this dynamic if needed
?>

<div class="row">
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_sales_by_family', [
            'totalSales' => $totalSales,
            'categories' => $categories
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_eighty_percet_of_sales.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_eighty_percet_of_popularity.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_more_profitable.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_spend_the_most.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_frequent_ingredients.php') ?>
    </div>
</div>
<!-- Modal para pantalla completa -->
<div class="modal fade" id="chartFullscreenModal" tabindex="-1" aria-labelledby="chartFullscreenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chartFullscreenModalLabel"><?= Yii::t('app', 'Detalles del gráfico') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center">
                <div id="fullscreen-chart-container" class="w-100 h-100 d-flex flex-column justify-content-center">
                    <!-- El gráfico se renderizará aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Yii::t('app', 'Cerrar') ?></button>
            </div>
        </div>
    </div>
</div>


