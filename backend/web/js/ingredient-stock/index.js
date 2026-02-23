$(document).on('click', '#btn-duplicate-insumos', function(event){
    event.preventDefault();
    var selectedInsumos = $('#ingredient-stock-grid').yiiGridView('getSelectedRows');
    if(selectedInsumos.length > 0){
        var url = $(this).attr('href');
        $.ajax({
            url: url,
            type: 'POST',
            data: {insumos: selectedInsumos},
        });
    }
});
$(document).on('click', '#bulk-remove', function(event) {
    event.preventDefault();
    var keys = $('#ingredient-stock-grid').yiiGridView('getSelectedRows');
    console.log('Elementos seleccionados:', keys.length);
    
    // Si no hay elementos seleccionados, mostrar modal de error
    if (keys.length === 0) {
        $('#modal-no-selection').modal('show');
        return false;
    }
    
    // Verificar cuántas recetas y subrecetas se verán afectadas
    var totalRecipes = 0;
    var totalSubrecipes = 0;
    
    keys.forEach(function(key) {
        var $row = $('tr[data-key="' + key + '"]');
        var recipes = parseInt($row.find('.update-ingredient-link').attr('data-recipes')) || 0;
        var subrecipes = parseInt($row.find('.update-ingredient-link').attr('data-subrecipes')) || 0;
        totalRecipes += recipes;
        totalSubrecipes += subrecipes;
    });
    
    console.log('Total recetas afectadas:', totalRecipes, 'Total subrecetas afectadas:', totalSubrecipes);
    
    // Si hay recetas o subrecetas afectadas, mostrar advertencia
    if (totalRecipes > 0 || totalSubrecipes > 0) {
        var message = '⚠️ ADVERTENCIA: Los insumos seleccionados están siendo utilizados en:\n\n';
        if (totalRecipes > 0) {
            message += '• ' + totalRecipes + ' receta' + (totalRecipes > 1 ? 's' : '') + '\n';
        }
        if (totalSubrecipes > 0) {
            message += '• ' + totalSubrecipes + ' subreceta' + (totalSubrecipes > 1 ? 's' : '') + '\n';
        }
        message += '\nEliminar estos insumos afectará estas recetas y puede causar errores en el sistema.\n\n¿Desea continuar?';
        
        if (!confirm(message)) {
            return false;
        }
    }
    
    // Si hay elementos seleccionados
    // Actualizar el mensaje con el número de elementos seleccionados
    $('#selected-count-message').text(keys.length);
    
    // Determinar qué modal mostrar
    if (keys.length === $('.grid-view tbody tr').length) {
        // Si seleccionaste todos los de la página actual
        $('#modal-bulk-remove').modal('show');
    } else {
        // Si seleccionaste solo algunos
        $('#modal-confirm-selected-remove').modal('show');
    }
    
    return false;
});

// Capturar el clic en "Eliminar solo los de la página actual" (modal para todos)
$(document).on('click', '#delete-current-page', function() {
    var keys = $('#ingredient-stock-grid').yiiGridView('getSelectedRows');
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

// Capturar el clic en "Eliminar todos los seleccionados" (modal para todos)
$(document).on('click', '#delete-all', function() {
    var keys = $('#ingredient-stock-grid').yiiGridView('getSelectedRows');
    $('#modal-bulk-remove').modal('hide'); // Ocultar el modal
    
    $.ajax({
        url: '/ingredient-stock/bulk-remove',
        type: 'POST',
        data: { keys: 'all' }, // Enviar todos los IDs seleccionados
        success: function(data) {
            $.pjax.reload({ container: '#ingredient-stock-pjax' });
        },
        error: function() {
            alert('Hubo un error al eliminar los insumos.');
        }
    });
});

// Capturar el clic en "Eliminar" del modal de confirmación (para seleccionados parcialmente)
$(document).on('click', '#confirm-delete-selected', function() {
    var keys = $('#ingredient-stock-grid').yiiGridView('getSelectedRows');
    $('#modal-confirm-selected-remove').modal('hide'); // Ocultar el modal
    
    $.ajax({
        url: '/ingredient-stock/bulk-remove',
        type: 'POST',
        data: { keys: keys }, // Enviar los IDs seleccionados
        success: function(data) {
            $.pjax.reload({ container: '#ingredient-stock-pjax' });
        },
        error: function() {
            alert('Hubo un error al eliminar los insumos seleccionados.');
        }
    });
});