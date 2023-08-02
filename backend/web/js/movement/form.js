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

}

function applyOutputSettings(form) {
    $(form).find('*[disabled]').removeAttr('disabled');
    $(form).find("*[data-setting='input']").attr('disabled', true);
    $(form).find("*[data-setting='input']").val('');
}

function applyOrderSettings(form) {
    $(form).find('*[disabled]').removeAttr('disabled');
    $(form).find("*[data-setting='input']").attr('disabled', true);
    $(form).find("*[data-setting='input']").val('');
}
