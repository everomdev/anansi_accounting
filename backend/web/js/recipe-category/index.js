$(document).on('click', "#create-recipe-category, .update-recipe-category", function (event) {
    event.preventDefault();
    let url = $(this).attr('href');

    $.ajax({
        url,
        method: 'get'
    }).done(function (response) {
        $("#form-recipe-category-container").html(response);
        $("#modal-form-recipe-category").modal("show");
    })
    return false;
})
