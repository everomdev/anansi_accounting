<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseMovement */

$this->title = 'Actualizar Movimiento: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Movimientos de Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="expense-movement-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
