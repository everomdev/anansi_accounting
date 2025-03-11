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