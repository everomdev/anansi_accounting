<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */
$this->title = Yii::t('app', 'Crear Gasto');
$this->params['breadcrumbs'][] = ['label' => 'Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
