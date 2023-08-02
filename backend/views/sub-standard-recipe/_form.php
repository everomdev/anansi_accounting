<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipe */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="standard-recipe-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-body">
                <?= $form->field($model, 'title')->textInput() ?>

        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('Continue', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>



    <?php ActiveForm::end(); ?>

</div>
