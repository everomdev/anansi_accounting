$(document).on('click', "#compute-yield", function (event) {
    event.preventDefault();
    let finalQuantity = $("#ingredientstock-final_quantity").val();
    let quantity = $("#ingredientstock-quantity").val();
    let yield = (Number.parseFloat(finalQuantity) / Number.parseFloat(quantity)).toFixed(2) * 100;
    console.log(finalQuantity, quantity, yield);
    $("#ingredientstock-yield").val(isNaN(yield) ? 0 : yield);
    return false;
})
