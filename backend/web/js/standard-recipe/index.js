$(document).on('click', '#btn-duplicate-recipes', function(event){
    event.preventDefault();
    var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
    if(selectedRecipes.length > 0){
        var url = $(this).attr('href');
        $.ajax({
            url: url,
            type: 'POST',
            data: {recipes: selectedRecipes},
        });
    }
});
$(document).on('click', '#btn-download-recipes', function(event) {
    event.preventDefault(); // Evitar que el enlace se comporte como un enlace normal

    // Obtener las recetas seleccionadas en la cuadrícula
    var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
    console.log('Recetas seleccionadas:', selectedRecipes); // Depuración: Verificar los IDs seleccionados

    // Verificar si hay recetas seleccionadassad
    if (selectedRecipes.length > 0) {
        var baseUrl = $(this).attr('href');
        console.log(baseUrl);
        
        // Construir la URL con los IDs de las recetas seleccionadas
        var url = baseUrl + '&id=' + selectedRecipes.join(',');
        console.log('URL generada:', url); // Depuración: Verificar la URL generada


        // Redireccionar a la URL para iniciar la descarga
        window.location.href = url;
    } else {
        // Mostrar un mensaje de error si no hay recetas seleccionadas
        window.location.href = $(this).attr('href');
    }
});
 $(document).ready(function() {
//     // Hide the button initially
//     $('#btn-download-recipes-complete').hide();

//     // Show/hide the button based on checkbox selection
//     $('#standard-recipes-grid').on('change', 'input[type="checkbox"]', function() {
//         var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
//         if (selectedRecipes.length > 0) {
//             $('#btn-download-recipes-complete').show();
//         } else {
//             $('#btn-download-recipes-complete').hide();
//         }
//     });

    // Handle the download button click
    $(document).on('click', '#btn-download-recipes-complete', function(event) {
        event.preventDefault(); // Prevent the default link behavior
        var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');

        // Check if any recipes are selected
        if (selectedRecipes.length > 0) {
            // If recipes are selected, proceed with the download
            var baseUrl = $(this).attr('href');
            var url = baseUrl + '?id=' + selectedRecipes.join(',');
            window.location.href = url;
        }
    });
});
$(document).ready(function() {
    // Hide the button initially
    $('#btn-download-recipes-complete-excel').hide();

    // Show/hide the button based on checkbox selection
    $('#standard-recipes-grid').on('change', 'input[type="checkbox"]', function() {
        var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
        if (selectedRecipes.length > 0) {
            $('#btn-download-recipes-complete-excel').show();
        } else {
            $('#btn-download-recipes-complete-excel').hide();
        }
    });

    // Handle the download button click
    $(document).on('click', '#btn-download-recipes-complete-excel', function(event) {
        event.preventDefault(); // Prevent the default link behavior
        var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');

        // Check if any recipes are selected
        if (selectedRecipes.length > 0) {
            // If recipes are selected, proceed with the download
            var baseUrl = $(this).attr('href');
            var url = baseUrl + '?id=' + selectedRecipes.join(',');
            window.location.href = url;
        } else {
            // If no recipes are selected, show an alert
            alert('No se ha seleccionado ninguna receta');
        }
    });
});
// Manejar el clic en el botón de eliminar recetas seleccionadas
$(document).on('click', '#btn-delete-recipes', function(event) {
    event.preventDefault();
    var keys = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
    console.log('Recetas seleccionadas:', keys.length);
    
    // Si no hay elementos seleccionados, mostrar modal de error
    if (keys.length === 0) {
        $('#modal-no-selection').modal('show');
        return false;
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

// Capturar el clic en "Eliminar las seleccionadas" (modal para todos)
$(document).on('click', '#delete-current-page', function() {
    var keys = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
    $('#modal-bulk-remove').modal('hide'); // Ocultar el modal
    
    $.ajax({
        url: '/standard-recipe/delete',
        type: 'POST',
        data: { keys: keys }, // Enviar solo los IDs de la página actual
        success: function(data) {
            $.pjax.reload({ container: '#standard-recipes-pjax' });
        },
        error: function() {
            alert('Hubo un error al eliminar las recetas.');
        }
    });
});

// Capturar el clic en "Eliminar todas" (modal para todos)
$(document).on('click', '#delete-all', function() {
    var keys = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
    $('#modal-bulk-remove').modal('hide'); // Ocultar el modal
    
    $.ajax({
        url: '/standard-recipe/delete',
        type: 'POST',
        data: { keys: 'all' }, // Enviar todos los IDs seleccionados
        success: function(data) {
            $.pjax.reload({ container: '#standard-recipes-pjax' });
        },
        error: function() {
            alert('Hubo un error al eliminar las recetas.');
        }
    });
});

// Capturar el clic en "Eliminar" (modal para selección parcial)
$(document).on('click', '#confirm-delete-selected', function() {
    var keys = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
    $('#modal-confirm-selected-remove').modal('hide'); // Ocultar el modal
    
    $.ajax({
        url: '/standard-recipe/delete',
        type: 'POST',
        data: { keys: keys }, // Enviar los IDs seleccionados
        success: function(data) {
            $.pjax.reload({ container: '#standard-recipes-pjax' });
        },
        error: function() {
            alert('Hubo un error al eliminar las recetas seleccionadas.');
        }
    });
});
$(document).ready(function () {
    // Código para cargar datos en el modal de paso normal
    $(document).on('click', '.edit-step', function () {
        var stepId = $(this).data('id');
        var activity = $(this).data('activity');
        var time = $(this).data('time');
        var indicator = $(this).data('indicator');

        $('#edit-step-id').val(stepId);
        $('#edit-step-activity').val(activity);
        $('#edit-step-time').val(time);
        $('#edit-step-indicator').val(indicator);
    });
    // Código para guardar los cambios de paso normal
    $('#save-edit-step').on('click', function () {
        var stepId = $('#edit-step-id').val();
        var activity = $('#edit-step-activity').val();
        var time = $('#edit-step-time').val();
        var indicator = $('#edit-step-indicator').val();
        var _image = $('#edit-step-image')[0].files[0];
        var removeImage = $('#edit-step-remove-image').val();
        var formData = new FormData();
        formData.append('id', stepId);
        formData.append('activity', activity);
        formData.append('time', time);
        formData.append('indicator', indicator);
        formData.append('_image', _image);
        formData.append('remove_image', removeImage);
        // Depuración
        console.log('remove_image:', removeImage);
        $.ajax({
            url: '/standard-recipe/edit-step',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                // Mover el foco fuera del modal antes de ocultarlo para evitar advertencia aria-hidden
                $('body').focus();
                $('#modal-edit-step').modal('hide');
                console.log('Cambios guardados exitosamente:', response);
                $.pjax.reload({container: '#pjax-list-steps'});
            },
            error: function (xhr, status, error) {
                console.error('Error al guardar los cambios:', error);
                alert('Error al guardar los cambios.');
            }
        });
    });

    // Código para guardar los cambios de paso especial
    $('#save-edit-special-step').on('click', function () {
        var stepId = $('#edit-special-step-id').val();
        var activity = $('#edit-special-step-activity').val();
        var time = $('#edit-special-step-time').val();
        var indicator = $('#edit-special-step-indicator').val();
        var fileInput = $('#edit-special-step-image')[0];
        var _image = (fileInput && fileInput.files.length > 0) ? fileInput.files[0] : null;
        var removeImage = $('#edit-special-step-remove-image').val();
        var formData = new FormData();
        formData.append('id', stepId);
        formData.append('activity', activity);
        formData.append('time', time);
        formData.append('indicator', indicator);
        if (_image) formData.append('_image', _image);
        formData.append('remove_image', removeImage);
        formData.append('type', 'special');
        // Depuración
        console.log('remove_image (special):', removeImage);
        $.ajax({
            url: '/standard-recipe/edit-step',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                // Mover el foco fuera del modal antes de ocultarlo para evitar advertencia aria-hidden
                $('body').focus();
                $('#modal-edit-special-step').modal('hide');
                console.log('Cambios guardados exitosamente (special):', response);
                $.pjax.reload({container: '#pjax-list-special-steps'});
                location.reload(); // Recargar la página para reflejar los cambios
            },
            error: function (xhr, status, error) {
                console.error('Error al guardar los cambios (special):', error);
                alert('Error al guardar los cambios.');
            }
        });
    });
});