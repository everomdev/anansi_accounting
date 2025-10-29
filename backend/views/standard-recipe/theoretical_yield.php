<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$this->title = Yii::t('app', "Theoretical Yield");
$this->params['breadcrumbs'][] = $this->title;
$emptyMessage = Yii::t('app', "Select some recipe to know the theoretical yield");
$message = Yii::t('app', "The theoretical yield is: ");
$messageFood = Yii::t('app', "Rentabilidad teórica de los alimentos: ");
$messageNonFood = Yii::t('app', "Rentabilidad teórica de las bebidas: ");

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="standard-recipe-index"><div class="row mb-4">
        <div class="col-12">
            <?php if ($theoricalTotal !== null): ?>
                <h4 class="alert alert-warning"
                    id="theoretical-yield-message"><?= sprintf("%s %s", $message, $theoricalTotal) ?></h4>
            <?php endif; ?>
        </div>
    </div>

    <div class="col mb-4">
        <div class="row-md-6">
            <?php if (isset($recipesByType) && isset($recipesByType['food']) && isset($recipesByType['food']['theoricalYield'])): ?>
                <div class="alert alert-info" id="food-yield-message">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-utensils me-2"></i>
                        <span>
                            <?= sprintf("%s %s", $messageFood, $recipesByType['food']['theoricalYield']) ?>
                            <?php if (isset($recipesByType['food']['count'])): ?>
                                <small class="ms-2">(<?= Yii::t('app', '{n, plural, =1{# receta} other{# recetas}}', ['n' => $recipesByType['food']['count']]) ?>)</small>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row-md-6">
            <?php if (isset($recipesByType) && isset($recipesByType['nonFood']) && isset($recipesByType['nonFood']['theoricalYield'])): ?>
                <div class="alert alert-info" id="beverage-yield-message">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-glass-martini-alt me-2"></i>
                        <span>
                            <?= sprintf("%s %s", $messageNonFood, $recipesByType['nonFood']['theoricalYield']) ?>
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
            <div class="input-group mb-3">
                <?= \yii\bootstrap5\Html::textInput('search-box', null, [
                    'class' => 'form-control', 
                    'placeholder' => 'Buscar',
                    'id' => 'search-box'
                ]) ?>
                <button class="btn btn-outline-secondary" type="button" id="clear-search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <th><?= Yii::t('app', "Name") ?></th>
                    <th><?= Yii::t('app', "Cost") ?></th>
                    <th><?= Yii::t('app', "Price") ?></th>
                    <th><?= Yii::t('app', "Cost percent") ?></th>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $category): ?>
                        <?php
                        $totalCostPercent = 0;
                        $totalCostCombo = 0;
                        $recipeCount = count($category['recipes']);
                        $recipeCount += count($category['combos']);
                        foreach ($category['recipes'] as $recipe) {
                            $totalCostPercent += $recipe->costPercent;
                        }
                        foreach ($category['combos'] as $combo) {
                            $totalCostPercent += $combo->costPercent;
                        }
                        $averageCostPercent = $recipeCount > 0 ? $totalCostPercent / $recipeCount : 0;
                        ?>                        <tr class="bg-secondary text-white ">
                            <td colspan="5" class="text-center"
                                style="font-weight: bold"><?= sprintf("%s: %s", $category['category']->name, formatPercentage($averageCostPercent*100)) ?></td>
                        </tr>
                        <?php foreach ($category['recipes'] as $recipe): ?>
                            <tr>
                                <td><?= $recipe->title ?></td>
                                <td><?= formatPrice($recipe->recipeLastPrice) ?></td>
                                <td><?= formatPrice($recipe->price) ?></td>
                                <td><?= formatPercentage($recipe->costPercent*100) ?></td>
                                <td>
                                    <?php if ($recipe->is_food): ?>
                                        <span class="badge bg-success"><i class="fas fa-utensils me-1"></i> <?= Yii::t('app', "Alimento") ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><i class="fas fa-glass-martini-alt me-1"></i> <?= Yii::t('app', "Bebida") ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>                        <?php foreach ($category['combos'] as $combo): ?>
                            <tr>
                                <td><?= $combo->title ?></td>
                                <td><?= formatPrice($combo->cost) ?></td>
                                <td><?= formatPrice($combo->total_price) ?></td>
                                <td><?= formatPercentage($combo->costPercent*100) ?></td>
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
$(document).on('change', "input[type='checkbox']", (event) => {
    computeCost();
});

function computeCost(){
    let checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');

    // Crear un array para almacenar los valores de data-cost de los checkboxes marcados
    let costs = 0;
    let total = 0;
    // Iterar sobre los checkboxes
    checkboxes.forEach(checkbox => {
      // Verificar si el checkbox está marcado
      if (checkbox.checked) {
        // Obtener el valor de data-cost y agregarlo al array
        const dataCostValue = checkbox.getAttribute('data-cost');
        costs += parseFloat(dataCostValue);
        total++;
      }
    });
    
    let costPercent = (costs / total * 100).toFixed(2);
    
    if(!isNaN(costPercent)){
        $("#theoretical-yield-message").html(`${message}` + costPercent + ' %');
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
    
    
        computeCost();
    
});

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

$(document).on('click', '#clear-search', (event) => {
    $('#search-box').val('');
    // Trigger the search to show all rows
    $('#search-box').trigger('keyup');
});
JS;
$this->registerJs($js);
?>