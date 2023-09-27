<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StandardRecipeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \common\models\Business $business */
/** @var \common\models\Category[] $categories */

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$this->title = Yii::t('app', "Real Yield");
$this->params['breadcrumbs'][] = $this->title;
$emptyMessage = Yii::t('app', "Select some recipe to know the real yield");
$message = Yii::t('app', "The real yield is: ");


$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);


?>
<div class="standard-recipe-index">

    <h4 class="alert alert-warning" id="theoretical-yield-message"><?= sprintf("%s %s", $message, $business->formatter->asPercent($totalPcr)) ?></h4>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <th><?= Yii::t('app', "Name") ?></th>
                    <th><?= Yii::t('app', "Cost") ?></th>
                    <th><?= Yii::t('app', "Price") ?></th>
                    <th><?= Yii::t('app', "Cost percent") ?></th>
                    <th><?= Yii::t('app', "Sales") ?></th>
                    <th><?= Yii::t('app', "% Sales") ?></th>

                    </thead>
                    <tbody>
                    <?php foreach ($data as $category): ?>
                        <?php

                        ?>
                        <tr class="bg-secondary text-white ">
                            <td colspan="7" class="text-center"
                                style="font-weight: bold"><?= sprintf("%s: %s", $category['category']->name, $business->formatter->asPercent($category['category']->getCpr(), 2)) ?></td>
                        </tr>
                        <?php foreach ($category['recipes'] as $recipe): ?>
                        <?php
                            /** @var $recipe \common\models\StandardRecipe */

                            ?>
                            <tr>

                                <td><?= $recipe->title ?></td>
                                <td><?= $business->formatter->asCurrency($recipe->cost) ?></td>
                                <td><?= $business->formatter->asCurrency($recipe->price) ?></td>
                                <td><?= Yii::$app->formatter->asPercent($recipe->costPercent, 2) ?></td>
                                <td><?= $recipe->sales ?></td>
                                <td><?= $business->formatter->asPercent($recipe->getSalesPercent($totalSales), 2) ?></td>

                            </tr>
                        <?php endforeach; ?>
                        <?php foreach ($category['combos'] as $combo): ?>
                            <?php
                            /** @var $combo \common\models\Menu */

                            ?>
                            <tr>

                                <td><?= $combo->title ?></td>
                                <td><?= $business->formatter->asCurrency($combo->cost) ?></td>
                                <td><?= $business->formatter->asCurrency($combo->total_price) ?></td>
                                <td><?= Yii::$app->formatter->asPercent($combo->costPercent, 2) ?></td>
                                <td><?= $combo->sales ?></td>
                                <td><?= $business->formatter->asPercent($combo->getSalesPercent($totalSales), 2) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--    --><?php //= GridView::widget([
    //        'id' => 'grid-theoretical-yield',
    //        'dataProvider' => $dataProvider,
    //        'filterModel' => $searchModel,
    //        'formatter' => [
    //            'class' => \yii\i18n\Formatter::class,
    //            'currencyCode' => 'usd',
    //        ],
    //        'columns' => [
    //            ['class' => 'yii\grid\SerialColumn'],
    //            [
    //                'class' => \yii\grid\CheckboxColumn::class,
    //                'checkboxOptions' => function ($model, $key, $index, $column) {
    //                    return ['value' => $model->costPercent];
    //                }
    //            ],
    //            'title',
    //            [
    //                'attribute' => 'recipeLastPrice',
    //                'format' => 'currency',
    //                'label' => Yii::t('app', 'Cost')
    //            ],
    //            'price:currency',
    //            'costPercent:percent',
    //            [
    //                'class' => 'yii\grid\ActionColumn',
    //                'template' => "{update} {delete}"
    //            ],
    //        ],
    //    ]); ?>


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
JS;
$this->registerJs($js);
?>
