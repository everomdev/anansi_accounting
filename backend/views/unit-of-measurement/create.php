<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UnitOfMeasurement */

$this->title = Yii::t('app', 'Create Unit Of Measurement');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Unit Of Measurements'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="unit-of-measurement-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
