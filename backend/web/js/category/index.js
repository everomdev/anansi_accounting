$(document).on('click', "#create-category, .update-category", function (event) {
    event.preventDefault();
    let url = $(this).attr('href');

    $.ajax({
        url,
        method: 'get'
    }).done(function (response) {
        $("#form-category-container").html(response);
        $("#modal-form-category").modal("show");
    })
    return false;
})
