<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Crear Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Inventario de Insumos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventory-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
