<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ConsumptionCenter */

$this->title = Yii::t('app', 'Create Consumption Center');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Consumption Centers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="consumption-center-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
