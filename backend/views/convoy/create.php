<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Convoy */

$this->title = Yii::t('app', 'Create Convoy');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Convoys'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="convoy-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
