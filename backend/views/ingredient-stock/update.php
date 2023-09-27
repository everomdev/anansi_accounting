<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\IngredientStock */

$this->title = Yii::t('app', 'Update Ingredient Stock: {name}', [
    'name' => $model->ingredient,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Ingredient Stocks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="ingredient-stock-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
