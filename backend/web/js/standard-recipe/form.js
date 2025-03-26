$(document).ready(function () {
    function drawArrows() {
        $(".arrow").remove(); // Eliminar flechas previas
        let currentRow = 0;
        let rowTop = 0;
        let rowNodes = [];

        $(".node").each(function (index) {
            if (index > 0 && $(this).position().top > rowTop) {
                // Nueva fila de nodos
                if (currentRow % 2 === 1) {
                    rowNodes.reverse(); // Invertir el orden de los nodos
                }
                positionNodesInRow(rowNodes);
                rowNodes = [];
                currentRow++;
                rowTop = $(this).position().top;
            }
            rowNodes.push(this);
        });

        // Posicionar nodos en la última fila
        if (currentRow % 2 === 1) {
            rowNodes.reverse(); // Invertir el orden de los nodos
        }
        positionNodesInRow(rowNodes);
    }

    function positionNodesInRow(nodes) {
        if (nodes.length <= 1) return;

        let prevNode = $(nodes[0]);
        for (let i = 1; i < nodes.length; i++) {
            const currentNode = $(nodes[i]);
            const nodeLeft = prevNode.position().left + prevNode.outerWidth();
            const nodeTop = currentNode.position().top;
            const arrow = $("<div class='arrow'></div>").appendTo(".diagram");
            arrow.css("left", nodeLeft);
            arrow.css("top", nodeTop + currentNode.outerHeight() / 2);
            arrow.width(currentNode.position().left - nodeLeft);
            prevNode = currentNode;
        }
    }

    drawArrows(); // Dibujar flechas iniciales

    // Vuelve a dibujar las flechas cuando se cambie el tamaño de la ventana
    $(window).resize(function () {
        drawArrows();
    });

    let cost = $("#standardrecipe-custom_cost").val();
    $("#ingredients-selection-total-cost").data('value', cost);
    $("#ingredients-selection-total-cost").html(Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(cost));

    computeCost();
});
$(document).on('change', '#standardrecipe-type_of_recipe', function (event) {
    console.log(createNewCategoryUrl);
    let value = $(this).val();
    if (value == 'add') {
        window.location.href = createNewCategoryUrl;
    }
})
$(document).on("filebeforedelete", "#stepsImagesInput", function (event, key, data) {
    var aborted = !window.confirm('Are you sure you want to delete this image?');
    return aborted;
});

$(document).on("filedeleted", "#stepsImagesInput", function (event, key, data) {
    var aborted = !window.confirm('Are you sure you want to delete this image?');
    return aborted;
});

$(document).on('beforeSubmit', "#form_ingredient", function (event) {
    event.preventDefault();
    const _form = $(this);
    let data = _form.serializeArray();
    let url = _form.attr('action');
    let method = _form.attr('method');

    $.ajax({
        url,
        data,
        type: method
    }).done(function (response) {
        $.pjax.reload({container: "#pjax-ingredients-selection"});
        $("#standardrecipe-price").trigger('change');
    })

    return false;
});
$(document).on('pjax:complete', "#pjax-ingredients-selection", (event) => {
    $("#modal-add-ingredient").modal('hide');
    $(".modal-backdrop").remove();
    $('body').removeAttr('style');
    $('body').removeAttr('class');
    computeCost();
})

$(document).on('change', '#standardrecipe-yield, #standardrecipe-portions', function (event) {
    computeCost();
})

function computeCost() {
    let totalCost = 0;
    console.log("--- INICIO CÁLCULO DE COSTOS ---");

    $('table tbody tr').each(function(index) {
        if ($(this).find('.exclude-checkbox').length > 0) {
            let ingredientName = $(this).find('td:nth-child(2)').text().trim();
            let costText = $(this).find('td:nth-child(4)').text().trim();
            let cost = parseFloat(costText.replace(/[^\d,.-]/g, '').replace(',', '.'));
            let isExcluded = $(this).find('.exclude-checkbox').is(':checked');
            let discountPercentage = parseInt($(this).find('.cost-percentage').val(), 10);
            
            console.log(`Ingrediente #${index + 1}: ${ingredientName}`);
            console.log(`- Costo base: ${cost}`);
            console.log(`- Excluido: ${isExcluded}`);
            console.log(`- Porcentaje de descuento: ${discountPercentage}%`);

            if (isExcluded) {
                if (discountPercentage > 0) {
                    // Si está excluido Y tiene descuento: aplicar descuento y sumar ese valor
                    let discountedCost = cost * (discountPercentage / 100);
                    totalCost += discountedCost;
                    console.log(`- INGREDIENTE EXCLUIDO: Aplicado ${discountPercentage}% → ${discountedCost} añadido`);
                } else {
                    // Si está excluido SIN descuento: no se suma nada
                    console.log(`- INGREDIENTE EXCLUIDO SIN DESCUENTO (no se suma)`);
                }
            } else {
                // Si NO está excluido: sumar costo completo
                totalCost += cost;
                console.log(`- Costo incluido (completo): ${cost}`);
            }
        }
    });

    console.log(`Costo total antes de porciones: ${totalCost}`);

    // Resto del cálculo (porciones, yield, formato)
    let portions = parseFloat($("#standardrecipe-portions").val()) || 1;
    let _yield = parseFloat($("#standardrecipe-yield").val()) || 1;
    
    if (!isNaN(totalCost)) {
        let costPerPortion = portions > 0 && _yield > 0 ? totalCost / _yield / portions : totalCost;
        
        // Actualizar la interfaz
        $("#ingredients-selection-total-cost").data('total', costPerPortion.toFixed(2));
        $("#standardrecipe-custom_cost").val(costPerPortion.toFixed(2));
        
        let formattedCost = Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency
        }).format(costPerPortion.toFixed(2));
        
        $("#ingredients-selection-total-cost").html(formattedCost);
        
        if ($("#cost-value").length > 0) {
            $("#cost-value").html(formattedCost);
        }
    }

    console.log("--- FIN CÁLCULO DE COSTOS ---");
}

// Event listeners para actualización automática
$(document).on('change', '.exclude-checkbox, .discount-percentage, .cost-percentage', computeCost);

$(document).on('show.bs.modal', "#modal-add-ingredient", (event) => {

    $.ajax({
        url: formUrl,
        type: 'get'
    }).done((response) => {
        $("#container-form-ingredient").html(response);
    })
})

$(document).on('click', '.delete-ingredient, .delete', function (event) {
    event.preventDefault();
    const _this = $(this);
    let url = _this.attr('href');
    let message = _this.data('confirm-message');
    let pjax = _this.data('pjax');
    if (confirm(message)) {
        $.ajax({
            url,
            type: 'post'
        }).done((response) => {
            $.pjax.reload({container: pjax});
        })
    }
    return false;
})

$(document).on('beforeSubmit', "#form_step", function (event) {
    event.preventDefault();
    const _form = $(this);
    let data = _form.serializeArray();
    let url = _form.attr('action');
    let method = _form.attr('method');
    let pjax = _form.data('pjax');
    $.ajax({
        url,
        data,
        type: method
    }).done(function (response) {
        console.log(response);
        $.pjax.reload({container: pjax});
    })

    return false;
});
$(document).on('pjax:complete', "#pjax-list-steps, #pjax-list-special-steps", (event) => {
    $("#modal-add-step").modal('hide');
    $(".modal-backdrop").remove();

})

$(document).on('hidden.bs.modal', "#modal-add-step, #modal-add-special-step, #modal-add-ingredient", (event) => {
    $('body').attr('style', '');
})

$(document).on('change', "#standardrecipe-price", (event) => {
    let cost = $("#cost-value").data('price');

    let price = $("#standardrecipe-price").val();
    let costPercent = Number.parseFloat((cost / price) * 100).toFixed(0);

    if (!isNaN(costPercent)) {
        $("#cost-percent").html(`${costPercent} %`);
    }
});

$(document).on('click', '.update-ingredient', function (event) {
    event.preventDefault();
    let url = $(this).attr('href');
    let quantity = $(this).data('current');

    $("#btn-update-ingredient").data('url', url);
    $("#ingredient-update-quantity").val(quantity);

    $("#modal-update-ingredient").modal('show');

    return false;
});

$(document).on('click', '#btn-update-ingredient', function (event) {
    event.preventDefault();
    let url = $(this).data('url');
    let quantity = $("#ingredient-update-quantity").val();

    $.ajax({
        url,
        type: 'post',
        data: {quantity}
    }).done((response) => {
        $.pjax.reload({container: "#pjax-ingredients-selection"});
        $("#standardrecipe-price").trigger('change');
        $("#modal-update-ingredient").modal('hide');
    })


    return false;
})

$(document).on('hidden.bs.modal', "#modal-update-ingredient", function (event) {
    $("#ingredient-update-quantity").val('');
    $("#btn-update-ingredient").removeAttr('data-url');
});

$(document).on('change', ".allergen-checkbox", function (event) {
    let input = $(this);
    let label = $(`label[for='${input.attr('id')}']`);

    let allergen = label.text();
    selectUnselectAllergies(allergen);

})

$(document).on('keyup', "#allergies-other", function (event) {
    if(event.keyCode !== 186){
        return;
    }
    let input = $(this);
    let value = input.val();
    if(value.indexOf(";") === value.length - 1){
        value = value.slice(0, -1);
        addAllergiesOption(value);
        selectUnselectAllergies(value);
        input.val('');
    }
})
function selectUnselectAllergies(allergen) {
    let selectedAllergies = $("#standardrecipe-allergies").val();
    if (selectedAllergies) {
        selectedAllergies = selectedAllergies.split(';');
    } else {
        selectedAllergies = [];
    }

    if (selectedAllergies.indexOf(allergen) === -1) {
        selectedAllergies.push(allergen);
    } else {
        selectedAllergies = selectedAllergies.filter(a => a !== allergen);
    }

    $("#standardrecipe-allergies").val(selectedAllergies.join(';'));
}

function addAllergiesOption(option){
    let wrapper = document.createElement('div');
    wrapper.className = 'form-check';
    let input = document.createElement('input');
    input.type = 'checkbox';
    input.className = 'form-check-input allergen-checkbox';
    input.name = '_allergies[]';
    input.checked = true;
    input.id = `allergen-${option}`;

    let label = document.createElement('label');
    label.className = 'form-check-label';
    label.htmlFor = `allergen-${option}`;
    label.innerHTML = option;

    wrapper.appendChild(input);
    wrapper.appendChild(label);

    let list = document.getElementById('allergies-list');
    list.appendChild(wrapper);
}

document.addEventListener('DOMContentLoaded', function() {
    const yieldUmField = document.getElementById('standardrecipe-yield_um');
    const portionsContainer = document.getElementById('portions-container');
    const portionsField = document.getElementById('standardrecipe-portions');
    const yieldField = document.getElementById('standardrecipe-yield');
    
    // Lista de unidades discretas que requieren números enteros ≥ 1
    const discreteUnits = ['porción', 'porcion', 'porciones', 'rebanada', 'rebanadas', 'pieza', 'piezas', 'trozo', 'trozos', 'unidad', 'unidades'];
    
    // Función para verificar si la unidad seleccionada es discreta
    function isDiscreteUnit(unit) {
        return discreteUnits.some(discrete => unit.toLowerCase().includes(discrete.toLowerCase()));
    }
    
    // Función para validar el valor en caso de unidades discretas
    function validateYieldValue() {
        if (isDiscreteUnit(yieldUmField.value)) {
            const value = parseFloat(yieldField.value);
            
            // Buscar o crear el contenedor de mensajes de error
            let errorContainer = document.getElementById('yield-error-container');
            if (!errorContainer) {
                errorContainer = document.createElement('div');
                errorContainer.id = 'yield-error-container';
                errorContainer.className = 'invalid-feedback';
                yieldField.parentNode.appendChild(errorContainer);
            }
            
            // Verificar si el valor es un número entero y mayor o igual a 1
            if (isNaN(value) || value % 1 !== 0 || value < 1) {
                // Aplicar estilo de error
                yieldField.classList.add('is-invalid');
                errorContainer.textContent = `Por favor asegúrese que el número es correcto para la unidad de medida ${yieldUmField.value}. Debe ser un número entero mayor o igual a 1.`;
                errorContainer.style.display = 'block';
                
                // Opcional: aplicar un fondo rosa pastel para destacar el error
                yieldField.style.backgroundColor = '#ffebee';
            } else {
                // Quitar estilo de error
                yieldField.classList.remove('is-invalid');
                errorContainer.style.display = 'none';
                yieldField.style.backgroundColor = '';
            }
        } else {
            // Para unidades no discretas, eliminar cualquier mensaje de error
            yieldField.classList.remove('is-invalid');
            const errorContainer = document.getElementById('yield-error-container');
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
            yieldField.style.backgroundColor = '';
        }
    }
    
    function checkYieldUnit() {
        if (isDiscreteUnit(yieldUmField.value)) {
            portionsContainer.style.display = 'none';
            portionsField.value = '1';
        } else {
            portionsContainer.style.display = '';
        }
        
        // Validar el valor de yield según la unidad seleccionada
        validateYieldValue();
        
        // Llamar al método computeCost cuando cambie la unidad de medida
        computeCost();
    }
    
    // Verificar inicial
    checkYieldUnit();
    
    // Verificar en cambios de unidad
    yieldUmField.addEventListener('change', checkYieldUnit);
    
    // Verificar también cuando cambie el valor de yield
    yieldField.addEventListener('input', validateYieldValue);
    
    // Validar antes de enviar el formulario
    document.getElementById('form-recipe').addEventListener('submit', function(event) {
        validateYieldValue();
        
        // Opcional: evitar envío si hay error
        // Si quieres bloquear el envío del formulario cuando hay errores, descomenta estas líneas:
        /*
        if (yieldField.classList.contains('is-invalid')) {
            event.preventDefault();
            // Desplazarse al campo con error para que el usuario lo vea
            yieldField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        */
    });
});