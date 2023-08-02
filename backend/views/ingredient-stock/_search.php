<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\IngredientStockSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ingredient-stock-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ingredient') ?>

    <?= $form->field($model, 'business_id') ?>

    <?= $form->field($model, 'quantity') ?>

    <?= $form->field($model, 'um') ?>

    <?php // echo $form->field($model, 'yield') ?>

    <?php // echo $form->field($model, 'portions_per_unit') ?>

    <?php // echo $form->field($model, 'portion_um') ?>

    <?php // echo $form->field($model, 'observations') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
