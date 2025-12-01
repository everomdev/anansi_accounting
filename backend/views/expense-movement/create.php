<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseMovement */

$this->title = 'Nuevo Movimiento de Gasto';
$this->params['breadcrumbs'][] = ['label' => 'Movimientos de Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-movement-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
