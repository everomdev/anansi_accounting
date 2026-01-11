// Interceptar solo los clics en ver detalles, no en conversión
$(document).on('click', ".movement-details:not(.convert-order)", function (event) {
    event.preventDefault();
    let href = $(this).attr('href')
    $.ajax({
        url: href,
        type: 'get',
    }).done(function (response) {
        $("#container-modal-details-movement").html(response);
        $("#modal-details-movement").modal('show');
    });
    return false;
})

// Permitir navegación normal para botones de conversión
$(document).on('click', ".convert-order", function (event) {
    // No interceptar - permitir navegación normal
    return true;
})

$(document).on('show.bs.modal', '#modal-balance', function (event) {
   fetchBalances();

});

function fetchBalances(){
    const url = $("#modal-balance").data('url');

    $.ajax({
        url,
        type: 'get'
    }).done(function (response) {
        $("#balance-container").html(response);
    })
}

$(document).on('beforeSubmit', '#form-balance', function (event) {
    event.preventDefault();
    let _this = $(this);
    let url = _this.attr('action');
    let data = _this.serializeArray();

    $.ajax({
        url,
        type: 'post',
        data
    }).done(function (response) {
        if (response.success) {
            fetchBalances();
        } else {
            let errors = response.errors.join('\n');
            alert(errors);
        }
    })
    return false;
});

// Manejar el clic en el botón de eliminar movimientos seleccionados
$(document).on('click', '#btn-delete-movements', function(event) {
    console.log('Botón eliminar movimientos clickeado');
    event.preventDefault();
    var keys = $('#movements-grid').yiiGridView('getSelectedRows');
    console.log('Movimientos seleccionados:', keys.length);
    
    // Si no hay elementos seleccionados, mostrar modal de error
    if (keys.length === 0) {
        console.log('Mostrando modal de no selección');
        $('#modal-no-selection-movements').modal('show');
        return false;
    }
    
    // Si hay elementos seleccionados
    // Actualizar el mensaje con el número de elementos seleccionados
    $('#selected-count-message-movements').text(keys.length);
    
    // Determinar qué modal mostrar
    if (keys.length === $('.grid-view tbody tr').length) {
        // Si seleccionaste todos los de la página actual
        console.log('Mostrando modal bulk remove');
        $('#modal-bulk-remove-movements').modal('show');
    } else {
        // Si seleccionaste solo algunos
        console.log('Mostrando modal confirm selected');
        $('#modal-confirm-selected-remove-movements').modal('show');
    }
    
    return false;
});

// Capturar el clic en "Eliminar las seleccionadas" (modal para todos)
$(document).on('click', '#delete-current-page-movements', function() {
    var keys = $('#movements-grid').yiiGridView('getSelectedRows');
    $('#modal-bulk-remove-movements').modal('hide'); // Ocultar el modal
    
    console.log('Enviando AJAX para eliminar movimientos:', keys);
    $.ajax({
        url: '/movement/delete',
        type: 'POST',
        data: { keys: keys }, // Enviar solo los IDs de la página actual
        success: function(data) {
            console.log('Respuesta del servidor:', data);
            if (data.success) {
                console.log('Recargando PJAX...');
                $.pjax.reload({ container: '#movements-pjax' });
            } else {
                alert('Error al eliminar los movimientos: ' + (data.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', xhr, status, error);
            alert('Hubo un error al eliminar los movimientos: ' + error);
        }
    });
});

// Capturar el clic en "Eliminar todas" (modal para todos)
$(document).on('click', '#delete-all-movements', function() {
    var keys = $('#movements-grid').yiiGridView('getSelectedRows');
    $('#modal-bulk-remove-movements').modal('hide'); // Ocultar el modal
    
    console.log('Enviando AJAX para eliminar todos los movimientos');
    $.ajax({
        url: '/movement/delete',
        type: 'POST',
        data: { keys: 'all' }, // Enviar todos los IDs seleccionados
        success: function(data) {
            console.log('Respuesta del servidor:', data);
            if (data.success) {
                console.log('Recargando PJAX...');
                $.pjax.reload({ container: '#movements-pjax' });
            } else {
                alert('Error al eliminar los movimientos: ' + (data.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', xhr, status, error);
            alert('Hubo un error al eliminar los movimientos: ' + error);
        }
    });
});

// Capturar el clic en "Eliminar" (modal para selección parcial)
$(document).on('click', '#confirm-delete-selected-movements', function() {
    var keys = $('#movements-grid').yiiGridView('getSelectedRows');
    $('#modal-confirm-selected-remove-movements').modal('hide'); // Ocultar el modal
    
    console.log('Enviando AJAX para eliminar movimientos seleccionados:', keys);
    $.ajax({
        url: '/movement/delete',
        type: 'POST',
        data: { keys: keys }, // Enviar los IDs seleccionados
        success: function(data) {
            console.log('Respuesta del servidor:', data);
            console.log('Tipo de data.success:', typeof data.success);
            console.log('Valor de data.success:', data.success);
            
            if (data.success === true || data.success === "true") {
                console.log('Recargando PJAX...');
                try {
                    $.pjax.reload({ 
                        container: '#movements-pjax',
                        timeout: 5000
                    }).done(function() {
                        console.log('PJAX recargado exitosamente');
                    }).fail(function() {
                        console.log('PJAX falló, recargando página manualmente');
                        window.location.reload();
                    });
                } catch (e) {
                    console.error('Error al recargar PJAX:', e);
                    window.location.reload();
                }
            } else {
                alert('Error al eliminar los movimientos seleccionados: ' + (data.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', xhr, status, error);
            alert('Hubo un error al eliminar los movimientos seleccionados: ' + error);
        }
    });
});

// Manejar el clic en el botón de exportar movimientos
$(document).on('click', '#btn-export-movements', function(e) {
    e.preventDefault();
    
    // Obtener IDs de las filas seleccionadas
    const selectedIds = $('#movements-grid').yiiGridView('getSelectedRows');
    
    if (selectedIds.length === 0) {
        // Mostrar modal de error si no hay selección
        $('#modal-no-export-selection-movements').modal('show');
        return;
    }
    
    // Mostrar el modal de confirmación para exportación
    $('#modal-export-movements').modal('show');
});

// Manejar la exportación de los movimientos seleccionados (página actual)
$(document).on('click', '#export-current-page-movements', function() {
    const selectedIds = $('#movements-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length > 0) {
        window.location.href = '/movement/export-movements?ids=' + selectedIds.join(',');
    }
    $('#modal-export-movements').modal('hide');
});

// Manejar la exportación de todos los movimientos seleccionados (todas las páginas)
$(document).on('click', '#export-all-movements', function() {
    const selectedIds = $('#movements-grid').yiiGridView('getSelectedRows');
    if (selectedIds.length > 0) {
        window.location.href = '/movement/export-movements?ids=' + selectedIds.join(',') + '&all=true';
    }
    $('#modal-export-movements').modal('hide');
});
