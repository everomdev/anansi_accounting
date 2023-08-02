<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="standard-recipe-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'business_id') ?>

    <?= $form->field($model, 'flowchart') ?>

    <?= $form->field($model, 'equipment') ?>

    <?= $form->field($model, 'steps') ?>

    <?php // echo $form->field($model, 'allergies') ?>

    <?php // echo $form->field($model, 'type') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
