<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseUnitMeasurement */

$this->title = 'Nueva Unidad de Medida';
$this->params['breadcrumbs'][] = ['label' => 'Unidades de Medida', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-unit-measurement-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
