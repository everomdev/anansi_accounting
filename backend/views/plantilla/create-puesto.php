<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PlantillaPuesto */
/* @var $area common\models\AreaTrabajo */

$this->title = 'Crear Puesto en ' . $area->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Plantilla vs Realidad', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Áreas', 'url' => ['areas']];
$this->params['breadcrumbs'][] = ['label' => $area->nombre, 'url' => ['puestos', 'area_id' => $area->id]];
$this->params['breadcrumbs'][] = 'Crear Puesto';
?>
<div class="plantilla-puesto-create">

    <div class="card mt-3">
        <div class="card-body">
            <?= $this->render('_form_puesto', [
                'model' => $model,
                'area' => $area,
            ]) ?>
        </div>
    </div>

</div>
