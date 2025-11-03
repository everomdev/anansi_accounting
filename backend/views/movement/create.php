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

    <?php
    // Mostrar mensajes flash de error
    foreach (Yii::$app->session->getAllFlashes() as $key => $messages) {
        if ($key === 'error') {
            foreach ((array) $messages as $message) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo Html::encode($message);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            }
        }
    }
    ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
