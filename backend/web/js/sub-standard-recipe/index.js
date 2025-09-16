$(document).on('click', '#btn-duplicate-recipes', function(event){
    event.preventDefault();
    var selectedRecipes = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    if(selectedRecipes.length > 0){
        var url = $(this).attr('href');
        $.ajax({
            url: url,
            type: 'POST',
            data: {recipes: selectedRecipes},
        });
    }
})
// Variable para rastrear si hay una petición activa
let isDeleting = false;

// Manejar clic en el botón principal
$(document).on('click', '#btn-delete-recipes', function(event) {
    event.preventDefault();
    if (isDeleting) return false; // Evitar múltiples clics
    
    var $mainButton = $(this);
    isDeleting = true;
    $mainButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    
    var keys = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    
    if (keys.length === 0) {
        $('#modal-no-selection').modal('show');
        resetMainButton($mainButton);
        return false;
    }
    
    $('#selected-count-message').text(keys.length);
    
    if (keys.length === $('.grid-view tbody tr').length) {
        $('#modal-bulk-remove').modal('show');
    } else {
        $('#modal-confirm-selected-remove').modal('show');
    }
    
    return false;
});

// Función para resetear el botón principal
function resetMainButton($button) {
    isDeleting = false;
    $button.prop('disabled', false).html('Eliminar seleccionados');
}

// Función para manejar todas las peticiones de eliminación
function handleDeleteRequest(url, data, $triggerButton) {
    $triggerButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        complete: function() {
            // Solo resetea el botón principal cuando termine ESTA petición
            resetMainButton($('#btn-delete-recipes'));
            $triggerButton.prop('disabled', false).html(function() {
                return $triggerButton.is('#delete-current-page') ? 'Eliminar las seleccionadas' : 
                       $triggerButton.is('#delete-all') ? 'Eliminar todas' : 'Eliminar';
            });
        },
        success: function() {
            $.pjax.reload({ container: '#sub-standard-recipes-pjax' });
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Error al procesar la solicitud');
        }
    });
}

// Manejadores para los diferentes modales
$(document).on('click', '#delete-current-page, #delete-all, #confirm-delete-selected', function() {
    var $button = $(this);
    var modalId = $button.closest('.modal').attr('id');
    $('#' + modalId).modal('hide');
    
    var requestData = {};
    
    if ($button.is('#delete-all')) {
        requestData = { keys: 'all' };
    } else {
        requestData = { keys: $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows') };
    }
    
    handleDeleteRequest('/sub-standard-recipe/delete-sub-recipe', requestData, $button);
});

// Exportar subrecetas completas (Excel)
$(document).on('click', '#download-recipes-complete-excel', function(e) {
    e.preventDefault();
    var selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length === 0) {
        $('#modal-no-export-selection').modal('show');
        return false;
    }
    $('#modal-export-recipes').modal('show');
});

// Exportar solo las seleccionadas
$(document).on('click', '#export-current-page', function() {
    var selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length > 0) {
        window.location.href = '/standard-recipe/export-recipes-to-excel?id=' + selectedIds.join(',') + '&type=sub';
    }
});

// Exportar todas (de todas las páginas)
$(document).on('click', '#export-all', function() {
    var selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length > 0) {
        window.location.href = '/standard-recipe/export-recipes-to-excel?id=' + selectedIds.join(',') + '&all=true&type=sub';
    }
});