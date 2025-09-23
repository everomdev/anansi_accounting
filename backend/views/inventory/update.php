<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Actualizar Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Inventario de Insumos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventory-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="inventory-form">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'ingredient_stock_id')->textInput() ?>
        <?= $form->field($model, 'business_id')->textInput() ?>
        <?= $form->field($model, 'inventario_almacen')->textInput(['type' => 'number', 'step' => '0.001']) ?>
        <?= $form->field($model, 'inventario_cocina')->textInput(['type' => 'number', 'step' => '0.001']) ?>
        <?= $form->field($model, 'inventario_barra')->textInput(['type' => 'number', 'step' => '0.001']) ?>
        <?= $form->field($model, 'inventario_servicio')->textInput(['type' => 'number', 'step' => '0.001']) ?>
        <?= $form->field($model, 'inventario_otro')->textInput(['type' => 'number', 'step' => '0.001']) ?>
        <div class="form-group">
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
