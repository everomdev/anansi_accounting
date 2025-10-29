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
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filtrar por Mes y Año</h5>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-4">
                        <label for="month" class="form-label">Mes</label>
                        <select name="month" id="month" class="form-select">
                            <?php 
                            $meses = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                            ];
                            for ($m = 1; $m <= 12; $m++): 
                            ?>
                                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $selectedMonth == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                    <?= $meses[$m] ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="year" class="form-label">Año</label>
                        <select name="year" id="year" class="form-select">
                            <?php for ($y = date('Y') - 2; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_sales_by_family', [
            'totalSales' => $totalSales,
            'categories' => $categories,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_eighty_percet_of_sales.php', [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_eighty_percet_of_popularity.php', [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_more_profitable.php') ?>
    </div>
    <div class="col-sm-12 col-md-6 mb-3">
        <?= $this->render('charts/_spend_the_most.php', [
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear
        ]) ?>
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


