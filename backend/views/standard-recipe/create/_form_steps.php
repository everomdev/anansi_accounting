<?php
/** @var $recipe \common\models\StandardRecipe */
/** @var $model \common\models\RecipeStep */
/** @var $pjaxId string */
?>

<div class="row gap-3">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'id' => 'form_step',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'action' => \yii\helpers\Url::to(['standard-recipe/add-step', 'id' => $recipe->id]),
        'method' => 'post',
        'options' => [
            'enctype' => 'multipart/form-data',
            'data-pjax' => $pjaxId
        ]
    ]) ?>
    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
    <div class="col-12">
        <?= $form->field($model, 'activity')->textarea() ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'time')->textInput(['type' => 'time']) ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'indicator')->textInput() ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, '_image')->fileInput() ?>
    </div>
    <div class="col-12">
        <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Add'), [
            'class' => 'btn btn-success'
        ]) ?>
    </div>
    <?php \yii\bootstrap5\ActiveForm::end(); ?>
</div>
