<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipe */

$this->title = Yii::t('app', "Update: {name}", ['name' => $model->title]);
$this->params['breadcrumbs'][] = ['label' => 'Standard Recipes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="standard-recipe-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
