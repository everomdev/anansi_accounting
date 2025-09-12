// Inicializar el formateador de números si está disponible
$(document).ready(function() {
    if (window.BusinessNumberFormatter) {
        window.BusinessNumberFormatter.setupAutoFormatInputs();
    }
    
    // Validación para campos de stock mínimo y máximo
    initStockValidation();
});

// Función para inicializar la validación de stock
function initStockValidation() {
    const minStockField = $('#ingredientstock-min_stock');
    const maxStockField = $('#ingredientstock-max_stock');
    
    if (minStockField.length && maxStockField.length) {
        // Validar cuando cambie el stock mínimo
        minStockField.on('input change', function() {
            validateStockFields();
        });
        
        // Validar cuando cambie el stock máximo
        maxStockField.on('input change', function() {
            validateStockFields();
        });
    }
}

// Función para validar que el stock máximo sea mayor que el mínimo
function validateStockFields() {
    const minStockField = $('#ingredientstock-min_stock');
    const maxStockField = $('#ingredientstock-max_stock');
    const minValue = parseFloat(minStockField.val()) || 0;
    const maxValue = parseFloat(maxStockField.val()) || 0;
    
    // Remover clases previas
    minStockField.removeClass('is-invalid');
    maxStockField.removeClass('is-invalid');
    $('.stock-validation-error').remove();
    
    // Validar si ambos campos tienen valores
    if (minValue > 0 && maxValue > 0 && maxValue <= minValue) {
        maxStockField.addClass('is-invalid');
        
        // Agregar mensaje de error
        const errorMsg = $('<div class="stock-validation-error text-danger mt-1">')
            .text('El stock máximo debe ser mayor que el stock mínimo');
        maxStockField.closest('.form-group').append(errorMsg);
    }
}

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
    // Solo ejecutar si no es el campo de precio (que ya tiene su propio manejo en tiempo real)
    if (event.target.id === 'ingredientstock-price') {
        return; // El formateo en tiempo real ya maneja esto
    }
    
    let priceField = $("#ingredientstock-price")[0];
    let portions = $("#ingredientstock-portions_per_unit").val();
    let yieldValue = $("#ingredientstock-yield").val();
    
    // Usar el valor raw guardado por el formateador automático si está disponible
    let price;
    if (priceField && priceField.hasAttribute('data-raw-value')) {
        price = parseFloat(priceField.getAttribute('data-raw-value'));
    } else {
        let priceValue = $("#ingredientstock-price").val();
        price = window.BusinessNumberFormatter ? 
            window.BusinessNumberFormatter.parseNumber(priceValue) : 
            parseFloat(priceValue);
    }
        
    if (price > 0 && parseFloat(portions) > 0 && parseFloat(yieldValue) > 0) {
        let adjustedPrice = (price / parseFloat(portions)) / (parseFloat(yieldValue) / 100);
        
        // Usar el nuevo formateador para mostrar el resultado
        if (window.BusinessNumberFormatter) {
            $("#ingredientstock-adjustedprice").val(window.BusinessNumberFormatter.formatNumber(adjustedPrice, 2));
            $("#ingredientstock-adjustedprice")[0].setAttribute('data-raw-value', adjustedPrice);
        } else {
            $("#ingredientstock-adjustedprice").val(adjustedPrice.toFixed(2));
        }
    }
});
