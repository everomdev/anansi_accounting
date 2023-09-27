<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UnitOfMeasurement */

$this->title = Yii::t('app', 'Update Unit Of Measurement: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Unit Of Measurements'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="unit-of-measurement-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
