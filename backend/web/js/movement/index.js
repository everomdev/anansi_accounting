$(document).on('click', ".movement-details", function (event) {
    event.preventDefault();
    let href = $(this).attr('href')
    $.ajax({
        url: href,
        type: 'get',
    }).done(function (response) {
        $("#container-modal-details-movement").html(response);
        $("#modal-details-movement").modal('show');
    });
    return false;
})
