// --- Convoy cost integration ---
// Precargar los montos de convoy desde una variable global (inyectada desde PHP)
let convoyAmounts = window.convoyAmounts || {};

$(document).ready(function () {
    // Si existe el selector de convoy, actualizar el costo al cambiar
    const convoySelect = $("#standardrecipe-convoy_id");
    if (convoySelect.length > 0) {
        convoySelect.on('change', function() {
            // Forzar recálculo tras un pequeño delay para asegurar que el valor esté actualizado
            setTimeout(computeCost, 10);
        });
    }
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



    // Forzar recálculo inicial del costo (por si hay convoy seleccionado al cargar)
    setTimeout(computeCost, 50);
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
    // Ocultar el modal y limpiar backdrop y clases del body
    $("#modal-add-ingredient").modal('hide');
    setTimeout(function() {
        $(".modal-backdrop").remove();
        $('body').removeClass('modal-open').removeAttr('style');
    }, 100);
    computeCost();
});

// Refuerzo: limpiar backdrop y clases del body al cerrar cualquier modal de ingredientes
$(document).on('hidden.bs.modal', "#modal-add-ingredient", function (event) {
    setTimeout(function() {
        $(".modal-backdrop").remove();
        $('body').removeClass('modal-open').removeAttr('style');
    }, 100);
});

$(document).on('change', '#standardrecipe-yield, #standardrecipe-portions', function (event) {
    computeCost();
})

function computeCost() {
    let totalCost = 0;
    console.log('--- computeCost called ---');

    $('table tbody tr').each(function(index) {
        if ($(this).find('.exclude-checkbox').length > 0) {
            let ingredientName = $(this).find('td:nth-child(2)').text().trim();
            let costElement = $(this).find('.ingredient-cost, .subrecipe-cost');
            let costText = costElement.length > 0 ? costElement.text().trim() : $(this).find('td:nth-child(4)').text().trim();
            let cost = parseUserNumber(costText);
            let isExcluded = $(this).find('.exclude-checkbox').is(':checked');
            let discountPercentage = parseInt($(this).find('.cost-percentage').val(), 10);
            console.log(`[Row ${index}] Ingredient: ${ingredientName}, Cost: ${cost}, Excluded: ${isExcluded}, Discount: ${discountPercentage}`);
            if (isExcluded) {
                if (discountPercentage > 0) {
                    let discountedCost = cost * (discountPercentage / 100);
                    totalCost += discountedCost;
                    console.log(`  -> Excluded with discount. DiscountedCost: ${discountedCost}, totalCost: ${totalCost}`);
                } else {
                    console.log('  -> Excluded without discount. Not added.');
                }
            } else {
                totalCost += cost;
                console.log(`  -> Included. totalCost: ${totalCost}`);
            }
        }
    });

    // Sumar el costo del convoy seleccionado si existe
    let convoyCost = 0;
    let convoyId = $("#standardrecipe-convoy_id").val();
    console.log('Convoy seleccionado:', convoyId, 'convoyAmounts:', convoyAmounts);
    if (convoyId && typeof convoyAmounts[convoyId] !== 'undefined') {
        convoyCost = parseFloat(convoyAmounts[convoyId]) || 0;
        if (!isNaN(convoyCost)) {
            totalCost += convoyCost;
            console.log(`Convoy cost (${convoyId}):`, convoyCost, 'totalCost after convoy:', totalCost);
        } else {
            console.log('Convoy cost is NaN para id', convoyId, convoyAmounts[convoyId]);
        }
    } else {
        console.log('No convoy seleccionado o convoyAmounts no tiene ese id');
    }

    // Resto del cálculo (porciones, yield, formato)
    let portions = parseFloat($("#standardrecipe-portions").val()) || 1;
    let _yield = parseFloat($("#standardrecipe-yield").val()) || 1;
    // El convoyCost NO se divide entre el rendimiento/yield, solo los ingredientes
    let ingredientsCost = totalCost - convoyCost;
    let costPerPortion = 0;
    if (!isNaN(ingredientsCost)) {
        costPerPortion = portions > 0 && _yield > 0 ? (ingredientsCost / _yield / portions) : ingredientsCost;
    }
    let finalTotal = costPerPortion + convoyCost;
    console.log('Final totalCost:', totalCost, 'ingredientsCost:', ingredientsCost, 'convoyCost:', convoyCost, 'costPerPortion:', costPerPortion, 'finalTotal:', finalTotal, 'portions:', portions, 'yield:', _yield);
    if (!isNaN(finalTotal)) {
        $("#ingredients-selection-total-cost").data('total', finalTotal.toFixed(2));
        $("#standardrecipe-custom_cost").val(finalTotal.toFixed(2));
        let formattedCost = formatUserNumber(finalTotal);
        $("#ingredients-selection-total-cost").text(formattedCost);
        if ($("#cost-value").length > 0) {
            $("#cost-value").html(formatUserNumber(finalTotal));
            $("#cost-value").data('price', finalTotal);
        }
    } else {
        console.log('finalTotal es NaN, no se actualiza la UI');
    }
}
// Función para formatear números según las preferencias del usuario
function formatNumberWithUserPreferences(value) {
    // Usar el sistema global de formateo si está disponible
    if (window.BusinessNumberFormatter) {
        return window.BusinessNumberFormatter.formatPrice(value);
    }
    
    // Fallback a configuración local
    const formatted = formatUserNumber(value);
    if (typeof userFormatConfig !== 'undefined' && userFormatConfig.currencySymbol) {
        return userFormatConfig.currencySymbol + formatted; // Sin espacio para mantener consistencia
    }
    return formatted;
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

// Desactivado: confirmación y borrado antiguo para .delete-ingredient, ahora se usa modal personalizado en el PHP

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
    let isRecipe = $(this).data('is-recipe');
    let currentItemName = $(this).data('name');
    let currentItemId = $(this).data('id');

    // Limpiar el select
    $("#ingredient-select").empty();
    $("#ingredient-select").html('<option>Cargando...</option>');

    // Cargar ambos catálogos en paralelo
    $.when(
        $.get('/standard-recipe/get-available-ingredients'),
        $.get('/standard-recipe/get-sub-standard-recipes')
    ).done(function(ingredientsRes, subrecipesRes) {
        // ingredientsRes[0] y subrecipesRes[0] contienen los datos
        let ingredients = ingredientsRes[0].ingredients || [];
        let subrecipes = subrecipesRes[0] || [];
        let selectOptions = '';

        // Ordenar ingredientes alfabéticamente por nombre
        ingredients.sort(function(a, b) {
            return a.name.localeCompare(b.name, undefined, {sensitivity: 'base'});
        });

        // Ordenar subrecetas alfabéticamente por título
        subrecipes.sort(function(a, b) {
            return a.title.localeCompare(b.title, undefined, {sensitivity: 'base'});
        });

        // Ingredientes (mayúsculas)
        if (ingredients.length > 0) {
            selectOptions += '<optgroup label="INGREDIENTES">';
            ingredients.forEach(function(ingredient) {
                let label = (ingredient.name + ' (' + ingredient.um + ')').toUpperCase();
                let selected = (isRecipe !== 1 && ingredient.id == currentItemId) ? 'selected' : '';
                selectOptions += `<option value="${ingredient.id}" data-type="ingredient" ${selected}>${label}</option>`;
            });
            selectOptions += '</optgroup>';
        }

        // Subrecetas (minúsculas)
        if (subrecipes.length > 0) {
            selectOptions += '<optgroup label="subrecetas">';
            subrecipes.forEach(function(subrecipe) {
                let label = (subrecipe.title + ' (' + subrecipe.um + ')').toLowerCase();
                let selected = (isRecipe === 1 && subrecipe.id == currentItemId) ? 'selected' : '';
                selectOptions += `<option value="${subrecipe.id}" data-type="subrecipe" ${selected}>${label}</option>`;
            });
            selectOptions += '</optgroup>';
        }

        $("#ingredient-select").html(selectOptions);
    });

    // Almacenar la URL y el tipo en el botón de actualización
    $("#btn-update-ingredient").data('url', url);
    $("#btn-update-ingredient").data('is-recipe', isRecipe);

    // Establecer la cantidad en el campo correspondiente
    $("#ingredient-update-quantity").val(quantity);

    // Actualizar el título del modal según el tipo
    let modalTitle = isRecipe ? 'Modificar subreceta' : 'Modificar ingrediente';
    $("#modal-update-ingredient .modal-title").text(modalTitle);

    // Mostrar el modal
    $("#modal-update-ingredient").modal('show');

    return false;
});


$(document).on('click', '#btn-update-ingredient', function (event) {
    event.preventDefault();
    let url = $(this).data('url');
    let quantity = $("#ingredient-update-quantity").val();
    let selectedItem = $("#ingredient-select").val();
    // Detectar el tipo del nuevo elemento seleccionado
    let selectedOption = $("#ingredient-select option:selected");
    let isRecipe = selectedOption.data('type') === 'subrecipe' ? 1 : 0;
    console.log(selectedItem, quantity, isRecipe);

    // Validar que la cantidad sea un número válido
    if (!quantity || isNaN(parseFloat(quantity))) {
        alert('Por favor, ingresa una cantidad válida');
        return false;
    }

    // Mostrar indicador de carga
    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');

    $.ajax({
        url: url,
        type: 'post',
        data: {
            quantity: quantity,
            newItemId: selectedItem,
            isRecipe: isRecipe
        }
    }).done((response) => {
        $.pjax.reload({container: "#pjax-ingredients-selection"});
        $("#standardrecipe-price").trigger('change');
        $("#modal-update-ingredient").modal('hide');
    }).fail((error) => {
        console.error('Error al actualizar:', error);
        alert('Ha ocurrido un error al actualizar. Por favor, inténtalo de nuevo.');
    }).always(() => {
        // Restaurar el botón
        $(this).prop('disabled', false).html('Guardar');
    });

    return false;
});

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
    if(event.key !== ';'){
        return;
    }
    let input = $(this);
    let value = input.val();
    if(value.indexOf(";") === value.length - 1){
        value = value.slice(0, -1).trim();
        input.val('');
        if (value === '') {
            return;
        }
        // Validar duplicado: ignorar mayúsculas/minúsculas y espacios
        const normalizedNew = value.toLowerCase();
        const existingLabels = Array.from(document.querySelectorAll('#allergies-list label'));
        const duplicate = existingLabels.find(label => label.textContent.trim().toLowerCase() === normalizedNew);
        if (duplicate) {
            $('#allergen-duplicate-msg').show();
            setTimeout(() => $('#allergen-duplicate-msg').hide(), 3000);
            return;
        }
        $('#allergen-duplicate-msg').hide();
        addAllergiesOption(value);
        selectUnselectAllergies(value);
    }
})
function selectUnselectAllergies(allergen) {
    let selectedAllergies = $("#standardrecipe-allergies").val();
    if (selectedAllergies) {
        selectedAllergies = selectedAllergies.split(';');
    } else {
        selectedAllergies = [];
    }

    const normalizedAllergen = allergen.trim().toLowerCase();
    const existingIndex = selectedAllergies.findIndex(a => a.trim().toLowerCase() === normalizedAllergen);

    if (existingIndex === -1) {
        selectedAllergies.push(allergen.trim());
    } else {
        selectedAllergies.splice(existingIndex, 1);
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

// document.addEventListener('DOMContentLoaded', function() {
//     const yieldUmField = document.getElementById('standardrecipe-yield_um');
//     const portionsContainer = document.getElementById('portions-container');
//     const portionsField = document.getElementById('standardrecipe-portions');
//     const yieldField = document.getElementById('standardrecipe-yield');
    
//     // Lista de unidades discretas que requieren números enteros ≥ 1
//     const discreteUnits = ['porción', 'porcion', 'porciones', 'rebanada', 'rebanadas', 'pieza', 'piezas', 'trozo', 'trozos', 'unidad', 'unidades'];
    
//     // Función para verificar si la unidad seleccionada es discreta
//     function isDiscreteUnit(unit) {
//         return discreteUnits.some(discrete => unit.toLowerCase().includes(discrete.toLowerCase()));
//     }
    
//     // Función para validar el valor en caso de unidades discretas
//     function validateYieldValue() {
//         if (isDiscreteUnit(yieldUmField.value)) {
//             const value = parseFloat(yieldField.value);
            
//             // Buscar o crear el contenedor de mensajes de error
//             let errorContainer = document.getElementById('yield-error-container');
//             if (!errorContainer) {
//                 errorContainer = document.createElement('div');
//                 errorContainer.id = 'yield-error-container';
//                 errorContainer.className = 'invalid-feedback';
//                 yieldField.parentNode.appendChild(errorContainer);
//             }
            
//             // Verificar si el valor es un número entero y mayor o igual a 1
//             if (isNaN(value) || value % 1 !== 0 || value < 1) {
//                 // Aplicar estilo de error
//                 yieldField.classList.add('is-invalid');
//                 errorContainer.textContent = `Por favor asegúrese que el número es correcto para la unidad de medida ${yieldUmField.value}`;
//                 errorContainer.style.display = 'block';
                
//                 // Opcional: aplicar un fondo rosa pastel para destacar el error
//                 yieldField.style.backgroundColor = '#ffebee';
//             } else {
//                 // Quitar estilo de error
//                 yieldField.classList.remove('is-invalid');
//                 errorContainer.style.display = 'none';
//                 yieldField.style.backgroundColor = '';
//             }
//         } else {
//             // Para unidades no discretas, eliminar cualquier mensaje de error
//             yieldField.classList.remove('is-invalid');
//             const errorContainer = document.getElementById('yield-error-container');
//             if (errorContainer) {
//                 errorContainer.style.display = 'none';
//             }
//             yieldField.style.backgroundColor = '';
//         }
//     }
    
//     function checkYieldUnit() {
//         if (isDiscreteUnit(yieldUmField.value)) {
//             portionsContainer.style.display = 'none';
//             portionsField.value = '1';
//         } else {
//             portionsContainer.style.display = '';
//         }
        
//         // Validar el valor de yield según la unidad seleccionada
//         validateYieldValue();
        
//         // Llamar al método computeCost cuando cambie la unidad de medida
//         computeCost();
//     }
    
//     // Verificar inicial
//     checkYieldUnit();
    
//     // Verificar en cambios de unidad
//     yieldUmField.addEventListener('change', checkYieldUnit);
    
//     // Verificar también cuando cambie el valor de yield
//     yieldField.addEventListener('input', validateYieldValue);
    
//     // Validar antes de enviar el formulario
//     document.getElementById('form-recipe').addEventListener('submit', function(event) {
//         validateYieldValue();
        
//         // Opcional: evitar envío si hay error
//         // Si quieres bloquear el envío del formulario cuando hay errores, descomenta estas líneas:
//         /*
//         if (yieldField.classList.contains('is-invalid')) {
//             event.preventDefault();
//             // Desplazarse al campo con error para que el usuario lo vea
//             yieldField.scrollIntoView({ behavior: 'smooth', block: 'center' });
//         }
//         */
//     });
// });