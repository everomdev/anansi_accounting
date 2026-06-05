<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Empleado */

$this->title = 'Crear Colaborador';
$this->params['breadcrumbs'][] = ['label' => 'Colaboradores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="empleado-create">

    <div class="card">
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>
