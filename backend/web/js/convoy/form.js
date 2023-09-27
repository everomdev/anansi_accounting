$(document).on('beforeSubmit', '#form-add-ingredient', function (event) {
    event.preventDefault();
    const _this = $(this);
    const data = _this.serializeArray();
    const url = _this.attr('action');
    const type = _this.attr('method');

    $.ajax({
        url,
        data,
        type
    }).done(function (response) {
        $("#modal-add-ingredient").modal('hide');
        _this[0].reset;
        $.pjax.reload({container: "#pjax-ingredients"});
        $(".modal-backdrop").remove();
    })
    return false;
})

$(document).on('click', '.remove', function (event) {
    event.preventDefault();
    let url = $(this).data('url');
    let message = $(this).data('message');

    if (confirm(message)) {
        $.ajax({
            url
        }).done(function (response) {
            $.pjax.reload({container: "#pjax-ingredients"});
        })
    }
    return false;
})
