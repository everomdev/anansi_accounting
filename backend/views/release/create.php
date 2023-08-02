<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Release */

$this->title = Yii::t('app', 'Create Release');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Releases'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="release-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
