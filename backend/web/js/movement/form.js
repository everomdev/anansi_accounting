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

$(document).on('change', '#movement-amount, #movement-quantity', function (event) {
    let amount = $("#movement-amount").val();
    let quantity = $("#movement-quantity").val();

    let unitPrice = Number.parseFloat(amount) / Number.parseFloat(quantity);
    if (!isNaN(unitPrice)) {
        $("#movement-unit_price").val(unitPrice);
    }
})
