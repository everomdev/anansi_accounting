$(document).on('change', '#plate_sales', function (event) {
    let value = $(this).val();
    let url = $(this).data('url');
    $.ajax({
        url,
        type: 'post',
        data: {
            plate_sales: value
        }
    })
})
