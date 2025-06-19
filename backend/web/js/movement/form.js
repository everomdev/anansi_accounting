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
    let providerName = $(this).val();
    let paymentTypeSelect = $('#movement-payment-type');
    let providersData = $(this).data('providers');
    
    if (providerName && providersData) {
        let providerId = providersData[providerName];
        
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
                    // Limpiar las opciones actuales y habilitar el select
                    paymentTypeSelect.empty();
                    paymentTypeSelect.prop('disabled', false);
                    paymentTypeSelect.append('<option value="">Seleccionar Tipo de Pago</option>');
                    
                    // Agregar las nuevas opciones
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
                    // En caso de error, habilitar el select y mostrar todos los tipos de pago
                    paymentTypeSelect.prop('disabled', false);
                    resetPaymentTypes();
                }
            });
        }
    } else {
        // Si no hay proveedor seleccionado, mostrar todos los tipos de pago
        resetPaymentTypes();
    }
});

// Función para resetear los tipos de pago a la lista completa
function resetPaymentTypes() {
    let paymentTypeSelect = $('#movement-payment-type');
    
    // Mostrar indicador de carga
    paymentTypeSelect.empty();
    paymentTypeSelect.append('<option value="">Cargando tipos de pago...</option>');
    paymentTypeSelect.prop('disabled', true);
    
    // Hacer petición AJAX para obtener todos los tipos de pago específicos
    $.ajax({
        url: getProviderPaymentTypesUrl,
        type: 'GET',
        data: {
            providerId: null // Sin proveedor para obtener todos los tipos
        },
        dataType: 'json',
        success: function(response) {
            // Limpiar y habilitar el select
            paymentTypeSelect.empty();
            paymentTypeSelect.prop('disabled', false);
            paymentTypeSelect.append('<option value="">Seleccionar Tipo de Pago</option>');
            
            if (response.success && response.paymentTypes) {
                $.each(response.paymentTypes, function(key, value) {
                    paymentTypeSelect.append('<option value="' + key + '">' + value + '</option>');
                });
            }
        },
        error: function() {
            console.error('Error al obtener todos los tipos de pago');
            // En caso de error, al menos habilitar el select
            paymentTypeSelect.empty();
            paymentTypeSelect.prop('disabled', false);
            paymentTypeSelect.append('<option value="">Error al cargar tipos de pago</option>');
        }
    });
}
