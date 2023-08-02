<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\IngredientStock */

$this->title = Yii::t('app', 'Create Ingredient Stock');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Ingredient Stocks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ingredient-stock-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
