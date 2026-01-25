$(document).on("change", "#movement-type", function (event) {
    event.preventDefault();
    let value = $(this).val();
    let form = $("#movement-form");
    switch (value) {
        case movementTypeInput:
            applyInputSettings(form)
            break;
        case movementTypeOutput:
            applyOutputSettings(form)
            break;
        case movementTypeOrder:
            applyOrderSettings(form)
            break;
    }
    return false;
});

function applyInputSettings(form) {
    $(form).find('*[disabled]').removeAttr('disabled');
    let labelProvider = $(form).find("label[for='movement-provider']");
    let label = labelProvider.data('provider');
    labelProvider.html(label);
}

function applyOutputSettings(form) {
    $(form).find('*[disabled]').removeAttr('disabled');
    $(form).find("*[data-setting='input']").attr('disabled', true);
    $(form).find("*[data-setting='input']").val('');
    let labelProvider = $(form).find("label[for='movement-provider']");
    let label = labelProvider.data('cost-center');
    labelProvider.html(label);
}

function applyOrderSettings(form) {

    $(form).find('*[disabled]').removeAttr('disabled');
    $(form).find("*[data-setting='input']").attr('disabled', true);
    $(form).find("*[data-setting='input']").val('');
    let labelProvider = $(form).find("label[for='movement-provider']");
    let label = labelProvider.data('provider');
    labelProvider.html(label);
}

$(document).on('change', '#movement-amount, #movement-quantity, #movement-tax', function (event) {
    let amount = $("#movement-amount").val();
    let quantity = $("#movement-quantity").val();
    let tax = $("#movement-tax").val();
    
    // Función para validar si un valor es un número válido
    function isValidNumber(value) {
        if (value === '' || value === null || value === undefined) return true; // Permitir vacío
        return !isNaN(value) && !isNaN(parseFloat(value)) && isFinite(value);
    }
    
    // Función para mostrar error en un campo
    function showFieldError(fieldId, message) {
        let field = $(fieldId);
        let fieldGroup = field.closest('.form-group');
        
        // Remover errores anteriores
        fieldGroup.find('.invalid-feedback').remove();
        field.removeClass('is-invalid');
        
        // Agregar nuevo error
        field.addClass('is-invalid');
        field.after('<div class="invalid-feedback">' + message + '</div>');
    }
    
    // Función para limpiar errores de un campo
    function clearFieldError(fieldId) {
        let field = $(fieldId);
        let fieldGroup = field.closest('.form-group');
        
        fieldGroup.find('.invalid-feedback').remove();
        field.removeClass('is-invalid');
    }
    
    // Validar cada campo
    let hasErrors = false;
    
    if (amount !== '' && !isValidNumber(amount)) {
        showFieldError('#movement-amount', 'El precio de compra debe ser un número válido');
        hasErrors = true;
    } else {
        clearFieldError('#movement-amount');
    }
    
    if (quantity !== '' && !isValidNumber(quantity)) {
        showFieldError('#movement-quantity', 'La cantidad debe ser un número válido');
        hasErrors = true;
    } else {
        clearFieldError('#movement-quantity');
    }
    
    if (tax !== '' && !isValidNumber(tax)) {
        showFieldError('#movement-tax', 'El impuesto debe ser un número válido');
        hasErrors = true;
    } else {
        clearFieldError('#movement-tax');
    }
    
    // Solo hacer cálculos si no hay errores
    if (!hasErrors) {
        // Parsear los valores, usando 0 como valor por defecto si están vacíos
        let amountValue = Number.parseFloat(amount) || 0;
        let quantityValue = Number.parseFloat(quantity) || 0;
        let taxValue = Number.parseFloat(tax) || 0;

        // Calcular el precio unitario sumando el impuesto al monto total
        if (quantityValue > 0) {
            let totalWithTax = amountValue + taxValue;
            let unitPrice = totalWithTax / quantityValue;
            
            if (!isNaN(unitPrice)) {
                $("#movement-unit_price").val(unitPrice.toFixed(2));
            }
        } else {
            // Si no hay cantidad, limpiar precio unitario
            $("#movement-unit_price").val('');
        }
        
        // Calcular el total (amount + tax)
        let total = amountValue + taxValue;
        if (!isNaN(total) && total >= 0) {
            $("#movement-total").val(total.toFixed(2));
        }
    } else {
        // Si hay errores, limpiar los campos calculados
        $("#movement-unit_price").val('');
        $("#movement-total").val('');
    }
})

// Validación en tiempo real mientras se escribe
$(document).on('input', '#movement-amount, #movement-quantity, #movement-tax', function (event) {
    let fieldId = '#' + $(this).attr('id');
    let value = $(this).val();
    
    // Función para validar si un valor es un número válido
    function isValidNumber(value) {
        if (value === '' || value === null || value === undefined) return true; // Permitir vacío
        return !isNaN(value) && !isNaN(parseFloat(value)) && isFinite(value);
    }
    
    // Función para mostrar error en un campo
    function showFieldError(fieldId, message) {
        let field = $(fieldId);
        let fieldGroup = field.closest('.form-group');
        
        // Remover errores anteriores
        fieldGroup.find('.invalid-feedback').remove();
        field.removeClass('is-invalid');
        
        // Agregar nuevo error si el valor no está vacío
        if (value !== '') {
            field.addClass('is-invalid');
            field.after('<div class="invalid-feedback">' + message + '</div>');
        }
    }
    
    // Función para limpiar errores de un campo
    function clearFieldError(fieldId) {
        let field = $(fieldId);
        let fieldGroup = field.closest('.form-group');
        
        fieldGroup.find('.invalid-feedback').remove();
        field.removeClass('is-invalid');
    }
    
    // Validar el campo actual
    if (value !== '' && !isValidNumber(value)) {
        let fieldName = $(this).attr('id').replace('movement-', '');
        let message = '';
        
        switch (fieldName) {
            case 'amount':
                message = 'El precio de compra debe ser un número válido';
                break;
            case 'quantity':
                message = 'La cantidad debe ser un número válido';
                break;
            case 'tax':
                message = 'El impuesto debe ser un número válido';
                break;
            default:
                message = 'Este campo debe ser un número válido';
        }
        
        showFieldError(fieldId, message);
    } else {
        clearFieldError(fieldId);
    }
})

// Manejar el cambio de proveedor para actualizar tipos de pago
$(document).on('change', '#movement-provider', function() {
    let providerKey = $(this).val(); // Esto será "business_name_id"
    let paymentTypeSelect = $('#movement-payment-type');
    let providersData = $(this).data('providers');
    
    if (providerKey && providersData) {
        let providerId = providersData[providerKey];
        if (providerId) {
            // Mostrar indicador de carga
            paymentTypeSelect.empty();
            paymentTypeSelect.append('<option value="">Cargando tipos de pago...</option>');
            paymentTypeSelect.prop('disabled', true);
            // Hacer petición AJAX para obtener los tipos de pago del proveedor
            $.ajax({
                url: getProviderPaymentTypesUrl,
                type: 'GET',
                data: {
                    providerId: providerId
                },
                dataType: 'json',
                success: function(response) {
                    paymentTypeSelect.empty();
                    paymentTypeSelect.prop('disabled', false);
                    paymentTypeSelect.append('<option value="">Seleccionar Tipo de Pago</option>');
                    if (response.success && response.paymentTypes) {
                        $.each(response.paymentTypes, function(key, value) {
                            paymentTypeSelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                    } else {
                        paymentTypeSelect.append('<option value="">No hay tipos de pago disponibles</option>');
                    }
                },
                error: function() {
                    console.error('Error al obtener los tipos de pago del proveedor');
                    paymentTypeSelect.prop('disabled', false);
                    paymentTypeSelect.empty();
                    paymentTypeSelect.append('<option value="">Seleccionar Tipo de Pago</option>');
                }
            });
        }
    } else {
        // Si no hay proveedor seleccionado, mostrar solo 'Por definir' y deshabilitar el select
        paymentTypeSelect.empty();
        paymentTypeSelect.append('<option value="">Por definir</option>');
        paymentTypeSelect.prop('disabled', true);
    }
});

// Función para resetear los tipos de pago a la lista completa
function resetPaymentTypes() {
    let paymentTypeSelect = $('#movement-payment-type');
    
    // Si no hay proveedor, mostrar solo 'Por definir' y deshabilitar el select
    paymentTypeSelect.empty();
    paymentTypeSelect.append('<option value="">Por definir</option>');
    paymentTypeSelect.prop('disabled', true);
    // Si quieres mantener la lógica AJAX para otros casos, puedes agregarla aquí solo si hay proveedor
    // Por ahora, solo resetea el select a "Por definir" y lo deshabilita.
}

// Convertir el valor del proveedor antes de enviar el formulario
// IMPORTANTE: El campo 'provider' en la BD almacena el business_name del proveedor
// ya que business_name es obligatorio mientras que name es opcional
$(document).on('submit', '#movement-form', function(e) {
    let providerSelect = $('#movement-provider');
    let selectedKey = providerSelect.val();
    
    if (selectedKey && window.providerKeyToBusinessName && window.providerKeyToBusinessName[selectedKey]) {
        let realBusinessName = window.providerKeyToBusinessName[selectedKey];
        
        // Crear o actualizar un campo oculto con el valor real (business_name)
        let hiddenProviderField = $('#movement-provider-real');
        if (hiddenProviderField.length === 0) {
            // Si no existe, crear el campo oculto
            $('<input type="hidden" id="movement-provider-real" name="Movement[provider]">').insertAfter(providerSelect);
        }
        
        // Asignar el business_name real al campo oculto
        $('#movement-provider-real').val(realBusinessName);
        
        // Deshabilitar el select original para que no se envíe
        providerSelect.prop('disabled', true);
    } else {
        // Si no hay conversión, usar el valor original
        $('#movement-provider-real').remove();
        providerSelect.prop('disabled', false);
    }
    
    // Permitir el envío del formulario
    return true;
});

// Actualizar la unidad de medida cuando se selecciona un ingrediente (para entradas y salidas)
$(document).on('select2:select', '#movement-ingredient_id', function(e) {
    var movementType = (typeof currentMovementType !== 'undefined') ? currentMovementType : $('#movement-type').val();
    var selectedData = e.params.data;
    var selectedText = selectedData.text;
    
    // Extraer la unidad de medida del texto entre paréntesis
    var umMatch = selectedText.match(/\(([^)]+)\)/);
    var um = umMatch ? umMatch[1] : '';
    
    if (movementType === movementTypeOutput) {
        // Para salidas, actualizar el span de unidad de cocina
        $('#ingredient-um-display').text(um);
    } else {
        // Para entradas, actualizar el span de unidad de compra
        $('#ingredient-um-display-input').text(um);
    }
});

// También manejar el cambio normal del select (por si acaso)
$(document).on('change', '#movement-ingredient_id', function() {
    var movementType = (typeof currentMovementType !== 'undefined') ? currentMovementType : $('#movement-type').val();
    var selectedOption = $(this).find('option:selected');
    var selectedText = selectedOption.text();
    
    // Extraer la unidad de medida del texto entre paréntesis
    var umMatch = selectedText.match(/\(([^)]+)\)/);
    var um = umMatch ? umMatch[1] : '';
    
    if (movementType === movementTypeOutput) {
        // Para salidas, actualizar el span de unidad de cocina
        $('#ingredient-um-display').text(um);
    } else {
        // Para entradas, actualizar el span de unidad de compra
        $('#ingredient-um-display-input').text(um);
    }
});

// Función para actualizar la unidad de medida
function updateIngredientUM() {
    var movementType = (typeof currentMovementType !== 'undefined') ? currentMovementType : $('#movement-type').val();
    var ingredientSelect = $('#movement-ingredient_id');
    
    if (ingredientSelect.length > 0) {
        var selectedOption = ingredientSelect.find('option:selected');
        var selectedText = selectedOption.text();
        
        // Extraer la unidad de medida del texto entre paréntesis
        var umMatch = selectedText.match(/\(([^)]+)\)/);
        var um = umMatch ? umMatch[1] : '';
        
        if (movementType === movementTypeOutput) {
            // Para salidas, actualizar el span de unidad de cocina
            $('#ingredient-um-display').text(um);
        } else {
            // Para entradas, actualizar el span de unidad de compra
            $('#ingredient-um-display-input').text(um);
        }
    }
}

// Ejecutar al cargar la página para ingredientes ya seleccionados
$(document).ready(function() {
    updateIngredientUM();
});
