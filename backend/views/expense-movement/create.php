<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseMovement */

$this->title = 'Nuevo Registro de Gasto';
$this->params['breadcrumbs'][] = ['label' => 'Registros de Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-movement-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
