<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */

$this->title = Yii::t('app', 'Create {movement}', [
        'movement' => $model->type == $model::TYPE_INPUT ? Yii::t('app', 'Entry') : ($model->type == $model::TYPE_OUTPUT ? Yii::t('app', 'Output') : Yii::t('app', 'Order'))
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Movements'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movement-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
