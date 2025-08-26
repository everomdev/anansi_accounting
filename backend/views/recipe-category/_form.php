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
    <!-- Advertencia inicial para nuevas categorías de subrecetas -->
    <div class="alert alert-warning" role="alert" id="subrecipe-warning" style="display: none;">
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
    
    <?= $form->field($model, 'name')->textInput([
        'maxlength' => true,
        'autocomplete' => 'off',
        'placeholder' => $model->isNewRecord ? 'Escribe para ver categorías existentes...' : ''
    ]) ?>

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
    // Debug: Verificar qué IDs están disponibles
    console.log('IDs disponibles:', {
        nameInput: $('#recipe-category-name-input').length,
        nameInputDefault: $('#recipecategory-name').length,
        typeSelect: $('#recipecategory-type').length
    });
    
    // Usar el ID correcto generado por Yii
    var nameInput = $('#recipecategory-name');
    var typeSelect = $('#recipecategory-type');
    
    // Mostrar/ocultar advertencia según el tipo seleccionado
    function toggleWarning() {
        var selectedType = typeSelect.val();
        var warning = $('#subrecipe-warning');
        
        if (selectedType === 'sub') {
            warning.slideDown();
        } else {
            warning.slideUp();
        }
    }
    
    // Verificar tipo inicial al cargar la página
    toggleWarning();
    
    // Actualizar advertencia cuando cambie el tipo
    typeSelect.on('change', function() {
        toggleWarning();
        nameInput.trigger('input'); // También actualizar autocompletado
    });
    
    // Autocompletado inteligente para prevenir categorías duplicadas
    nameInput.on('input', function() {
        var term = $(this).val().trim();
        var type = typeSelect.val();
        
        console.log('Buscando:', term, 'Tipo:', type);
        
        if (term.length >= 2) {
            // Limpiar sugerencias anteriores
            $('.autocomplete-suggestions').remove();
            
            $.ajax({
                url: '<?= \yii\helpers\Url::to(['recipe-category/autocomplete']) ?>',
                data: {
                    term: term,
                    type: type
                },
                dataType: 'json',
                success: function(data) {
                    console.log('Respuesta recibida:', data);
                    if (data.length > 0) {
                        showAutocompleteSuggestions(data, nameInput);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en AJAX:', error);
                }
            });
        } else {
            $('.autocomplete-suggestions').remove();
        }
    });
    
    function showAutocompleteSuggestions(suggestions, inputElement) {
        $('.autocomplete-suggestions').remove();
        
        console.log('Mostrando sugerencias:', suggestions);
        
        var input = inputElement;
        
        // Crear contenedor relativo para el posicionamiento
        var container = input.closest('.form-group, .field-recipecategory-name');
        
        // Asegurar que el contenedor tenga position relative
        if (container.css('position') === 'static') {
            container.css('position', 'relative');
        }
        
        var suggestionBox = $('<div class="autocomplete-suggestions"></div>');
        
        // Posicionamiento absoluto dentro del contenedor relativo
        suggestionBox.css({
            position: 'absolute',
            top: '100%',
            left: '0',
            right: '0',
            'z-index': 9999,
            'background-color': '#ffffff',
            border: '2px solid #007bff',
            'border-radius': '6px',
            'box-shadow': '0 4px 12px rgba(0,0,0,0.2)',
            'max-height': '250px',
            'overflow-y': 'auto',
            'margin-top': '2px'
        });
        
        console.log('Creando', suggestions.length, 'elementos de sugerencia');
        
        $.each(suggestions, function(index, suggestion) {
            // Convertir tipo a texto amigable
            var typeText = suggestion.type === 'main' ? 'Para recetas' : 'Para subrecetas';
            
            var item = $('<div class="autocomplete-item"></div>')
                .html('<strong>' + suggestion.label + '</strong><br><small class="text-muted">Tipo: ' + typeText + '</small>')
                .css({
                    padding: '12px 15px',
                    cursor: 'pointer',
                    'border-bottom': '1px solid #eee',
                    'background-color': '#fff',
                    'transition': 'background-color 0.2s'
                })
                .hover(
                    function() { 
                        $(this).css('background-color', '#e3f2fd'); 
                        console.log('Hover en:', suggestion.label);
                    },
                    function() { $(this).css('background-color', '#fff'); }
                )
                .on('click', function() {
                    console.log('Click en sugerencia:', suggestion.label);
                    showDuplicateWarning(suggestion);
                    suggestionBox.remove();
                });
            
            suggestionBox.append(item);
        });
        
        // Insertar dentro del contenedor del campo
        container.append(suggestionBox);
        
        console.log('Sugerencias agregadas al DOM dentro del contenedor del campo');
        
        // Remover sugerencias al hacer clic fuera
        $(document).on('click.autocomplete', function(e) {
            if (!$(e.target).closest('.autocomplete-suggestions, #recipecategory-name').length) {
                console.log('Click fuera, removiendo sugerencias');
                suggestionBox.remove();
                $(document).off('click.autocomplete');
            }
        });
    }
    
    function showDuplicateWarning(suggestion) {
        var warningHtml = `
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i>
                <strong>Categoría existente:</strong> La categoría "${suggestion.label}" ya existe en el sistema.
                <br><small>Para evitar duplicados, considera usar la categoría existente en lugar de crear una nueva.</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remover alertas anteriores
        $('.recipe-category-form .alert-info').remove();
        
        // Agregar la nueva alerta después del campo de nombre
        $('#recipecategory-name').closest('.field-recipecategory-name').after(warningHtml);
        
        // Auto-remover después de 5 segundos
        setTimeout(function() {
            $('.recipe-category-form .alert-info').fadeOut();
        }, 5000);
    }

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

/* Estilos para autocompletado - Posicionamiento correcto */
.autocomplete-suggestions {
    font-family: inherit !important;
    font-size: 14px !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: 0 !important;
    margin-top: 2px !important;
    border: 2px solid #007bff !important;
    background: white !important;
    z-index: 9999 !important;
    border-radius: 6px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
}

.autocomplete-item {
    transition: background-color 0.2s ease !important;
    display: block !important;
}

.autocomplete-item:last-of-type {
    border-bottom: none !important;
}

.autocomplete-item:hover {
    background-color: #e3f2fd !important;
}

/* Asegurar que el contenedor del campo tenga position relative */
.field-recipecategory-name {
    position: relative !important;
}

.alert-info {
    border-left: 4px solid #17a2b8;
    animation: slideDown 0.3s ease-in-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mejorar la apariencia del input durante autocompletado */
#recipecategory-name:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
}
</style>
<?php endif; ?>
