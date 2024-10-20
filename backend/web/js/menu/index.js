$(document).on('click', '#bulk-remove', function(event) {
    event.preventDefault();
    var keys = $('#menu-grid').yiiGridView('getSelectedRows');


    var data = [];

    for (const key in keys) {
        let checkbox = $(`input[value='${key}']`);
        let rowData = {
            id: checkbox.data('model-id'),
            type: checkbox.data('type'),
        }

        data.push(rowData);
    }


    if(confirm(`Vas a quitar ${keys.length} recetas/combos del menú. ¿Estás seguro?`)) {
        $.ajax({
            url: urlBulkRemove,
            type: 'POST',
            data: {data},
        });
    }


    return false;
});
