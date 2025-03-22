$(document).on('click', '#bulk-remove', function(event) {
    event.preventDefault();
    var keys = $('#ingredient-stock-grid').yiiGridView('getSelectedRows');

    if (keys.length === 0) {
        alert('No has seleccionado ningún insumo.');
        return false;
    }

    // Mostrar el modal
    $('#modal-bulk-remove').modal('show');

    // Capturar el clic en "Eliminar solo los de la página actual"
    $(document).on('click', '#delete-current-page', function() {
        $('#modal-bulk-remove').modal('hide'); // Ocultar el modal
        
            $.ajax({
                url: '/ingredient-stock/bulk-remove',
                type: 'POST',
                data: { keys: keys }, // Enviar solo los IDs de la página actual
                success: function(data) {
                    $.pjax.reload({ container: '#ingredient-stock-pjax' });
                },
                error: function() {
                    alert('Hubo un error al eliminar los insumos.');
                }
            });
        
    });

    // Capturar el clic en "Eliminar todos los seleccionados"
    $(document).on('click', '#delete-all', function() {
        $('#modal-bulk-remove').modal('hide'); // Ocultar el modal
        
            $.ajax({
                url: '/ingredient-stock/bulk-remove',
                type: 'POST',
                data: { keys: 'all' }, // Enviar 'all' para eliminar todos
                success: function(data) {
                    $.pjax.reload({ container: '#ingredient-stock-pjax' });
                },
                error: function() {
                    alert('Hubo un error al eliminar los insumos.');
                }
            });
        
    });

    return false;
});