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
        var noSelectionModal = new bootstrap.Modal(document.getElementById('modal-no-selection'));
        noSelectionModal.show();
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
        url: checkLinksUrl,
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
                
                // Usar Bootstrap 5 sintaxis
                var linksWarningModal = new bootstrap.Modal(document.getElementById('modal-links-warning'));
                linksWarningModal.show();
            } else {
                // No hay vínculos, proceder directamente con la eliminación
                if (deleteType === 'bulk') {
                    var bulkModal = new bootstrap.Modal(document.getElementById('modal-bulk-remove'));
                    bulkModal.show();
                } else {
                    var confirmModal = new bootstrap.Modal(document.getElementById('modal-confirm-selected-remove'));
                    confirmModal.show();
                }
            }
        },
        error: function(xhr) {
            console.error('Error al verificar vínculos:', xhr);
            alert('Error al verificar vínculos: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            resetMainButton($('#btn-delete-recipes'));
        }
    });
}

// Manejadores para los diferentes modales
$(document).on('click', '#delete-current-page, #delete-all, #confirm-delete-selected', function() {
    var $button = $(this);
    var modalId = $button.closest('.modal').attr('id');
    
    // Cerrar modal usando Bootstrap 5
    var modalElement = document.getElementById(modalId);
    var modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
    }
    
    var requestData = {};
    
    if ($button.is('#delete-all')) {
        requestData = { keys: 'all' };
    } else {
        requestData = { keys: $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows') };
    }
    
    handleDeleteRequest(deleteSubRecipeUrl, requestData, $button);
});

// Manejar confirmación después de ver advertencia de vínculos
$(document).on('click', '#confirm-delete-with-links', function() {
    var $button = $(this);
    var deleteType = $button.data('deleteType');
    var keys = $button.data('keys');
    
    // Cerrar modal usando Bootstrap 5
    var linksWarningElement = document.getElementById('modal-links-warning');
    var linksWarningModal = bootstrap.Modal.getInstance(linksWarningElement);
    if (linksWarningModal) {
        linksWarningModal.hide();
    }
    
    // Mostrar el modal apropiado según el tipo de eliminación
    if (deleteType === 'bulk') {
        var bulkModal = new bootstrap.Modal(document.getElementById('modal-bulk-remove'));
        bulkModal.show();
    } else {
        var confirmModal = new bootstrap.Modal(document.getElementById('modal-confirm-selected-remove'));
        confirmModal.show();
    }
});

// Exportar subrecetas completas (Excel)
$(document).on('click', '#download-recipes-complete-excel', function(e) {
    e.preventDefault();
    var selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length === 0) {
        var noExportModal = new bootstrap.Modal(document.getElementById('modal-no-export-selection'));
        noExportModal.show();
        return false;
    }
    var exportModal = new bootstrap.Modal(document.getElementById('modal-export-recipes'));
    exportModal.show();
});

// Exportar solo las seleccionadas
$(document).on('click', '#export-current-page', function() {
    var selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length > 0) {
        window.location.href = exportRecipesUrl + '?id=' + selectedIds.join(',') + '&type=sub';
    }
});

// Exportar todas (de todas las páginas)
$(document).on('click', '#export-all', function() {
    var selectedIds = $('#sub-standard-recipes-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length > 0) {
        window.location.href = exportRecipesUrl + '?id=' + selectedIds.join(',') + '&all=true&type=sub';
    }
});