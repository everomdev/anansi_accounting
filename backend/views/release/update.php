<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Release */

$this->title = Yii::t('app', 'Update Release: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Releases'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="release-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
