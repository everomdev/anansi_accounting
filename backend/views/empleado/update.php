<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Empleado */

$this->title = 'Actualizar Colaborador: ' . $model->getNombreCompleto();
$this->params['breadcrumbs'][] = ['label' => 'Colaboradores', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->getNombreCompleto(), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="empleado-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card">
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>
