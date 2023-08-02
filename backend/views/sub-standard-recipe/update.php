<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipe */

$this->title = 'Update Standard Recipe: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Standard Recipes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="standard-recipe-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
