$(document).on('change', "#final-quantity, #initial-quantity", function (event) {
    $("#ingredientstock-yield").val(80);
    event.preventDefault();
    let finalQuantity = $("#final-quantity").val();
    let quantity = $("#initial-quantity").val();
    let yield = (Number.parseFloat(finalQuantity) / Number.parseFloat(quantity)) * 100;
    yield = isNaN(yield) ? 0 : yield.toFixed(2);
    $("#yield-result").text(`${yield}%`);
    $("#ingredientstock-yield").val(yield);

    $("#ingredientstock-final_quantity").val(finalQuantity);
    $("#ingredientstock-quantity").val(quantity);
    $("#ingredientstock-final_quantity").trigger('change');
    $("#ingredientstock-quantity").trigger('change');
    $("#ingredientstock-yield").trigger('change');

    return false;
});

$(document).on('click', "#btn-compute-yield", function (event) {
    $('#modal-yield').modal('hide');
});

$(document).on('click', "#compute-yield", function (event) {
    event.preventDefault();
    let finalQuantity = $("#ingredientstock-final_quantity").val();
    let quantity = $("#ingredientstock-quantity").val();
    let yield = $("#ingredientstock-yield").val();
    $("#final-quantity").val(finalQuantity);
    $("#initial-quantity").val(quantity);
    $("#yield-result").text(`${yield}%`);
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

$(document).on('change', '#ingredientstock-price, #ingredientstock-portions_per_unit, #ingredientstock-yield', function (event) {
    let price = $("#ingredientstock-price").val();
    let portions = $("#ingredientstock-portions_per_unit").val();
    let yield = $("#ingredientstock-yield").val();
    if (parseFloat(price) > 0 && parseFloat(portions) > 0 && parseFloat(yield) > 0) {
        let adjustedPrice = (parseFloat(price) / parseFloat(portions)) / (parseFloat(yield) / 100);
        $("#ingredientstock-adjustedprice").val(adjustedPrice.toFixed(2));
    }
});
