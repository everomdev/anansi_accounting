<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CategoryGroup */

$this->title = Yii::t('app', 'Create Category Group');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Category Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-group-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
