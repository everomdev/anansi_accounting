<?php

?>


<?php $form = \yii\bootstrap5\ActiveForm::begin([
    'id' => 'my-business-form',
    'enableClientValidation' => true,
    'enableAjaxValidation' => true
]) ?>

<?= $form->field($model, 'businessName')->textInput() ?>
<?= $form->field($model, 'name')->textInput() ?>
<div class="mb-3">
    <?= $form->field($model, 'password', [
        'template' => '{label}<div class="d-flex w-100" style="gap:0;"><input type="password" name="CreateUserForm[password]" class="form-control w-100" autocomplete="new-password" id="createuserform-password">'
            .'<button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" style="border-top-left-radius:0;border-bottom-left-radius:0;"><i class="bx bx-show"></i></button></div>{error}'
    ])->textInput(['style' => 'display:none']) ?>
</div>
<?php
$this->registerJs(<<<JS
$(document).on('click', '.toggle-password', function() {
    var input = $(this).closest('div.d-flex').find('input');
    var icon = $(this).find('i');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('bx-show').addClass('bx-hide');
    } else {
        input.attr('type', 'password');
        icon.removeClass('bx-hide').addClass('bx-show');
    }
});
JS);
?>
<div class="row">
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'currency_code')->textInput()->label('Moneda') ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'decimal_separator')->textInput()->label('Separador Decimal') ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'thousands_separator')->textInput()->label('Separador de Miles') ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'timezone')->widget(\kartik\select2\Select2::class, [
            'data' => $timezones
        ])->label('Zona Horaria') ?>
    </div>
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <?= $form->field($model, 'locale')->widget(\kartik\select2\Select2::class, [
            'data' => $locales
        ])->label('Idioma') ?>
    </div>
</div>


<p>
    <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Save'), [
        'class' => 'btn btn-success'
    ]) ?>
    <?php if (Yii::$app->user->can('manage_users')): ?>
        <?= \yii\bootstrap5\Html::a("Eliminar cuenta", '#', [
            'class' => 'btn btn-danger',
            'id' => 'btn-delete-account',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modal-delete-account'
        ]) ?>
    <?php endif; ?>
</p>

<?php \yii\bootstrap5\ActiveForm::end(); ?>

<?php if (Yii::$app->user->can('manage_users')): ?>
<?php
// Modal para eliminar cuenta
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-delete-account',
    'title' => '<i class="bx bx-error text-danger"></i> Eliminar Cuenta',
    'size' => \yii\bootstrap5\Modal::SIZE_DEFAULT,
    'options' => [
        'class' => 'modal-danger'
    ]
]);
?>
<div class="modal-body">
    <div class="alert alert-danger" role="alert">
        <h6 class="alert-heading"><i class="bx bx-error-circle"></i> Advertencia Crítica</h6>
        <p class="mb-0">
            Si eliminas tu cuenta, perderás permanentemente todos los datos, incluyendo:
        </p>
        <ul class="mt-2 mb-0">
            <li>Todas las recetas y elementos del menú</li>
            <li>Información de inventario y stock</li>
            <li>Todos los movimientos de inventario</li>
            <li>Proveedores y centros de consumo</li>
            <li>Reportes de ventas y análisis</li>
            <li>Configuración del negocio</li>
            <li>Información de facturación y suscripción</li>
            <li>Todos los datos históricos</li>
        </ul>
        <p class="mt-2 mb-0">
            <strong>Esta acción es IRREVERSIBLE y no se puede deshacer.</strong>
        </p>
    </div>
    
    <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" id="confirm-deletion" />
        <label class="form-check-label" for="confirm-deletion">
            Entiendo que perderé PERMANENTEMENTE todos mis datos y quiero eliminar mi cuenta
        </label>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        Mantener Cuenta
    </button>
    <button type="button" class="btn btn-danger" id="btn-confirm-delete" disabled>
        Eliminar Cuenta Permanentemente
    </button>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>
<?php endif; ?>

<?php if (Yii::$app->user->can('manage_users')): ?>
<?php
// Definir las variables PHP antes de crear el JS
$deleteAccountUrl = \yii\helpers\Url::to(['//user/admin/delete']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<< JS
// Control del checkbox para habilitar/deshabilitar el botón de eliminación
$(document).on('change', '#confirm-deletion', function() {
    const isChecked = $(this).is(':checked');
    const deleteBtn = $('#btn-confirm-delete');
    
    if (isChecked) {
        deleteBtn.prop('disabled', false).removeClass('btn-danger').addClass('btn-outline-danger');
    } else {
        deleteBtn.prop('disabled', true).removeClass('btn-outline-danger').addClass('btn-danger');
    }
});

// Manejar el click del botón de eliminación
$(document).on('click', '#btn-confirm-delete', function(e) {
    e.preventDefault();
    if (!$(this).prop('disabled')) {
        // Crear formulario POST para eliminar cuenta
        const form = $('<form>', {
            'method': 'POST',
            'action': '$deleteAccountUrl'
        });
        
        // Agregar CSRF token
        form.append($('<input>', {
            'type': 'hidden',
            'name': '$csrfParam',
            'value': '$csrfToken'
        }));
        
        // Enviar formulario
        $('body').append(form);
        form.submit();
    }
});

// Resetear el modal cuando se cierre
$('#modal-delete-account').on('hidden.bs.modal', function () {
    $('#confirm-deletion').prop('checked', false);
    $('#btn-confirm-delete')
        .prop('disabled', true)
        .removeClass('btn-outline-danger')
        .addClass('btn-danger');
});
JS;

$this->registerJs($js);
?>
<?php endif; ?>
