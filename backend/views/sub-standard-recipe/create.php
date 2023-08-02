<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipe */

$this->title = Yii::t('app', 'Create new sub recipe');
$this->params['breadcrumbs'][] = ['label' => 'Standard Recipes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="standard-recipe-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
