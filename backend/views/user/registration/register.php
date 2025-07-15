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
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="privacy-check">
                            <label class="form-check-label" for="privacy-check">
                                Acepto el <a href="#" id="privacy-link">aviso de privacidad y de protección de datos personales</a>.
                            </label>
                        </div>
                        <div class="invalid-feedback d-none" id="privacy-error">Debes aceptar el aviso de privacidad para continuar.</div>
                        <?= Html::submitButton(Yii::t('usuario', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>
                        <?= Html::a(Yii::t('usuario', 'Already registered? Sign in!'), ['/user/security/login']) ?>
                    </div>
                </div>
            </div>
            <!-- Modal Aviso de Privacidad -->
            <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Aviso de Privacidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body" style="white-space: pre-line;">
                    En AIA Consultores (en adelante, "la Empresa"), con domicilio en Cruz Blanca #21 2do piso, Peña Pobre, Tlalpan, CDMX, México, C.P. 14060, estamos comprometidos con la protección de la privacidad de nuestros clientes, prospectos, empleados, proveedores y cualquier otra persona de la que recabemos datos personales. Este Aviso de Privacidad describe cómo recolectamos, utilizamos, compartimos y protegemos tus datos personales.

1. Responsable del tratamiento de tus datos personales
La Empresa, con domicilio en Cruz Blanca #21 2do piso, Peña Pobre, Tlalpan, CDMX, México, C.P. 14060, es responsable del tratamiento de tus datos personales.

2. Datos personales que recabamos
Recabamos tus datos personales de distintas formas: cuando nos los proporcionas directamente, cuando visitas nuestro sitio web o utilizas nuestros servicios en línea, y cuando obtenemos información a través de otras fuentes que están permitidas por la ley.

Los datos que podemos recabar incluyen, pero no se limitan a:
Nombre completo
Domicilio
Teléfono
Correo electrónico
Datos fiscales
Información bancaria (en caso de realizar pagos o recibir reembolsos)

3. Finalidades del tratamiento de tus datos personales
Tus datos personales serán utilizados para las siguientes finalidades:
Firma de convenios
Proveer los servicios que has solicitado
Informarte sobre cambios en los mismos
Evaluar la calidad del servicio
Enviar información promocional y publicitaria
Cumplir con obligaciones legales

4. Transferencia de datos personales
La Empresa no transferirá tus datos personales a terceros sin tu consentimiento, salvo por las excepciones previstas en el artículo 37 de la Ley Federal de Protección de Datos Personales en Posesión de los Particulares y las transferencias necesarias para cumplir con nuestras obligaciones legales.

5. Medidas de seguridad
Implementamos las medidas de seguridad administrativas, técnicas y físicas para proteger tus datos personales contra daño, pérdida, alteración, destrucción o el uso, acceso o tratamiento no autorizado.

6. Derechos ARCO (Acceso, Rectificación, Cancelación y Oposición)
Tienes derecho a conocer qué datos personales tenemos de ti, para qué los utilizamos y las condiciones del uso que les damos (Acceso). Asimismo, es tu derecho solicitar la corrección de tu información personal en caso de que esté desactualizada, sea inexacta o incompleta (Rectificación); que la eliminemos de nuestros registros o bases de datos cuando consideres que la misma no está siendo utilizada adecuadamente (Cancelación); así como oponerte al uso de tus datos personales para fines específicos (Oposición).

Para ejercer tus derechos ARCO, puedes enviar una solicitud a nuestro correo electrónico: contacto@aia.com.mx.

7. Cambios en el aviso de privacidad
Nos reservamos el derecho de efectuar en cualquier momento modificaciones o actualizaciones al presente Aviso de Privacidad, para la atención de novedades legislativas o políticas internas. Estas modificaciones estarán disponibles al público a través de nuestra página web o se las haremos llegar al último correo electrónico que nos hayas proporcionado.

8. Consentimiento
Al proporcionar tus datos personales a la Empresa, ya sea de manera directa, a través de nuestra página web o de otras fuentes permitidas por la ley, consientes su tratamiento conforme a este Aviso de Privacidad.

Contacto
Si tienes preguntas o comentarios sobre este Aviso de Privacidad, por favor contáctanos a través de contacto@aia.com.mx.

Fecha de última actualización: 15 de julio del 2025
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                  </div>
                </div>
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
    // Validar aceptación de aviso de privacidad
    if (!$('#privacy-check').is(':checked')) {
        event.preventDefault();
        $('#privacy-error').removeClass('d-none');
        $('#privacy-check').focus();
    } else {
        $('#privacy-error').addClass('d-none');
    }
});

// Mostrar modal de aviso de privacidad
$(document).on('click', '#privacy-link', function(e) {
    e.preventDefault();
    var modal = new bootstrap.Modal(document.getElementById('privacyModal'));
    modal.show();
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