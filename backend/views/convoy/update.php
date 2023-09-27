<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Convoy */

$this->title = Yii::t('app', 'Manage Convoy: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Convoys'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="convoy-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
