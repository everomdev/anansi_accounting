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
    
    // Determinar el tipo de eliminación
    var deleteType = (keys.length === $('.grid-view tbody tr').length) ? 'bulk' : 'selected';
    
    // Verificar vínculos antes de mostrar el modal de eliminación
    checkLinksBeforeDelete(keys, deleteType);
    
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

// Función para verificar vínculos antes de eliminar
function checkLinksBeforeDelete(keys, deleteType) {
    $.ajax({
        url: '/sub-standard-recipe/check-links',
        type: 'POST',
        data: { keys: keys },
        success: function(response) {
            if (response.hasLinks) {
                // Mostrar modal de advertencia con información de vínculos
                var warningHtml = '<p>Esta' + (keys.length > 1 ? 's subrecetas están' : ' subreceta está') + ' vinculada' + (keys.length > 1 ? 's' : '') + ' a:</p>';
                warningHtml += '<ul>';
                if (response.recipeCount > 0) {
                    warningHtml += '<li><strong>' + response.recipeCount + '</strong> receta' + (response.recipeCount > 1 ? 's' : '') + '</li>';
                }
                if (response.subRecipeCount > 0) {
                    warningHtml += '<li><strong>' + response.subRecipeCount + '</strong> subreceta' + (response.subRecipeCount > 1 ? 's' : '') + '</li>';
                }
                warningHtml += '</ul>';
                warningHtml += '<p class="text-danger"><strong>Eliminarla' + (keys.length > 1 ? 's' : '') + ' modificará el costeo de esos elementos.</strong></p>';
                warningHtml += '<p>¿Desea continuar?</p>';
                
                $('#links-warning-content').html(warningHtml);
                
                // Guardar datos para usar después de la confirmación
                $('#confirm-delete-with-links').data('deleteType', deleteType);
                $('#confirm-delete-with-links').data('keys', keys);
                
                $('#modal-links-warning').modal('show');
            } else {
                // No hay vínculos, proceder directamente con la eliminación
                if (deleteType === 'bulk') {
                    $('#modal-bulk-remove').modal('show');
                } else {
                    $('#modal-confirm-selected-remove').modal('show');
                }
            }
        },
        error: function(xhr) {
            alert('Error al verificar vínculos: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            resetMainButton($('#btn-delete-recipes'));
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

// Manejar confirmación después de ver advertencia de vínculos
$(document).on('click', '#confirm-delete-with-links', function() {
    var $button = $(this);
    var deleteType = $button.data('deleteType');
    var keys = $button.data('keys');
    
    $('#modal-links-warning').modal('hide');
    
    // Mostrar el modal apropiado según el tipo de eliminación
    if (deleteType === 'bulk') {
        $('#modal-bulk-remove').modal('show');
    } else {
        $('#modal-confirm-selected-remove').modal('show');
    }
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