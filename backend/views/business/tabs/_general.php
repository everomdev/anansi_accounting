<?php

?>


<?php $form = \yii\bootstrap5\ActiveForm::begin([
    'id' => 'my-business-form',
    'enableClientValidation' => true,
    'enableAjaxValidation' => true
]) ?>

<?= $form->field($model, 'businessName')->textInput() ?>
<?= $form->field($model, 'name')->textInput() ?>
<?= $form->field($model, 'password')->textInput(['type' => 'password']) ?>
<div class="row">
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'currency_code')->textInput() ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'decimal_separator')->textInput() ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'thousands_separator')->textInput() ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'timezone')->widget(\kartik\select2\Select2::class, [
            'data' => $timezones
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'locale')->widget(\kartik\select2\Select2::class, [
            'data' => $locales
        ]) ?>
    </div>
</div>


<?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Save'), [
    'class' => 'btn btn-success'
]) ?>

<?php \yii\bootstrap5\ActiveForm::end(); ?>


