<?php
/** @var $this \yii\web\View */

use common\models\StandardRecipe;

$this->title = Yii::t('app', "Menu improvements");
$business = \backend\helpers\RedisKeys::getBusiness();

// Obtener los datos del análisis del menú
$menuAnalysisData = Yii::$app->session->get('menuAnalysisData', []);
$sortByCostPercent = Yii::$app->session->get('sortByCostPercent', []);
$sortByPopularity = Yii::$app->session->get('sortByPopularity', []);
$sortBySales = Yii::$app->session->get('sortBySales', []);
$paretoCategories = Yii::$app->session->get('paretoCategories', []);

// Categorizar recetas según análisis
$excellentRecipes = [];
$toxicRecipes = [];
$focusRecipes = [];
$promoteRecipes = [];

// Total de recetas para calcular percentiles
$total = count($menuAnalysisData);

if ($total > 0) {
    foreach ($menuAnalysisData as $item) {
        $itemKey = sprintf("%s_%s", get_class($item), $item->id);
        
        // Obtener posiciones en cada categoría
        $costPercentPosition = array_search($itemKey, $sortByCostPercent) + 1;
        $popularityPosition = array_search($itemKey, $sortByPopularity) + 1;
        $salesPosition = array_search($itemKey, $sortBySales) + 1;
        
        // Calcular si cada posición está en verde, amarillo o rojo
        $costPercentPercentile = $costPercentPosition / $total;
        $isCostGreen = $costPercentPercentile <= 0.2;
        $isCostYellow = $costPercentPercentile > 0.2 && $costPercentPercentile <= 0.5;
        $isCostRed = $costPercentPercentile > 0.5;
        
        $isPopularGreen = $popularityPosition <= ceil($total * 0.2);
        $isPopularYellow = $popularityPosition > ceil($total * 0.2) && $popularityPosition <= ceil($total * 0.5);
        $isPopularRed = $popularityPosition > ceil($total * 0.5);
        
        $isSalesGreen = $salesPosition <= ceil($total * 0.2);
        $isSalesYellow = $salesPosition > ceil($total * 0.2) && $salesPosition <= ceil($total * 0.5);
        $isSalesRed = $salesPosition > ceil($total * 0.5);
        
        // Categorizar las recetas según las reglas
        
        // 1. Recetas de excelencia: verde en todas las categorías
        if ($isCostGreen && $isPopularGreen && $isSalesGreen) {
            $excellentRecipes[] = $item->name;
        }
        
        // 2. Recetas tóxicas: rojo en todas las categorías
        if ($isCostRed && $isPopularRed && $isSalesRed) {
            $toxicRecipes[] = $item->name;
        }
        
        // 3. Recetas foco rojo: no rentables (rojo o amarillo en costo) pero populares y dejan buen dinero
        if (($isCostRed || $isCostYellow) && ($isPopularGreen || $isPopularYellow) && ($isSalesGreen || $isSalesYellow)) {
            $focusRecipes[] = $item->name;
        }
        
        // 4. Recetas a promocionar: rentables y populares pero no dejan tanto dinero
        if ($isCostGreen && ($isPopularGreen || $isPopularYellow) && ($isSalesRed || $isSalesYellow)) {
            $promoteRecipes[] = $item->name;
        }
    }
}

?>
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-menu-improvement']); ?>
<?php
$sum = array_sum(\yii\helpers\ArrayHelper::getColumn($data, function ($item) {
    return $item->getCostPercent(true);
}));

$countData = count($data);

$yield = $countData == 0 ? 0 : round($sum / $countData , 2);
?>
<div class="card">
    <div class="card-header">
        <h4>
            <?= Yii::t('app', "New profitability of menu: {yield}", [
                'yield' => $business->getFormatter()->asPercent($yield, 2)
            ]) ?>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <th><?= Yii::t('app', "Recipe") ?></th>
                <th><?= Yii::t('app', "Cost") ?></th>
                <th><?= Yii::t('app', "Price") ?></th>
                <th><?= Yii::t('app', "Cost percent") ?></th>
                </thead>
                <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td><?= $item->name ?></td>
                        <td><?= \yii\bootstrap5\Html::activeInput('number', $item, 'custom_cost', ['class' => 'form-control modify-custom-field', 'data-url' => get_class($item) == StandardRecipe::class ? \yii\helpers\Url::to(['standard-recipe/save-sales', 'id' => $item->id]) : \yii\helpers\Url::to(['menu/save-sales', 'id' => $item->id])]) ?></td>
                        <td><?= \yii\bootstrap5\Html::activeInput('number', $item, 'custom_price', ['class' => 'form-control modify-custom-field', 'data-url' => get_class($item) == StandardRecipe::class ? \yii\helpers\Url::to(['standard-recipe/save-sales', 'id' => $item->id]) : \yii\helpers\Url::to(['menu/save-sales', 'id' => $item->id])]) ?></td>
                        <td><?= $business->getFormatter()->asPercent($item->getCostPercent(true), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Nueva sección para las recomendaciones de mejora -->
<div class="card mt-4">
    <div class="card-header">
        <h4><?= Yii::t('app', "Recomendaciones de Mejora del Menú") ?></h4>
        <p class="text-muted small">
            <?= Yii::t('app', "Basado en los datos de análisis de su menú, hemos categorizado sus recetas y proporcionado recomendaciones específicas.") ?>
        </p>
    </div>
    <div class="card-body">
        <?php if (!$menuAnalysisData): ?>
            <div class="alert alert-info">
                <?= Yii::t('app', "Por favor, visite la página de Análisis del Menú primero para obtener recomendaciones basadas en sus datos de ventas.") ?>
                <?= \yii\bootstrap5\Html::a(
                    Yii::t('app', "Ir a Análisis del Menú"),
                    ['standard-recipe/analytics'],
                    ['class' => 'btn btn-primary btn-sm ms-3']
                ) ?>
            </div>
        <?php else: ?>
            <!-- Recetas de excelencia -->
            <div class="recommendation-section mb-4">
                <h5 class="text-success">
                    <i class="fas fa-star me-2"></i>
                    <?= Yii::t('app', "Recetas de Excelencia") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Son populares con los clientes, aportan dinero al restaurante y son rentables.") ?>
                </p>
                <?php if ($excellentRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($excellentRecipes as $recipe): ?>
                            <span class="badge bg-success me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- Recetas foco rojo -->
            <div class="recommendation-section mb-4">
                <h5 class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= Yii::t('app', "Recetas Foco Rojo") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Urgente tomar acción: hacerlas rentables o hacer un sustituto rentable por el bien de las finanzas del restaurante. Estas recetas no son rentables o tan rentables pero son populares con los clientes y dejan buen dinero.") ?>
                </p>
                <?php if ($focusRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($focusRecipes as $recipe): ?>
                            <span class="badge bg-danger me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- Recetas a promocionar -->
            <div class="recommendation-section mb-4">
                <h5 class="text-primary">
                    <i class="fas fa-bullhorn me-2"></i>
                    <?= Yii::t('app', "Recetas a Promocionar") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Estas recetas son rentables, son populares o medio populares y no dejan tanto dinero: promocionarlas e incluirlas en paquetes.") ?>
                </p>
                <?php if ($promoteRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($promoteRecipes as $recipe): ?>
                            <span class="badge bg-primary me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- Recetas tóxicas -->
            <div class="recommendation-section mb-4">
                <h5 class="text-secondary">
                    <i class="fas fa-trash-alt me-2"></i>
                    <?= Yii::t('app', "Recetas Tóxicas") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "NO son populares con los clientes, NO aportan dinero al restaurante y NO son rentables. Considere eliminarlas del menú o reformularlas completamente.") ?>
                </p>
                <?php if ($toxicRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($toxicRecipes as $recipe): ?>
                            <span class="badge bg-secondary me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- Personalizar las recomendaciones -->
            <div class="mt-4">
                <div class="alert alert-light">
                    <h5><?= Yii::t('app', "¿Necesita recomendaciones personalizadas?") ?></h5>
                    <p><?= Yii::t('app', "Estas son recomendaciones estándar basadas en el análisis de su menú. Para personalizar estas recomendaciones o para obtener información más detallada, póngase en contacto con nuestro equipo de soporte.") ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php \yii\widgets\Pjax::end(); ?>
<?php
$js = <<< JS
$(document).on('change', ".modify-custom-field", function(event){
    event.preventDefault();
    let _this = $(this);
    let value = _this.val();
    let url = _this.data('url');
    let fieldName = _this.attr('name');
    let data = {};
    data[fieldName] = value;
    $.ajax({
        url: url,
        type: 'post',
        data: data
    }).done(function(response){
        $.pjax.reload({container: "#pjax-menu-improvement"});
    })
    return false;
})
JS;
$this->registerJs($js);

// Estilos para la sección de recomendaciones
$css = <<< CSS
.recipe-list {
    line-height: 2;
}
.recommendation-section {
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}
.recommendation-section:last-child {
    border-bottom: none;
}
.badge {
    font-size: 0.9rem;
    padding: 8px 12px;
}
CSS;
$this->registerCss($css);
?>