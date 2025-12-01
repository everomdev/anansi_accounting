$(document).ready(function() {
    // Abrir modal con detalles del movimiento
    $(document).on('click', '.expense-movement-details', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        
        $.get(url, function(data) {
            $('#container-modal-details-expense-movement').html(data);
            $('#modal-details-expense-movement').modal('show');
        }).fail(function() {
            alert('Error al cargar los detalles del movimiento.');
        });
    });
    
    // Mejorar la experiencia de filtros
    $(document).on('keypress', '[data-trigger-change]', function(e) {
        if (e.which === 13) { // Enter key
            var form = $(this).closest('form');
            var container = '#expense-movement-pjax';
            
            $.pjax.reload({
                container: container,
                data: form.serialize(),
                timeout: 10000
            });
        }
    });
    
    // Mantener el estado visual de filtros activos después de PJAX
    $(document).on('pjax:success', function() {
        $('[data-trigger-change]').each(function() {
            if ($(this).val().trim() !== '') {
                $(this).addClass('filter-active');
            } else {
                $(this).removeClass('filter-active');
            }
        });
    });
});
