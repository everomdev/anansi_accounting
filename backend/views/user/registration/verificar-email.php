<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('usuario', 'Verificar tu Correo Electrónico');
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .verification-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
    }
    .code-input {
        font-size: 24px;
        letter-spacing: 10px;
        text-align: center;
        font-weight: bold;
        padding: 15px;
        border-radius: 8px;
    }
    .verification-card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-radius: 8px;
        border: none;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
");

?>
<div class="verification-container">
    <div class="card verification-card">
        <div class="card-header text-center py-3">
            <h3 class="card-title mb-0"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body p-4">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>
            
            <?php if (Yii::$app->session->hasFlash('danger')): ?>
                <div class="alert alert-danger">
                    <?= Yii::$app->session->getFlash('danger') ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$mostrarInputCodigo): ?>
                <!-- Paso 1: Formulario de introducción de email -->
                <div class="text-center mb-4">
                    <img src="<?= Yii::getAlias('@web/img/email-verification.svg') ?>" alt="Email Verification" width="120" class="mb-4" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                    <p class="mb-4"><?= Yii::t('usuario', 'Ingresa tu correo electrónico para comenzar el proceso de registro.') ?></p>
                </div>
                
                <?php $form = ActiveForm::begin(['id' => 'verificar-email-form']); ?>
                    
                    <?= $form->field($model, 'email')
                        ->textInput([
                            'autofocus' => true, 
                            'type' => 'email', 
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'ejemplo@correo.com'
                        ])
                        ->label(Yii::t('usuario', 'Correo electrónico')) ?>
                    
                    <?= Html::hiddenInput('enviar_codigo', '1') ?>
                    
                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton(
                            Yii::t('usuario', 'Enviar Código de Verificación'), 
                            ['class' => 'btn btn-primary btn-lg w-100']
                        ) ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <hr>
                        <?= Html::a(Yii::t('usuario', 'Volver a inicio de sesión'), ['/user/security/login'], [
                            'class' => 'btn btn-link'
                        ]) ?>
                    </div>
                    
                <?php ActiveForm::end(); ?>
            <?php else: ?>
                <!-- Paso 2: Formulario de verificación de código -->
                <div class="text-center mb-4">
                    <img src="<?= Yii::getAlias('@web/img/code-verification.svg') ?>" alt="Code Verification" width="100" class="mb-3" onerror="this.onerror=null; this.src=''; this.style.display='none';">
                    <p class="mb-2"><?= Yii::t('usuario', 'Hemos enviado un código de verificación a:') ?></p>
                    <h5 class="font-weight-bold mb-3"><?= Html::encode($email) ?></h5>
                    <p class="text-muted"><?= Yii::t('usuario', 'Ingresa el código de 6 dígitos para continuar con el registro.') ?></p>
                </div>
                
                <?php $form = ActiveForm::begin(['id' => 'verificar-codigo-form']); ?>
                    
                    <?= $form->field($model, 'email')->hiddenInput(['value' => $email])->label(false) ?>
                    
                    <div class="form-group text-center my-4">
                        <?= $form->field($model, 'codigo_verificacion')->textInput([
                            'autofocus' => true, 
                            'class' => 'form-control code-input',
                            'maxlength' => 6,
                            'placeholder' => '______',
                            'autocomplete' => 'off'
                        ])->label(false) ?>
                    </div>
                    
                    <?= Html::hiddenInput('verificar_codigo', '1') ?>
                    
                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton(
                            Yii::t('usuario', 'Verificar y Continuar'), 
                            ['class' => 'btn btn-primary btn-lg w-100']
                        ) ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <hr>
                        <p class="text-muted mb-2"><?= Yii::t('usuario', "¿No recibiste el código?") ?></p>
                        <?= Html::a(Yii::t('usuario', 'Reenviar código'), ['verificar-email'], [
                            'class' => 'btn btn-outline-secondary'
                        ]) ?>
                    </div>
                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Enfoque automático y formateo del campo de código
    $(document).ready(function() {
        var codeInput = $('#dynamicmodel-codigo_verificacion');
        if (codeInput.length) {
            codeInput.focus();
            
            // Permitir solo números en el campo de código
            codeInput.on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });
");
?>