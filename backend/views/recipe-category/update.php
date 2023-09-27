<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\RecipeCategory */

$this->title = Yii::t('app', 'Update Recipe Category: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Recipe Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="recipe-category-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
