<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\RecipeCategory */

$this->title = Yii::t('app', 'Create Recipe Category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Recipe Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="recipe-category-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
