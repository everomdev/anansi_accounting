<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AreaTrabajo */

$this->title = 'Editar Área: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Plantilla vs Realidad', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Áreas', 'url' => ['areas']];
$this->params['breadcrumbs'][] = 'Editar';
?>
<div class="area-trabajo-update">

    <div class="card mt-3">
        <div class="card-body">
            <?= $this->render('_form_area', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>
