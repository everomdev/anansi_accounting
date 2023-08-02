<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Provider */

$this->title = Yii::t('app', 'Update Provider: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Providers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="provider-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
