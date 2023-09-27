$(document).on('click', "#create-um, .update-um", function (event) {
    event.preventDefault();
    let url = $(this).attr('href');

    $.ajax({
        url,
        method: 'get'
    }).done(function (response) {
        $("#form-um-container").html(response);
        $("#modal-form-um").modal("show");
    })
    return false;
})
