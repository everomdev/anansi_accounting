$(document).ready(function() {
    let recipeCounter = 0;
    
    // Datos de las recetas disponibles con sus costos
    const availableRecipes = {};
    const recipeCosts = {};
    
    // Llenar datos de recetas desde el select y cargar costos via AJAX
    $('#recipe-selector option').each(function() {
        if ($(this).val()) {
            availableRecipes[$(this).val()] = $(this).text();
        }
    });
    
    // Cargar los costos de las recetas via AJAX
    function loadRecipeCosts() {
        $.ajax({
            url: '/menu/get-recipe-costs',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                Object.assign(recipeCosts, data);
                calculateTotals(); // Recalcular al cargar los costos
            },
            error: function() {
                console.error('Error loading recipe costs');
            }
        });
    }
    
    // Función para calcular totales
    function calculateTotals() {
        let totalCost = 0;
        
        // Sumar el costo de todas las recetas seleccionadas (incluyendo duplicados)
        $('.recipe-input').each(function() {
            const recipeId = $(this).val();
            if (recipeCosts[recipeId]) {
                totalCost += parseFloat(recipeCosts[recipeId]);
            }
        });
        
        // Actualizar el campo de costo total
        $('#total-cost-display').val(totalCost.toFixed(2));
        
        // Calcular porcentaje si hay precio total
        const totalPrice = parseFloat($('#total_price').val()) || 0;
        if (totalPrice > 0) {
            const percentage = (totalCost / totalPrice * 100).toFixed(2);
            $('#cost-percentage').val(percentage);
        } else {
            $('#cost-percentage').val('0.00');
        }
    }
    
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
        calculateTotals(); // Recalcular totales
        
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
        calculateTotals(); // Recalcular totales
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
    
    // Evento para recalcular cuando cambie el precio total
    $('#total_price').on('input', function() {
        calculateTotals();
    });
    
    // Inicializar el mensaje
    updateNoRecipesMessage();
    
    // Cargar costos de recetas
    loadRecipeCosts();
    
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
