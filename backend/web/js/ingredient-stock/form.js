$(document).on('click', "#btn-compute-yield", function (event) {
    event.preventDefault();
    let finalQuantity = $("#final-quantity").val();
    let quantity = $("#ingredientstock-quantity").val();
    let yield = (Number.parseFloat(finalQuantity) / Number.parseFloat(quantity)).toFixed(2) * 100;
    $("#ingredientstock-yield").val(isNaN(yield) ? 0 : yield);
    $("#modal-yield").modal('hide');
    $("#ingredientstock-final_quantity").val(finalQuantity);

    return false;
})

$(document).on('click', "#compute-yield", function (event) {
    event.preventDefault();
    $("#modal-yield").modal('show');
    return false;
})

$(document).on('change', '#ingredientstock-category_id', function (event) {
    let url = $(this).data('url');
    let id = $(this).val();
    url = `${url}?categoryId=${id}`;
    $.ajax({
        url,
        type: 'get'
    }).done(function (response) {
        let key = $("#ingredientstock-key");
        let value = key.val();
        if (value !== '' || value !== undefined) {
            key.val(response);
        }
    })
})
