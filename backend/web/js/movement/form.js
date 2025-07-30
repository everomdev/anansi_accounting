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
    }
      // Calcular el total (amount + tax)
    let total = amountValue + taxValue;
    if (!isNaN(total)) {
        $("#movement-total").val(total.toFixed(2));
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
