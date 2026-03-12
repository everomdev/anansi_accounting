<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AreaTrabajo */

$this->title = 'Crear Área de Trabajo';
$this->params['breadcrumbs'][] = ['label' => 'Plantilla vs Realidad', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Áreas', 'url' => ['areas']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="area-trabajo-create">

    <div class="card mt-3">
        <div class="card-body">
            <?= $this->render('_form_area', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>
