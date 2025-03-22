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
    // Hide the button initially
    $('#btn-download-recipes-complete').hide();

    // Show/hide the button based on checkbox selection
    $('#standard-recipes-grid').on('change', 'input[type="checkbox"]', function() {
        var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
        if (selectedRecipes.length > 0) {
            $('#btn-download-recipes-complete').show();
        } else {
            $('#btn-download-recipes-complete').hide();
        }
    });

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
        } else {
            // If no recipes are selected, show an alert
            alert('No se ha seleccionado ninguna receta');
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
$(document).ready(function() {
    // Hide the button initially
    $('#btn-delete-recipes').hide();

    // Show/hide the button based on checkbox selection
    $('#standard-recipes-grid').on('change', 'input[type="checkbox"]', function() {
        var selectedRecipes = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
        if (selectedRecipes.length > 0) {
            $('#btn-delete-recipes').show();
        } else {
            $('#btn-delete-recipes').hide();
        }
    });

    $(document).on('click', '#btn-delete-recipes', function(event) {
        event.preventDefault();
        var keys = $('#standard-recipes-grid').yiiGridView('getSelectedRows');
    
        if(confirm(`Vas a eliminar ${keys.length} recetas. ¿Estás seguro?`)) {
            $.ajax({
                url: '/standard-recipe/delete',
                type: 'POST',
                data: {id: keys}, // Changed 'keys' to 'id' to match the required parameter
                success: function(data) {
                    $.pjax.reload({container: '#standard-recipe-pjax'});
                },
            });
        }
    
    
        return false;
    });
    
});
$(document).ready(function () {
    // Código para cargar datos en el modal
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
    // Código para guardar los cambios
    $('#save-edit-step').on('click', function () {
        var stepId = $('#edit-step-id').val();
        var activity = $('#edit-step-activity').val();
        var time = $('#edit-step-time').val();
        var indicator = $('#edit-step-indicator').val();
    
        // Crear un objeto FormData
        var formData = new FormData();

        // Agregar los valores al FormData
        formData.append('id', stepId);
        formData.append('activity', activity);
        formData.append('time', time);
        formData.append('indicator', indicator);

        $.ajax({
            url: '/standard-recipe/edit-step',
            type: 'POST',
            data: formData,
            processData: false, // Evitar que jQuery procese los datos
            contentType: false, // Evitar que jQuery establezca el contentType
            success: function (response) {
                $('#modal-edit-step').modal('hide');
                $.pjax.reload({container: '#pjax-list-steps'});
            },
            error: function (xhr, status, error) {
                console.error('Error al guardar los cambios:', error);
                alert('Error al guardar los cambios.');
            }
        });
    });
});