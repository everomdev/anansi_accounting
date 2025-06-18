$(document).ready(function() {
    let recipeCounter = 0;
    
    // Datos de las recetas disponibles
    const availableRecipes = {};
    $('#recipe-selector option').each(function() {
        if ($(this).val()) {
            availableRecipes[$(this).val()] = $(this).text();
        }
    });
    
    // Función para actualizar el mensaje de "no recipes"
    function updateNoRecipesMessage() {
        const hasRecipes = $('#selected-recipes-body tr').length > 0;
        $('#no-recipes-message').toggle(!hasRecipes);
    }
    
    // Función para agregar una receta a la tabla
    function addRecipeToTable(recipeId, recipeName) {
        recipeCounter++;
        
        const row = $(`
            <tr data-recipe-id="${recipeId}" data-counter="${recipeCounter}">
                <td>${recipeName}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-recipe-btn">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </td>
            </tr>
        `);
        
        // Agregar input hidden para el formulario
        const hiddenInput = $(`<input type="hidden" name="Menu[_recipes][]" value="${recipeId}" class="recipe-input" data-counter="${recipeCounter}">`);
        
        $('#selected-recipes-body').append(row);
        $('#form-menu').append(hiddenInput);
        
        updateNoRecipesMessage();
        
        // Resetear el selector
        $('#recipe-selector').val('');
    }
    
    // Evento para agregar receta
    $('#add-recipe-btn').on('click', function() {
        const selectedRecipeId = $('#recipe-selector').val();
        
        if (!selectedRecipeId) {
            alert('Por favor selecciona una receta primero.');
            return;
        }
        
        const recipeName = availableRecipes[selectedRecipeId];
        addRecipeToTable(selectedRecipeId, recipeName);
    });
    
    // Evento para eliminar receta
    $(document).on('click', '.remove-recipe-btn', function() {
        const row = $(this).closest('tr');
        const counter = row.data('counter');
        
        // Eliminar la fila de la tabla
        row.remove();
        
        // Eliminar el input hidden correspondiente
        $(`.recipe-input[data-counter="${counter}"]`).remove();
        
        updateNoRecipesMessage();
    });
    
    // Permitir agregar con Enter en el select
    $('#recipe-selector').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#add-recipe-btn').click();
        }
    });
    
    // Permitir doble click en el select para agregar
    $('#recipe-selector').on('dblclick', function() {
        $('#add-recipe-btn').click();
    });
    
    // Inicializar el mensaje
    updateNoRecipesMessage();
    
    // Si hay recetas existentes, actualizar el contador
    $('#selected-recipes-body tr').each(function(index) {
        $(this).attr('data-counter', index + 1);
        recipeCounter = Math.max(recipeCounter, index + 1);
    });
    
    // Actualizar los inputs hidden existentes con data-counter
    $('.recipe-input').each(function(index) {
        $(this).attr('data-counter', index + 1);
    });
});
