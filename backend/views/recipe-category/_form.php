<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap5\Modal;

/* @var $this yii\web\View */
/* @var $model common\models\RecipeCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="recipe-category-form">

    <?php if ($model->isNewRecord): ?>
    <!-- Advertencia inicial para nuevas categorías -->
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong> Advertencia obligatoria:</strong> La creación de categorías personalizadas puede generar inconsistencias en reportes y organización de recetas. Úsela bajo su propio riesgo. Para garantizar el correcto funcionamiento del sistema, recomendamos trabajar con las categorías estándar.
    </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'recipe-category-form',
        'enableAjaxValidation' => true,
    ]); ?>

    <?= $form->field($model, 'type')->dropDownList([
        \common\models\RecipeCategory::TYPE_MAIN => Yii::t('app', 'For recipes'),
        \common\models\RecipeCategory::TYPE_SUB => Yii::t('app', 'For sub-recipes'),
    ]) ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php if ($model->isNewRecord): ?>
        <?= $form->field($model, 'custom')->hiddenInput(['value' => 1])->label(false) ?>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?php if ($model->isNewRecord): ?>
            <?= Html::button(Yii::t('app', 'Save'), [
                'class' => 'btn btn-success', 
                'id' => 'save-custom-category-btn'
            ]) ?>
        <?php else: ?>
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php if ($model->isNewRecord): ?>
<!-- Modal de confirmación para categorías personalizadas -->
<?php Modal::begin([
    'id' => 'custom-category-warning-modal',
    'title' => '<i class="fas fa-exclamation-triangle text-warning"></i> Mensaje de advertencia',
    'size' => Modal::SIZE_DEFAULT,
    'options' => [
        'data-bs-backdrop' => 'static',
        'data-bs-keyboard' => 'false'
    ]
]); ?>

<div class="modal-body">
    <p><strong>Estás a punto de crear una nueva categoría de receta personalizada.</strong></p>
    <p>Ten en cuenta que agregar categorías fuera del estándar puede generar inconsistencias en reportes y organización de recetas.</p>
    <p><strong>El uso de categorías personalizadas es bajo tu propio riesgo.</strong></p>
    <p>Para garantizar el correcto funcionamiento del sistema, recomendamos trabajar con las categorías estándar.</p>
</div>

<div class="modal-footer">
    <?= Html::button('Cancelar', [
        'class' => 'btn btn-warning',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= Html::button('Aceptar y crear categoría personalizada', [
        'class' => 'btn btn-secondary',
        'id' => 'confirm-custom-category-btn'
    ]) ?>
</div>

<?php Modal::end(); ?>

<script>
$(document).ready(function() {
    // Evento del botón "Guardar" inicial
    $('#save-custom-category-btn').on('click', function(e) {
        e.preventDefault();
        
        // Validar el formulario primero
        var form = $('#recipe-category-form');
        if (form.find('#recipecategory-name').val().trim() === '') {
            // Si el nombre está vacío, permitir que la validación normal de Yii se ejecute
            form.submit();
            return;
        }
        
        // Mostrar el modal de advertencia
        var modal = new bootstrap.Modal(document.getElementById('custom-category-warning-modal'));
        modal.show();
    });
    
    // Evento del botón "Aceptar y crear categoría personalizada"
    $('#confirm-custom-category-btn').on('click', function() {
        // Cerrar el modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('custom-category-warning-modal'));
        modal.hide();
        
        // Enviar el formulario
        $('#recipe-category-form').submit();
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

#custom-category-warning-modal .modal-body {
    font-size: 14px;
    line-height: 1.6;
}

#custom-category-warning-modal .modal-body p:last-child {
    margin-bottom: 0;
}
</style>
<?php endif; ?>
