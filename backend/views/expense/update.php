<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */

$this->title = Yii::t('app', 'Actualizar Gasto: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Catálogo de Gastos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Actualizar');
?>
<div class="expense-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
