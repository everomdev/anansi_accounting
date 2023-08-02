<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */

$this->title = Yii::t('app', 'Create Movement');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Movements'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movement-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
