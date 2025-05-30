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
