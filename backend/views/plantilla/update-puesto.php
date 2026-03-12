<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PlantillaPuesto */
/* @var $area common\models\AreaTrabajo */

$this->title = 'Editar Puesto: ' . $model->nombre_puesto;
$this->params['breadcrumbs'][] = ['label' => 'Plantilla vs Realidad', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Áreas', 'url' => ['areas']];
$this->params['breadcrumbs'][] = ['label' => $area->nombre, 'url' => ['puestos', 'area_id' => $area->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>
<div class="plantilla-puesto-update">

    <div class="card mt-3">
        <div class="card-body">
            <?= $this->render('_form_puesto', [
                'model' => $model,
                'area' => $area,
            ]) ?>
        </div>
    </div>

</div>
