<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\captcha\Captcha;

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
            'enableAjaxValidation' => false, // Deshabilitar validación AJAX
            'enableClientValidation' => false, // Deshabilitar validación del lado del cliente (Yii)
        ]); ?>
        <div class="card m-5">
            <div class="card-header">
                <div class="text-center">
                    <img src="<?= Yii::getAlias("@web/images/logo1.png") ?>" alt="" width="300">
                </div>
                <span class="card-title"><?= Html::encode($this->title) ?></span>
            </div>
            <div class="card-body">
                <div class="row gap-3">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 row">
                        <div class="col-12">
                            <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>
                            <div class="invalid-feedback">Por favor, ingresa tu nombre.</div>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'businessName')->textInput(['autofocus' => true]) ?>
                            <div class="invalid-feedback">Por favor, ingresa el nombre de tu negocio.</div>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
                            <div class="invalid-feedback">Por favor, ingresa un correo electrónico válido.</div>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'password', [
                                'template' => "{label}\n<div class='input-group'>{input}<span class='input-group-text'><i class='bx bxs-show'></i></span></div>\n{error}",
                            ])->passwordInput(['id' => 'password-field']) ?>
                            <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
                        </div>
                        <div class="col-12">
                            <?= Html::label('Confirmar contraseña', 'confirm-password-field') ?>
                            <div class="input-group mb-3">
                                <?= Html::passwordInput('confirmPassword', '', ['id' => 'confirm-password-field', 'class' => 'form-control']) ?>
                                <span class="input-group-text"><i class='bx bxs-show'></i></span>
                            </div>
                            <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'planId')->dropDownList(
                                \yii\helpers\ArrayHelper::map($plans, 'id', 'label'),
                            ) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'captcha')->widget(Captcha::class, [
                                'captchaAction' => '/site/captcha', // Ruta correcta de la acción CAPTCHA
                                'options' => ['class' => 'form-control', 'style' => 'width: 150px;'], // Ajusta el ancho del input
                                'template' => '
                                    <div class="captcha-container">
                                        <div class="captcha-image">
                                            {image}
                                            <button type="button" id="refresh-captcha" class="btn btn-secondary btn-sm mt-2">
                                                <i class="fas fa-sync-alt"></i> Recargar
                                            </button>
                                        </div>
                                        <div class="captcha-input">
                                            {input}
                                        </div>
                                    </div>
                                ',
                                'imageOptions' => [
                                    'id' => 'captcha-image', // Agrega un ID a la imagen
                                    'style' => 'cursor: pointer;', // Cambia el cursor al pasar sobre la imagen
                                ],
                            ]) ?>
                            <div class="invalid-feedback">Por favor, ingresa el código CAPTCHA.</div>
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
        alert('Las contraseñas no coinciden. Por favor, verifícalas.');
        $('#confirm-password-field').focus();
    }
});
JS;
$this->registerJs($js);
?>
<?php
$css = <<< CSS
.captcha-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.captcha-image {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.captcha-input {
    width: 150px; /* Ajusta el ancho del input */
}

#refresh-captcha {
    width: auto; /* Ancho automático para el botón */
    padding: 5px 10px; /* Ajusta el padding del botón */
}

.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875em;
}
CSS;
$this->registerCss($css);
?>
<?php
$js = <<< JS
$(document).on('click', '#refresh-captcha', function(e) {
    e.preventDefault(); // Evita cualquier comportamiento por defecto
    $.ajax({
        url: '/site/captcha?refresh=1',
        dataType: 'json',
        success: function(data) {
            if (data.url) {
                $('#captcha-image').attr('src', data.url);
            }
        }
    });
});
JS;
$this->registerJs($js);
?>