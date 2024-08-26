<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipe */


$this->title = "adsa";//Yii::t('app',  Yii::$app->request->get('type') == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN ? 'Create new recipe' : 'Create new sub-recipe');
$this->params['breadcrumbs'][] = ['label' => 'Standard Recipes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="standard-recipe-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
