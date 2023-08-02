<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Purchase */

$this->title = Yii::t('app', 'Purchase: {name}', [
    'name' => sprintf("%s - %s", $model->stock->ingredient, $model->date),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Purchases'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="purchase-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
