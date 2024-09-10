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
    let totalCost = $("#ingredients-selection-total-cost").data('value');
    let portions = $("#standardrecipe-portions").val();
    let yield = $("#standardrecipe-yield").val();
    if (parseFloat(totalCost) && parseFloat(portions) && portions > 0 && parseFloat(yield) && yield > 0) {
        let costPerPortion = totalCost / portions / yield;

        $("#ingredients-selection-total-cost").data('value', costPerPortion.toFixed(2));
        $("#standardrecipe-custom_cost").val(costPerPortion.toFixed(2));
        var cost = Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency
        }).format(costPerPortion.toFixed(2));
        $("#ingredients-selection-total-cost").html(cost);
        if ($("#cost-value").length > 0) {
            $("#cost-value").html(cost);
        }
    }
}

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
    let cost = $("#cost-value").data('value');

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
})
