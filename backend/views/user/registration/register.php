<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\RegistrationForm */
/* @var $form yii\widgets\ActiveForm */

$this->title = "Sign-up";
$plans = \common\models\Plan::find()->all();
?>
<div class="d-flex align-items-center justify-content-center">
    <div>
        <?php $form = ActiveForm::begin([
            'id' => $model->formName(),
            'enableAjaxValidation' => true,
            'enableClientValidation' => false,
        ]); ?>
        <div class="card m-5">
            <div class="card-header">
                <div class="text-center">
                    <img src="<?= Yii::getAlias("@web/images/logo.png") ?>" alt="" width="300">
                </div>
                <span class="card-title"><?= Html::encode($this->title) ?></span>
            </div>
            <div class="card-body">
                <div class="row gap-3">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 row">
                        <div class="col-12">
                            <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'businessName')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'password', [
                                'template' => "{label}\n<div class='input-group'>{input}<span class='input-group-text'><i class='bx bxs-show'></i></span></div>\n{error}",
                            ])->passwordInput(['id' => 'password-field']) ?>
                        </div>
                        <div class="col-12">
                            <?= Html::label('Confirm Password', 'confirm-password-field') ?>
                            <div class="input-group mb-3">
                                <?= Html::passwordInput('confirmPassword', '', ['id' => 'confirm-password-field', 'class' => 'form-control']) ?>
                                <span class="input-group-text"><i class="bx bxs-show"></i></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'planId')->dropDownList(
                                \yii\helpers\ArrayHelper::map($plans, 'id', 'label'),
                            ) ?>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <?php foreach ($plans as $index => $plan): ?>
                            <div id="plan_<?= $plan->id ?>" class="<?= $index == 0 ? '' : 'd-none' ?> plan">
                                <h3><?= 'Plan ' . $plan->name ?></h3>
                                <?= $plan->description ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <?= Html::submitButton(Yii::t('usuario', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>
                        <?= Html::a(Yii::t('usuario', 'Already registered? Sign in!'), ['/user/security/login']) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<< JS
$(function(){
    $("#registrationform-planid").trigger('change');
})
$(document).on('change', "#registrationform-planid", function(event){
    const id = $(this).val();
    $(".plan").addClass('d-none');
    $('#plan_' + id).removeClass('d-none');
})

$(document).on('click', '.input-group-text', function() {
    var input = $(this).parent().find('input');
    if (input.attr('type') == 'password') {
        input.attr('type', 'text');
        $(this).find('i').removeClass('bxs-show').addClass('bxs-hide');
    } else {
        input.attr('type', 'password');
        $(this).find('i').removeClass('bxs-hide').addClass('bxs-show');
    }
});

$(document).on('submit', '#{$model->formName()}', function(event) {
    let password = $('#password-field').val();
    let confirmPassword = $('#confirm-password-field').val();
    if (password !== confirmPassword) {
        event.preventDefault();
        alert('Passwords do not match!');
    }
});
JS;
$this->registerJs($js);
?>