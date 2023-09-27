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

$(document).on('show.bs.modal', '#modal-balance', function (event) {
   fetchBalances();

});

function fetchBalances(){
    const url = $("#modal-balance").data('url');

    $.ajax({
        url,
        type: 'get'
    }).done(function (response) {
        $("#balance-container").html(response);
    })
}

$(document).on('beforeSubmit', '#form-balance', function (event) {
    event.preventDefault();
    let _this = $(this);
    let url = _this.attr('action');
    let data = _this.serializeArray();

    $.ajax({
        url,
        type: 'post',
        data
    }).done(function (response) {
        if (response.success) {
            fetchBalances();
        } else {
            let errors = response.errors.join('\n');
            alert(errors);
        }
    })
    return false;
});
