<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseCategory */

$this->title = 'Editar Categoría: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categorías de Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>
<div class="expense-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
