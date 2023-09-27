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


$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="standard-recipe-index">

    <h4 class="alert alert-warning" id="theoretical-yield-message"><?= sprintf("%s %s", $message, $business->getFormatter()->asPercent($totalCost, 2)) ?></h4>
    <div class="card">
        <div class="card-body">
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
                        <tr class="bg-secondary text-white ">
                            <td colspan="5" class="text-center"
                                style="font-weight: bold"><?= sprintf("%s: %s", $category['category']->name, $business->getFormatter()->asPercent($category['category']->cpr, 2)) ?></td>
                        </tr>
                        <?php foreach ($category['recipes'] as $recipe): ?>
                            <tr>
                                <td><?= $recipe->title ?></td>
                                <td><?= $business->getFormatter()->asCurrency($recipe->recipeLastPrice) ?></td>
                                <td><?= $business->getFormatter()->asCurrency($recipe->price) ?></td>
                                <td><?= Yii::$app->formatter->asPercent($recipe->costPercent, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php foreach ($category['combos'] as $combo): ?>
                            <tr>
                                <td><?= $combo->title ?></td>
                                <td><?= $business->getFormatter()->asCurrency($combo->cost) ?></td>
                                <td><?= $business->getFormatter()->asCurrency($combo->total_price) ?></td>
                                <td><?= Yii::$app->formatter->asPercent($combo->costPercent, 2) ?></td>
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
    
})
JS;
$this->registerJs($js);
?>
