$(document).on('click', '#bulk-remove', function(event) {
    event.preventDefault();
    var keys = $('#ingredient-stock-grid').yiiGridView('getSelectedRows');

    if(confirm(`Vas a eliminar ${keys.length} insumos. ¿Estás seguro?`)) {
        $.ajax({
            url: '/ingredient-stock/bulk-remove',
            type: 'POST',
            data: {keys: keys},
            success: function(data) {
                $.pjax.reload({container: '#ingredient-stock-pjax'});
            }
        });
    }


    return false;
});
