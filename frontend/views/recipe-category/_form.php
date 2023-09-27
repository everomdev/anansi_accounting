<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\RecipeCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="recipe-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type')->dropDownList([
            \common\models\RecipeCategory::TYPE_MAIN => Yii::t('app', 'For recipes'),
            \common\models\RecipeCategory::TYPE_SUB => Yii::t('app', 'For sub-recipes'),
    ]) ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
