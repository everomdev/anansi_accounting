<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="expense-category-form">

    <?php $form = ActiveForm::begin([
        'id' => 'expense-category-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validateOnSubmit' => true
    ]); ?>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-12 col-md-12">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Nombre de la Categoría') ?>
                </div>
                
                <div class="col-sm-12 col-md-12">
                    <?= $form->field($model, 'description')->textarea(['rows' => 3])->label('Descripción') ?>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-warning']) ?>
                <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
