<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseCategory */

$this->title = 'Crear Categoría de Gasto';
$this->params['breadcrumbs'][] = ['label' => 'Categorías de Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-category-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
