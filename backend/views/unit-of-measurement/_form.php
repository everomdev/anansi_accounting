<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap5\Modal;

/* @var $this yii\web\View */
/* @var $model common\models\UnitOfMeasurement */
/* @var $form yii\widgets\ActiveForm */
$controller = $model->isNewRecord
    ? \yii\helpers\Url::to(['unit-of-measurement/create'])
    : \yii\helpers\Url::to(['unit-of-measurement/update', 'id' => $model->id])
?>

<div class="unit-of-measurement-form">

    <?php if ($model->isNewRecord): ?>
    <!-- Advertencia inicial para nuevas unidades -->
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong> Advertencia obligatoria:</strong> La creación de unidades personalizadas puede generar inconsistencias en equivalencias y reportes. Úsela bajo su propio riesgo. Para garantizar el correcto funcionamiento del sistema, recomendamos trabajar con las unidades estándar.
    </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'form-um',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validationUrl' => $controller,
        'action' => $controller
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(
        \common\models\UnitOfMeasurement::getTypeOptions(),
        ['prompt' => 'Selecciona el tipo de unidad...']
    ) ?>

    <?php if ($model->isNewRecord): ?>
        <?= $form->field($model, 'custom')->hiddenInput(['value' => 1])->label(false) ?>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?php if ($model->isNewRecord): ?>
            <?= Html::button(Yii::t('app', 'Save'), [
                'class' => 'btn btn-success', 
                'id' => 'save-custom-unit-btn'
            ]) ?>
        <?php else: ?>
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php if ($model->isNewRecord): ?>
<!-- Modal de confirmación para unidades personalizadas -->
<?php Modal::begin([
    'id' => 'custom-unit-warning-modal',
    'title' => '<i class="fas fa-exclamation-triangle text-warning"></i> Mensaje de advertencia',
    'size' => Modal::SIZE_DEFAULT,
    'options' => [
        'data-bs-backdrop' => 'static',
        'data-bs-keyboard' => 'false'
    ]
]); ?>

<div class="modal-body">
    <p><strong>Estás a punto de crear una nueva unidad de medida personalizada.</strong></p>
    <p>Ten en cuenta que agregar unidades fuera del estándar puede generar inconsistencias en equivalencias, inventarios y reportes.</p>
    <p><strong>El uso de unidades personalizadas es bajo tu propio riesgo.</strong></p>
    <p>Para garantizar el correcto funcionamiento del sistema, recomendamos trabajar con las unidades estándar.</p>
</div>

<div class="modal-footer">
    <?= Html::button('Cancelar', [
        'class' => 'btn btn-warning',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= Html::button('Aceptar y crear unidad personalizada', [
        'class' => 'btn btn-secondary',
        'id' => 'confirm-custom-unit-btn'
    ]) ?>
</div>

<?php Modal::end(); ?>

<script>
$(document).ready(function() {
    // Cartel de error personalizado
    var errorAlert = $('<div id="um-duplicate-error" class="alert alert-danger" style="display:none;"></div>');
    $('.unit-of-measurement-form').prepend(errorAlert);

    // Evento del botón "Guardar" inicial
    $('#save-custom-unit-btn').on('click', function(e) {
        e.preventDefault();
        var form = $('#form-um');
        if (form.find('#unitofmeasurement-name').val().trim() === '') {
            form.submit();
            return;
        }
        var modal = new bootstrap.Modal(document.getElementById('custom-unit-warning-modal'));
        modal.show();
    });

    // Evento del botón "Aceptar y crear unidad personalizada"
    $('#confirm-custom-unit-btn').on('click', function() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('custom-unit-warning-modal'));
        modal.hide();

        var form = $('#form-um');
        var formData = form.serialize();
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Recarga la página o cierra el modal principal si lo usas
                    location.reload();
                } else if (response.error) {
                    // Mostrar el cartel de error
                    errorAlert.text(response.error).show();
                }
            },
            error: function() {
                errorAlert.text('Error al procesar la solicitud.').show();
            }
        });
    });
});
</script>

<style>
.alert-warning {
    border-left: 4px solid #f39c12;
}

.modal-title {
    color: #f39c12;
}

#custom-unit-warning-modal .modal-body {
    font-size: 14px;
    line-height: 1.6;
}

#custom-unit-warning-modal .modal-body p:last-child {
    margin-bottom: 0;
}
</style>
<?php endif; ?>
