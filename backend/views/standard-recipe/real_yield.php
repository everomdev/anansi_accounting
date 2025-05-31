<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $data array */
/* @var $totalPcr float */
/* @var $totalSales float */
/* @var $month int */
/* @var $year int */
/* @var $recipesByType array */

$business = \backend\helpers\RedisKeys::getBusiness();
$this->title = Yii::t('app', "Real Yield");
$this->params['breadcrumbs'][] = $this->title;
$emptyMessage = Yii::t('app', "Select some recipe to know the real yield");
$message = Yii::t('app', "The real yield is: ");
$messageFood = Yii::t('app', "La rentabilidad real de los alimentos es: ");
$messageNonFood = Yii::t('app', "La rentabilidad real de las bebidas es: ");

// Configurar años para el selector
$currentYear = (int)date('Y');
$years = [];
for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
    $years[$i] = $i;
}
$selectedYear = Yii::$app->request->get('year', $year ?? $currentYear);
$selectedMonth = Yii::$app->request->get('month', $month ?? (int)date('n'));

// Opciones de meses
$months = [
    '1' => 'Enero',
    '2' => 'Febrero', 
    '3' => 'Marzo',
    '4' => 'Abril',
    '5' => 'Mayo',
    '6' => 'Junio',
    '7' => 'Julio',
    '8' => 'Agosto',
    '9' => 'Septiembre',
    '10' => 'Octubre',
    '11' => 'Noviembre',
    '12' => 'Diciembre',
];
?>

<div class="standard-recipe-index">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><?= Yii::t('app', 'Filtrar por mes y año') ?></h3>
            <div class="d-flex gap-2">
                <?= Html::beginForm(['real-yield'], 'get') ?>
                <div class="d-flex align-items-center gap-2">
                    <?= Html::dropDownList('month',
                        $selectedMonth,
                        $months,
                        ['class' => 'form-select', 'id' => 'month-select']
                    ) ?>
                    <?= Html::dropDownList('year',
                        $selectedYear,
                        $years,
                        ['class' => 'form-select', 'id' => 'year-select']
                    ) ?>
                    <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-0 text-muted">
                <i class="fas fa-calendar-alt me-2"></i>
                <?= Yii::t('app', 'Mostrando datos de {month} {year}', [
                    'month' => $months[$month],
                    'year' => $year
                ]) ?>
                <span class="ms-3">
                    <i class="fas fa-chart-bar me-2"></i>                    <?= Yii::t('app', 'Total de ventas: {sales}', ['sales' => formatPrice($totalSales)]) ?>
                </span>
            </p>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="alert alert-warning" id="theoretical-yield-message"><?= sprintf("%s %s", $message, formatPercentage($totalPcr*100)) ?></h4>
        </div>
    </div>

    <div class="col mb-4">
        <div class="row-md-6">
            <?php if (isset($recipesByType) && isset($recipesByType['food']) && isset($recipesByType['food']['pcr'])): ?>
                <div class="alert alert-info" id="food-yield-message">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-utensils me-2"></i>
                        <span>                            
                            <?= sprintf("%s %s", $messageFood, formatPercentage($recipesByType['food']['pcr']*100)) ?>
                            <?php if (isset($recipesByType['food']['count'])): ?>
                                <small class="ms-2">(<?= Yii::t('app', '{n, plural, =1{# receta} other{# recetas}}', ['n' => $recipesByType['food']['count']]) ?>)</small>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row-md-6">
            <?php if (isset($recipesByType) && isset($recipesByType['nonFood']) && isset($recipesByType['nonFood']['pcr'])): ?>
                <div class="alert alert-info" id="beverage-yield-message">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-glass-martini-alt me-2"></i>
                        <span>
                            <?= sprintf("%s %s", $messageNonFood, formatPercentage($recipesByType['nonFood']['pcr']*100)) ?>
                            <?php if (isset($recipesByType['nonFood']['count'])): ?>
                                <small class="ms-2">(<?= Yii::t('app', '{n, plural, =1{# receta} other{# recetas}}', ['n' => $recipesByType['nonFood']['count']]) ?>)</small>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?= \yii\bootstrap5\Html::textInput('search-box', null, ['class' => 'form-control', 'placeholder' => 'Buscar']) ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <th><?= Yii::t('app', "Name") ?></th>
                    <th><?= Yii::t('app', "Cost") ?></th>
                    <th><?= Yii::t('app', "Price") ?></th>
                    <th><?= Yii::t('app', "Cost percent") ?></th>
                    <th><?= Yii::t('app', "Sales") ?></th>
                    <th><?= Yii::t('app', "% Sales") ?></th>
                    <th><?= Yii::t('app', "Tipo") ?></th>
                    </thead>
                    <tbody>                    
                        <?php foreach ($data as $category): ?>
                        <?php
                        // Calcular CPR de la categoría usando los datos actuales con ventas históricas
                        $categoryTotalPcr = 0;
                        foreach ($category['recipes'] as $recipe) {
                            $categoryTotalPcr += $recipe->getCpr($totalSales);
                        }
                        foreach ($category['combos'] as $combo) {
                            $categoryTotalPcr += $combo->getCpr($totalSales);
                        }
                        ?>                        
                        <tr class="bg-secondary text-white">
                            <td colspan="7" class="text-center"
                                style="font-weight: bold"><?= sprintf("%s: %s", $category['category']->name, formatPercentage($categoryTotalPcr*100)) ?></td>
                        </tr>
                        <?php foreach ($category['recipes'] as $recipe): ?>
                        <?php
                            /** @var $recipe \common\models\StandardRecipe */
                        ?>
                            <tr>
                                <td><?= $recipe->title ?></td>
                                <td><?= formatPrice($recipe->cost) ?></td>
                                <td><?= formatPrice($recipe->price) ?></td>
                                <td><?= formatPercentage($recipe->costPercent*100) ?></td>
                                <td><?= number_format($recipe->sales, 2, '.', ',') ?></td>
                                <td><?= formatPercentage($recipe->getSalesPercent($totalSales)*100) ?></td>
                                <td>
                                    <?php if ($recipe->is_food): ?>
                                        <span class="badge bg-success"><i class="fas fa-utensils me-1"></i> <?= Yii::t('app', "Alimento") ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><i class="fas fa-glass-martini-alt me-1"></i> <?= Yii::t('app', "Bebida") ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php foreach ($category['combos'] as $combo): ?>
                            <?php
                            /** @var $combo \common\models\Menu */
                            ?>                            <tr>
                                <td><?= $combo->title ?></td>
                                <td><?= formatPrice($combo->cost) ?></td>
                                <td><?= formatPrice($combo->total_price) ?></td>
                                <td><?= formatPercentage($combo->costPercent*100) ?></td>
                                <td><?= number_format($combo->sales, 2, '.', ',') ?></td>
                                <td><?= formatPercentage($combo->getSalesPercent($totalSales)*100) ?></td>
                                <td>
                                    <span class="badge bg-secondary"><i class="fas fa-layer-group me-1"></i> <?= Yii::t('app', "Combo") ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(document).on('change', "input[type='checkbox']", function (event) {
    computeCost();
});
function computeCost(forceZero = false){
    $('.recipe-sales').trigger('change');
    let checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');

    // Crear un array para almacenar los valores de data-cost de los checkboxes marcados
    let totalPcr = 0;
    // Iterar sobre los checkboxes
    checkboxes.forEach(checkbox => {
      // Verificar si el checkbox está marcado
      if (checkbox.checked) {
        // Obtener el valor de data-cost y agregarlo al array
        const pcr = checkbox.getAttribute('data-pcr');
        totalPcr += parseFloat(pcr);
      }
    });
    
    totalPcr = (totalPcr * 100).toFixed(2);
    if(!isNaN(totalPcr) && !forceZero){
        $("#theoretical-yield-message").html(`${message}` + totalPcr + ' %');
    }else{
        $("#theoretical-yield-message").html(`${emptyMessage}`);
    }
}
$(document).on('change', '#check-all', (event) => {
    let checkboxes = document.querySelectorAll('.recipe-check');
    let _checkAll = document.getElementById("check-all");
    checkboxes.forEach(checkbox => {
        checkbox.checked = _checkAll.checked;
    });
    computeCost(!_checkAll.checked);
});

$(document).on('change', '.recipe-sales', function (event) {
    let sales = $(this).val();
    let cost = $(this).data('cost')
    let totalSales = getTotalSales();
    let input = $(this)[0];
    let tr = input.parentNode.parentNode;
    let checkbox = $(tr).find("input[type='checkbox']")[0];
    
    let salesPercent = parseFloat(sales / totalSales);
    let pcr = salesPercent * cost;
    checkbox.setAttribute('data-sales', sales);
    checkbox.setAttribute('data-sales-percent', salesPercent);
    checkbox.setAttribute('data-pcr', pcr);
})

function getIsRowSelected(element){
    return element.parentNode.parentNode.querySelector('input[type="checkbox"]').checked;
}

function getTotalSales(){
    const inputs = document.querySelectorAll('.recipe-sales');
   
    let sales = 0;
    inputs.forEach(input => {
        if(getIsRowSelected(input)){
            let value = parseFloat(input.value);
            if(!isNaN(value)){
                sales += value;
            }
        }
    });
    
    return sales;
}

$(document).on('keyup', 'input[name="search-box"]', (event) => {
    let search = event.target.value;
    let rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        let td = row.querySelector('td');
        if(td.hasAttribute('colspan')){
            return;
        }
        let title = td.textContent;
        if(title.toLowerCase().includes(search.toLowerCase())){
            row.style.display = '';
        }else{
            row.style.display = 'none';
        }
    });
});
JS;
$this->registerJs($js);
?>