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
        var timeNa = $(this).data('time-na') === '1' || $(this).data('time-na') === 1;
        var indicator = $(this).data('indicator');

        $('#edit-step-id').val(stepId);
        $('#edit-step-activity').val(activity);
        var timeNaCb = $('#edit-step-time-na');
        var timeInput = $('#edit-step-time');
        if (timeNaCb.length) {
            timeNaCb.prop('checked', timeNa);
            timeInput.prop('disabled', timeNa);
            timeInput.css('opacity', timeNa ? '0.4' : '1');
            timeInput.val(timeNa ? '' : time);
        } else {
            timeInput.val(time);
        }
        $('#edit-step-indicator').val(indicator);
    });
    function getRecipeId() {
        return new URLSearchParams(window.location.search).get('id');
    }
    // Código para guardar los cambios de paso normal
$(document).on('click', '#save-edit-step', function () {
        var stepId = $('#edit-step-id').val();
        var activity = $('#edit-step-activity').val();
        var timeNa = $('#edit-step-time-na').is(':checked');
        var time = timeNa ? '' : $('#edit-step-time').val();
        var indicator = $('#edit-step-indicator').val();
        var _image = $('#edit-step-image')[0].files[0];
        var removeImage = $('#edit-step-remove-image').val();
        var formData = new FormData();
        formData.append('id', stepId);
        formData.append('activity', activity);
        formData.append('time', time);
        formData.append('time_na', timeNa ? '1' : '0');
        formData.append('indicator', indicator);
        formData.append('_image', _image);
        formData.append('remove_image', removeImage);
        $.ajax({
            url: '/standard-recipe/edit-step',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                var rid = getRecipeId();
                $('#modal-edit-step').one('hidden.bs.modal', function () {
                    $.getJSON('/standard-recipe/render-steps?id=' + rid, function(data) {
                        window.replaceStepsHtml('#pjax-list-steps', data.html);
                    });
                });
                $('#modal-edit-step').modal('hide');
            },
            error: function (xhr, status, error) {
                alert('Error al guardar los cambios.');
            }
        });
    });

    // Código para guardar los cambios de paso especial
    $(document).on('click', '#save-edit-special-step', function () {
        var $btn     = $(this);
        var $spinner = $('#save-edit-special-step-spinner');
        var $text    = $('#save-edit-special-step-text');
        $btn.prop('disabled', true);
        if ($spinner.length) $spinner.show();
        if ($text.length) $text.text('Guardando...');

        var stepId = $('#edit-special-step-id').val();
        var activity = $('#edit-special-step-activity').val();
        var timeNa = $('#edit-special-step-time-na').is(':checked');
        var time = timeNa ? '' : $('#edit-special-step-time').val();
        var indicator = $('#edit-special-step-indicator').val();
        var fileInput = $('#edit-special-step-image')[0];
        var _image = (fileInput && fileInput.files.length > 0) ? fileInput.files[0] : null;
        var removeImage = $('#edit-special-step-remove-image').val();
        var formData = new FormData();
        formData.append('id', stepId);
        formData.append('activity', activity);
        formData.append('time', time);
        formData.append('time_na', timeNa ? '1' : '0');
        formData.append('indicator', indicator);
        if (_image) formData.append('_image', _image);
        formData.append('remove_image', removeImage);
        $.ajax({
            url: '/standard-recipe/edit-step',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                var rid = getRecipeId();
                $('#modal-edit-special-step').one('hidden.bs.modal', function () {
                    $.getJSON('/standard-recipe/render-special-steps?id=' + rid, function(data) {
                        window.replaceStepsHtml('#pjax-list-special-steps', data.html);
                    });
                });
                $('#modal-edit-special-step').modal('hide');
            },
            error: function (xhr, status, error) {
                alert('Error al guardar los cambios.');
            },
            complete: function() {
                $btn.prop('disabled', false);
                if ($spinner.length) $spinner.hide();
                if ($text.length) $text.text('Guardar');
            }
        });
    });
});